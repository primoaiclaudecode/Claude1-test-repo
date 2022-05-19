@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Vending Sales</strong>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif

			@if(Session::has('error_message'))
				<div class="alert alert-danger"><em> {!! session('error_message') !!}</em></div>
			@endif

			{!! Form::open(['url' => 'sheets/vending-sales/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'vend_sales']) !!}
			{{ Form::hidden('sheet_id', $sheetId, array('id' => 'sheet_id')) }}
			{{ Form::hidden('currency_id', 0, array('id' => 'currency_id')) }}

			<div class="form-group">
				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Unit Name:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					{!! Form::select('unit_id', $userUnits, $selectedUnit, ['id' => 'unit_id', 'class'=>'form-control margin-bottom-15', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus']) !!}
				</div>

				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Sale Date:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					<div class="input-group">
						{{ Form::text('sale_date', $saleDate, array('id' => 'sale_date', 'class' => 'form-control text-right cursor-pointer', 'tabindex' => 2, 'readonly' => '')) }}
						<span class="input-group-addon cursor-pointer" id="sale_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Machine Name:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
                    <span id="vending_machines">
	                    {!! Form::select('vend_machine_id', $vendMachines, $selectedVendMachine, ['id' => 'vend_machine_id', 'class'=>'form-control margin-bottom-15 ' . (count($vendMachines) === 0 ? 'hidden' : ''), 'tabindex' => 3]) !!}
                    </span>
				</div>
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Opening Reading:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					<div class="input-group margin-bottom-15">
						<span class="input-group-addon currency-symbol"></span>
						{{ Form::text('opening', $opening, array('class' => 'form-control text-right currencyFields', 'tabindex' => 4, 'id' => 'opening')) }}
					</div>
				</div>

				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Closing Reading:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					<div class="input-group margin-bottom-15">
						<span class="input-group-addon currency-symbol"></span>
						{{ Form::text('closing', $closing, array('class' => 'form-control text-right currencyFields', 'tabindex' => 5, 'id' => 'closing')) }}
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Cash Count:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					<div class="input-group">
						<span class="input-group-addon currency-symbol"></span>
						{{ Form::text('cash', $cash, array('class' => 'form-control text-right currencyFields', 'tabindex' => 6, 'id' => 'cash')) }}
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Reg Number:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					<div id="till_numbers" class="margin-bottom-15" style="min-height: 34px">
						@foreach($regNumbers as $index => $regNumber)
							<div class="radio">
								<label style="padding-left: 5px">
									<input type="radio" name="till_number_id" value="{{ $regNumber->reg_management_id }}" class="margin-right-8" {{ $index === 0 || $regNumber->reg_management_id == $selectedRegNumber ? 'checked' : '' }} >
										{{ $regNumber->reg_number }}
									</label>
							</div>
						@endforeach
					</div>
				</div>

				<label class="col-xs-12 col-sm-4 col-md-2 control-label custom-labels">Z Read Number:</label>
				<div class="col-xs-12 col-sm-8 col-md-4">
					{{ Form::text('z_read', $zRead, array('id' => 'z_read', 'class' => 'form-control text-right')) }}
				</div>
			</div>

			<div class="form-group margin-top-35">
				<h4 class="text-center">Goods:</h4>
				<div class="responsive-content">
					<table id="goodsTable" class="table table-bordered table-striped table-small">
						@foreach($goods as $id => $good)
							<tr class="good-row">
								<td width="17%">
									{{ $good['name'] }}
									{{ Form::hidden('good_id[]', $id) }}
								</td>

								<td width="17%">
									<div class="input-group">
										<span class="input-group-addon currency-symbol"></span>
										{{ Form::text('good_amount[]', $good['amount'], array('class' => 'form-control text-right currencyFields')) }}
									</div>
								</td>

								<td width="17%">
									<select name="good_tax_rate[]" class="form-control tax-rate">
										@if(count($good['taxCodes']) > 1)
											<option value="0">Choose:</option>
										@endif
										@foreach($good['taxCodes'] as $taxCode)
											<option value="{{ $taxCode['id'] }}" {{ $taxCode['id'] == $good['taxCode'] ? 'selected' : '' }}>
												{{ $taxCode['title'] }}
											</option>
										@endforeach
									</select>
								</td>
							</tr>
						@endforeach
						<tr>
							<td>
								<label class="col-xs-4 control-label custom-labels" style="padding-left: 0">Total:</label>
							</td>
							<td>
								<div class="input-group">
									<span class="input-group-addon currency-symbol"></span>
									{{ Form::text('total', $total, array('class' => 'form-control text-right auto_calc', 'id' => 'total', 'readonly' => 'readonly')) }}
								</div>
							</td>
							<td></td>
						</tr>
					</table>
				</div>				
			</div>

			<div class="form-group set-margin-left-0 set-margin-right-0">
				<span id="total_span" class="error_message"></span>
				<input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-25" name='submit' value='Add Sales'/>
			</div>
			{!!Form::close()!!}
		</section>
	</section>
@stop

@section('scripts')
	<script src="{{ elixir('js/format_number.js') }}"></script>
	<script src="{{ elixir('js/vend_sales_js.js') }}"></script>
@stop