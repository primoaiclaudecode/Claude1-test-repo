var foods = document.getElementById('foods');
var minerals = document.getElementById('minerals');
var choc_snacks = document.getElementById('choc_snacks');
var vending = document.getElementById('vending');
var foods_plus_minerals = document.getElementById('foods_plus_minerals');

var chemicals = document.getElementById('chemicals');
var clean_disp = document.getElementById('clean_disp');
var free_issues = document.getElementById('free_issues');
var total_chemicals_clean_disp_free_issues = document.getElementById('total_chemicals_clean_disp_free_issues');

var total = document.getElementById('total');

$('#foods, #minerals, #choc_snacks, #vending').change(function() {
	foods.value = (Math.round(foods.value * 100) / 100).toFixed(2);
	minerals.value = (Math.round(minerals.value * 100) / 100).toFixed(2);
	choc_snacks.value = (Math.round(choc_snacks.value * 100) / 100).toFixed(2);
	vending.value = (Math.round(vending.value * 100) / 100).toFixed(2);

	foods_plus_minerals.value = (Math.round((Number(foods.value) + Number(minerals.value) + Number(choc_snacks.value) + Number(vending.value)) * 100) / 100).toFixed(2);
	total.value = (Math.round((Number(foods.value) + Number(minerals.value) + Number(choc_snacks.value) + Number(vending.value)) * 100) / 100).toFixed(2);
});

$('#chemicals, #clean_disp, #free_issues').change(function() {
	foods.value = (Math.round(foods.value * 100) / 100).toFixed(2);
	minerals.value = (Math.round(minerals.value * 100) / 100).toFixed(2);
	choc_snacks.value = (Math.round(choc_snacks.value * 100) / 100).toFixed(2);
	vending.value = (Math.round(vending.value * 100) / 100).toFixed(2);

	chemicals.value = (Math.round(chemicals.value * 100) / 100).toFixed(2);
	clean_disp.value = (Math.round(clean_disp.value * 100) / 100).toFixed(2);
	free_issues.value = (Math.round(free_issues.value * 100) / 100).toFixed(2);
	total_chemicals_clean_disp_free_issues.value = (Math.round((Number(chemicals.value) + Number(clean_disp.value) + Number(free_issues.value)) * 100) / 100).toFixed(2);

	total.value = (Math.round((Number(foods.value) + Number(minerals.value) + Number(choc_snacks.value) + Number(vending.value) + Number(chemicals.value) + Number(clean_disp.value) + Number(free_issues.value)) * 100) / 100).toFixed(2);

});

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
  else
    validation_success = true;

  return validation_success;
}

$(document).ready(function() {
  $("#stock_control_form").on("submit", function () {
    return validation();
  });

  $("#unit_name").on("change", function () {
    if($("#unit_name").val() != '') {
      $("#unit_name_span.error_message").html("");
    }
  });
});

window.onload = function() {
  $('#hidden_unit_name').val($('#unit_name').find(':selected').text());
};