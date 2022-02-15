<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\LoanPayments;
use App\Models\LoanTransaction;
use App\Traits\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoanController extends ApiController
{
    use Utility;

    public function loanApplication()
    {
        try {
            $user = auth()->user();

            //check if already applied
            if ($user->loan != null) {
                return $this->errorResponse('already applied for loan');
            }

            //dummy loan approval for ex - 5000.00
            $user->loan()->create([
                'loan_amount' => 5000,
                'spent' => 0
            ]);

            $response = [
                'loan approved' => $user->fresh('loan')->loan->loan_amount,
            ];

            return $this->successResponse('Congratulations! loan approved', $response, 200);
        } catch (\Exception $ex) {
            return $this->errorResponse('something went wrong!', $ex, 400);
        }
    }

    public function getLoanDetails()
    {
        try {

            $loan = auth()->user()->loan;

            //check if already applied
            if ($loan == null) {
                return $this->errorResponse('loan details not found');
            }

            $loanDetails = $this->getloanInfo($loan);

            $response = [
                'loanDetails' => $loanDetails,
                'billing' => $this->getBillingDetails()
            ];

            return $this->successResponse('Loan details', $response, 200);
        } catch (\Exception $ex) {
            return $this->errorResponse('something went wrong!', $ex, 400);
        }
    }

    public function spendLoanMoney(Request $request)
    {
        try {
            DB::beginTransaction();

            $loan = auth()->user()->loan;

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric',
                'item' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation error.', $validator->errors(), 400);
            }

            $availableBalance = $this->availableCredit($loan);
            if ($request->amount > $availableBalance) {
                return $this->successResponse('Credit limit exceeds');
            }

            $loanTransaction = LoanTransaction::create([
                'loan_id' => $loan->id,
                'item' => $request->item,
                'amount' => $request->amount
            ]);
            //calculate loan repayments or installment

            $installmentAmount = $loanTransaction->amount / 4;

            $emiDates = $this->getEmiDates();

            foreach ($emiDates as $key => $value) {
                $loanPayments = LoanPayments::create([
                    'loan_transaction_id' => $loanTransaction->id,
                    'user_id' => auth()->user()->id,
                    'installment_no' => $key + 1,
                    'installment_amount' => $installmentAmount,
                    'total_recievable' => $installmentAmount,
                    'due_date' => $value
                ]);
            }

            //debit amount
            $loan->update([
                'spent' => $loan->spent + $request->amount
            ]);

            $response = [
                'transaction' => $loanTransaction
            ];

            DB::commit();
            return $this->successResponse('transaction success!', $response);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->errorResponse('something went wrong!', $ex, 400);
        }
    }

    public function availableCredit($loan)
    {
        return $loan->loan_amount - $loan->spent;
    }

    public function getLoanInfo($loan): array
    {
        return [
            'available' => $loan->loan_amount - $loan->spent,
            'credit limit' => $loan->loan_amount,
            'spent' => $loan->spent
        ];
    }

    public function getBillingDetails(): array
    {
        $activeEmi = $this->getActiveEmi();

        $emiDetails = [];

        if (count($activeEmi) > 0) {
            $emiDetails['installmentTotal'] = $activeEmi->sum('installment_amount');

            foreach ($activeEmi as $emi) {
                $emiData = [
                    'installment_no' => $emi->installment_no,
                    'installment_amount' => $emi->installment_amount,
                    'emi_status' => ($emi->penalty == 0) ? 'regular' : 'bounced',
                    'due_date' => $emi->due_date,
                    'item' => $emi->loanTransaction->item,
                    'item_amount' => $emi->loanTransaction->amount
                ];
                $emiDetails['details'][] = $emiData;
            }
        }

        return $emiDetails;
    }

    public function getActiveEmi()
    {
        $user = auth()->user();

        $today = Carbon::today();
        $bounceEmi = LoanPayments::where('user_id', $user->id)
            ->where('active', 1)
            ->whereDate('due_date', '<', $today->format('Y-m-d'))
            ->where('penalty', 1)
            ->get();


        $upcomingDate = $today->addDays(8)->format('Y-m-d');
        $upcomingEmi  = LoanPayments::where('user_id', $user->id)
            ->where('active', 1)
            ->whereDate('due_date', '<', $upcomingDate)
            ->where('penalty', 0)
            ->get();

        $emiToPaid = (count($upcomingEmi) > 0) ?  $upcomingEmi->merge($bounceEmi) : $upcomingEmi;

        return $emiToPaid;
    }

    public function payBack()
    {
        try {
            DB::beginTransaction();
            $activeEmi = $this->getActiveEmi();
            if (count($activeEmi) == 0) {
                return $this->successResponse('No active emi found');
            }

            foreach ($activeEmi as $emi) {
                $emi->update([
                    'amount_received' => $emi->total_recievable,
                    'active' => 0,
                    'penalty' => 0,
                    'payment_date' => Carbon::now()->format('Y-m-d h:i:s')
                ]);
            }

            $emiTotalAmount = $activeEmi->sum('installment_amount');

            $loan = auth()->user()->loan;
            $loan->update([
                'amount' => $loan->loan_amount + $emiTotalAmount,
                'spent' => $loan->spent - $emiTotalAmount
            ]);

            DB::commit();
            return $this->successResponse('EMI paid successfully!');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->errorResponse('something went wrong!', $ex, 400);
        }
    }
}
