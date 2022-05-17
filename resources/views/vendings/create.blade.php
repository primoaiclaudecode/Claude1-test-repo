@extends('layouts/dashboard_master')

@section('content')
	@if(isset($vending))
    	{!! Form::model($vending, ['method' => 'PATCH', 'route' => ['vendings.update', $vending->vend_management_id]]) !!}
	@else
	    {!! Form::open(['url' => 'vendings']) !!}
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
			        {!! Form::label('vend_name', 'Vending Machine Name:') !!}
			        {!! Form::text('vend_name', null, ['class'=>'form-control']) !!}
			    </div>				
			    <div class="form-group">
			        {!! Form::label('machine_brand', 'Machine Brand:') !!}
			        {!! Form::select('machine_brand', $machine_brand_arr, isset($selectedMachineBrand) ? $selectedMachineBrand : 'Select Machine Brand', ['class'=>'form-control','placeholder' => 'Select Machine Brand']) !!}
			    </div>
				<div class="form-group">
			        {!! Form::label('machine_contents', 'Machine Contents:') !!}
			        {!! Form::select('machine_contents', $machine_contents_arr, isset($selectedMachineContents) ? $selectedMachineContents : 'Select Machine Contents', ['class'=>'form-control','placeholder' => 'Select Machine Contents']) !!}
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
		// Prevent double submit
		$('form').on('submit', function () {
			if ($(this).hasClass('processing')) {
				return false;
			}

			$(this).addClass('processing');
		});
	</script>
@stop