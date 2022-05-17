@extends('layouts/dashboard_master')

@section('content')
	@if(isset($netExt))
		{!! Form::model($netExt, ['method' => 'PATCH', 'route' => ['netexts.update', $netExt->net_ext_ID]]) !!}
	@else
		{!! Form::open(['url' => 'netexts']) !!}
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
				{!! Form::label('net_ext', 'Net Ext:') !!}
				{!! Form::text('net_ext',null,['class'=>'form-control','autofocus' => 'autofocus']) !!}
			</div>

			<div class="form-group">
				{!! Form::label('nominal_code', 'Nominal Code:') !!}
				{!! Form::text('nominal_code',null,['class'=>'form-control']) !!}
			</div>

			<div class="form-group">
				<label class="normal-font-weight margin-bottom-3">{!! Form::checkbox('cash_purch', 1, isset($netExt->cash_purch) && $netExt->cash_purch == 1 ? true : !isset($netExt->cash_purch) ? true : false) !!}
					Cash Purch </label>
			</div>

			<div class="form-group">
				<label class="normal-font-weight margin-top-3">{!! Form::checkbox('credit_purch', 1, isset($netExt->credit_purch) && $netExt->credit_purch == 1 ? true : !isset($netExt->credit_purch) ? true : false) !!}
					Credit Purch </label>
			</div>

			<div class="form-group">
				<label class="normal-font-weight margin-top-3">{!! Form::checkbox('vending_sales', 1, isset($netExt->vending_sales) && $netExt->vending_sales == 1 ? true : !isset($netExt->vending_sales) ? true : false) !!}
					Vending Sales </label>
			</div>

			<div class="form-group">
				<div class="row">
					<div class="col-xs-12 col-sm-2">
						<a href="/netexts" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
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
	<script type="text/javascript">
		// Prevent double submit
		$('form').on('submit', function () {
			if ($(this).hasClass('processing')) {
				return false;
			}

			$(this).addClass('processing');
		});
	</script>
@stop