function changeUnit() {
	var unitId = $('#unit_id').val();
	
	if (!unitId) {
		$('#region_name').val('');
		$('#operations_manager_name').val('');
		$('#contract_status').val('');
		$('#contract_type').val('');
		$('#onsite_visits').val('');
		$('#client_contact').val('');

		$('#customer_feedback_tbl tr:gt(1)').remove();
		
		return;
	}
	
	$.ajax({
		type: "get",
		url: '/sheets/unit/info',
		data: {
			_token: '{{csrf_token()}}',
			unit_id: unitId,
		},
		success: function (data) {
			$('#region_name').val(data.regionName);
			$('#operations_manager_name').val(data.operationsManagerName);
			$('#contract_status').val(data.contractStatus);
			$('#contract_type').val(data.contractType);
			$('#onsite_visits').val(data.onsiteVisits);
			$('#client_contact').val(data.clientContact);
			
			$('#customer_feedback_tbl tr:gt(1)').remove();
			
			$.each(data.lastFeedbacks, function (i, feedback) {
				$('#customer_feedback_tbl')
					.append(
						$('<tr />')
							.append(
								$('<td />').addClass('text-center').text(feedback.contact_date)
							)
							.append(
								$('<td />').addClass('text-center').text(feedback.contact_type.title)
							)
							.append(
								$('<td />').text(feedback.notes)
							)
							.append(
								$('<td />').addClass('text-center').text(feedback.feedback_type.title)
							)
					)
			})
		}
	});
}

function validateForm() {
	if ($('#unit_id').val() === '') {
		$("#unit_id").focus();
		$("#unit_id_span").text("Please select a Unit.");
		
		return false;
	}

	if (!$('#contact_type').val()) {
		$("#contact_type").focus();
		$("#contact_type_span").text("Mandatory field");

		return false;
	}

	if (!$('#customer_feedback').val()) {
		$("#customer_feedback").focus();
		$("#customer_feedback_span").html("Mandatory field");

		return false;
	}
	
	return true;
}

$(document).ready(function() {
	// Date picker
	$('#contact_date').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	}).on('changeDate',function(){
		$('.contact-date-cell').text($(this).val());
	});

	$('#contact_date_icon').click(function() {
		$("#contact_date").datepicker().focus();
	});
	
	$('#contact_time').timepicker({
		showInputs: false,
		showMeridian: false,
		minuteStep: 1,
		icons: {
			up: 'fa fa-chevron-up',
			down: 'fa fa-chevron-down'
		}
	});

	$('#contact_time_icon').click(function() {
		$("#contact_time").trigger('click');
	});
	
	// Change unit
	$('#unit_id').on('change', changeUnit);
	$('#unit_id').trigger('change');


	// Clear validation errors
	$('#customer_feedback_frm select').on('change', function() {
		$(this).parent().find('.error_message').text('');
	});

	$('#customer_feedback_frm input').on('keypress', function() {
		$(this).parent().find('.error_message').text('');
	});
	
	// Submit form
	$("#customer_feedback_frm").on("submit", function () {
		return validateForm();
	});
});