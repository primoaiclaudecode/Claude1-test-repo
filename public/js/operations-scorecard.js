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
        url: '/sheets/scorecard/info',
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

            $('.highlighted').removeClass('highlighted');

            $.each(data.avgScorecards, function (field, avgValue) {
                if (!avgValue) {
                    $('#' + field).addClass('highlighted');
                }
            })
        }
    });
}

function validateForm() {
    $('.error_message').remove();

    if ($('#unit_id').val() === '') {
        $("#unit_id").focus();
        $("#unit_id")
            .after(
                $('<span />').addClass('error_message').text('Please select a Unit.')
            );

        return false;
    }

    var isValid = true;

    $('#operations_scorecard_tbl select').each(function () {
        var note = $(this).parents('tr').find('input').val();

        if ($(this).val() != 0 && note.length == 0) {
            var input = $(this).parents('tr').find('input');

            input
                .after(
                    $('<span />').addClass('error_message').text('Mandatory field')
                )

            if (isValid) {
                input.focus();
            }

            isValid = false;
        }
    });

    return isValid;
}

$(document).ready(function () {
    // Date picker
    $('#scorecard_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    });

    $('#scorecard_date_icon').click(function () {
        $("#scorecard_date").datepicker().focus();
    });

    // Change unit
    $('#unit_id').on('change', changeUnit);
    $('#unit_id').trigger('change');

    // Clear validation errors
    $('#operations_scorecard_frm select').on('change', function () {
        $(this).parent().find('.error_message').text('');
    });

    $('#operations_scorecard_frm input').on('keypress', function () {
        $(this).parent().find('.error_message').text('');
    });

    // Submit form
    $("#operations_scorecard_frm").on("submit", function () {
        return validateForm();
    });
    
    // Private fields
    $('.private-field').on('change', function() {
        var icon = $(this).parent().find('i');
        
        if ($(this).prop('checked')) {
            icon.removeClass('fa-unlock').addClass('fa-lock');
        } else {
            icon.removeClass('fa-lock').addClass('fa-unlock');
        }
    });
    
    // Attach files
    $(document).on("click", "#browse_btn", function (e) {
        $('#myModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
    });

    $(document).on("click", ".fileClass", function (e) {
        var file_val = $(this).val();
        var fileSplit = file_val.split("~");
        $('#attached_file_name').html(fileSplit[1]);
        $('#file_id').val(fileSplit[0]);
    });
    
});