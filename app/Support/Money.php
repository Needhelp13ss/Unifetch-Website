<?php

namespace App\Support;

class Money
{
    /** 129 => "₱129", 129.5 => "₱129.50" */
    public static function format(float|int|string $amount): string
    {
        $amount = (float) $amount;
        $decimals = floor($amount) == $amount ? 0 : 2;

        return '₱' . number_format($amount, $decimals);
    }
}
