<?php

namespace App\Traits;

use Carbon\Carbon;

trait Utility
{
    public function getEmiDates(): array
    {
        $today = Carbon::today();
        $emiDates = [];
        $noOfEMI = config('loan.no_of_emi');

        for ($i = 0; $i < $noOfEMI; $i++) {
            $today = $today->addDays(7);
            $emiDates[] = $today->format('Y-m-d');
        }

        return $emiDates;
    }
}
