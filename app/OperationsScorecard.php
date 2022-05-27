<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OperationsScorecard extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ops_scorecard';
    /**
     * Primary key
     *
     * @var string
     */
    protected $primaryKey = 'ops_scorecard_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unit_id',
        'presentation',
        'presentation_notes',
        'presentation_private',
        'foodcost_awareness',
        'foodcost_awareness_notes',
        'foodcost_awareness_private',
        'hr_issues',
        'hr_issues_notes',
        'hr_issues_private',
        'morale',
        'morale_notes',
        'morale_private',
        'purch_compliance',
        'purch_compliance_notes',
        'purch_compliance_private',
        'haccp_compliance',
        'haccp_compliance_notes',
        'haccp_compliance_private',
        'health_safety_iso',
        'health_safety_iso_notes',
        'health_safety_iso_private',
        'accidents_incidents',
        'accidents_incidents_notes',
        'accidents_incidents_private',
        'security_cash_ctl',
        'security_cash_ctl_notes',
        'security_cash_ctl_private',
        'marketing_upselling',
        'marketing_upselling_notes',
        'marketing_upselling_private',
        'training',
        'training_notes',
        'training_private',
        'objectives',
        'objectives_private',
        'outstanding_issues',
        'outstanding_issues_private',
        'sp_projects_functions',
        'sp_projects_functions_private',
        'innovation',
        'innovation_private',
        'add_support_req',
        'add_support_req_private',
        'attached_files',
        'attached_files_private',
        'send_email',
        'scorecard_date',
        'created_by',
    ];
    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var array Score fields
     */
    public static $scoreFields = [
        'presentation',
        'foodcost_awareness',
        'hr_issues',
        'morale',
        'purch_compliance',
        'haccp_compliance',
        'health_safety_iso',
        'accidents_incidents',
        'security_cash_ctl',
        'marketing_upselling',
        'training',
    ];

    /**
     * Get the unit.
     */
    public function unit()
    {
        return $this->belongsTo('App\Unit');
    }
}
