@extends('layouts/dashboard_master')

@section('content')

<section class="panel">
    <header class="panel-heading">
        <strong>Vending Sales Report</strong>
    </header>

    <section class="dataTables-padding">
        @if(Session::has('flash_message'))
            <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
        @endif

        {!! Form::open(['url' => 'reports/vending-sales/grid', 'class' => 'form-horizontal form-bordered', 'id' => 'vending_sales_form']) !!}
            <div class="form-group">
                <label class="col-xs-4 col-sm-3 control-label custom-labels">Unit Name:</label>
                <div class="col-xs-8 col-sm-4">
                    {!! Form::select('unit_name', $userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'All', 'tabindex' => 1, 'autofocus']) !!}
                    <span id="unit_name_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-4 col-sm-3 control-label custom-labels">Machine Name:</label>
                <div class="col-xs-8 col-sm-4">
                    <span id="vending_machines"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-4 col-sm-3 control-label custom-labels">From Date:</label>
                <div class="col-xs-8 col-sm-4">
                    <div class="input-group">
                        {{ Form::text('from_date', $fromDate, array('id' => 'from_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 3, 'readonly' => '')) }}
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
                        {{ Form::text('to_date', $toDate, array('id' => 'to_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 4, 'readonly' => '')) }}
                        <span class="input-group-addon cursor-pointer" id="to_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>

            @can('hq-user-group')
                <div class="form-group">
                    <label class="col-xs-4 col-sm-3 control-label custom-labels">All Records:</label>
                    <div class="col-xs-8 col-sm-4">
                        {{ Form::checkbox('all_records', 1, false, array('class' => 'margin-top-10 checkbox-outline', 'tabindex' => 5)) }}
                    </div>
                </div>
            @endcan
            
            <div class="btn-toolbar">
                {{ Form::hidden('page', 'first') }}
                <input type='submit' id="submit_btn" class="btn btn-primary btn-md" name='submit' value='Get Report' tabindex='20' />
                <input type='button' id="cancel_btn" class="btn btn-primary btn-md" name='cancel' value='Cancel' tabindex='21' onclick="window.location='{{ $backUrl }}'" />
            </div>
        {!!Form::close()!!}
    </section>
</section>
@stop

@section('scripts')
<script type="text/javascript">
    $('#from_date').datepicker({
        ignoreReadonly: true,
        format: 'dd-mm-yyyy',
        autoclose: true
    }).on('changeDate',function(e){
        $('#to_date').focus();
    });

    $('#to_date').datepicker({
        ignoreReadonly: true,
        format: 'dd-mm-yyyy',
        autoclose: true
    }).on('changeDate',function(e){
        $('#to_date').focus();
    });

    function vendingMachines(unitId, selectedMachine)
    {
        $.ajax({
            type: 'GET',
            url: "{{ url('/vending-machines/json') }}",
            data: { unit_id: unitId, selectedMachine: selectedMachine }
        }).done(function( data ) {
            var obj = jQuery.parseJSON(data);

            if(obj.vendingMachinesData)
                $('#vending_machines').html(obj.vendingMachinesData);
        });
    }

    $(document).ready(function() {
        $("#unit_name").change(function() {
            vendingMachines($("#unit_name").val(), '{{ $selectedMachine }}');
        });

        $('#from_date_icon').click(function(){
           $(document).ready(function(){
               $("#from_date").datepicker().focus();
           });
       });

       $('#to_date_icon').click(function(){
           $(document).ready(function(){
               $("#to_date").datepicker().focus();
           });
       });
    });

    jQuery(window).on("load", function() {
        vendingMachines($("#unit_name").val(), '{{ $selectedMachine }}');
    });
</script>
@stop