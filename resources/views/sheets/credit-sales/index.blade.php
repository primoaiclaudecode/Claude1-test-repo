@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Credit Sales</strong>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif

			@if(Session::has('error_message'))
				<div class="alert alert-danger"><em> {!! session('error_message') !!}</em></div>
			@endif

			{!! Form::open(['url' => 'sheets/credit-sales/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'credit_sales']) !!}
			{{ Form::hidden('sheet_id', $sheetId) }}

			<div class="form-group">
				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Unit Name:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					{!! Form::select('unit_id', $userUnits, $selectedUnit, ['id' => 'unit_id', 'class'=>'form-control', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus']) !!}
					<span id="unit_name_span" class="error_message"></span>
				</div>

				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Sale Date:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					<div class="input-group">
						{{ Form::text('sale_date', $saleDate, array('id' => 'sale_date', 'class' => 'form-control text-right cursor-pointer', 'tabindex' => 2, 'readonly' => '')) }}
						<span class="input-group-addon cursor-pointer" id="sale_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
					</div>
					<span id="sale_date_span" class="error_message"></span>
					<span id="closed_unit" class="error_message" style="display: none;"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Credit Ref. (Name):</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					{{ Form::text('credit_reference', $creditReference, array('class' => 'form-control text-mobile-right', 'tabindex' => 3, 'id' => 'credit_reference')) }}
					<span id="credit_reference_span" class="error_message"></span>
				</div>

				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Docket Number:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					{{ Form::text('docket_number', $docketNumber, array('id' => 'docket_number', 'class' => 'form-control text-right', 'tabindex' => 4)) }}
					<span id="docket_number_span" class="error_message"></span>
				</div>
			</div>
				
			<div class="form-group">
				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Cost Centre/P.O.#:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					{{ Form::text('cost_centre', $costCentre, array('id' => 'cost_centre', 'class' => 'form-control text-mobile-right', 'tabindex' => 5)) }}
					<span id="cost_centre_span" class="error_message"></span>
				</div>
			</div>

			<h3 class="margin-top-35">Net Extensions:</h3>

			@foreach($saleTaxCodes as $taxCodeId => $taxCode)
				<div class="form-group tax-row" data-rate="{{ $taxCode['rate'] }}">
					{{ Form::hidden('tax_code[]', $taxCodeId) }}

					<label class="col-xs-4 col-md-2 control-label custom-labels">Gross {{ $taxCode['tax'] }}:</label>
					<div class="col-xs-8 col-md-2">
						<div class="input-group margin-bottom-15">
							<span class="input-group-addon">&euro;</span>
							{{ Form::text('gross[]', $taxCode['gross'], array('class' => 'form-control text-right currencyFields')) }}
						</div>
					</div>

					<label class="col-xs-4 col-md-2 control-label custom-labels">Net {{ $taxCode['tax'] }}:</label>
					<div class="col-xs-8 col-md-2">
						<div class="input-group margin-bottom-15">
							<span class="input-group-addon">&euro;</span>
							{{ Form::text('goods[]', 0, array('class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
						</div>
					</div>

					<label class="col-xs-4 col-md-2 control-label custom-labels">VAT {{ $taxCode['tax'] }}:</label>
					<div class="col-xs-8 col-md-2">
						<div class="input-group margin-bottom-15">
							<span class="input-group-addon">&euro;</span>
							{{ Form::text('vat[]', 0, array('class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
						</div>
					</div>
				</div>
			@endforeach

			<div class="form-group">
				<label class="col-xs-4 col-md-2 control-label custom-labels">Total Gross:</label>
				<div class="col-xs-8 col-md-2">
					<div class="input-group margin-bottom-15">
						<span class="input-group-addon">&euro;</span>
						{{ Form::text('gross_total', null, array('class' => 'form-control text-right auto_calc', 'id' => 'gross_total', 'readonly' => 'readonly')) }}
					</div>
				</div>

				<label class="col-xs-4 col-md-2 control-label custom-labels">Total Net:</label>
				<div class="col-xs-8 col-md-2">
					<div class="input-group margin-bottom-15">
						<span class="input-group-addon">&euro;</span>
						{{ Form::text('goods_total', null, array('id' => 'goods_total', 'class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
					</div>
				</div>

				<label class="col-xs-4 col-md-2 control-label custom-labels">Total VAT:</label>
				<div class="col-xs-8 col-md-2">
					<div class="input-group margin-bottom-15">
						<span class="input-group-addon">&euro;</span>
						{{ Form::text('vat_total', null, array('id' => 'vat_total', 'class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
					</div>
				</div>

				<div class="table-responsive margin-top-25 col-md-4 div_sales_total hidden_element padding-left-0 padding-right-0">
					<table class="table">
						<tr>
							<td class="border-top-0 padding-0">
								<h2>Sales Total</h2>
							</td>
							<td class="border-top-0 padding-0" align="right">
								<h2>
									<span id="sales_total"></span>
								</h2>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="form-group set-margin-left-0 set-margin-right-0">
				<input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-35" name='submit' value='Add Sale'/>
			</div>
			{!!Form::close()!!}
		</section>
	</section>
@stop

@section('scripts')
	<style>
		#sales_total {
			margin-left: 10px;
		}
	</style>
	
	<script src="{{ elixir('js/format_number.js') }}"></script>
	<script src="{{ elixir('js/cred_sales_js.js') }}"></script>
@stop