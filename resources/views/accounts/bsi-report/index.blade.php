@extends('layouts/dashboard_master')

@section('content')
  	<section class="panel">
        <header class="panel-heading">
            <strong>Batch Suppliers Invoice Report</strong>
        </header>

		<section class="dataTables-padding">
  			@if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
  			@endif

            {!! Form::open(['url' => 'accounts/bsi-report', 'class' => 'form-horizontal form-bordered', 'id' => 'bsi_report_form']) !!}
			<div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Unit Name:</label>
                <div class="col-xs-12 col-sm-4 col-sm-4">
                    {!! Form::select('unit_name', $userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'All', 'tabindex' => 1, 'autofocus']) !!}
                    <span id="unit_name_span" class="error_message"></span>
                </div>
   			</div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Supplier:</label>
                <div class="col-xs-12 col-ms-6 col-sm-4">
                    {{ Form::select('supplier',[], $selectedSupplier, ['id' => 'supplier', 'class'=>'form-control', 'placeholder' => 'All', 'tabindex' => 1, 'autofocus']) }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Date Start:</label>
                <div class="col-xs-12 col-sm-4">
                    <div class="input-group">
                        {{ Form::text('from_date', $fromDate, array('id' => 'from_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 3, 'readonly' => '')) }}
                        <span class="input-group-addon cursor-pointer" id="from_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                </div>
     		</div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Date End:</label>
                <div class="col-xs-12 col-sm-4">
                    <div class="input-group">
                        {{ Form::text('to_date', $toDate, array('id' => 'to_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 4, 'readonly' => '')) }}
                        <span class="input-group-addon cursor-pointer" id="to_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="btn-toolbar">
                <input type='submit' id="submit_btn" class="btn btn-primary btn-md" name='submit' value='Get Report' tabindex='5' />
                <input type='button' id="cancel_btn" class="btn btn-primary btn-md" name='cancel' value='Cancel' tabindex='6' onclick="window.location='{{ $backUrl }}'" />
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
            $('#to_date').focus();
        });

        $(document).ready(function() {
            $('#from_date_icon').click(function() {
                $("#from_date").datepicker().focus();
            });

            $('#to_date_icon').click(function() {
                $("#to_date").datepicker().focus();
            });
            $("#unit_name").change(function() {
                supplier($("#unit_name").val(), '{{ $selectedSupplier }}');
            });
        });

        function supplier(unitId, selectedSupplier = null)
        {
            $.ajax({
                type: 'GET',
                url: "{{ url('/supplier/json') }}",
                data: { unit_id: unitId, selectedSupplier: selectedSupplier }
            }).done(function( data ) {
                $('#supplier').empty();

                $.each(data, function (index, supplier) {
                    $('#supplier').append(
                        $('<option />').val(supplier.suppliers_id).text(supplier.supplier_name)
                    )
                });

                $('#supplier').prepend(
	                $('<option />').val('').text('All').attr('selected', 'selected')
                )
            });
        }

        jQuery(window).on("load", function() {
            supplier($("#unit_name").val(), '{{ $selectedSupplier }}');
        });
    </script>
@stop