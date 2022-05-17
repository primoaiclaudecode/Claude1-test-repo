@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Operations Scorecard Confirmation</strong>
        </header>

        <section class="dataTables-padding">
            {!! Form::open(['url' => 'sheets/operations-scorecard/post', 'class' => 'form-horizontal form-bordered']) !!}
            <div>
                {{ Form::hidden('presentation', $presentation) }}
                {{ Form::hidden('presentation_notes', $presentationNotes) }}
                {{ Form::hidden('presentation_private', $presentationPrivate) }}
                {{ Form::hidden('foodcost_awareness', $foodcostAwareness) }}
                {{ Form::hidden('foodcost_awareness_notes', $foodcostAwarenessNotes) }}
                {{ Form::hidden('foodcost_awareness_private', $foodcostAwarenessPrivate) }}
                {{ Form::hidden('hr_issues', $hrIssues) }}
                {{ Form::hidden('hr_issues_notes', $hrIssuesNotes) }}
                {{ Form::hidden('hr_issues_private', $hrIssuesPrivate) }}
                {{ Form::hidden('morale', $morale) }}
                {{ Form::hidden('morale_notes', $moraleNotes) }}
                {{ Form::hidden('morale_private', $moralePrivate) }}
                {{ Form::hidden('purch_compliance', $purchCompliance) }}
                {{ Form::hidden('purch_compliance_notes', $purchComplianceNotes) }}
                {{ Form::hidden('purch_compliance_private', $purchCompliancePrivate) }}
                {{ Form::hidden('haccp_compliance', $haccpCompliance) }}
                {{ Form::hidden('haccp_compliance_notes', $haccpComplianceNotes) }}
                {{ Form::hidden('haccp_compliance_private', $haccpCompliancePrivate) }}
                {{ Form::hidden('health_safety_iso', $healthSafetyIso) }}
                {{ Form::hidden('health_safety_iso_notes', $healthSafetyIsoNotes) }}
                {{ Form::hidden('health_safety_iso_private', $healthSafetyIsoPrivate) }}
                {{ Form::hidden('accidents_incidents', $accidentsIncidents) }}
                {{ Form::hidden('accidents_incidents_notes', $accidentsIncidentsNotes) }}
                {{ Form::hidden('accidents_incidents_private', $accidentsIncidentsPrivate) }}
                {{ Form::hidden('security_cash_ctl', $securityCashControl) }}
                {{ Form::hidden('security_cash_ctl_notes', $securityCashControlNotes) }}
                {{ Form::hidden('security_cash_ctl_private', $securityCashControlPrivate) }}
                {{ Form::hidden('marketing_upselling', $marketingUpselling) }}
                {{ Form::hidden('marketing_upselling_notes', $marketingUpsellingNotes) }}
                {{ Form::hidden('marketing_upselling_private', $marketingUpsellingPrivate) }}
                {{ Form::hidden('training', $training) }}
                {{ Form::hidden('training_notes', $trainingNotes) }}
                {{ Form::hidden('training_private', $trainingPrivate) }}
                {{ Form::hidden('objectives', $objectives) }}
                {{ Form::hidden('objectives_private', $objectivesPrivate) }}
                {{ Form::hidden('outstanding_issues', $outstandingIssues) }}
                {{ Form::hidden('outstanding_issues_private', $outstandingIssuesPrivate) }}
                {{ Form::hidden('sp_projects_functions', $spProjectsFunctions) }}
                {{ Form::hidden('sp_projects_functions_private', $spProjectsFunctionsPrivate) }}
                {{ Form::hidden('innovation', $innovation) }}
                {{ Form::hidden('innovation_private', $innovationPrivate) }}
                {{ Form::hidden('add_support_required', $addSupportRequired) }}
                {{ Form::hidden('add_support_required_private', $addSupportRequiredPrivate) }}
                {{ Form::hidden('send_email', $sendEmail) }}
                {{ Form::hidden('attached_files', $attachedFiles) }}
            </div>

            <div class="form-group margin-bottom-0 margin-left-0 margin-right-0">
                <div class="clearfix"></div>

                <div class="col-md-12 padding-left-0 padding-right-0 border-top-0">
                    <div class="responsive-content">
                        <table id="operations_scorecard_tbl" class="table table-bordered table-striped table-small">
                            <tr>
                                <td>
                                    <label>Unit Name</label>
                                    {{ Form::text('unit_name', $unitName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                    {{ Form::hidden('unit_id', $unitId) }}
                                </td>
                                <td>
                                    <label>Date</label>
                                    {{ Form::text('scorecard_date', $scorecardDate, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Client Communications</label>
                                    {{ Form::text('onsite_visits', $onsiteVisits, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                </td>
                                <td>
                                    <label>Average Score</label>
                                    {{ Form::text('average-score', $averageScore, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Attached Files</label>
                                    {{ Form::text('attached_files_list', $files, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                </td>
                                <td>
                                    <label>Send Email</label>
                                    {{ Form::text('average-score', $sendEmail ? 'Yes' : 'No', array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <h5>Are you sure you want to submit this operations scorecard?</h5>
                    <input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm'/>
                </div>
            </div>
            {!!Form::close()!!}

            {!! Form::open(['url' => 'sheets/operations-scorecard', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
                {{ Form::hidden('back_data', $backData) }}
            {!!Form::close()!!}

            <p>
                <a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter operations scorecard</a>
                <br/>
            </p>
        </section>
    </section>
@stop
@section('scripts')
    <script src="{{asset('js/jquery.backDetect.js')}}"></script>
    <script type="text/javascript">
        $(window).load(function () {
            $('body').backDetect(function () {
                alert('Confirm form resubmission');
                $('#re_enter_frm').submit()
            });
        });

        // Prevent double submit
        $('form').on('submit', function () {
            if ($(this).hasClass('processing')) {
                return false;
            }

            $(this).addClass('processing');
        });
    </script>
@stop