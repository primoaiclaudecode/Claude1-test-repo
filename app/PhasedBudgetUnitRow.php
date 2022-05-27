<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhasedBudgetUnitRow extends Model
{
    public $table = "phased_budget_unit_rows";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'unit_id',
        'row_index',
    ];
    public static $rows = [
        'labour'                      => 'Labour',
        'training'                    => 'Training',
        'cleaning'                    => 'Cleaning',
        'disposables'                 => 'Disposables',
        'uniform'                     => 'Uniform',
        'delph_and_cutlery'           => 'Delph & Cutlery',
        'bank_charges'                => 'Bank Charges',
        'investment'                  => 'Investment',
        'management_fee'              => 'Management fee',
        'insurance_and_related_costs' => 'Insurance & related costs',
        'coffee_machine_rental'       => 'Coffee Machine rental',
        'other_rental'                => 'Other Rental',
        'it_support'                  => 'IT Support',
        'free_issues'                 => 'Free Issues',
        'marketing'                   => 'Marketing',
        'set_up_costs'                => 'Set Up costs',
        'credit_card_machines'        => 'Credit Card Machines',
        'bizimply_cost'               => 'Bizimply cost',
        'kitchtech'                   => 'Kitchtech',
    ];
}
