@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Vending Sales Confirmation</strong>
		</header>

		<section class="dataTables-padding">
			{!! Form::open(['url' => 'sheets/vending-sales/post', 'class' => 'form-horizontal form-bordered']) !!}
			{{ Form::hidden('sheet_id', $sheetId, array('id' => 'sheet_id')) }}
			{{ Form::hidden('currency_id', $currencyId, array('id' => 'sheet_id')) }}
			{{ Form::hidden('good_items', $goodItems) }}

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
									<label>Sale Date:</label>
									{{ Form::text('sale_date', $saleDate, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
								<td>
									<label>Vending Machine:</label>
									{{ Form::text('vend_name', $vendName, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('vend_id', $vendId) }}
								</td>
							</tr>
							<tr>
								<td>
									<label>Opening:</label>
									<div class="input-group">
										<span class="input-group-addon">{{ $currencySymbol }}</span>
										{{ Form::text('opening', $opening, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
								<td>
									<label>Closing:</label>
									<div class="input-group">
										<span class="input-group-addon">{{ $currencySymbol }}</span>
										{{ Form::text('closing', $closing, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
								<td>
									<label>Till Number:</label>
									{{ Form::text('till_number_name', $tillNumber, array('class' => 'form-control', 'readonly' => 'readonly')) }}
									{{ Form::hidden('till_number_id', $tillNumberId) }}
								</td>
							</tr>
							<tr>
								<td>
									<label>Z Read Number:</label>
									{{ Form::text('z_read', $zRead, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
								<td>
									<label>Cash Count:</label>
									<div class="input-group">
										<span class="input-group-addon">{{ $currencySymbol }}</span>
										{{ Form::text('cash', $cash, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
								<td>&nbsp;</td>
							</tr>
							@foreach($taxes as $tax)
								<tr>
									<td>
										<label>{{ $tax['title'] }} Goods:</label>
									</td>
									<td>
										<div class="input-group">
											<span class="input-group-addon">{{ $currencySymbol }}</span>
											{{ Form::text('taxes[]', number_format($tax['amount'], 2, '.', ','), array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
										</div>
									</td>
									<td>&nbsp;</td>
								</tr>
							@endforeach
							<tr>
								<td>
									<label>Total:</label>
								</td>
								<td>
									<div class="input-group">
										<span class="input-group-addon">{{ $currencySymbol }}</span>
										{{ Form::text('total', $total, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
								<td>&nbsp;</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			
			<div class="form-group">
				<div class="col-md-12">
					<h5>Are you sure you want to confirm this sale</h5>
					<input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm Sale'/>
				</div>
			</div>
			{!!Form::close()!!}

			{!! Form::open(['url' => 'sheets/vending-sales', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
				{{ Form::hidden('back_data', $backData) }}
			{!!Form::close()!!}

			<p>
				<a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter vending sales</a>
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