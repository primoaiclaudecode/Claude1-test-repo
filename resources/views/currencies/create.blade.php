@extends('layouts/dashboard_master')

@section('content')
	@if(isset($currency))
		{!! Form::model($currency, ['method' => 'PATCH', 'route' => ['currencies.update', $currency->currency_id], 'autocomplete' => 'off']) !!}
	@else
		{!! Form::open(['url' => 'currencies','autocomplete' => 'off']) !!}
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
				{!! Form::label('currency_name', 'Currency Name:') !!}
				{!! Form::text('currency_name',null,['class'=>'form-control', 'autofocus' => 'autofocus', 'autocomplete' => 'off']) !!}
			</div>

			<div class="form-group">
				{!! Form::label('currency_code', 'Currency Code:') !!}
				{!! Form::text('currency_code',null,['class'=>'form-control', 'autocomplete' => 'off']) !!}
			</div>

			<div class="form-group">
				{!! Form::label('currency_symbol', 'Currency Symbol:') !!}
				{!! Form::text('currency_symbol',null,['class'=>'form-control', 'autocomplete' => 'off']) !!}
			</div>

			<div class="form-group">
				<label class="normal-font-weight margin-top-3">
					{!! Form::checkbox('is_default', 1, null, ['id' => 'is_default']) !!}
					Use as default currency
				</label>
			</div>

			<div class="form-group">
				<div class="row">
					<div class="col-xs-12 col-sm-2">
						<a href="/currencies" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
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