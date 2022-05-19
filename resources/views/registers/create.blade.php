@extends('layouts/dashboard_master')

@section('content')
	@if(isset($register))
		{!! Form::model($register, ['method' => 'PATCH', 'route' => ['registers.update', $register->reg_management_id], 'id' => 'save_register']) !!}
	@else
		{!! Form::open(['url' => 'registers', 'id' => 'save_register']) !!}
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
				{!! Form::label('unit_id', 'Unit Name:') !!}
				{!! Form::select('unit_id', $units, isset($register) ? $register->unit_id : 0, ['class'=>'form-control','autofocus' => 'autofocus','placeholder' => 'Select Unit']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('reg_number', 'Reg Number:') !!}
				{!! Form::text('reg_number', null, ['class'=>'form-control', 'id' => 'reg_number']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('currency_id', 'Currency:') !!}
				{!! Form::select('currency_id', $currencies, isset($register) ? $register->currency_id : 0, ['class'=>'form-control', 'id' => 'currency_id', 'placeholder' => 'Select currency']); !!}
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-xs-12 col-sm-2">
						<a href="/registers" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
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
        $('#save_register').on('submit', function () {
            $('.error_message').remove();

            if (!$('#unit_id').val()) {
                $('#unit_id').focus();

                $('#unit_id')
                    .after(
                        $('<span />').addClass('error_message').text('The Unit Name field is required.')
                    )

                return false;
            }

            if (!$('#reg_number').val()) {
                $('#reg_number').focus();

                $('#reg_number')
                    .after(
                        $('<span />').addClass('error_message').text('The Reg Number field is required')
                    )

                return false;
            }
            
            if (!$('#currency_id').val()) {
                $('#currency_id').focus();

                $('#currency_id')
                    .after(
                        $('<span />').addClass('error_message').text('The Currency field is required')
                    )

                return false;
            }

			if ($(this).hasClass('processing')) {
				return false;
			}

			$(this).addClass('processing');

            return true;
        })
	</script>
@stop