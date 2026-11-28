<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/** Small dependency-free SVG charts for the admin Reports page. */
class Charts
{
    public const COLORS = ['#FF6B35', '#1A1A2E', '#1FA187', '#3E7BFA', '#F2B134', '#9AA3B5'];

    private static function nice(float $max): float
    {
        if ($max <= 0) {
            return 1;
        }
        $pow = 10 ** floor(log10($max));
        foreach ([1, 2, 5, 10] as $m) {
            if ($max <= $m * $pow) {
                return $m * $pow;
            }
        }

        return 10 * $pow;
    }

    private static function fmt(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }

    /** Donut with a big number in the middle. $slices = [[label, value, color?], ...] */
    public static function donut(array $slices, string $center, string $caption = ''): HtmlString
    {
        $total = array_sum(array_column($slices, 1));
        $r = 70; $c = 2 * M_PI * $r;
        $svg = '<svg viewBox="0 0 200 200" role="img" aria-label="' . e($center . ' ' . $caption) . '" class="chart-donut">';
        $svg .= '<circle cx="100" cy="100" r="' . $r . '" fill="none" stroke="#EEF0F4" stroke-width="28"/>';

        $offset = 0;
        foreach ($slices as $i => $s) {
            if ($total <= 0 || $s[1] <= 0) {
                continue;
            }
            $len = $c * $s[1] / $total;
            $color = $s[2] ?? self::COLORS[$i % count(self::COLORS)];
            $svg .= '<circle cx="100" cy="100" r="' . $r . '" fill="none" stroke="' . $color . '" stroke-width="28"'
                . ' stroke-dasharray="' . round($len, 2) . ' ' . round($c - $len, 2) . '" stroke-dashoffset="' . round(-$offset, 2) . '"'
                . ' transform="rotate(-90 100 100)"><title>' . e($s[0]) . ': ' . self::fmt($s[1]) . '</title></circle>';
            $offset += $len;
        }

        $svg .= '<text x="100" y="104" text-anchor="middle" font-size="30" font-weight="800" fill="#1A1A2E">' . e($center) . '</text>';
        if ($caption !== '') {
            $svg .= '<text x="100" y="124" text-anchor="middle" font-size="11" fill="#6B7185">' . e($caption) . '</text>';
        }
        $svg .= '</svg>';

        return new HtmlString($svg . self::legend($slices));
    }

    private static function legend(array $slices): string
    {
        $html = '<ul class="chart-legend">';
        foreach ($slices as $i => $s) {
            $color = $s[2] ?? self::COLORS[$i % count(self::COLORS)];
            $html .= '<li><i style="background:' . $color . '"></i>' . e($s[0]) . ' <b>' . self::fmt($s[1]) . '</b></li>';
        }

        return $html . '</ul>';
    }

    /**
     * Vertical bars. $series = [[name, [values...], color?], ...]; several series are stacked.
     * $prefix goes before values in tooltips (e.g. "₱").
     */
    public static function bars(array $labels, array $series, string $prefix = '', string $aria = 'Bar chart'): HtmlString
    {
        $w = 560; $h = 240; $l = 36; $b = 30; $t = 12; $n = max(1, count($labels));
        $totals = [];
        foreach ($labels as $i => $_) {
            $totals[$i] = array_sum(array_map(fn ($s) => $s[1][$i] ?? 0, $series));
        }
        $max = self::nice((float) max($totals ?: [0]));
        $plotH = $h - $t - $b; $slot = ($w - $l) / $n; $bw = min(34, $slot * 0.62);

        $svg = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="' . e($aria) . '" class="chart-svg">';
        for ($g = 0; $g <= 4; $g++) {
            $y = $t + $plotH - $plotH * $g / 4;
            $svg .= '<line x1="' . $l . '" x2="' . $w . '" y1="' . $y . '" y2="' . $y . '" stroke="#EEF0F4"/>'
                . '<text x="' . ($l - 6) . '" y="' . ($y + 4) . '" text-anchor="end" font-size="10" fill="#6B7185">' . self::fmt($max * $g / 4) . '</text>';
        }
        foreach ($labels as $i => $label) {
            $x = $l + $slot * $i + ($slot - $bw) / 2;
            $base = $t + $plotH;
            foreach ($series as $si => $s) {
                $v = (float) ($s[1][$i] ?? 0);
                if ($v <= 0) {
                    continue;
                }
                $bh = $plotH * $v / $max;
                $base -= $bh;
                $color = $s[2] ?? self::COLORS[$si % count(self::COLORS)];
                $svg .= '<rect x="' . round($x, 1) . '" y="' . round($base, 1) . '" width="' . round($bw, 1) . '" height="' . round($bh, 1) . '" rx="3" fill="' . $color . '">'
                    . '<title>' . e($label . ' · ' . $s[0] . ': ' . $prefix . self::fmt($v)) . '</title></rect>';
            }
            if ($n <= 14 || $i % 2 === 0) {
                $svg .= '<text x="' . round($x + $bw / 2, 1) . '" y="' . ($h - 10) . '" text-anchor="middle" font-size="10" fill="#6B7185">' . e($label) . '</text>';
            }
        }
        $svg .= '</svg>';

        $legend = count($series) > 1 ? self::legend(array_map(fn ($s, $i) => [$s[0], array_sum($s[1]), $s[2] ?? self::COLORS[$i % count(self::COLORS)]], $series, array_keys($series))) : '';

        return new HtmlString($svg . $legend);
    }

    /** Line with dots. */
    public static function line(array $labels, array $values, string $prefix = '', string $aria = 'Line chart', string $color = '#FF6B35'): HtmlString
    {
        $w = 560; $h = 240; $l = 40; $b = 30; $t = 14; $n = count($labels);
        $max = self::nice((float) max($values ?: [0]));
        $plotH = $h - $t - $b; $step = $n > 1 ? ($w - $l - 14) / ($n - 1) : 0;

        $svg = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="' . e($aria) . '" class="chart-svg">';
        for ($g = 0; $g <= 4; $g++) {
            $y = $t + $plotH - $plotH * $g / 4;
            $svg .= '<line x1="' . $l . '" x2="' . $w . '" y1="' . $y . '" y2="' . $y . '" stroke="#EEF0F4"/>'
                . '<text x="' . ($l - 6) . '" y="' . ($y + 4) . '" text-anchor="end" font-size="10" fill="#6B7185">' . $prefix . self::fmt($max * $g / 4) . '</text>';
        }
        $pts = [];
        foreach ($values as $i => $v) {
            $pts[] = [round($l + 6 + $step * $i, 1), round($t + $plotH - $plotH * $v / $max, 1), $v, $labels[$i]];
        }
        if ($pts) {
            $svg .= '<polyline fill="none" stroke="' . $color . '" stroke-width="3" stroke-linejoin="round" points="' . implode(' ', array_map(fn ($p) => $p[0] . ',' . $p[1], $pts)) . '"/>';
            foreach ($pts as $i => $p) {
                $svg .= '<circle cx="' . $p[0] . '" cy="' . $p[1] . '" r="4" fill="#fff" stroke="' . $color . '" stroke-width="2.5"><title>' . e($p[3] . ': ' . $prefix . self::fmt($p[2])) . '</title></circle>';
                if ($n <= 14 || $i % 2 === 0) {
                    $svg .= '<text x="' . $p[0] . '" y="' . ($h - 10) . '" text-anchor="middle" font-size="10" fill="#6B7185">' . e($p[3]) . '</text>';
                }
            }
        }

        return new HtmlString($svg . '</svg>');
    }

    /** Horizontal bars with the name on the left and the value at the end. */
    public static function hbars(array $rows, string $prefix = '', string $color = '#FF6B35'): HtmlString
    {
        if (!$rows) {
            return new HtmlString('<p class="chart-empty">No data yet.</p>');
        }
        $max = max(array_column($rows, 1)) ?: 1;
        $html = '<div class="hbars">';
        foreach ($rows as [$label, $value]) {
            $pct = max(2, round($value / $max * 100));
            $html .= '<div class="hbar"><span class="hb-l" title="' . e($label) . '">' . e($label) . '</span>'
                . '<span class="hb-t"><span class="hb-f" style="width:' . $pct . '%;background:' . $color . '"></span></span>'
                . '<b class="hb-v">' . $prefix . self::fmt($value) . '</b></div>';
        }

        return new HtmlString($html . '</div>');
    }
}
