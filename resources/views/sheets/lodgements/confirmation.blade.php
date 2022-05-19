@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Lodgements Confirmation</strong>
		</header>

		<section class="dataTables-padding">
			{!! Form::open(['url' => 'sheets/lodgements/post', 'class' => 'form-horizontal form-bordered']) !!}
			{{ Form::hidden('lodgement_id', $lodgementId, array('id' => 'lodgement_id')) }}
			{{ Form::hidden('remarks', $remarks) }}
			{{ Form::hidden('selected_cash_sales', $selectedCashSales) }}
			{{ Form::hidden('selected_vending_sales', $selectedVendingSales) }}
			{{ Form::hidden('lodgement_costs', $lodgementCosts) }}

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
									<label>Date:</label>
									{{ Form::text('date', $date, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
							</tr>
							<tr>
								<td>
									<label>Slip No.:</label>
									{{ Form::text('slip_number', $slipNumber, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
								<td>
									<label>G4S Bag Number:</label>
									{{ Form::text('bag_number', $bagNumber, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
							</tr>
							<tr>
								<td>
									<label>Cash:</label>
									<div class="input-group">
										<span class="input-group-addon">{{ $currencySymbol }}</span>
										{{ Form::text('cash_total', $cashTotal, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
								<td>
									<label>Coin:</label>
									<div class="input-group">
										<span class="input-group-addon">{{ $currencySymbol }}</span>
										{{ Form::text('coin_total', $coinTotal, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									</div>
								</td>
							</tr>
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
							</tr>
						</table>
					</div>					
				</div>
			</div>
			
			<div class="form-group">
				<div class="col-md-12">
					<h5>Are you sure you want to confirm this lodgement?</h5>
					<input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm Lodgement'/>
				</div>
			</div>
			{!!Form::close()!!}

			{!! Form::open(['url' => 'sheets/lodgements', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
				{{ Form::hidden('back_data', $backData) }}
			{!!Form::close()!!}

			<p>
				<a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter lodgements</a>
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