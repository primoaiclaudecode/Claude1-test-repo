@extends('layouts/dashboard_master')

@section('content')
	@if(isset($unit))
		{!! Form::model($unit, ['method' => 'PATCH', 'route' => ['units.update', $unit->unit_id], 'id' => 'save_unit']) !!}
	@else
		{!! Form::open(['url' => 'units', 'id' => 'save_unit']) !!}
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
				{!! Form::text('unit_name',null,['class'=>'form-control','autofocus' => 'autofocus']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('status_id', 'Status:') !!}
				{!! Form::select('status_id', $statuses, null, ['class'=>'form-control', 'id' => 'status_id', 'placeholder' => 'Select status']); !!}
			</div>
			<div class="form-group">
				{!! Form::label('details', 'Details:') !!}
				{!! Form::text('details',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('location', 'Address (location):') !!}
				{!! Form::text('location',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('town', 'Town:') !!}
				{!! Form::text('town',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('county', 'County:') !!}
				{!! Form::text('county',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('contact_number', 'Contact Number:') !!}
				{!! Form::text('contact_number',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('client_contact_name', 'Client Contact Name:') !!}
				{!! Form::text('client_contact_name',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('client_contact_email', 'Client Contact Email:') !!}
				{!! Form::text('client_contact_email',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('email', 'Email:') !!}
				{!! Form::text('email',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('head_count', 'Head Count:') !!}
				{!! Form::text('head_count',null,['class'=>'form-control']) !!}
			</div>
			<div class="form-group">
				{!! Form::label('operations_group', 'Region:') !!}
				{!! Form::select('operations_group[]', $opsGroup, isset($selectedOpsGroup) ? $selectedOpsGroup : null, ['class'=>'form-control', 'multiple' => 'multiple', 'size' => 7, 'id' => 'operations_group']); !!}
			</div>
			<div class="form-group">
				{!! Form::label('operation_manager', 'Operations Manager:') !!}
				{!! Form::select('operation_manager[]', $operationManager, isset($selectedOperationManager) ? $selectedOperationManager : null, ['class'=>'form-control', 'multiple' => true, 'size' => 7, 'id' => 'operation_manager']); !!}
			</div>
			<div class="form-group">
				{!! Form::label('unit_manager', 'Unit Manager:') !!}
				{!! Form::select('unit_manager[]', $unitManager, isset($selectedUnitManager) ? $selectedUnitManager : null, ['class'=>'form-control', 'multiple' => true, 'size' => 7, 'id' => 'unit_manager']); !!}
			</div>
			<div class="form-group">
				{!! Form::label('currency_id', 'Currency:') !!}
				{!! Form::select('currency_id', $currencies, null, ['class'=>'form-control', 'id' => 'currency_id', 'placeholder' => 'Select currency']); !!}
			</div>
			@if(isset($associatedUsers))
				<div class="form-group">
					{!! Form::label('Associated Users', 'Associated Users:') !!}
					<table class="table associated-user table-hover table-bordered table-striped margin-bottom-5">
						<thead>
						<tr>
							<th>Username</th>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Contact #</th>
							<th class="email-address">Email</th>
						</tr>
						</thead>
						<tbody>
						@foreach($associatedUsers as $associatedUser)
							<tr>
								<td scope="row">{{ $associatedUser->username }}</td>
								<td>{{ $associatedUser->user_first }}</td>
								<td>{{ $associatedUser->user_last }}</td>
								<td>{{ $associatedUser->contact_number }}</td>
								<td class="email-address">{{ $associatedUser->user_email }}</td>
							</tr>
						@endforeach
						</tbody>
					</table>
					{!! link_to('/users', $title = 'Manage Users', $attributes = [], $secure = null); !!}
				</div>
				<div class="form-group">
					{!! Form::label('Associated Suppliers', 'Associated Suppliers:') !!}
					<table class="table table-hover table-bordered table-striped margin-bottom-5">
						<thead>
						<tr>
							<th>Supplier Name</th>
							<th>Account Number</th>
							<th>Sage Account Number</th>
						</tr>
						</thead>
						<tbody>
						@foreach($associatedSuppliersDetails as $associatedSuppliersDetail)
							<tr>
								<td scope="row">{{ $associatedSuppliersDetail->supplier_name }}</td>
								<td>{{ $associatedSuppliersDetail->account_number }}</td>
								<td>{{ $associatedSuppliersDetail->sage_account_number }}</td>
							</tr>
						@endforeach
						</tbody>
					</table>
					{!! link_to('#', $title = 'Manage Suppliers', $attributes = [], $secure = null); !!}
				</div>
			@endif
			<div class="form-group">
				{!! Form::label('unitsuppliers', 'Associated Suppliers:') !!}
				<div class="multiselect form-control" id="asp">
					@foreach($associatedSuppliers as $associatedSupplier)
						<label class="chk_as">{!! Form::checkbox('unitsuppliers[]', $associatedSupplier->suppliers_id, isset($selectedAssociatedSuppliers) ? in_array($associatedSupplier->suppliers_id, $selectedAssociatedSuppliers) ? true : false : false, ['id' => $associatedSupplier->suppliers_id, 'class' => 'as_chk']) !!} {{ $associatedSupplier->supplier_name }} </label>
					@endforeach
				</div>
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-xs-12 col-sm-2">
						<a href="/units" name="cancel_btn" class="btn btn-danger btn-block">Cancel</a>
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
        $('#operations_group').select2();
        $('#unit_manager').select2();
        $('#operation_manager').select2();

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

        $('#save_unit').on('submit', function () {
            $('.error_message').remove();

            var unitName = $('#unit_name').val();

            if (unitName.length === 0) {
                $('#unit_name').focus();

                $('#unit_name')
                    .after(
                        $('<span />').addClass('error_message').text('The Unit Name field is required.')
                    )

                return false;
            }

            if (unitName.length < 5) {
                $('#unit_name').focus();

                $('#unit_name')
                    .after(
                        $('<span />').addClass('error_message').text('The unit name must be at least 5 characters')
                    )

                return false;
            }

            if (!$('#status_id').val()) {
                $('#status_id').focus();

                $('#status_id')
                    .after(
                        $('<span />').addClass('error_message').text('The Status field is required')
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

			// Prevent double submit
			if ($(this).hasClass('processing')) {
				return false;
			}

			$(this).addClass('processing');

            return true;
        })
	</script>
@stop
