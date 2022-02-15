<?php

namespace App\Traits;

use Carbon\Carbon;

trait Utility
{
    public function getEmiDates(): array
    {
        $today = Carbon::today();
        $emiDates = [];

        for ($i = 0; $i < 4; $i++) {
            $today = $today->addDays(7);
            $emiDates[] = $today->format('Y-m-d');
        }

        return $emiDates;
    }
}
