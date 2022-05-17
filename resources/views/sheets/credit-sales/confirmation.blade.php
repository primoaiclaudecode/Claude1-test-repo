@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Credit Sales Confirmation</strong>
		</header>

		<section class="dataTables-padding">
			{!! Form::open(['url' => 'sheets/credit-sales/post', 'class' => 'form-horizontal form-bordered']) !!}
			{{ Form::hidden('sheet_id', $sheetId) }}
			{{ Form::hidden('sale_items', $saleItems) }}

			<div class="form-group margin-bottom-0 margin-left-0 margin-right-0">
				<div class="clearfix"></div>
				<div class="col-md-12 padding-left-0 padding-right-0 border-top-0">
					<div class="responsive-content">
						<table id="cash_purchases_tbl" class="table table-bordered table-striped table-small">
							<tr>
								<td>
									<label>Unit Name:</label>
									{{ Form::text('unit_name', $unitName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
									{{ Form::hidden('unit_id', $unitId) }}
								</td>
								<td>
									<label>Docket Number:</label>
									{{ Form::text('docket_number', $docketNumber, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
								<td>
									<label>Sale Date:</label>
									{{ Form::text('sale_date', $saleDate, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
							</tr>
							<tr>
								<td>
									<label>Credit Reference (Name):</label>
									{{ Form::text('credit_reference', $creditReference, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
								<td>
									<label>Cost Centre:</label>
									{{ Form::text('cost_centre', $costCentre, array('class' => 'form-control', 'readonly' => 'readonly')) }}
								</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>
									<label>Total Goods:</label>
									<div class="input-group">
										<span class="input-group-addon">&euro;</span>
										{{ Form::text('total_goods', $goodsTotal, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
								<td>
									<label>Total VAT:</label>
									<div class="input-group">
										<span class="input-group-addon">&euro;</span>
										{{ Form::text('total_vat', $vatTotal, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
								<td>
									<label>Total Gross:</label>
									<div class="input-group">
										<span class="input-group-addon">&euro;</span>
										{{ Form::text('total_gross', $grossTotal, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
							</tr>
						</table>
					</div>					
				</div>
			</div>

			<div class="form-group">
				<div class="col-md-12">
					<h5>Are you sure you want to add this sale?</h5>
					<input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm Sale'/>
				</div>
			</div>
			{!!Form::close()!!}

			{!! Form::open(['url' => 'sheets/credit-sales', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
				{{ Form::hidden('back_data', $backData) }}
			{!!Form::close()!!}

			<p>
				<a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter credit sales</a>
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