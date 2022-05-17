@extends('layouts/dashboard_master')

@section('content')
	@if(isset($supplier))
		{!! Form::model($supplier, ['method' => 'PATCH', 'route' => ['suppliers.update', $supplier->suppliers_id]]) !!}
	@else
		{!! Form::open(['url' => 'suppliers']) !!}
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
				{!! Form::label('supplier_name', 'Name:') !!}
				{!! Form::text('supplier_name',null,['class'=>'form-control','autofocus' => 'autofocus']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('supplier_address', 'Address:') !!}
				{!! Form::text('supplier_address',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('supplier_details', 'Details:') !!}
				{!! Form::textarea('supplier_details', null, ['class' => 'form-control', 'rows' => 3]) !!}
			</div>
			<div class="form-group">
				{!! Form::label('supplier_phone', 'Phone:') !!}
				{!! Form::text('supplier_phone',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('supplier_fax', 'Fax:') !!}
				{!! Form::text('supplier_fax',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('sage_account_number', 'Sage Account Number:') !!}
				{!! Form::text('sage_account_number',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('account_number', 'Account Number:') !!}
				{!! Form::text('account_number',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('accounts_contact', 'Account Contact:') !!}
				{!! Form::text('accounts_contact',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('accounts_email', 'Accounts Query Email Address:') !!}
				{!! Form::text('accounts_email',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('remit_email', 'Remittance Email Address:') !!}
				{!! Form::text('remit_email',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-xs-12 col-sm-2">
						<a href="/suppliers" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
					</div>

					<div class="col-xs-12 col-sm-8">
						{!! Form::submit($btn_caption, array('class'=>'btn btn-primary btn-block')) !!}
					</div>

					<div class="col-xs-12 col-sm-2">
						@if(isset($supplier))
							{!! Form::submit('Save as new', array('name' => 'copy_supplier', 'class'=>'btn btn-success btn-block')) !!}
						@endif
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