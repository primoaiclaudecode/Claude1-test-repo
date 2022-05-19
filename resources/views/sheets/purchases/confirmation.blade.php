@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>{{ ucfirst($purchType) }} Purchases Confirmation</strong>
		</header>

		<section class="dataTables-padding">
			{!! Form::open(['url' => $purchType == 'cash' ? 'sheets/purchases/cash/post' : 'sheets/purchases/credit/post', 'class' => 'form-horizontal form-bordered']) !!}
			{{ Form::hidden('sheet_id', $sheetId, array('id' => 'sheet_id')) }}
			{{ Form::hidden('purchase_items', $purchaseItems) }}

			<div class="form-group margin-bottom-0 margin-left-0 margin-right-0">
				<div class="clearfix"></div>
				<div class="col-md-12 padding-left-0 padding-right-0 border-top-0">
					<div class="responsive-content">
						<table id="cash_purchases_tbl" class="table table-bordered table-striped table-small">
							<tr>
								<td>
									<label>Unit Name:</label>
									{{ Form::text('unit_name', $unitName, array('class' => 'form-control', 'readonly' => 'readonly','id'=>'unit_name')) }}
									{{ Form::hidden('unit_id', $unitId) }}
								</td>
								@if ($purchType == 'cash')
									<td>
										<label>Supplier:</label>
										{{ Form::text('supplier_name', $supplierName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
									</td>
									<td>
										<label>Reference Number:</label>
										{{ Form::text('reference_number', $referenceNumber, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									</td>
								@else
									<td>
										<label>Supplier:</label>
										{{ Form::text('supplier_name', $supplierName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
										{{ Form::hidden('supplier_id', $supplierId) }}
									</td>
									<td>
										<label>Invoice Number:</label>
										{{ Form::text('invoice_number', $invoiceNumber, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									</td>
								@endif
							</tr>
							<tr>
								@if ($purchType == 'cash')
									<td>
										<label>Receipt Date:</label>
										{{ Form::text('receipt_date', $receiptDate, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									</td>
								@else
									<td>
										<label>Invoice Date:</label>
										{{ Form::text('invoice_date', $invoiceDate, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									</td>
								@endif
								<td>
									<label>Purchase Details:</label>
									{{ Form::text('purchase_details', $purchaseDetails, array('class' => 'form-control', 'readonly' => 'readonly')) }}
								</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								{{ Form::hidden('currency_id', $currencyId) }}
								<td>
									<label>Total Goods:</label>
									<div class="input-group">
										<span class="input-group-addon">{{ $currencySymbol }}</span>
										{{ Form::text('total_goods', $totalGoods, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
								<td>
									<label>Total VAT:</label>
									<div class="input-group">
										<span class="input-group-addon">{{ $currencySymbol }}</span>
										{{ Form::text('total_vat', $totalVat, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
								<td>
									<label>Total Gross:</label>
									<div class="input-group">
										<span class="input-group-addon">{{ $currencySymbol }}</span>
										{{ Form::text('total_gross', $totalGross, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
									</div>
									{{ Form::hidden('total', $analysisGoodsTotal) }}
								</td>
							</tr>
						</table>
					</div>					
				</div>
			</div>
			
			<div class="form-group">
				<div class="col-md-12">
					<h5>Are you sure you want to submit this purchase?</h5>
					<input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm Purchase' />					
				</div>
			</div>
			{!!Form::close()!!}

			{!! Form::open(['url' => $purchType == 'cash' ? 'sheets/purchases/cash' : 'sheets/purchases/credit', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
				{{ Form::hidden('back_data', $backData) }}
			{!!Form::close()!!}

			<p>
				<a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter {{ $purchType }} purchase</a>
				<br />
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