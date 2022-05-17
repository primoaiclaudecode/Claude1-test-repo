@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Statement Check</strong>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif

			{!! Form::open(['url' => 'accounts/statement-check', 'class' => 'form-horizontal form-bordered', 'id' => 'statement_check_form']) !!}
			<div class="form-group">
				<label class="col-xs-12 col-sm-3 control-label custom-labels">Unit Name:</label>
				<div class="col-xs-12 col-sm-4">
					{{ Form::select('unit_name',$userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'All', 'tabindex' => 1, 'autofocus']) }}
					<span id="unit_name_span" class="error_message"></span>
				</div>
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-3 control-label custom-labels">Supplier:</label>
				<div class="col-xs-12 col-sm-4">
					{{ Form::select('supplier',$suppliers, $selectedSupplier, ['id' => 'supplier', 'class'=>'form-control', 'placeholder' => 'All', 'tabindex' => 1, 'autofocus']) }}
				</div>
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-3 control-label custom-labels">From Date:</label>
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
				<label class="col-xs-12 col-sm-3 control-label custom-labels">To Date:</label>
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
				<input type='submit' id="submit_btn" class="btn btn-primary btn-md" name='submit' value='Get Report' tabindex='5'/>
				<input type='button' id="cancel_btn" class="btn btn-primary btn-md" name='cancel' value='Cancel' tabindex='6' onclick="window.location='{{ $backUrl }}'" />
			</div>
			{!!Form::close()!!}

			{!! Form::open(['url' => 'accounts/statement-check', 'class' => 'form-horizontal form-bordered']) !!}
			<div class="fixed-width-table-with-x-scroll purchases-summary margin-top-25">
				<table class="table table-hover table-striped margin-bottom-0 table-class-for-links">
					<thead>
					<tr>
						<th>Sheet ID</th>
						<th class="text-center">Unit Name</th>
						<th class="text-center">Supplier Details</th>
						<th class="text-center">Invoice Date</th>
						<th class="text-center">Inv #</th>
						<th class="text-center">Total Goods</th>
						<th class="text-center">Total VAT</th>
						<th class="text-center">Total Gross</th>
						<th class="text-center">Stmt Chk</th>
						<th class="text-center">N.S.R</th>
						<th class="text-center">Pending</th>
					</tr>
					</thead>
					<tbody>
					
					@foreach($statementCheckData as $sc)
						@php
							$totalGoods += $sc->goods_total; 
							$totalVat += $sc->vat_total; 
							$totalGross += $sc->gross_total;						
						@endphp
						<tr>
							<td class="vertical-align-middle"><a target="_blank" href="/sheets/purchases/credit/{{ $sc->unique_id}}">{{ $sc->unique_id }}</a></td>
							<td>{{ $sc->unit_name }}</td>
							<td>{{ $sc->supplier }}</td>
							<td class="text-center">{{ $sc->receipt_invoice_date }}</td>
							<td>{{ $sc->reference_invoice_number }}</td>
							<td class="text-right">{{ number_format($sc->goods_total, 2, '.', '') }}</td>
							<td class="text-right">{{ number_format($sc->vat_total, 2, '.', '') }}</td>
							<td class="text-right">{{ number_format($sc->gross_total, 2, '.', '') }}</td>
							<td class="text-center"><input value="{{ $sc->unique_id }}-stmt_chk" class="radio_stmt_chk" type="radio"
							                               name="stmt_nsr_pend_{{ $sc->unique_id }}"></td>
							<td class="text-center"><input value="{{ $sc->unique_id }}-nsr" class="radio_nsr" type="radio"
							                               name="stmt_nsr_pend_{{ $sc->unique_id }}"></td>
							<td class="text-center"><input value="{{ $sc->unique_id }}-pending" class="radio_pending" type="radio"
							                               name="stmt_nsr_pend_{{ $sc->unique_id }}" checked=""></td>
						</tr>
					@endforeach
					<tr>
						<td><strong>Total</strong></td>
						<td colspan="4"></td>
						<td class="text-right">{{ number_format($totalGoods, 2, '.', '') }}</td>
						<td class="text-right">{{ number_format($totalVat, 2, '.', '') }}</td>
						<td class="text-right">{{ number_format($totalGross, 2, '.', '') }}</td>
						<td class="text-center"><a id="stmt_chk" href="javascript: void(0)">Select All</a></td>
						<td class="text-center"><a id="nsr" href="javascript: void(0)">Select All</a></td>
						<td class="text-center"><a id="pending" href="javascript: void(0)">Select All</a></td>
					</tr>
					</tbody>
				</table>
			</div>
			<div class="btn-toolbar margin-top-25">
				<input type='submit' id="submit_btn" class="btn btn-primary btn-md" name='submit' value='Submit' tabindex='7'/>
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
        }).on('changeDate', function (e) {
            $('#to_date').focus();
        });

        $('#to_date').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        }).on('changeDate', function (e) {
            $('#to_date').focus();
        });

        function getSuppliers() {
            $.ajax({
                type: 'GET',
                url: "{{ url('/supplier/json') }}",
                data: {
                    unit_id: $('#unit_name').val(),
                },
                dataType: 'json'
            }).done(function (data) {
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

        $(document).ready(function () {
            $('#from_date_icon').click(function () {
                $("#from_date").datepicker().focus();
            });

            $('#to_date_icon').click(function () {
                $("#to_date").datepicker().focus();
            });

            $("#unit_name").change(function () {
                getSuppliers();
            });

            $("#stmt_chk").click(function () {
                $(".radio_stmt_chk").prop("checked", true);
                $("#nsr").prop("checked", false);
                $("#pending").prop("checked", false);
            });

            $("#nsr").click(function () {
                $(".radio_nsr").prop("checked", true);
                $("#stmt_chk").prop("checked", false);
                $("#pending").prop("checked", false);
            });

            $("#pending").click(function () {
                $(".radio_pending").prop("checked", true);
                $("#stmt_chk").prop("checked", false);
                $("#nsr").prop("checked", false);
            });
        });
	</script>
@stop