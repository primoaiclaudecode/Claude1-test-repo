@extends('layouts/dashboard_master')

@section('content')
  	<section class="panel">
        <header class="panel-heading">
            <strong>Purchases Summary Report</strong>
        </header>

		<section class="dataTables-padding">
  			@if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
  			@endif

            {!! Form::open(['url' => 'reports/purchases-summary/grid', 'class' => 'form-horizontal form-bordered', 'id' => 'purchases_summary_report_form']) !!}

            <div class="form-group">
                <label class="col-xs-4 col-sm-3 control-label custom-labels">Unit Name:</label>
                <div class="col-xs-8 col-sm-4">
                    {!! Form::select('unit_name', $userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'All', 'tabindex' => 1, 'autofocus']) !!}
                    <span id="unit_name_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-4 col-sm-3 control-label custom-labels">From Date:</label>
                <div class="col-xs-8 col-sm-4">
                    <div class="input-group">
                        {{ Form::text('from_date', $fromDate, array('id' => 'from_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 2, 'readonly' => '')) }}
                        <span class="input-group-addon cursor-pointer" id="from_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-4 col-sm-3 control-label custom-labels">To Date:</label>
                <div class="col-xs-8 col-sm-4">
                    <div class="input-group">
                        {{ Form::text('to_date', $toDate, array('id' => 'to_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 3, 'readonly' => '')) }}
                        <span class="input-group-addon cursor-pointer" id="to_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-4 col-sm-3 control-label custom-labels">Purchase Type:</label>
                <div class="col-xs-8 col-sm-4">
                    {!! Form::select('purchase_type', ['both' => 'Both', 'cash' => 'Cash', 'credit' => 'Credit'], $selectedPurchaseType, ['id' => 'purchase_type', 'class'=>'form-control', 'tabindex' => 4]) !!}
                </div>
            </div>

            <div class="btn-toolbar">
                <input type='submit' id="submit_btn" class="btn btn-primary btn-md" name='submit' value='Get Report' tabindex='6' />
                <input type='button' id="cancel_btn" class="btn btn-primary btn-md" name='cancel' value='Cancel' tabindex='7' onclick="window.location='{{ url()->previous() }}'" />
            </div>
   			{!!Form::close()!!}
       	</section>
  	</section>
@stop

@section('scripts')
    <script type="text/javascript">
        $('#from_date').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        }).on('changeDate',function(e){
            $('#to_date').focus();
        });

        $('#to_date').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        }).on('changeDate',function(e){
            $('#purchase_type').focus();
        });

        $(document).ready(function() {
            $('#from_date_icon').click(function() {
                $("#from_date").datepicker().focus();
            });

            $('#to_date_icon').click(function() {
                $("#to_date").datepicker().focus();
            });
        });
    </script>
@stop