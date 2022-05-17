@extends('layouts/dashboard_master')

@section('content')
	{!! Form::model($city,['method' => 'PATCH','route'=>['cities.update',$city->id]]) !!}
		<section class="panel">
			<header class="panel-heading"><strong>Update City</strong></header>
			<div class="panel-body">
				@if($errors->any())
					<div class="alert alert-danger">
						<em>
							@foreach($errors->all() as $error)
								{{ $error }}
							@endforeach			
						</em>
					</div>
				@endif				
				<div class="form-group">
					{!!Form::label('name','Name')!!}
						{!!Form::text('name',$city->name,['class'=>'form-control'])!!}		
				</div>
				<div class="form-group">
					{!!Form::label('is_active','Status')!!}
					{!!Form::select('is_active',$status,$city->is_active,['class'=>'form-control'])!!}
				</div>
				<div class="form-group">
					{!! Form::submit('Update',array('class'=>'btn btn-primary btn-block')) !!}
				</div>				
			</div>
		</section>
	{!!Form::close()!!}
@stop