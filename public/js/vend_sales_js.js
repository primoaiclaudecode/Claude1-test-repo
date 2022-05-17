function unitCloseCheck() {
    // Restore form state
    $('.unit-check-error').remove();
    $('#submit_btn').show("slow");
    $('#vend_sales').data('isValid', true);

    var dateEl = $('#sale_date');
    var selectedUnit = $("#unit_id").val();

    if (!selectedUnit) {
        return;
    }

    // Send request
    $.ajax({
        type: "GET",
        url: "/sheets/unit/close-check",
        data: {
            unit_id: selectedUnit,
            date: dateEl.val()
        },
        dataType: 'json'
    }).done(function (data) {
        if (data.closeDate.length) {
            dateEl.parents('.input-group').after(
                $('<span />').addClass('error_message unit-check-error').html('This unit has been closed for <strong>' + data.closeDate + '</strong>')
            );

            $('#submit_btn').hide("slow");
            $('#vend_sales').data('isValid', false);
        }
    });
}

function getVendingMachines() {
    $.ajax({
        type: 'GET',
        url: "/machine_names/json",
        data: {
            unit_id: $('#unit_id').val()
        },
        dataType: 'json'
    }).done(function (data) {
        // Vending Machines
        $('#vend_machine_id option:gt(0)').remove();

        if (data.machines && data.machines.length > 0) {
            $.each(data.machines, function (index, machine) {
                $('#vend_machine_id').append(
                    $('<option />').val(machine.vend_management_id).text(machine.vend_name)
                )
            });
            $('#vend_machine_id').val(0);
        }

        // Reg number
        $('#till_numbers').empty();
        $('#empty_number').remove();

        if (data.regNumbers && data.regNumbers.length > 0) {
            $('#till_numbers').removeClass('hidden');
            
            $.each(data.regNumbers, function (index, regNumber) {
                $('#till_numbers')
                    .append(
                        $('<div />').addClass('radio')
                            .append(
                                $('<label />').css('padding-left', '0')
                                    .append(
                                        $('<input />').attr({
                                            type: 'radio',
                                            name: 'till_number_id'
                                        }).addClass('margin-right-8').val(regNumber.reg_management_id).prop('checked', index === 0)
                                    )
                                    .append(
                                        regNumber.reg_number
                                    )
                            )
                    )
            });
        } else {
            $('#till_numbers').addClass('hidden');
            $('#till_numbers')
                .after(
                    $('<input />').addClass('form-control').attr({id: 'empty_number', type: 'text', tabindex: 7 })
                )
        }
        
        setTabbing();
    });
}

function getClosing() {
    // Do not search closing if we edit Sale
    if ($('#sheet_id').val() > 0) {
        return;    
    }
    
    $.ajax({
        type: 'GET',
        url: "/closing_reading/json",
        data: {
            unit_id: $('#unit_id').val(),
            selected_machine: $('#vend_machine_id').val()
        },
        dataType: 'json',
    }).done(function (data) {
        $('#opening').val(addCommas((Math.round(data.closing * 100) / 100).toFixed(2)));

        if (data.closing) {
            $('#opening').addClass("auto_calc numeric_val_right_aligned");
            $('#opening').prop('readonly', true);
        } else {
            $('#opening').removeClass("auto_calc");
            $('#opening').prop('readonly', false);
        }
    });
}

function scrollToElement(element) {
    element.focus(function () {
        $(this).get(0).scrollIntoView({block: "center", behavior: "smooth"});
    });

    element.focus();
}

function validation() {
    // Form contains errors like Unit Closed
    if (!$('#vend_sales').data('isValid')) {
        return false;    
    }
    
    $('.error_message').remove();

    // Validate unit
    var unit = $("#unit_id");

    if (!unit.val()) {
        scrollToElement(unit);
        unit
            .after(
                $('<span />').addClass('error_message').text('Please select a Unit.')
            );

        return false;
    }

    // Validate machine
    var machine = $("#vend_machine_id");

    if (!machine.val() || machine.val() == 0) {
        scrollToElement(machine);
        machine
            .after(
                $('<span />').addClass('error_message').text('Please select a machine name.')
            );

        return false;
    }

    // Validate sale date
    var saleDate = $("#sale_date");

    var selectedDate = $("#sale_date").datepicker('getDate');
    var dateLow = new Date();
    dateLow.setMonth(dateLow.getMonth() - 12);
    var dateHigh = new Date();

    if (selectedDate < dateLow || selectedDate > dateHigh) {
        scrollToElement(saleDate);
        saleDate
            .parent()
            .after(
                $('<span />').addClass('error_message').text('Sale Date cannot be in the future or > 1 year in the past.')
            );

        return false;
    }

    // Validate Goods and Cash
    var cash = $('#cash');
    var cashAmount = parseFloat(cash.val().replace(/,/g, ""));
    var totalAmount = 0;
    var isValidGoods = true;

    $('input[name="good_amount[]"]').each(function (index, el) {
        var row = $(el).parents('.good-row');
        var rowAmount = parseFloat($(this).val().replace(/,/g, ""));

        if (rowAmount > 0) {
            if ($('.tax-rate', row).val() == 0) {
                $('.tax-rate', row)
                    .after(
                        $('<span />').addClass('error_message').text('Choose rate.')
                    )
                
                isValidGoods = false;
            }

            totalAmount += rowAmount;
        }
    })

    if (!isValidGoods) {
        return false;    
    }
    
    if (cashAmount !== totalAmount) {
        scrollToElement(cash);
        cash
            .parent()
            .after(
                $('<span />').addClass('error_message').text('Cash Count must be equal to Goods Total.')
            );

        return false;
    }

    return true;
}

function setTabbing() {
    var tabIndex = 7;

    $('input[name="till_number_id"]').each(function () {
        $(this).attr('tabindex', tabIndex++);
    });

    $('#z_read').attr('tabindex', tabIndex++);
    
    $('.good-row').each(function () {
        $(this).find('input[name="good_amount[]"]').attr('tabindex', tabIndex++);
        
        var rateDropdown = $(this).find('.tax-rate');
        
        // Set tabindex only for dropdowns with more then 1 value
        if (rateDropdown.find('option').length > 1) {
            rateDropdown.attr('tabindex', tabIndex++);
        }
    });
    
    $('#submit_btn').attr('tabindex', tabIndex++);
}

function calculations() {
    var total = 0;

    $('input[name="good_amount[]"]').each(function (index, el) {
        var rowAmount = parseFloat($(el).val().replace(/,/g, ""));
        
        if (isNaN(rowAmount)) {
            rowAmount = 0;
        }
        
        total += rowAmount;
    })

    $('#total').val(addCommas(total.toFixed(2)));
}

$(document).ready(function () {
    // Check if unit is open
    unitCloseCheck();

    // Check closing
    getClosing();

    // Calculate totals
    calculations();

    // Set tab index
    setTabbing();
    
    // Unit
    $("#unit_id").change(function () {
        unitCloseCheck();
        getVendingMachines();
    });

    // Vending machine
    $('#vend_machine_id').on('change', function() {
        getClosing();    
    });
    
    // Number format
    $('#opening, #closing, #cash').on('change', function() {
        var amount = $(this).val();
        $(this).val(addCommas((Math.round(amount * 100) / 100).toFixed(2)));
    });
    
    // Dates
    var d = new Date();
    (d.setMonth(d.getMonth() - 12)) + (d.setDate(d.getDate() + 1));

    $('#sale_date').datepicker({
        format: 'dd-mm-yyyy',
        startDate: new Date(d),
        endDate: '+0d',
        autoclose: true
    }).on('changeDate', function (e) {
        unitCloseCheck();
        $('#vend_name').focus();
    });

    $('#sale_date_icon').click(function () {
        $(document).ready(function () {
            $("#sale_date").datepicker().focus();
        });
    });

    // Currency fields    
    $('.currencyFields').each(function (index, el) {
        var amount = parseFloat($(el).val().replace(/,/g, ""));
        $(el).val(addCommas(amount.toFixed(2)));
    });

    $('input[name="good_amount[]"]').on('change', function () {
        var amount = parseFloat($(this).val().replace(/,/g, ""));

        $(this).val(addCommas(amount.toFixed(2)));

        calculations();
    });

    // Form
    $("#vend_sales").on("submit", function () {
        return validation();
    });
});