@extends('layouts/dashboard_master')

@section('content')
	@if(isset($region))
    	{!! Form::model($region, ['method' => 'PATCH', 'route' => ['regions.update', $region->region_id,'autocomplete' => 'off']]) !!}
	@else
	    {!! Form::open(['url' => 'regions','autocomplete' => 'off']) !!}
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
			        {!! Form::label('unit_name', 'Region Name:') !!}
			        {!! Form::text('region_name',null,['class'=>'form-control','autofocus' => 'autofocus','autocomplete' => 'off']) !!}
			    </div>

				<div class="form-group">
					<div class="row">
						<div class="col-xs-12 col-sm-2">
							<a href="/regions" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
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
		$("#asp").focusin(function () {
		   if ($(this).has(document.activeElement).length != 0) {
		       $('#asp').css('border', '1px solid #797979');
		   }
		});

		$("#asp").focusout(function () {
		   if ($(this).has(document.activeElement).length == 0) {
		       $('#asp').css('border', '1px solid #e2e2e4');
		   }
		});

		// Prevent double submit
		$('form').on('submit', function () {
			if ($(this).hasClass('processing')) {
				return false;
			}

			$(this).addClass('processing');
		});
	</script>
@stop