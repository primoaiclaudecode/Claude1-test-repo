function unitCloseCheck() {
    // Restore form state
    $('.unit-check-error').remove();
    $('#submit_btn').show("slow");
    $('#credit_sales').data('isValid', true);

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

function scrollToElement(element) {
    element.focus(function () {
        $(this).get(0).scrollIntoView({block: "center", behavior: "smooth"});
    });

    element.focus();
}

function validation() {
    // Form contains errors like Unit Closed
    if (!$('#credit_sales').data('isValid')) {
        return false;
    }

    $('.error_message').remove();

    // Validate unit and currency
    var unit = $("#unit_id");

    if (!unit.val()) {
        scrollToElement(unit);
        unit
            .after(
                $('<span />').addClass('error_message').text('Please select a Unit.')
            );

        return false;
    }
    
    if ($('#currency_id').val() == 0) {
        scrollToElement(unit);
        unit
            .after(
                $('<span />').addClass('error_message').text('This unit has no currency assigned.')
            );

        return false;
    }
        
    // Sale date
    var saleDate = $("#sale_date");

    var selectedDate = $("#sale_date").datepicker('getDate');
    var dateLow = new Date();
    dateLow.setMonth(dateLow.getMonth() - 12);
    var dateHigh = new Date();

    if (selectedDate < dateLow || selectedDate > dateHigh) {
        scrollToElement(selectedDate);
        selectedDate
            .parent()
            .after(
                $('<span />').addClass('error_message').text('Sale Date cannot be in the future or > 1 year in the past.')
            );

        return false;
    }

    // Validate credit reference
    var creditRef = $("#credit_reference");

    if (!creditRef.val()) {
        scrollToElement(creditRef);
        creditRef
            .after(
                $('<span />').addClass('error_message').text('Field cannot be left blank.')
            );

        return false;
    }

    // Validate docket number
    var docketNum = $("#docket_number");

    if (!docketNum.val()) {
        scrollToElement(docketNum);
        docketNum
            .after(
                $('<span />').addClass('error_message').text('Field cannot be left blank.')
            );

        return false;
    }

    // Validate cost centre
    var costCentre = $("#cost_centre");

    if (!costCentre.val()) {
        scrollToElement(costCentre);
        costCentre
            .after(
                $('<span />').addClass('error_message').text('Field cannot be left blank.')
            );

        return false;
    }

    return true;
}

function setTabbing() {
    var tabIndex = 6;

    $('input[name="gross[]"]').each(function () {
        $(this).attr('tabindex', tabIndex++);
    });

    $('#submit_btn').attr('tabindex', tabIndex++);
}

function calculations() {
    var totals = {
        goods: 0,
        vat: 0,
        gross: 0
    };

    $('input[name="gross[]"]').each(function (index, el) {
        var row = $(el).parents('.tax-row');
        var gross = parseFloat($(el).val().replace(/,/g, ""));
        var rate = parseFloat(row.data('rate'));

        if (isNaN(gross)) {
            gross = 0;
        }

        totals.gross += gross;

        if (!isNaN(rate)) {
            var goods = gross /  (1 + rate / 100);
            var vat = goods * rate / 100;

            totals.goods += goods;
            totals.vat += vat;
            
            $('input[name="vat[]"]', row).val(addCommas(vat.toFixed(2)));
            $('input[name="goods[]"]', row).val(addCommas(goods.toFixed(2)));
        }
    })

    $('#gross_total').val(addCommas(totals.gross.toFixed(2)));
    $('#goods_total').val(addCommas(totals.goods.toFixed(2)));
    $('#vat_total').val(addCommas(totals.vat.toFixed(2)));
    
    if (totals.gross > 0) {
        $('.div_sales_total').show('slow')
        $('#sales_total').text(addCommas(totals.gross.toFixed(2)));
    } else {
        $('.div_sales_total').hide('slow')
        $('#sales_total').text('');
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
        $('#currency_id').val(data.currencyId)
        $('.currency-symbol').text(data.currencySymbol)
    });
}

$(document).ready(function () {
    // Check if unit is open
    unitCloseCheck();

    // Currency
    getUnitCurrency();
    
    // Run calculations
    calculations();

    // Set tab index
    setTabbing();
    
    // Unit
    $("#unit_id").change(function () {
        unitCloseCheck();
        
        getUnitCurrency();
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
        $("#sale_date_span.error_message").html("");
        $('#credit_reference').focus();
    });

    $('#sale_date_icon').click(function () {
        $(document).ready(function () {
            $("#sale_date").datepicker().focus();
        });
    });

    $('input[name="gross[]"]').on('change', function () {
        var amount = parseFloat($(this).val().replace(/,/g, ""));

        $(this).val(addCommas(amount.toFixed(2)));

        calculations();
    });

    $('.currencyFields').each(function (index, el) {
        var amount = parseFloat($(el).val().replace(/,/g, ""));
        $(el).val(addCommas(amount.toFixed(2)));
    });
    
    // Form	
    $("#credit_sales").on("submit", function () {
        return validation();
    });

});