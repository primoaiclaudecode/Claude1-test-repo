$(document).ready(function() {
  $("#unit_month_end_closing_form").on("submit", function () {
    alert('vvvvvv');
    return validation();
  });

  $("#month, #year, #unit_name").on("change", function () {
    if($("#month").val() != '') {
      $("#month_span.error_message").html("");
    }
    if($("#year").val() != '') {
      $("#year_span.error_message").html("");
    }
    if($("#unit_name").val() != '') {
      $("#unit_name_span.error_message").html("");
    }
  });
});

function validation() {
  var validation_success = false;
  var month_val = $("#month").val();
  var year_val = $("#year").val();
  var unit_name_val = $("#unit_name").val();

  if(month_val == '') {
    $("#month").focus();
    $("#month_span.error_message").html("Please select a Month.");
  } else if(year_val == '') {
    $("#month_span.error_message").html("");
    $("#year").focus();
    $("#year_span.error_message").html("Please select a Year.");
  } else if(unit_name_val == '') {
    $("#month_span.error_message").html("");
    $("#year_span.error_message").html("");
    $("#unit_name").focus();
    $("#unit_name_span.error_message").html("Please select a Unit.");
  }
  else
    validation_success = true;

  return validation_success;
}

window.onload = function() {
  $('#hidden_unit_name').val($('#unit_name').find(':selected').text());
};