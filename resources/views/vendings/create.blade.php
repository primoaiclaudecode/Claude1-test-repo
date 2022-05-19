@extends('layouts/dashboard_master')

@section('content')
	@if(isset($vending))
		{!! Form::model($vending, ['method' => 'PATCH', 'route' => ['vendings.update', $vending->vend_management_id], 'id' => 'save_vending']) !!}
	@else
		{!! Form::open(['url' => 'vendings', 'id' => 'save_vending']) !!}
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
				{!! Form::select('unit_id', $units, isset($vending) ? $vending->unit_id : 0, ['class'=>'form-control', 'id' => 'unit_id', 'autofocus' => 'autofocus','placeholder' => 'Select Unit']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('vend_name', 'Vending Machine Name:') !!}
				{!! Form::text('vend_name', null, ['class'=>'form-control', 'id' => 'vend_name']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('machine_brand', 'Machine Brand:') !!}
				{!! Form::select('machine_brand', $machine_brand_arr, isset($vending) ? $vending->machine_brand : 0, ['class'=>'form-control','placeholder' => 'Select Machine Brand']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('machine_contents', 'Machine Contents:') !!}
				{!! Form::select('machine_contents', $machine_contents_arr, isset($vending) ? $vending->machine_contents : 0, ['class'=>'form-control','placeholder' => 'Select Machine Contents']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('currency_id', 'Currency:') !!}
				{!! Form::select('currency_id', $currencies, isset($vending) ? $vending->currency_id : 0, ['class'=>'form-control', 'id' => 'currency_id', 'placeholder' => 'Select currency']); !!}
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-xs-12 col-sm-2">
						<a href="/vendings" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
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
        $('#save_vending').on('submit', function () {
            $('.error_message').remove();

            if (!$('#unit_id').val()) {
                $('#unit_id').focus();

                $('#unit_id')
                    .after(
                        $('<span />').addClass('error_message').text('The Unit Name field is required.')
                    )

                return false;
            }

            if (!$('#vend_name').val()) {
                $('#vend_name').focus();

                $('#vend_name')
                    .after(
                        $('<span />').addClass('error_message').text('The Vending Machine Name field is required')
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