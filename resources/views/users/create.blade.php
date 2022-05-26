@extends('layouts/dashboard_master')

@section('content')
	@if(isset($user))
    	{!! Form::model($user, ['method' => 'PATCH', 'route' => ['users.update', $user->user_id]]) !!}
	@else
	    {!! Form::open(['url' => 'users']) !!}
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
			        {!! Form::label('username', 'Username:') !!}
			        {!! Form::text('username',null,['class'=>'form-control','autofocus' => 'autofocus', isset($user) ? 'readonly' : '']) !!}
			    </div>
			    <div class="form-group">
			        {!! Form::label('password', 'Password:') !!}
			        @if(isset($user))
			        	{!! Form::text('password',$userPassword,['class'=>'form-control']) !!}
			        @else
			        	{!! Form::password('password',['class'=>'form-control', 'autocomplete'=>'new-password']) !!}
			        @endif
			    </div>
			    <div class="form-group">
			        {!! Form::label('user_first', 'First Name:') !!}
			        {!! Form::text('user_first',null,['class'=>'form-control']) !!}
			    </div>
			    <div class="form-group">
			        {!! Form::label('user_last', 'Last Name:') !!}
			        {!! Form::text('user_last',null,['class'=>'form-control']) !!}
			    </div>
			    <div class="form-group">
			        {!! Form::label('contact_number', 'Contact Number:') !!}
			        {!! Form::text('contact_number',null,['class'=>'form-control']) !!}
			    </div>
			    <div class="form-group">
			        {!! Form::label('user_email', 'E-Mail Address:') !!}
			        {!! Form::text('user_email',null,['class'=>'form-control']) !!}
			    </div>
					<div class="form-group">
						{!! Form::label('status', 'User status:') !!}
						{!! Form::select('status', $statuses, $user->status, ['class'=>'form-control', 'id'=>'status']); !!}
					</div>
			    <div class="form-group">
			        {!! Form::label('user_group_member', 'User Group Member:') !!}
			        {!! Form::select('user_group_member[]', $userGroups, isset($selectedUserGroups) ? $selectedUserGroups : null, ['class'=>'form-control', 'multiple' => 'multiple', 'size' => 7,'id'=>'user_group_member']); !!}
			    </div>
			    <div class="form-group">
			        {!! Form::label('unit_member', 'Unit Member:') !!}
			        {!! Form::select('unit_member[]', $unitMembers, isset($selectedUnitMembers) ? $selectedUnitMembers : null, ['class'=>'form-control','id'=>'unit_member', 'multiple' => true, 'size' => 7]); !!}
			    </div>
			    <div class="form-group">
			        {!! Form::label('ops_mgr', 'Operations Manager:') !!}
			        {!! Form::select('ops_mgr[]', $operationsManager, isset($selectedOperationsManager) ? $selectedOperationsManager : null, ['class'=>'form-control','id'=>'ops_mgr', 'multiple' => true, 'size' => 7]); !!}
			    </div>
			    <div class="form-group">
			        {!! Form::label('ops_group_member', 'Region:') !!}
			        {!! Form::select('ops_group_member[]', $opsGroupMember, isset($selectedOpsGroupMember) ? $selectedOpsGroupMember : null, ['class'=>'form-control', 'id'=>'ops_group_member', 'multiple' => true, 'size' => 7]); !!}
			    </div>
				
				<div class="form-group">
					<div class="row">
						<div class="col-xs-12 col-sm-2">
							<a href="/users" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
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
   $(document).ready(function() {
      $('#user_group_member').select2();
      $('#unit_member').select2();
      $('#ops_mgr').select2();
      $('#ops_group_member').select2();

	   // Prevent double submit
	   $('form').on('submit', function () {
		   if ($(this).hasClass('processing')) {
			   return false;
		   }

		   $(this).addClass('processing');
	   });
   })
   </script>
@stop