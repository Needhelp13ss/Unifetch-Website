<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CampusLocation;

trait PricesDeliveries
{
    protected function places()
    {
        return CampusLocation::orderBy('name')->get();
    }

    /** "Library · Room 2" : the place plus the details the customer typed. */
    protected function deliveryLabel(CampusLocation $to, ?string $details): string
    {
        $details = trim((string) $details);

        return $details === '' ? $to->name : $to->name . ' · ' . $details;
    }
}
