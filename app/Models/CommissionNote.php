<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'branch_id', 'employee_id',
        'created_by', 'amount', 'description', 'payment_date',
    ];

    protected $casts = ['amount' => 'decimal:2', 'payment_date' => 'date'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
