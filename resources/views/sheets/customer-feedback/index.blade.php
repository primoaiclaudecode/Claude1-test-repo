@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Client Feedback</strong>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif

			@if(Session::has('error_message'))
				<div class="alert alert-danger"><em> {!! session('error_message') !!}</em></div>
			@endif

			{!! Form::open(['url' => 'sheets/customer-feedback/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'customer_feedback_frm']) !!}
			<div class="form-group">
				<label class="col-xs-12 col-sm-12 col-md-6 col-lg-2 control-label custom-labels">Unit Name:</label>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					{!! Form::select('unit_id', $userUnits, $selectedUnit, ['id' => 'unit_id', 'class'=>'form-control', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus']) !!}
					<span id="unit_id_span" class="error_message"></span>
				</div>

				<label class="col-xs-12 col-sm-12 col-md-6 col-lg-2 control-label custom-labels">Date:</label>
				<div class="col-xs-6 col-sm-6 col-md-3 col-lg-2">
					<div class="input-group">
						{{ Form::text('contact_date', $contactDate, array('id' => 'contact_date', 'class' => 'form-control text-right cursor-pointer', 'tabindex' => 5, 'readonly' => '')) }}
						<span class="input-group-addon cursor-pointer" id="contact_date_icon">
							<i class="fa fa-calendar"></i>
						</span>
					</div>
				</div>
				<div class="col-xs-6 col-sm-6 col-md-3 col-lg-2">
					<div class="input-group">
						{{ Form::text('contact_time', $contactTime, array('id' => 'contact_time', 'class' => 'form-control text-right cursor-pointer', 'tabindex' => 5, 'readonly' => '')) }}
						<span class="input-group-addon cursor-pointer" id="contact_time_icon">
							<i class="fa fa-clock-o"></i>
						</span>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-12 col-sm-12 col-md-6 col-lg-2 control-label custom-labels">Region:</label>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					{{ Form::text('region_name', $regionName, array('id' => 'region_name', 'class' => 'form-control form-field-margin text-mobile-right', 'tabindex' => 2, 'readonly' => '')) }}
				</div>

				<label class="col-xs-12 col-sm-12 col-md-6 col-lg-2 control-label custom-labels">Contract Status:</label>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					{{ Form::text('contract_status', $contractStatus, array('id' => 'contract_status', 'class' => 'form-control text-right', 'tabindex' => 6, 'readonly' => '')) }}
				</div>
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-12 col-md-6 col-lg-2 control-label custom-labels">Operations manager:</label>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					{{ Form::text('operations_manager_name', $operationsManagerName, array('id' => 'operations_manager_name', 'class' => 'form-control text-mobile-right form-field-margin', 'tabindex' => 3, 'readonly' => '')) }}
				</div>

				<label class="col-xs-12 col-sm-12 col-md-6 col-lg-2 control-label custom-labels">Contract Type:</label>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					{{ Form::text('contract_type', $contractType, array('id' => 'contract_type', 'class' => 'form-control text-right', 'tabindex' => 7, 'readonly' => '')) }}
				</div>
			</div>

			<div class="form-group">
				<label class="col-xs-12 col-sm-12 col-md-6 col-lg-2 control-label custom-labels">Client communications month to date:</label>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					{{ Form::text('onsite_visits', $onsiteVisits, array('id' => 'onsite_visits', 'class' => 'form-control form-field-margin text-mobile-right', 'tabindex' => 4, 'readonly' => '')) }}
				</div>

				<label class="col-xs-12 col-sm-12 col-md-6 col-lg-2 control-label custom-labels">Client contact:</label>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
					{{ Form::text('client_contact', $clientContact, array('id' => 'client_contact', 'class' => 'form-control text-right', 'tabindex' => 8, 'readonly' => '')) }}
				</div>
			</div>

			<div class="form-group">
				<div class="col-xs-12 margin-top-25">
					<div class="responsive-content">
						<table id="customer_feedback_tbl" class="table table-bordered table-striped table-medium">
							<thead>
							<tr>
								<th width="10%" class="text-center">Contact Date</th>
								<th width="15%" class="text-center">Contact Type</th>
								<th width="60%" class="text-center">Notes</th>
								<th width="15%" class="text-center">Customer Feedback</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td class="text-center contact-date-cell">{{ $contactDate }}</td>
								<td>
									{!! Form::select('contact_type', $contactTypes, $contactType, ['id' => 'contact_type', 'class'=>'form-control', 'placeholder' => 'Please select', 'tabindex' => 10]) !!}
									<span id="contact_type_span" class="error_message"></span>
								</td>
								<td>
									<input id="notes" type="text" name="notes" value="{{ $notes }}" class="form-control" maxlength="250" tabindex=10></input>
									<span id="notes_span" class="error_message"></span>
								</td>
								<td>
									{!! Form::select('customer_feedback', $feedbackTypes, $customerFeedback, ['id' => 'customer_feedback', 'class'=>'form-control', 'placeholder' => 'Please select', 'tabindex' => 11]) !!}
									<span id="customer_feedback_span" class="error_message"></span>
								</td>
							</tr>
							</tbody>
						</table>
					</div>					
				</div>
			</div>

			<div class="form-group">
				<input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-35" name='submit' value='Submit' tabindex='12'/>
			</div>
			{!!Form::close()!!}
		</section>
	</section>
@stop

@section('scripts')
	<link href="{{ asset('css/bootstrap-timepicker.min.css') }}" rel="stylesheet">
	<script src="{{ asset('js/bootstrap-timepicker.min.js') }}"></script>
	<script src="{{ elixir('js/customer_feedback.js') }}"></script>
@stop
