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

    // Validate Currency/Cash/Coin
    var lastRow = $('#datatable tr').last();

    var currency = $('select[name="currency"]', lastRow);

    if (currency.val() == 0) {
        scrollToElement(currency);
        currency
            .after(
                $('<span />').addClass('error_message').text('Please select a Currency')
            );

        return false;
    }

    var cash = $('input[name="cash[]"]', lastRow);

    var coin = $('input[name="coin[]"]', lastRow);

    if (parseFloat(coin.val()) === 0 && parseFloat(cash.val()) === 0) {
        scrollToElement(coin);
        coin
            .after(
                $('<span />').addClass('error_message').text('Please enter a value for Coin or Cash')
            );

        return false;
    }

    // validate duplicate currency selection
    var selectedCurrencies = [];
    var hasDuplicates = false;

    $('select[name="currency[]"]').each(function() {
        if (selectedCurrencies.includes($(this).val())) {
            hasDuplicates = true;

            scrollToElement($(this));
            $(this)
                .after(
                    $('<span />').addClass('error_message add-row-error').text('This Currency has already been selected')
                );
            
            return false;
        }
        
        selectedCurrencies.push($(this).val());
    });

    return hasDuplicates ? false : true;
}

function setTabbing() {
    var tabIndex = 6;

    $('.data-table-row').each(function () {
        $(this).find('select[name="currency[]"]').attr('tabindex', tabIndex++);
        $(this).find('input[name="cash[]"]').attr('tabindex', tabIndex++);
        $(this).find('input[name="coin[]"]').attr('tabindex', tabIndex++);
    });

    $('#add_line').attr('tabindex', tabIndex++);

    $('input[type="checkbox"]').each(function () {
        $(this).attr('tabindex', tabIndex++);
    });

    $('#submit_btn').attr('tabindex', tabIndex++);
}

function calculations() {
    var cashTotal = 0;
    var coinTotal = 0;

    $('.data-table-row').each(function (index, el) {
        var cashAmount = parseFloat($(el).find('input[name="cash[]"]').data('exchanged_amount'));

        if (isNaN(cashAmount)) {
            cashAmount = 0;
        }

        cashTotal += cashAmount;

        var coinAmount = parseFloat($(el).find('input[name="coin[]"]', $(el)).data('exchanged_amount'));

        if (isNaN(coinAmount)) {
            coinAmount = 0;
        }

        coinTotal += coinAmount;
    })

    $('#cash_total').val(addCommas(cashTotal.toFixed(2)));
    $('#coin_total').val(addCommas(coinTotal.toFixed(2)));

    if (cashTotal + coinTotal > 0) {
        $('.div_lodgement_total').slideDown("slow");
        $("#lodgement_total").html(addCommas((cashTotal + coinTotal).toFixed(2)));
    } else {
        $('.div_lodgement_total').slideUp("slow");
    }
}

function getUnitCurrency() {
    $.ajax({
        type: 'GET',
        url: "/unit_currency/json",
        data: {
            unit_id: $('#unit_id').val()
        },
        dataType: 'json'
    }).done(function (data) {
        // Unit currency
        $('#currency_id').val(data.currencyId);
        $('.currency-symbol').text(data.currencySymbol)

        // Currency dropdowns
        $('select[name="currency[]"]').each(function () {
            if ($(this).val() == 0) {
                $(this).val(data.currencyId);
                $(this).trigger('change');
                $(this).parents('.data-table-row').find('.custom-symbol').text(data.currencySymbol);
            }
        });
    });
}

function getCurrency(currencyEl) {
    $.ajax({
        type: 'GET',
        url: "/currency_data/json",
        data: {
            currency_id: currencyEl.val()
        },
        dataType: 'json'
    }).done(function (data) {
        currencyEl.parents('.data-table-row').find('.custom-symbol').text(data.currencySymbol)
    });
}

function addRow() {
    var lastRow = $('#datatable tr').last();

    // Remove previous errors
    $('.add-row-error', lastRow).remove();

    // Check for empty fields
    var currency = $('select[name="currency[]"]', lastRow);

    if (currency.val() == 0) {
        scrollToElement(currency);
        currency
            .after(
                $('<span />').addClass('error_message add-row-error').text('Please select a Currency')
            );

        return;
    }

    var cash = $('input[name="cash[]"]', lastRow);

    if (parseFloat(cash.val()) === 0) {
        scrollToElement(cash);
        cash.parents('.input-group')
            .after(
                $('<span />').addClass('error_message add-row-error').text('Please enter a value for Cash')
            );

        return;
    }

    var coin = $('input[name="coin[]"]', lastRow);

    if (parseFloat(coin.val()) == 0) {
        scrollToElement(coin);
        coin.parents('.input-group')
            .after(
                $('<span />').addClass('error_message add-row-error').text('Please enter a value for Coin')
            );

        return;
    }

    // Check for duplicate currency selection
    var lastSelectedCurrency = $('select[name="currency[]"]', lastRow).val();
    var hasDuplicates = false;
    
    $('select[name="currency[]"]').not(':last').each(function() {
        if ($(this).val() == lastSelectedCurrency) {
            hasDuplicates = true;
            
            return false;
        }
    });

    if (hasDuplicates) {
        scrollToElement(currency);
        currency
            .after(
                $('<span />').addClass('error_message add-row-error').text('This Currency has already been selected')
            );

        return;
    }
    
    // Add new row
    var newRow = lastRow.clone(true);

    $('select[name="currency[]"]', newRow).val($('#currency_id').val()).trigger('change');
    $('input[name="cash[]"]', newRow).val('0.0');
    $('input[name="coin[]"]', newRow).val('0.0');

    lastRow.after(newRow);

    // Set focus on the added line
    $('select[name="currency[]"]', newRow).focus();

    // Hide "Add Line" button
    if ($('.data-table-row').length == $('#currencies_count').val()) {
        $('#add_line').hide();
    }

    setTabbing();
}

function exchangeAmount(currencyEl) {
    var amount = parseFloat(currencyEl.val().replace(/,/g, ""));
    var domesticCurrency = parseInt(currencyEl.parents('.data-table-row').find('select[name="currency[]"]').val());
    var foreignCurrency = parseInt($('#currency_id').val());
    var date = $('#date').val();

    if (amount === 0 || domesticCurrency === 0 || foreignCurrency === 0) {
        currencyEl.removeData('exchanged_amount');

        calculations();

        return;
    }

    if (domesticCurrency === foreignCurrency) {
        currencyEl.data('exchanged_amount', amount);

        calculations();

        return;
    }

    $.ajax({
        type: 'GET',
        url: "/exchange_amount/json",
        data: {
            amount: amount,
            domestic_currency_id: domesticCurrency,
            foreign_currency_id: foreignCurrency,
            date: date,
        },
        dataType: 'json'
    }).done(function (data) {
        currencyEl.data('exchanged_amount', data);

        calculations();
    });
}

$(document).ready(function () {
    // Check if unit is open
    unitCloseCheck();

    // Get sales
    getSales();

    // Currency
    getUnitCurrency();

    // Calculate totals
    calculations();

    // Unit
    $("#unit_id").change(function () {
        unitCloseCheck();

        getSales();

        getUnitCurrency();
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
        var amount = parseFloat($(el).val().replace(/,/g, ""));

        $(el).val(addCommas(amount.toFixed(2)));

        exchangeAmount($(this));
    });

    $('.currencyFields').on('change', function () {
        var amount = parseFloat($(this).val().replace(/,/g, ""));

        $(this).val(addCommas(amount.toFixed(2)));

        exchangeAmount($(this));
    });

    // Currency dropdown
    $(document).on('change', 'select[name="currency[]"]', function () {
        getCurrency($(this));

        $(this).parents('.data-table-row').find('.currencyFields').each(function () {
            exchangeAmount($(this));
        });
    });

    // Costs
    $('#add_line').on('click', function () {
        addRow();
    });

    $(document).on('click', '.delete-line', function (e) {
        e.preventDefault();

        if ($('.data-table-row').length === 1) {
            alert('Cannot delete all the row.');
            return;
        }

        $(this).parents('.data-table-row').remove();

        // Show "Add Line" button
        $('#add_line').show();

        setTabbing();

        calculations();
    });

    // Form
    $("#lodgements").on("submit", function () {
        return validation();
    });
});