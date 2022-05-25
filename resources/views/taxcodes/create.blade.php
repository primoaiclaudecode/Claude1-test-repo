@extends('layouts/dashboard_master')

@section('content')
	@if(isset($taxCode))
		{!! Form::model($taxCode, ['method' => 'PATCH', 'route' => ['taxcodes.update', $taxCode->tax_code_ID]]) !!}
	@else
		{!! Form::open(['url' => 'taxcodes']) !!}
	@endif

	<section class="panel">
		<header class="panel-heading"><strong>{{ $heading }}</strong></header>
		<div class="panel-body">
			@if(count($errors) > 0)
				<div class="alert alert-danger">
					<em>
						<ul>
							@foreach($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</em>
				</div>
			@endif
			<div class="form-group">
				{!! Form::label('tax_code_title', 'Tax Code Title:') !!}
				{!! Form::text('tax_code_title',null,['class'=>'form-control','autofocus' => 'autofocus']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('tax_rate', 'Tax Rate:') !!}
				{!! Form::text('tax_rate',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('tax_code_display_rate', 'Tax Code Display Rate:') !!}
				{!! Form::text('tax_code_display_rate',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('tax_code_display_rate', 'Tax Code Display Rate:') !!}
				{!! Form::select('currency_id', $currencies, null, ['class'=>'form-control', 'placeholder' => 'Choose',]) !!}
			</div>
			<div class="form-group">
				<label class="normal-font-weight margin-bottom-3">{!! Form::checkbox('cash_purch', 1, isset($taxCode->cash_purch) && $taxCode->cash_purch == 1 ? true : !isset($taxCode->cash_purch) ? true : false) !!}
					Cash Purch
				</label>
			</div>

			<div class="form-group">
				<label class="normal-font-weight">{!! Form::checkbox('credit_purch', 1, isset($taxCode->credit_purch) && $taxCode->credit_purch == 1 ? true : !isset($taxCode->credit_purch) ? true : false) !!}
					Credit Purch
				</label>
			</div>

			<div class="form-group">
				<label class="normal-font-weight">{!! Form::checkbox('credit_sales', 1, isset($taxCode->credit_sales) && $taxCode->credit_sales == 1 ? true : !isset($taxCode->credit_sales) ? true : false) !!}
					Credit Sales
				</label>
			</div>

			<div class="form-group">
				<label class="normal-font-weight margin-top-3">
					{!! Form::checkbox('vending_sales', 1, isset($taxCode->vending_sales) && $taxCode->vending_sales == 1 ? true : !isset($taxCode->vending_sales) ? true : false, ['id' => 'toggle_vending_sales']) !!}
					Vending Sales
				</label>
			</div>

			<div id="vending_sales_settings" class="form-group margin-left-20" style="display: none">
				<h4>Select Net Ext:</h4>
				@foreach($vendingSalesGoods as $item)
					<div class="form-group">
						<label class="normal-font-weight">
							{!! Form::checkbox('net_ext[]', $item['id'], $item['selected']) !!}
							{{ $item['name'] }}
						</label>
					</div>
				@endforeach
			</div>

			<div class="form-group">
				<div class="row">
					<div class="col-xs-12 col-sm-2">
						<a href="/taxcodes" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
					</div>

					<div class="col-xs-12 col-sm-10">
						{!! Form::submit($btn_caption,array('class'=>'btn btn-primary btn-block')) !!}
					</div>
				</div>
			</div>
		</div>
	</section>
	{!!Form::close()!!}
@stop

@section('scripts')
	<script type="text/javascript" class="init">
        $(document).ready(function () {
            if ($('#toggle_vending_sales').prop('checked')) {
                $('#vending_sales_settings').show('slow');
            } else {
                $('#vending_sales_settings').hide('slow');
            }

            $('#toggle_vending_sales').on('click', function () {
                if ($(this).prop('checked')) {
                    $('#vending_sales_settings').show('slow');
                } else {
                    $('#vending_sales_settings').hide('slow');
                }
            });

			// Prevent double submit
			$('form').on('submit', function () {
				if ($(this).hasClass('processing')) {
					return false;
				}

				$(this).addClass('processing');
			});
		});
	</script>
@stop