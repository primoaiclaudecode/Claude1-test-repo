@extends('layouts/dashboard_master')

@section('content')
	@if(isset($register))
    	{!! Form::model($register, ['method' => 'PATCH', 'route' => ['registers.update', $register->reg_management_id]]) !!}
	@else
	    {!! Form::open(['url' => 'registers']) !!}
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
			        {!! Form::label('unit_name', 'Unit Name:') !!}
			        {!! Form::select('unit_name', $units, isset($selectedUnit) ? $selectedUnit : 'Select Unit', ['class'=>'form-control','autofocus' => 'autofocus','placeholder' => 'Select Unit']) !!}
			    </div>
				<div class="form-group">
			        {!! Form::label('reg_number', 'Reg Number:') !!}
			        {!! Form::text('reg_number', null, ['class'=>'form-control']) !!}
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
		// Prevent double submit
		$('form').on('submit', function () {
			if ($(this).hasClass('processing')) {
				return false;
			}

			$(this).addClass('processing');
		});
	</script>
@stop