var net9 = document.getElementById('goods_9');
var vat9 = document.getElementById('vat_9');
var gross9 = document.getElementById('gross_9');
var net23 = document.getElementById('goods_23');
var vat23 = document.getElementById('vat_23');
var gross23 = document.getElementById('gross_23');
var totalNet = document.getElementById('goods_total');
var totalVAT = document.getElementById('vat_total');
var totalGross = document.getElementById('gross_total');

var errorMsg = document.getElementById('error_message');

$(document).ready(function() {
	$("#credit_sales").on("submit", function () {
		return validation();
	});

	$("#unit_name").on("change", function () {
		if($("#unit_name").val() != '') {
			$("#unit_name_span.error_message").html("");
		}
	});

	/*$(document).on('change', '#supplier', function() {
		if($("#supplier").val() != 0) {
			$("#suppliers_span.error_message").html("");
		}
	});

	$("#invoice_number").on("blur", function () {
		if($("#invoice_number").val() != '') {
			$("#invoice_number").removeClass("errorfield");
			$("#invoice_number_span.error_message").html("");
		}
	});*/
});

// Calculate values on Tab Press [ Start ]

$('#gross_9').change(function() {
	gross9.value = addCommas((Math.round(gross9.value.replace(/,/g, "") * 100) / 100).toFixed(2));
        calculate_total_gross();
});
$('#gross_23').change(function() {
	gross23.value = addCommas((Math.round(gross23.value.replace(/,/g, "") * 100) / 100).toFixed(2));
        calculate_total_gross();
});

function calculate_total_gross() {

	var gross = Number(gross9.value.replace(/,/g, ""));
	var vat = (gross / 109) * 9;
	vat9.value = addCommas((Math.round(vat * 100) / 100).toFixed(2));
	var net = (gross / 109) * 100;
	net9.value = addCommas((Math.round(net * 100) / 100).toFixed(2));

	var gross = Number(gross23.value.replace(/,/g, ""));
	var vat = (gross / 123) * 23;
	vat23.value = addCommas((Math.round(vat * 100) / 100).toFixed(2));
	var net = (gross / 123) * 100;
	net23.value = addCommas((Math.round(net * 100) / 100).toFixed(2));

	totalNet.value = addCommas((Math.round((Number(net9.value.replace(/,/g, "")) + Number(net23.value.replace(/,/g, ""))) * 100) / 100).toFixed(2));
	totalVAT.value = addCommas((Math.round((Number(vat9.value.replace(/,/g, "")) + Number(vat23.value.replace(/,/g, ""))) * 100) / 100).toFixed(2));
	totalGross.value = addCommas((Math.round((Number(totalNet.value.replace(/,/g, "")) + Number(totalVAT.value.replace(/,/g, ""))) * 100) / 100).toFixed(2));

	// Sales Total [ Starts ]
	//alert(totalGross.value);
    if(totalGross.value && totalGross.value != 0.00) {
		$('.div_sales_total').show("slow");
		$("#sales_total").html(totalGross.value);
		$('#submit_btn').removeClass('margin-top-35');
	} else
		$('.simple_table_small').hide("slow");
	// Sales Total [ Ends ]

}

// Calculate values on Tab Press [ End ]

function validation() {
	var validation_success = false;
	var d = new Date();
	d.setMonth(d.getMonth() - 12);
	var e = new Date();

	var unit_name_val = $("#unit_name").val();

	if(unit_name_val == '') {
		$("#unit_name").focus();
		$("#unit_name_span.error_message").html("Please select a Unit.");
	}
	else if($("#sale_date").datepicker('getDate') < d || ($("#sale_date").datepicker('getDate') > e)) {
		$("#sale_date").focus();
		$("#sale_date_span.error_message").html("Sale Date cannot be in the future or > 1 year in the past.");
	}
	else if($("#credit_reference").val() == '') {
		$("#sale_date_span.error_message").html("");
		$("#credit_reference").focus();
		$("#credit_reference_span.error_message").html("Field cannot be left blank.");
	}
	else if($("#docket_number").val() == '') {
		$("#credit_reference_span.error_message").html("");
		$("#docket_number").focus();
		$("#docket_number_span.error_message").html("Field cannot be left blank.");
	}
	else if($("#cost_centre").val() == '') {
		$("#docket_number_span.error_message").html("");
		$("#cost_centre").focus();
		$("#cost_centre_span.error_message").html("Field cannot be left blank.");
	}
	else
		validation_success = true;

	return validation_success;
}
function checkInputData() {
	var unit_name_val = $("#unit_name").val();
    var credit_reference_val = $("#credit_reference").val();
	var docket_number_val = $("#docket_number").val();
	var cost_centre_val = $("#cost_centre").val();
	//alert(cost_centre_val)
    var passedValidation = false;

	if(unit_name_val == '0') {
		$("#unit_name").addClass("errorfield");
		$("#unit_name").focus();
		$("#unit_name_span.error_message").html("Please select a Unit.");
	} else if(credit_reference_val == '') {
		$("#unit_name").removeClass("errorfield");
		$("#unit_name_span.error_message").html("");
		$("#credit_reference").addClass("errorfield");
		$("#credit_reference").focus();
		$("#credit_reference_span.error_message").html("Field cannot be left blank.");
	} else if(docket_number_val == '') {
		$("#unit_name").removeClass("errorfield");
		$("#unit_name_span.error_message").html("");
		$("#credit_reference").removeClass("errorfield");
		$("#credit_reference_span.error_message").html("");
		$("#docket_number").addClass("errorfield");
		$("#docket_number").focus();
		$("#docket_number_span.error_message").html("Field cannot be left blank.");
	} else if(cost_centre_val == '') {
		$("#unit_name").removeClass("errorfield");
		$("#unit_name_span.error_message").html("");
		$("#credit_reference").removeClass("errorfield");
		$("#credit_reference_span.error_message").html("");
		$("#docket_number").removeClass("errorfield");
		$("#docket_number_span.error_message").html("");
		$("#cost_centre").addClass("errorfield");
		$("#cost_centre").focus();
		$("#cost_centre_span.error_message").html("Field cannot be left blank.");
	} else
		passedValidation = true;

	if(passedValidation) return calculate_total_gross();
    return passedValidation;
}

window.onload = function() {
	$('#hidden_unit_name').val($('#unit_name').find(':selected').text());
	gross9.value = addCommas((Math.round(gross9.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	gross23.value = addCommas((Math.round(gross23.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	calculate_total_gross();
};