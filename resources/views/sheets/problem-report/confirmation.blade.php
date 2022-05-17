@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Corrective Action Report Confirmation</strong>
        </header>

        <section class="dataTables-padding">
            {!! Form::open(['url' => 'sheets/problem-report/post', 'class' => 'form-horizontal form-bordered']) !!}

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-md-offset-6 control-label custom-labels">CAR #:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('id', $carNum, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">User:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('user_name', $userName, array('class' => 'form-control margin-bottom-15', 'readonly' => 'readonly')) }}
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Date:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('problem_date', $problemDate, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Category:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('problem_type_text', $problemTypeText, array('class' => 'form-control margin-bottom-15', 'readonly' => 'readonly')) }}
                    {{ Form::hidden('problem_type', $problemType) }}
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Unit:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('unit_name_text', $unitName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                    {{ Form::hidden('unit_id', $unitId) }}
                </div>
            </div>

            @if($problemType == 1 && $supplier != '')
                <div class="form-group supplier-div">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Supplier:</label>
                    <div class="col-xs-12 col-sm-9 col-md-4">
                            <span id="supplier_span">
                                {{ Form::text('supplier_text', $supplierText, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                {{ Form::hidden('problem_type_val', $supplier) }}
                                {{ Form::hidden('suppliers_feedback_title', $supplierText) }}
                            </span>
                    </div>
                </div>
            @endif

            @if($problemType == 6 && $feedback != '')
                <div class="form-group supplier-div">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Feedback:</label>
                    <div class="col-xs-12 col-sm-9 col-md-4">
                            <span id="supplier_span">
                                {{ Form::text('feedback_text', $feedbackText, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                {{ Form::hidden('problem_type_val', $feedback) }}
                                {{ Form::hidden('suppliers_feedback_title', $feedbackText) }}
                            </span>
                    </div>
                </div>
            @endif

            @if($problemType == 6 && $feedbackText == 'Comment')
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Comments:</label>
                    <div class="col-xs-12 col-sm-9 col-md-10">
                        {{ Form::textarea('comments', $commentsText, array('class' => 'form-control', 'rows' => 2, 'readonly' => 'readonly')) }}
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Details:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    {{ Form::textarea('details', $details, array('class' => 'form-control', 'rows' => 2, 'readonly' => 'readonly')) }}
                </div>
            </div>

            <div class="form-group supplier-div">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Root Cause Analysis:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('root_cause_analysis_text', $rootCauseAnalysisText, array('class' => 'form-control margin-bottom-15', 'readonly' => 'readonly')) }}
                    {{ Form::hidden('root_cause_analysis', $rootCauseAnalysis) }}
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Attached Files:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
					<?php
					if ($file_id != '') {
						$fileArr = explode(",", $file_id);
						if (count($fileArr) > 0 && !empty($fileArr)) {
							foreach ($fileArr as $key => $value) {
								echo $value;
								echo '<br>';
							}
						}
					}
					?>
                    {{ Form::hidden('newFileID', $newFileID) }}
                </div>
            </div>

            @if($rootCauseAnalysis == 1)
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Root Cause Analysis Description:</label>
                    <div class="col-xs-12 col-sm-9 col-md-10">
                        {{ Form::textarea('root_cause_analysis_desc', $rootCauseAnalysisDesc, array('class' => 'form-control', 'rows' => 2, 'readonly' => 'readonly')) }}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Action:</label>
                    <div class="col-xs-12 col-sm-9 col-md-10">
                        {{ Form::textarea('root_cause_analysis_action', $rootCauseAnalysisAction, array('class' => 'form-control', 'rows' => 2, 'readonly' => 'readonly')) }}
                    </div>
                </div>
            @endif

            <div class="form-group supplier-div">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">CAR Status:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('problem_status_title', $problemStatusText, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                    {{ Form::hidden('problem_status', $problemStatus) }}
                </div>
            </div>

            @if($problemStatus == 0)
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Closing Comments:</label>
                    <div class="col-xs-12 col-sm-9 col-md-10">
                        {{ Form::textarea('closing_comments', $closingComments, array('class' => 'form-control', 'rows' => 2, 'readonly' => 'readonly')) }}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Closed By:</label>
                    <div class="col-xs-12 col-sm-9 col-md-4">
                        {{ Form::text('user_name', $userName, array('class' => 'form-control margin-bottom-15', 'readonly' => 'readonly')) }}
                    </div>

                    <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Date Closed:</label>
                    <div class="col-xs-12 col-sm-9 col-md-4">
                        {{ Form::text('closed_date', $closedDate, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                    </div>
                </div>
            @endif

            <div class="form-group">
                <div class="col-xs-12">
                    {{ Form::hidden('sheet_id', $sheetId) }}
                    <input type='submit' class='btn btn-primary btn-block margin-top-10' name='submit' value='Confirm CAR'/>
                </div>
            </div>
            {!!Form::close()!!}

            {!! Form::open(['url' => 'sheets/problem-report', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
                {{ Form::hidden('return_from', 'confirm') }}
                {{ Form::hidden('unit_id', $unitId) }}
                {{ Form::hidden('unit_name', $unitName) }}
                {{ Form::hidden('problem_date', $problemDate) }}
                {{ Form::hidden('problem_type', $problemType) }}
                {{ Form::hidden('hidden_problem_type', $problemTypeText) }}
                {{ Form::hidden('supplier', $supplier) }}
                {{ Form::hidden('hidden_supplier', $supplierText) }}
                {{ Form::hidden('feedback', $feedback) }}
                {{ Form::hidden('details', $details) }}
                {{ Form::hidden('root_cause_analysis', $rootCauseAnalysis) }}
                {{ Form::hidden('root_cause_analysis_desc', $rootCauseAnalysisDesc) }}
                {{ Form::hidden('root_cause_analysis_action', $rootCauseAnalysisAction) }}
                {{ Form::hidden('problem_status', $problemStatus) }}
                {{ Form::hidden('closing_comments', $closingComments) }}
                {{ Form::hidden('closed_date', $closedDate) }}
                {{ Form::hidden('sheet_id', $sheetId) }}
            {!!Form::close()!!}

            <p>
                <a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter problem</a>
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
