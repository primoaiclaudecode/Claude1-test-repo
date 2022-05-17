@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Client Feedback Confirmation</strong>
		</header>

		<section class="dataTables-padding">
			{!! Form::open(['url' => 'sheets/customer-feedback/post', 'class' => 'form-horizontal form-bordered']) !!}
				<div class="form-group margin-bottom-0 margin-left-0 margin-right-0">
					<div class="clearfix"></div>
					<div class="col-md-12 padding-left-0 padding-right-0 border-top-0">
						<div class="responsive-content">
							<table id="customer_feedback_tbl" class="table table-bordered table-striped table-small">
								<tr>
									<td>
										<label>Unit Name:</label>
										{{ Form::text('unit_name', $unitName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
										{{ Form::hidden('unit_id', $unitId) }}
									</td>
									<td>
										<label>Date</label>
										{{ Form::text('contact_date_time', $contactDateTime, array('class' => 'form-control', 'readonly' => 'readonly')) }}
									</td>
									<td>
										<label>Contact Type:</label>
										{{ Form::text('contact_type_legend', $contactTypeLegend, array('class' => 'form-control', 'readonly' => 'readonly')) }}
										{{ Form::hidden('contact_type', $contactType) }}
									</td>
									<td>
										<label>Customer Feedback:</label>
										{{ Form::text('customer_feedback_legend', $customerFeedbackLegend, array('class' => 'form-control', 'readonly' => 'readonly')) }}
										{{ Form::hidden('customer_feedback', $customerFeedback) }}
									</td>
								</tr>
								<tr>
									<td colspan="4">
										<label>Notes:</label>
										{{ Form::text('notes', $notes, array('class' => 'form-control', 'readonly' => 'readonly')) }}
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-12">
						<h5>Are you sure you want to submit this customer feedback?</h5>
						<input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm'/>
					</div>
				</div>
			{!!Form::close()!!}

			{!! Form::open(['url' => 'sheets/customer-feedback', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
				{{ Form::hidden('back_data', $backData) }}
			{!!Form::close()!!}

			<p>
				<a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter customer feedback</a>
				<br/>
			</p>
		</section>
	</section>
@stop
@section('scripts')
	<script src="{{asset('js/jquery.backDetect.js')}}"></script>
	<script type="text/javascript">
		$(window).load(function () {
			$('body').backDetect(function () {
				alert('Confirm form resubmission');
				$('#re_enter_frm').submit()
			});
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
