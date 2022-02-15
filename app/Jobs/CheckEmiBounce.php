<?php

namespace App\Jobs;

use App\Models\LoanPayments;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckEmiBounce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('checking emi');

        $today = Carbon::today()->format('Y-m-d');
        $loanPayments = LoanPayments::where('active', 1)
            ->whereDate('due_date', '<', $today)
            ->get();

        foreach ($loanPayments as  $payment) {
            $payment->update(['penalty' => 1]);
        }

        Log::info('loan emi penalty added!');
    }
}
