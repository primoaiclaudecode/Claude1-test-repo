@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>{{ ucfirst($purchType) }} Purchases</strong>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif
			
			@if(Session::has('error_message'))
				<div class="alert alert-danger"><em> {!! session('error_message') !!}</em></div>
			@endif

			{!! Form::open(['url' => $purchType == 'cash' ? 'sheets/purchases/cash/confirmation' : 'sheets/purchases/credit/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'form_purchase']) !!}
			{{ Form::hidden('sheet_id', $sheetId, array('id' => 'sheet_id')) }}
			{{ Form::hidden('purch_type', $purchType, array('id' => 'purch_type')) }}

			<div class="form-group">
				<label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Unit Name:</label>
				<div class="col-xs-12 col-sm-9 col-md-4">
					{!! Form::select('unit_id', $userUnits, $selectedUnit, ['id' => 'unit_id', 'class'=>'form-control margin-bottom-15', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus']) !!}
				</div>

				@if ($purchType == 'cash')
					<label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Receipt Date:</label>
					<div class="col-xs-12 col-sm-9 col-md-4">
						<div class="input-group">
							{{ Form::text('receipt_date', $receiptDate, array('id' => 'receipt_date', 'class' => 'form-control text-right cursor-pointer', 'tabindex' => 3, 'readonly' => '')) }}
							<span class="input-group-addon cursor-pointer" id="receipt_date_icon">
                                <i class="fa fa-calendar"></i>
                            </span>
						</div>
					</div>
				@elseif ($purchType == 'credit')
					<label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Invoice Date:</label>
					<div class="col-xs-12 col-sm-9 col-md-4">
						<div class="input-group">
							{{ Form::text('invoice_date', $invoiceDate, array('id' => 'invoice_date', 'class' => 'form-control text-right cursor-pointer', 'tabindex' => 3, 'readonly' => '')) }}
							<span class="input-group-addon cursor-pointer" id="invoice_date_icon">
                                <i class="fa fa-calendar"></i>
                            </span>
						</div>
					</div>
				@endif
			</div>

			<div class="form-group">
				@if ($purchType == 'cash')
					<label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Supplier:</label>
					<div class="col-xs-12 col-sm-9 col-md-4">
						{{ Form::text('supplier', $supplier, array('id' => 'supplier', 'class' => 'form-control text-right margin-bottom-15', 'tabindex' => 4)) }}
					</div>

					<label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Reference Number:</label>
					<div class="col-xs-12 col-sm-9 col-md-4">
						{{ Form::text('reference_number', $referenceNumber, array('id' => 'reference_number', 'class' => 'form-control text-right', 'tabindex' => 4)) }}
					</div>
				@elseif ($purchType == 'credit')
					<label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Supplier:</label>
					<div class="col-xs-12 col-sm-9 col-md-4">
						{{ Form::text('supplier', $selectedSupplier, array('id' => 'supplier', 'class' => 'form-control margin-bottom-15 text-right ' . (count($suppliers) > 0 ? 'hidden' : ''), 'tabindex' => 4)) }}
						{!! Form::select('supplier_id', $suppliers, $selectedSupplier, ['id' => 'supplier_id', 'class'=>'form-control margin-bottom-15 ' . (count($suppliers) === 0 ? 'hidden' : ''), 'tabindex' => 4]) !!}
					</div>

					<label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Invoice Number:</label>
					<div class="col-xs-12 col-sm-9 col-md-4">
						{{ Form::text('invoice_number', $invoiceNumber, array('id' => 'invoice_number', 'class' => 'form-control text-right', 'tabindex' => 4)) }}
					</div>
				@endif
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Purchase Details:</label>
				<div class="col-xs-12 col-sm-9 col-md-10">
					<textarea name="purchase_details" id="purchase_details" class="form-control" tabindex="5">{{ $purchaseDetails }}</textarea>
				</div>
			</div>

			<div class="form-group margin-left-0 margin-right-0 margin-top-35">
				<div class="col-md-12 padding-left-0 padding-right-0">
					<div class="responsive-content">
						<table class="table table-bordered table-striped table-small">
							<thead>
							<tr>
								<th width="27%" class="text-center"><span class="net_ext_label">Net Ext</span></th>
								<th width="17%" class="text-center"><span class="goods_label">Goods</span></th>
								<th width="17%" class="text-center"><span class="tax_rate_label">Tax Rate</span></th>
								<th width="17%" class="text-center"><span class="vat_label">VAT</span></th>
								<th width="17%" class="text-center"><span class="gross_label">Gross</span></th>
								<th width="5%">&nbsp;</th>
							</tr>
							</thead>
						</table>

						<table id="datatable" class="table table-bordered table-striped table-small">
							@forelse ($purchaseItems as $purchaseItem)
								<tr class="data-table-row">
									<td width="27%">
										{!! Form::select('net_ext[]', $netExt, $purchaseItem['netExt'], ['class'=>'form-control', 'placeholder' => 'Choose']) !!}
									</td>

									<td width="17%">
										<div class="input-group">
											<span class="input-group-addon">&euro;</span>
											{{ Form::text('goods[]', $purchaseItem['goods'], array('class' => 'form-control text-right currencyFields', 'onchange' => 'calculations()')) }}
										</div>
									</td>

									<td width="17%">
										{!! Form::select('tax_rate[]', $taxCodeTitles, $purchaseItem['tax'], ['class'=>'form-control', 'placeholder' => 'Choose', 'dir' => 'rtl', 'onchange' => 'calculations()']) !!}
									</td>

									<td width="17%">
										<div class="input-group">
											<span class="input-group-addon">&euro;</span>
											{{ Form::text('vat[]', $purchaseItem['vat'], array('class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
										</div>
									</td>

									<td width="17%">
										<div class="input-group">
											<span class="input-group-addon">&euro;</span>
											{{ Form::text('gross[]', $purchaseItem['gross'], array('class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
										</div>
									</td>

									<td width="5%" id="b_drop_td">
										<a href="" class="delete-line">{!! Html::image('/img/b_drop.png', '', array('id' => 'b_drop')) !!}</a>
									</td>
								</tr>
							@empty
								<tr class="data-table-row">
									<td width="27%">
										{!! Form::select('net_ext[]', $netExt, 0, ['class'=>'form-control', 'placeholder' => 'Choose']) !!}
									</td>
									<td width="17%">
										<div class="input-group">
											<span class="input-group-addon">&euro;</span>
											{{ Form::text('goods[]', '0.00', array('class' => 'form-control text-right currencyFields', 'onchange' => 'calculations()')) }}
										</div>
									<td width="17%">
									{!! Form::select('tax_rate[]', $taxCodeTitles, null, ['class'=>'form-control', 'placeholder' => 'Choose', 'dir' => 'rtl', 'onchange' => 'calculations()']) !!}
									<td width="17%">
										<div class="input-group">
											<span class="input-group-addon">&euro;</span>
											{{ Form::text('vat[]', '0.00', array('class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
										</div>
									</td>
									<td width="17%">
										<div class="input-group">
											<span class="input-group-addon">&euro;</span>
											{{ Form::text('gross[]', '0.00', array('class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
										</div>
									</td>
									<td width="5%" id="b_drop_td">
										<a href="" class="delete-line">{!! Html::image('/img/b_drop.png', '', array('id' => 'b_drop')) !!}</a>
									</td>
								</TR>
							@endforelse
						</table>

						<table class="table table-bordered table-striped table-small">
							<tr>
								<td width="27%">
									<input id="add_line" class="btn btn-primary" type="button" value="add line"/>
								</td>
								<td width="17%">
									<div class="input-group"><span class="input-group-addon">&euro;</span>
										<input name="goods_total" type="text" class="auto_calc text-right form-control" id="goods_total" value="0.00"
										       readonly="readonly"/>
									</div>
								</td>
								<td width="17%">&nbsp;</td>
								<td width="17%">
									<div class="input-group"><span class="input-group-addon">&euro;</span>
										<input name="vat_total" type="text" class="auto_calc text-right form-control" id="vat_total" value="0.00"
										       readonly="readonly"/>
									</div>
								</td>
								<td width="17%">
									<div class="input-group"><span class="input-group-addon">&euro;</span>
										<input name="gross_total" type="text" class="auto_calc text-right form-control" id="gross_total" value="0.00"
										       readonly="readonly"/>
									</div>
								</td>
								<td width="5%"></td>
							</tr>
						</table>
					</div>
				</div>

				<div class="col-md-12 col-lg-8 margin-top-25 padding-left-0 padding-right-0">
					<div class="responsive-content">
						<table id="rates_tbl" class="table table-bordered table-striped table-mobile">
							<thead>
							<tr>
								<th></th>
								<th class="text-center">Goods</th>
								<th class="text-center">VAT</th>
								<th class="text-center">Gross</th>
							</tr>
							</thead>
							<tbody>

							@foreach($taxCodeTitles as $id => $title)
								<tr id="tax_row_{{ $id }}" data-rate="{{ $taxCodeRates[$id] }}">
									<td class="vertical-align-middle">{{ $title }}</td>
									<td>
										<div class="input-group"><span class="input-group-addon">&euro;</span>
											<input name="analysis_goods[]" type="text" class="auto_calc text-right form-control" value="0.00"
											       readonly="readonly"/>
										</div>
									</td>
									<td>
										<div class="input-group"><span class="input-group-addon">&euro;</span>
											<input name="analysis_vat[]" type="text" class="auto_calc text-right form-control" value="0.00"
											       readonly="readonly"/>
										</div>
									</td>
									<td>
										<div class="input-group"><span class="input-group-addon">&euro;</span>
											<input name="analysis_gross[]" type="text" class="auto_calc text-right form-control" value="0.00"
											       readonly="readonly"/>
										</div>
									</td>
								</tr>
							@endforeach

							<tr id="tax_row_total">
								<td class="vertical-align-middle"><strong>total</strong></td>
								<td>
									<div class="input-group">
										<span class="input-group-addon">&euro;</span>
										<input name="analysis_goods_total" type="text" class="auto_calc text-right form-control" id="analysis_goods_total"
										       value="0.00" readonly="readonly"/>
									</div>
								</td>
								<td>
									<div class="input-group">
										<span class="input-group-addon">&euro;</span>
										<input name="analysis_vat_total" type="text" class="auto_calc text-right form-control" id="analysis_vat_total"
										       value="0.00" readonly="readonly"/>
									</div>
								</td>
								<td>
									<div class="input-group">
										<span class="input-group-addon">&euro;</span>
										<input name="analysis_gross_total" type="text" class="auto_calc text-right form-control" id="analysis_gross_total"
										       value="0.00" readonly="readonly"/>
									</div>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="table-responsive col-md-8 div_invoice_total hidden_element padding-left-0 padding-right-0">
					<table class="table">
						<tr>
							<td class="border-top-0 padding-0"><h2>Invoice Total</h2></td>
							<td class="border-top-0 padding-0" align="right">
								<h2>
									<span id="invoice_total"></span>
								</h2>
							</td>
						</tr>
					</table>
				</div>

				<input type='submit' id="submit_btn" class="btn btn-primary btn-block button" name='submit' value='Add Purchase' tabindex='12'/>
			</div>
			{!!Form::close()!!}
		</section>
	</section>
@stop

@section('scripts')
	<style>
		#invoice_total {
			margin-left: 10px;
		}
	</style>
	
	<script src="{{ elixir('js/format_number.js') }}"></script>
	<script src="{{ elixir('js/purchases.js') }}"></script>
@stop
