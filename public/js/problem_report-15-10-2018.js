jQuery( document ).ready(function() {

    $("#user_name_span").hide();
    $("#problem_type_span").hide();
    $("#details_span").hide();
    $("#car_span").hide();
    $("#root_cause_analysis_desc_span").hide();
    $("#action_span").hide();
    $("#closing_comments_span").hide();

    $("#problem_report").on("submit", function () {
        return validation();
    });

    $("#unit_name").change(function() {
        if($('#unit_name').val() != 0)
            $("#unit_name_span.error_message").hide();
    });

    $('#problem_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    }).on('changeDate',function(e){
        $('#problem_type').focus();
    });

    $('#closed_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    });

    $(".supplier-div").hide();
    $(".feedback-div").hide();
    $(".comment-div").hide();
    
    $(".root_cause_analysis-div").hide();
    $(".problem_status-div").hide();

    $("#problem_type").change(function() {
        $('#hidden_problem_type').val($(this).find(':selected').text());
        problem_type_supplier();
    });

    $("input[name='root_cause_analysis']").on("click", function() {
        root_cause_analysis_chk();
    });

    $("input[name='problem_status']").on("click", function() {
        problem_status_chk();
    });

    /*$(document).on('change', '#unit_name', function() {
        $('#hidden_unit_name').val($(this).find(':selected').text());
        //load_suppliers();
    });*/

    $(document).on('change', '#supplier', function() {
        if($(this).val() != 0) {
            $("#suppliers_span.error_message").hide();
            $('#hidden_supplier').val($(this).find(':selected').text());
        }
    });

    $("#details").on("blur", function () {
        if($("#details").val() != '') {
            $("#details_span.error_message").hide();
        }
    });

    $("#car").on("blur", function () {
        if($("#car").val() != '') {
            $("#car_span.error_message").hide();
        }
    });

    $("#root_cause_analysis_desc").on("blur", function () {
        if($("#root_cause_analysis_desc").val() != '') {
            $("#root_cause_analysis_desc_span.error_message").hide();
        }
    });

    $("#action").on("blur", function () {
        if($("#action").val() != '') {
            $("#action_span.error_message").hide();
        }
    });

    $("#closing_comments").on("blur", function () {
        if($("#closing_comments").val() != '') {
            $("#closing_comments_span.error_message").hide();
        }
    });

    //load_unit_names();
    problem_type_supplier();
    //load_suppliers();
    root_cause_analysis_chk();
    problem_status_chk();
});

function problem_type_supplier() {
    var problem_type_val = $("#problem_type").val();
    if(problem_type_val != 0 )
        $("#problem_type_span.error_message").hide();

    if(problem_type_val == 1)
        $(".supplier-div").show(1000);
    else
        $(".supplier-div").hide(1000);

    if(problem_type_val == 6)
        $(".feedback-div").show(1000);
    else
        $(".feedback-div").hide(1000);
}

/*function load_unit_names() {
    var user_name_val = $('#user_name').val();
    var unit_id_val = $('#hidden_unit_id').val();

    setTimeout(function() { $('#hidden_unit_name').val($('#unit_name').find(':selected').text()); }, 3000);

    //alert(unit_id_val)
    //$('#hidden_user_name').val($(this).find(':selected').text());

    if(user_name_val != 0) {
        $("#user_name_span.error_message").hide();
        $.ajax({
            type: 'POST',
            url: "unit_names_ajax.php",
            data: { user_id: user_name_val, selected_unit: unit_id_val }
        }).done(function( data ) {
            var obj = jQuery.parseJSON(data);

            if(obj.unit_names_data) {
                $('#unit_name_div').html(obj.unit_names_data);
                $("#unit_name_span.error_message").hide();
                //$('#supplier').focus();
            } else
                $('#unit_name_div').html("<input type='text' class='form-control' name='unit_name' value='' tabindex='3' />");
        });
    } else
        $('#unit_name_div').html("<input type='text' class='form-control' name='unit_name' value='' tabindex='3' />");
}

function load_suppliers() {
    var unit_name_val = $('#unit_name').val();
    if(unit_name_val == undefined)
        var unit_name_val = $('#hidden_unit_id').val();

    var supplier_val = $('#supplier').val();

    setTimeout(function() { $('#hidden_unit_name').val($('#unit_name').find(':selected').text()); }, 3000);

    if(supplier_val == undefined)
        var supplier_val = $('#hidden_supplier_id').val();

    if(unit_name_val != 0) {
        $("#unit_name_span.error_message").hide();
        $("#unit_name_span.error_message").html("");
        $.ajax({
            type: 'POST',
            url: "suppliers_ajax_problem_report.php",
            data: { unit_name: unit_name_val, selected_supplier: supplier_val }
        }).done(function( data ) {
            var obj = jQuery.parseJSON(data);

            if(obj.suppliers_data) {
                $('#supplier_div').html(obj.suppliers_data);
                $("#supplier_span.error_message").hide();
            } else
                $('#supplier_div').html("<input type='text' class='form-control' name='supplier' value='' tabindex='4' />");
        });
    } else
        $('#supplier_div').html("<input type='text' class='form-control' name='supplier' value='' tabindex='4' />");
}*/

function root_cause_analysis_chk() {
    var root_cause_analysis_val = $("input[name='root_cause_analysis']:checked") .val();
    //alert(root_cause_analysis_val)
    if(root_cause_analysis_val == 1)
        $(".root_cause_analysis-div").show(1000);
    else
        $(".root_cause_analysis-div").hide(1000);
}

function problem_status_chk() {
    var problem_status_val = $("input[name='problem_status']:checked").val();
    if(problem_status_val == 0)
        $(".problem_status-div").show(1000);
    else
        $(".problem_status-div").hide(1000);
}

function validation() {
    var success = false;
    var user_name_val = $("#user_name").val();
    var unit_name_val = $("#unit_name").val();
    var problem_type_val = $("#problem_type").val();
    var supplier_val = $("#supplier").val();
    var details_val = $("#details").val();
    var car_val = $("#car").val();
    var root_cause_analysis_desc_val = $("#root_cause_analysis_desc").val();
    var action_val = $("#action").val();
    var closing_comments_val = $("#closing_comments").val();

    if(user_name_val == 0) {
        $("#user_name").focus();
        $("#user_name_span").show();
        $("#user_name_span.error_message").html("Please select a User.");
    } else if(problem_type_val == 0) {
        $("#problem_type").focus();
        $("#problem_type_span").show();
        $("#problem_type_span.error_message").html("Please select a Problem Type.");
    } else if(unit_name_val == 0) {
        $("#unit_name").focus();
        $("#unit_name_span").show();
        $("#unit_name_span.error_message").html("Please select a Unit.");
    } else if(problem_type_val == 1 && supplier_val == 0) {
        $("#supplier").focus();
        $("#suppliers_span").show();
        $("#suppliers_span.error_message").html("Please select a Supplier.");
    } else if(details_val == 0) {
        $("#details").focus();
        $("#details_span").show();
        $("#details_span.error_message").html("Please enter Details.");
    } else if(car_val == 0) {
        $("#car").focus();
        $("#car_span").show();
        $("#car_span.error_message").html("Please enter CAR.");
    } else if($("input[name='root_cause_analysis']:checked").val() == 1 && root_cause_analysis_desc_val == '') {
        $("#root_cause_analysis_desc").focus();
        $("#root_cause_analysis_desc_span").show();
        $("#root_cause_analysis_desc_span.error_message").html("Please enter Root Cause Analysis Description.");
    } else if($("input[name='root_cause_analysis']:checked").val() == 1 && action_val == '') {
        $("#action").focus();
        $("#action_span").show();
        $("#action_span.error_message").html("Please enter Action.");
    } else if($("input[name='problem_status']:checked").val() == 0 && closing_comments_val == '') {
        $("#closing_comments").focus();
        $("#closing_comments_span").show();
        $("#closing_comments_span.error_message").html("Please enter Closing Comments.");
    } else
        success = true;

    return success;
}

window.onload = function() {
  $('#hidden_unit_name').val($('#unit_name').find(':selected').text());
};