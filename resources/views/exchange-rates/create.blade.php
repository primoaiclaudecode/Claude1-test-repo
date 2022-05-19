@extends('layouts/dashboard_master')

@section('content')
	@if(isset($id))
		{!! Form::model(null, ['method' => 'PATCH', 'route' => ['exchange-rates.update', $id], 'id' => 'save_rate', 'autocomplete' => 'off']) !!}
	@else
		{!! Form::open(['url' => 'exchange-rates', 'id' => 'save_rate', 'autocomplete' => 'off']) !!}
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
				<div class="input-group">
					{{ Form::text('date', $date, array('id' => 'date', 'class' => 'form-control text-right', 'readonly' => '')) }}
					<span class="input-group-addon cursor-pointer" id="sale_date_icon">
						<i class="fa fa-calendar"></i>
					</span>
				</div>
			</div>

			@foreach($currencies as $currencyId => $currencyName)
				<div class="form-group">
					{{ Form::hidden('currencies[]', $currencyId) }}
					{!! Form::label('exchange_rates[]', $currencyName) !!}
					{!! Form::text('exchange_rates[]', isset($exchangeRates) ? $exchangeRates[$currencyId] : '' ,['class'=>'form-control', 'autocomplete' => 'off']) !!}
				</div>
			@endforeach

			<div class="form-group">
				<div class="row">
					<div class="col-xs-12 col-sm-2">
						<a href="/exchange-rates" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
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
        $('#save_rate').on('submit', function () {
            $('.error_message').remove();

            var isValid = true;

            $('input[name="exchange_rates[]"]').each(function () {
                if (!$(this).val()) {
                    $(this).focus();

                    $(this)
                        .after(
                            $('<span />').addClass('error_message').text('Mandatory field.')
                        )

                    isValid = false;

                    return false;
                }
            });

            return isValid;
        })
	</script>
@stop
