var supervisor = document.getElementById('supervisor');
var zNum = document.getElementById('z_number');

var zFood = document.getElementById('z_food');
var zMinerals = document.getElementById('z_minerals');
var zConfect = document.getElementById('z_confect');
var zConfectFood = document.getElementById('z_confect_food');
var zFruit = document.getElementById('z_fruit');
var zRead = document.getElementById('z_read');
var cashCount = document.getElementById('cash_count');
var variance = document.getElementById('variance');
var saleDate = document.getElementById('sale_date');
var cashVar = document.getElementById('cash_var');
var overRing = document.getElementById('over_ring');
var opsMan = document.getElementById('ops_manager');
var creditCard = document.getElementById('credit_card');
var cashCreditCard = document.getElementById('cash_credit_card');
var staffCards = document.getElementById('staff_cards');

var errorMsg = document.getElementById('error_message');
var all = document.getElementsByClassName("checkboxes");

function getRegisterCurrency(regNumber) {
	$.ajax({
		type: 'GET',
		url: "/register_currency/json",
		data: {
			reg_number: regNumber
		},
		dataType: 'json'
	}).done(function (data) {
		$('#currency_id').val(data.currencyId);
		$('.currency-symbol').text(data.currencySymbol);

		$('input[name="exchange_data"]').each(function() {
			exchangeAmount($(this), data.currencyId);
		});
	});
}

function exchangeAmount(exchangeElement, currencyId) {
	var exchangeArr = exchangeElement.val().split('_');
	
	$.ajax({
		type: 'GET',
		url: "/exchange_amount/json",
		data: {
			amount: exchangeArr[0],
			domestic_currency_id: exchangeArr[1],
			foreign_currency_id: currencyId,
			date: exchangeArr[2],
		},
		dataType: 'json'
	}).done(function (data) {
		exchangeElement.parents('.checkbox-wrapper').find('.currency-amount').text(data);
	});
}

$(document).ready(function() {
	$("#cash_sales").on("submit", function () {
		return validation();
	});

	$("#unit_name").on("change", function () {
		if($("#unit_name").val() != '') {
			$("#unit_name_span.error_message").html("");
		}
	});

	$('#reg_num').on("click", 'input[name="reg_number"]',function () {
		var regNumber = parseInt($(this).val());
		
		if (!isNaN(regNumber) && regNumber > 0) {
			getRegisterCurrency(regNumber);
		}
	});
	
	// Z and Cash / Credit fields displaying upto 2 decimals
	zFood.value = addCommas((Math.round(zFood.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	zConfectFood.value = addCommas((Math.round(zConfectFood.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	zFruit.value = addCommas((Math.round(zFruit.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	zMinerals.value = addCommas((Math.round(zMinerals.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	zConfect.value = addCommas((Math.round(zConfect.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	cashCount.value = addCommas((Math.round(cashCount.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	creditCard.value = addCommas((Math.round(creditCard.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	staffCards.value = addCommas((Math.round(staffCards.value.replace(/,/g, "") * 100) / 100).toFixed(2));

	overRing.value = addCommas((Math.round(overRing.value * 100) / 100).toFixed(2));
});

document.onclick = function() {

	// Z and Cash / Credit fields displaying upto 2 decimals
	zFood.value = addCommas((Math.round(zFood.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	zConfectFood.value = addCommas((Math.round(zConfectFood.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	zFruit.value = addCommas((Math.round(zFruit.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	zMinerals.value = addCommas((Math.round(zMinerals.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	zConfect.value = addCommas((Math.round(zConfect.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	cashCount.value = addCommas((Math.round(cashCount.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	creditCard.value = addCommas((Math.round(creditCard.value.replace(/,/g, "") * 100) / 100).toFixed(2));
	staffCards.value = addCommas((Math.round(staffCards.value.replace(/,/g, "") * 100) / 100).toFixed(2));

	var ztotal = Number(zFood.value.replace(/,/g, "")) + Number(zConfectFood.value.replace(/,/g, "")) + Number(zMinerals.value.replace(/,/g, "")) + Number(zConfect.value.replace(/,/g, "")) + Number(zFruit.value.replace(/,/g, ""));
	zRead.value = addCommas((Math.round(ztotal * 100) / 100).toFixed(2));

	// Sales Total [ Starts ]
	if(ztotal) {
		$('.div_sales_total').show("slow");
		$("#sales_total").html(addCommas((Math.round(ztotal * 100) / 100).toFixed(2)));
	} else
		$('.simple_table_small').hide("slow");
	// Sales Total [ Ends ]

	var cashCreditCardTotal = Number(cashCount.value.replace(/,/g, "")) + Number(creditCard.value.replace(/,/g, "")) + Number(staffCards.value.replace(/,/g, ""));
	cashCreditCard.value = addCommas((Math.round(cashCreditCardTotal * 100) / 100).toFixed(2));

	var checktotal = 0;
	for (var i=0; i < all.length; i++) {
	     if(all[i].checked){
	     	checktotal += Number(all[i].value.replace(/,/g, ""));
	     }
	}

	var vari = Number(cashCount.value.replace(/,/g, "")) - Number(zRead.value.replace(/,/g, "")) + checktotal + Number(overRing.value.replace(/,/g, ""));

	var total_z_read = Math.round(ztotal * 100) / 100;
	var total_cash_credit_card = Math.round(cashCreditCardTotal * 100) / 100;

	if(total_z_read != '' && total_cash_credit_card != '') {
		// variance_total = total_z_read - (total_cash_credit_card + checktotal);
		variance_total = total_cash_credit_card + checktotal - total_z_read;
		variance.value = addCommas(variance_total.toFixed(2));
	}
};

// Calculate values on Tab Press [ Start ]

$('#z_food').change(function() {
	zFood.value = addCommas((Math.round(zFood.value * 100) / 100).toFixed(2));
        calculate_zread_variance();
});
$('#z_confect_food').change(function() {
	zConfectFood.value = addCommas((Math.round(zConfectFood.value * 100) / 100).toFixed(2));
        calculate_zread_variance();
});
$('#z_fruit').change(function() {
	zFruit.value = addCommas((Math.round(zFruit.value * 100) / 100).toFixed(2));
        calculate_zread_variance();
});
$('#z_minerals').change(function() {
	zMinerals.value = addCommas((Math.round(zMinerals.value * 100) / 100).toFixed(2));
        calculate_zread_variance();
});
$('#z_confect').change(function() {
	zConfect.value = addCommas((Math.round(zConfect.value * 100) / 100).toFixed(2));
        calculate_zread_variance();
});
$('#cash_count').change(function() {
	cashCount.value = addCommas((Math.round(cashCount.value * 100) / 100).toFixed(2));
        calculate_zread_variance();
});
$('#credit_card').change(function() {
	creditCard.value = addCommas((Math.round(creditCard.value * 100) / 100).toFixed(2));
        calculate_zread_variance();
});
$('#staff_cards').change(function() {
	staffCards.value = addCommas((Math.round(staffCards.value * 100) / 100).toFixed(2));
        calculate_zread_variance();
});


$('#over_ring').change(function() {
	overRing.value = addCommas((Math.round(overRing.value * 100) / 100).toFixed(2));
});

function calculate_zread_variance() {

	var ztotal = Number(zFood.value.replace(/,/g, "")) + Number(zConfectFood.value.replace(/,/g, "")) + Number(zMinerals.value.replace(/,/g, "")) + Number(zConfect.value.replace(/,/g, "")) + Number(zFruit.value.replace(/,/g, ""));
	zRead.value = addCommas((Math.round(ztotal * 100) / 100).toFixed(2));

	// Sales Total [ Starts ]
	if(ztotal) {
		$('.div_sales_total').show("slow");
		$("#sales_total").html(addCommas((Math.round(ztotal * 100) / 100).toFixed(2)));
	} else
		$('.simple_table_small').hide("slow");
	// Sales Total [ Ends ]

	var cashCreditCardTotal = Number(cashCount.value.replace(/,/g, "")) + Number(creditCard.value.replace(/,/g, "")) + Number(staffCards.value.replace(/,/g, ""));
	cashCreditCard.value = addCommas((Math.round(cashCreditCardTotal * 100) / 100).toFixed(2));

	var total_z_read = Math.round(ztotal * 100) / 100;
	var total_cash_credit_card = Math.round(cashCreditCardTotal * 100) / 100;

	var checktotal = 0;
	for (var i=0; i < all.length; i++) {
	     if(all[i].checked){
                //alert(Number(all[i].value.replace(/,/g, "")));
	     	checktotal += Number(all[i].value.replace(/,/g, ""));
	     }
	}

	//alert(total_z_read + '---' + checktotal + '---' + total_cash_credit_card);
        if(total_z_read != '' && total_cash_credit_card != '') {
		// variance_total = total_z_read - (total_cash_credit_card + checktotal);
		variance_total = total_cash_credit_card + checktotal - total_z_read;
		$('#variance').val(addCommas(variance_total.toFixed(2)));
		//variance.value = addCommas(variance_total.toFixed(2));
	}

}

// Calculate values on Tab Press [ End ]

cashCount.onchange = function() {

	var checktotal = 0;
	for (var i=0; i < all.length; i++) {
	     if(all[i].checked){
	     	checktotal += Number(all[i].value.replace(/,/g, ""));
	     }
	}

	var vari = Number(cashCount.value.replace(/,/g, "")) - Number(zRead.value.replace(/,/g, "")) + checktotal + Number(overRing.value.replace(/,/g, ""));

	var ztotal = Number(zFood.value.replace(/,/g, "")) + Number(zConfectFood.value.replace(/,/g, "")) + Number(zMinerals.value.replace(/,/g, "")) + Number(zConfect.value.replace(/,/g, "")) + Number(zFruit.value.replace(/,/g, ""));

	var total_z_read = Math.round(ztotal * 100) / 100;
	var cashCreditCardTotal = Number(cashCount.value.replace(/,/g, "")) + Number(creditCard.value.replace(/,/g, ""));
	var total_cash_credit_card = Math.round(cashCreditCardTotal * 100) / 100;

	var checktotal = 0;
	for (var i=0; i < all.length; i++) {
	     if(all[i].checked){
	     	checktotal += Number(all[i].value.replace(/,/g, ""));
	     }
	}

	if(total_z_read != '' && total_cash_credit_card != '') {
		// variance_total = total_z_read - (total_cash_credit_card + checktotal);
		variance_total = total_cash_credit_card + checktotal - total_z_read;
		variance.value = addCommas(variance_total.toFixed(2));
	}
}

zRead.onchange = function() {
	var vari = Number(cashCount.value) - Number(zRead.value) + checktotal + Number(overRing.value);

	if(total_z_read != '' && total_cash_credit_card != '') {
		// variance_total = total_z_read - (total_cash_credit_card + checktotal);
		variance_total = total_cash_credit_card + checktotal - total_z_read;
		variance.value = addCommas(variance_total.toFixed(2));
	}
}


function validation() {
	var validation_success = false;
	var d = new Date();
	d.setMonth(d.getMonth() - 12);
	var e = new Date();

	var unit_name_val = $("#unit_name").val();

	if(unit_name_val == '' || unit_name_val == '0' || unit_name_val == null) {
		$("#unit_name").focus();
		$("#unit_name_span.error_message").html("Please select a Unit.");
	}
	else if($("#sale_date").datepicker('getDate') < d || ($("#sale_date").datepicker('getDate') > e)) {
		$("#sale_date").focus();
		$("#sale_date_span.error_message").html("Sale Date cannot be in the future or > 1 year in the past.");
	}
	else if($("#z_number").val() == '') {
		$("#sale_date_span.error_message").html("");
		$("#z_number").focus();
		$("#z_number_span.error_message").html("Field cannot be left blank.");
	}
	else if($.isNumeric($("#z_number").val()) == false) {
		$("#sale_date_span.error_message").html("");
		$("#z_number").focus();
		$("#z_number_span.error_message").html("Field can only contain numbers.");
	}
	else if ($('#currency_id').val() == 0) {
		$('#reg_num').focus();
		$('#reg_num')
			.after(
				$('<span />').addClass('error_message').text('This register has no currency assigned.')
			);

	} else {
		validation_success = true;
	}

	return validation_success;
}

window.onload = function() {
	$('#hidden_unit_name').val($('#unit_name').find(':selected').text());
	calculate_zread_variance();
};