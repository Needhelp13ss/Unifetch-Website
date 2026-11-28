<?php

namespace App\Support;

use App\Models\CampusLocation;

/**
 * How a delivery is priced. Change the numbers here.
 *  runner fee = BASE_FEE + STEP_FEE for every full STEP_METERS between the pickup and the drop-off
 *  service fee = SERVICE_FEE on every order (UniFetch's profit)
 */
class Pricing
{
    public const BASE_FEE = 7;
    public const STEP_METERS = 25;
    public const STEP_FEE = 2;
    public const SERVICE_FEE = 3;

    public static function runnerFee(int $meters): int
    {
        return self::BASE_FEE + intdiv(max(0, $meters), self::STEP_METERS) * self::STEP_FEE;
    }

    /** Straight-line distance in whole metres (haversine). */
    public static function meters(CampusLocation $a, CampusLocation $b): int
    {
        $r = 6371000;
        $dLat = deg2rad($b->latitude - $a->latitude);
        $dLng = deg2rad($b->longitude - $a->longitude);
        $h = sin($dLat / 2) ** 2 + cos(deg2rad($a->latitude)) * cos(deg2rad($b->latitude)) * sin($dLng / 2) ** 2;

        return (int) round(2 * $r * asin(min(1, sqrt($h))));
    }

    /**
     * Quote for going from one or more pickup places to a drop-off place.
     * A place that is unknown (null) counts as "no distance", so the base fee applies.
     * With several pickup places the farthest one decides the distance.
     *
     * @param iterable<CampusLocation|null> $from
     */
    public static function quote(iterable $from, ?CampusLocation $to): array
    {
        $meters = 0;
        if ($to) {
            foreach ($from as $place) {
                if ($place) {
                    $meters = max($meters, self::meters($place, $to));
                }
            }
        }

        $fee = self::runnerFee($meters);

        return [
            'meters' => $meters,
            'runner_fee' => $fee,
            'service_fee' => self::SERVICE_FEE,
            'total_fees' => $fee + self::SERVICE_FEE,
        ];
    }
}
