function unitCloseCheck() {
    // Restore form state
    $('.unit-check-error').remove();
    $('#submit_btn').show("slow");
    $('#lodgements').data('isValid', true);

    var dateEl = $('#date');
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
            $('#lodgements').data('isValid', false);
        }
    });
}

function getSales() {
    $.ajax({
        type: 'GET',
        url: '/lodgement_sales/json',
        data: {
            unit_id: $('#unit_id').val(),
            lodgement_id: $('#lodgement_id').val()
        },
        dataType: 'json'
    }).done(function (data) {
        // Cash Sales
        var selectedCashSales = $('#selected_cash_sales').val().split(',')
        
        $('#cash_sales').empty();

        if (data.cashSales && data.cashSales.length > 0) {
            $.each(data.cashSales, function (index, sale) {
                $('#cash_sales').append(
                    $('<div />').addClass('checkbox')
                        .append(
                            $('<label />')
                                .append(
                                    $('<input />')
                                        .attr({
                                            type: 'checkbox',
                                            name: 'cash_sales[]'
                                        })
                                        .prop('checked', selectedCashSales.includes(String(sale.id)))
                                        .addClass('checkboxes')
                                        .val(sale.id)
                                )
                                .append(
                                    $('<span />')
                                        .addClass('currency-symbol')
                                        .text('€')
                                )
                                .append(
                                    sale.title
                                )
                        )
                )
            });
        }

        // Vending Sales
        var selectedVendingSales = $('#selected_vending_sales').val().split(',')

        $('#vending_sales').empty();

        if (data.vendingSales && data.vendingSales.length > 0) {
            $.each(data.vendingSales, function (index, sale) {
                $('#vending_sales').append(
                    $('<div />').addClass('checkbox')
                        .append(
                            $('<label />')
                                .append(
                                    $('<input />')
                                        .attr({
                                            type: 'checkbox',
                                            name: 'vending_sales[]'
                                        })
                                        .prop('checked', selectedVendingSales.includes(String(sale.id)))
                                        .addClass('checkboxes')
                                        .val(sale.id)
                                )
                                .append(
                                    $('<span />')
                                        .addClass('currency-symbol')
                                        .text('€')
                                )
                                .append(
                                    sale.title
                                )
                        )
                )
            });
        }
        
        setTabbing();
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
    if (!$('#lodgements').data('isValid')) {
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

    return true;
}

function setTabbing() {
    var tabIndex = 7;

    $('input[type="checkbox"]').each(function () {
        $(this).attr('tabindex', tabIndex++);
    });

    $('#submit_btn').attr('tabindex', tabIndex++);
}

function calculations() {
    var total = 0;

    $('.currencyFields').each(function (index, el) {
        var amount = parseFloat($(el).val().replace(/,/g, ""));
        
        if (isNaN(amount)) {
            a = 0;
        }
        
        total += amount;
    })

    $('#total').val(addCommas(total.toFixed(2)));
}

$(document).ready(function () {
    // Check if unit is open
    unitCloseCheck();

    // Get sales
    getSales();
    
    // Calculate totals
    calculations();

    // Unit
    $("#unit_id").change(function () {
        unitCloseCheck();
        getSales();
    });

    // Dates
    $('#date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    }).on('changeDate', function (e) {
        unitCloseCheck();
    });

    $('#date_icon').click(function () {
        $(document).ready(function () {
            $("#date").datepicker().focus();
        });
    });
    
    // Currency fields    
    $('.auto_calc').bind("cut copy paste", function (e) {
        e.preventDefault();
    });

    $('.currencyFields').each(function (index, el) {
        $(el).on('keypress', function (e) {
            var allowedChars = '-0123456789.';

            function contains(stringValue, charValue) {
                return stringValue.indexOf(charValue) > -1;
            }

            var invalidKey = e.key.length === 1 && !contains(allowedChars, e.key)
                || e.key === '.' && contains(e.target.value, '.') || e.key === '-' && contains(e.target.value, '-');
            invalidKey && e.preventDefault();
        });
        
        var amount = parseFloat($(el).val().replace(/,/g, ""));
        
        $(el).val(addCommas(amount.toFixed(2)));
    });

    $('.currencyFields').on('change', function () {
        var amount = parseFloat($(this).val().replace(/,/g, ""));

        $(this).val(addCommas(amount.toFixed(2)));

        calculations();
    });

    // Form
    $("#lodgements").on("submit", function () {
        return validation();
    });
});