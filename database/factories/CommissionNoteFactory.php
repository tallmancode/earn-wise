<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommissionNote>
 */
class CommissionNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'employee_id' => Employee::factory(),
            'created_by' => User::factory(),
            'amount' => fake()->numberBetween(1000, 50000),
            'description' => null,
            'payment_date' => fake()->date(),
        ];
    }
}
