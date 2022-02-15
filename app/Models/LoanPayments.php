<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_transaction_id',
        'user_id',
        'installment_no',
        'installment_amount',
        'previous_outstanding',
        'total_recievable',
        'amount_received',
        'payment_date',
        'due_date',
        'penalty',
        'active'
    ];

    protected $cast = [
        'installment_no' => 'integer',
        'due_date' => 'date'
    ];

    /**
     * Get the transaction that owns the LoanPayments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loanTransaction(): BelongsTo
    {
        return $this->belongsTo(LoanTransaction::class, 'loan_transaction_id');
    }
}
