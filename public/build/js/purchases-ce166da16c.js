function unitCloseCheck() {
    // Restore form state
    $('.unit-check-error').remove();
    $('#submit_btn').show("slow");
    $('#form_purchase').data('isValid', true);

    var dateEl = $('#receipt_date').length ? $('#receipt_date') : $('#invoice_date');
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
            $('#form_purchase').data('isValid', false);
        }
    });
}

function getSuppliers() {
    $.ajax({
        type: 'GET',
        url: '/unit_suppliers/json',
        data: {
            unit_id: $('#unit_id').val()
        },
        dataType: 'json'
    }).done(function (data) {
        $('#supplier').val('').removeClass('hidden');
        $('#supplier_id').addClass('hidden');

        if (data.length > 0) {
            $('#supplier_id').empty();

            $.each(data, function (index, supplier) {
                $('#supplier_id').append(
                    $('<option />').val(supplier.suppliers_id).text(supplier.supplier_name)
                )
            });

            $('#supplier').addClass('hidden');
            $('#supplier_id').removeClass('hidden').val(0);
        }
    });
}

function getSupplierCurrency() {
    $.ajax({
        type: 'GET',
        url: "/supplier_currency/json",
        data: {
            supplier_id: $('#supplier_id').val()
        },
        dataType: 'json'
    }).done(function (data) {
        $('#currency_id').val(data.currencyId)
        $('.currency-symbol').text(data.currencySymbol)
    });
}

function getCurrency() {
    $.ajax({
        type: 'GET',
        url: "/currency_data/json",
        data: {
            currency_id: $('#currency_id').val()
        },
        dataType: 'json'
    }).done(function (data) {
        $('.currency-symbol').text(data.currencySymbol)
    });
}

function setTabbing() {
    var tabIndex = $('#purch_type').val() === 'cash' ? 7 : 6;
    
    $('.data-table-row').each(function() {
        $(this).find('select[name="net_ext[]"]').attr('tabindex', tabIndex++);
        $(this).find('input[name="goods[]"]').attr('tabindex', tabIndex++);
        $(this).find('select[name="tax_rate[]"]').attr('tabindex', tabIndex++);
    });

    $('#add_line').attr('tabindex', tabIndex++);
    $('#submit_btn').attr('tabindex', tabIndex++);
}

function scrollToElement (element) {
    element.focus(function() {
        $(this).get(0).scrollIntoView({block: "center", behavior: "smooth"});    
    });
    
    element.focus();
}

function validation() {
    // Form contains errors like Unit Closed
    if (!$('#form_purchase').data('isValid')) {
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

    // Validate supplier
    var supplier = $("#supplier").hasClass('hidden') ? $("#supplier_id") : $("#supplier");

    if (!supplier.val() || supplier.val() == 0) {
        scrollToElement(supplier);
        supplier
            .parent()
            .append(
                $('<span />').addClass('error_message').text('Please select a Supplier.')
            );

        return false;
    }

    // Only Credit Purchase validation
    if ($('#purch_type').val() === 'credit') {
        // Validate invoice date
        var invoiceDate = $("#invoice_date");

        var selectedDate = $("#invoice_date").datepicker('getDate');
        var dateLow = new Date();
        dateLow.setMonth(dateLow.getMonth() - 12);
        var dateHigh = new Date();

        if (selectedDate < dateLow || selectedDate > dateHigh) {
            scrollToElement(invoiceDate);
            invoiceDate
                .parent()
                .after(
                    $('<span />').addClass('error_message').text('Invoice Date cannot be in the future or > 1 year in the past.')
                );

            return false;
        }

        // Validate invoice number
        var invoiceNumber = $("#invoice_number")

        if (!invoiceNumber.val()) {
            scrollToElement(invoiceNumber)
            invoiceNumber
                .after(
                    $('<span />').addClass('error_message').text('Field cannot be left blank.')
                );

            return false;
        }

        // AJAX call to check Supplier / Invoice Number uniqueness
        var sheet = $("#sheet_id");
        var data = 'supplier=' + supplier.val() + '&invoice_number=' + invoiceNumber.val();
        var isUnique = true;

        if (sheet.val()) {
            data += '&sheet_id=' + sheet.val()
        }

        $.ajax({
            type: "GET",
            url: "/supplier_invoice_no_unique/json",
            data: data,
            success: function (result) {
                if (!result.available) {
                    isUnique = false;
                }
            },
            async: false
        });

        if (!isUnique) {
            scrollToElement(invoiceNumber)
            invoiceNumber
                .after(
                    $('<span />').addClass('error_message').text('This Invoice Number is already in use.')
                );

            return false;
        }
    }
    
    // Currency
    if ($('#purch_type').val() === 'credit') {
        var supplier = $("#supplier_id");
        var currency = $("#currency_id");

        if (!currency.val() || currency.val() == 0) {
            scrollToElement(supplier);
            supplier
                .after(
                    $('<span />').addClass('error_message').text('This supplier has no currency assigned.')
                );

            return false;
        }
    } else {
        var currency = $("#currency_id");

        if (!currency.val()) {
            scrollToElement(currency);
            currency
                .after(
                    $('<span />').addClass('error_message').text('Please select a Currency.')
                );

            return false;
        }
    }
    
    // Validate netExt, goods, rates
    var lastRow = $('#datatable tr').last();

    var netExt = $('select[name="net_ext[]"]', lastRow);

    if (!netExt.val()) {
        scrollToElement(netExt);
        netExt
            .after(
                $('<span />').addClass('error_message add-row-error').text('Please select a net ext')
            )

        return false;
    }

    var goods = $('input[name="goods[]"]', lastRow);

    if (parseFloat(goods.val()) == 0) {
        scrollToElement(goods);
        goods.parents('.input-group')
            .after(
                $('<span />').addClass('error_message add-row-error').text('Please enter a value for goods')
            )

        return false;
    }

    var taxRate = $('select[name="tax_rate[]"]', lastRow);

    if (!taxRate.val()) {
        scrollToElement(taxRate);
        taxRate
            .after(
                $('<span />').addClass('error_message add-row-error').text('Please select a tax rate')
            )

        return false;
    }
    
    return true;    
}

function addRow() {
    var lastRow = $('#datatable tr').last();
    
    // Remove previous errors
    $('.add-row-error',lastRow).remove();
    
    // Check for empty fields
    var netExt = $('select[name="net_ext[]"]', lastRow);

    if (!netExt.val()) {
        netExt
            .after(
                $('<span />').addClass('error_message add-row-error').text('Please select a net ext')
            )

        return;
    }
    
    var goods = $('input[name="goods[]"]', lastRow);
    
    if (parseFloat(goods.val()) == 0) {
        goods.parents('.input-group')
            .after(
                $('<span />').addClass('error_message add-row-error').text('Please enter a value for goods')
            )
        
        return;
    }

    var taxRate = $('select[name="tax_rate[]"]', lastRow);

    if (!taxRate.val()) {
        taxRate
            .after(
                $('<span />').addClass('error_message add-row-error').text('Please select a tax rate')
            )
        
        return;
    }
    
    var newRow = lastRow.clone(true);

    $('select[name="net_ext[]"]', newRow).val('');
    $('input[name="goods[]"]', newRow).val('0.0');
    $('select[name="tax_rate[]"]', newRow).val('');
    $('input[name="vat[]"]', newRow).val('0.0');
    $('input[name="gross[]"]', newRow).val('0.0');
    
    lastRow.after(newRow);

    // Set focus on the added line
    $('select[name="net_ext[]"]', newRow).focus();
    
    setTabbing();
}

function calculations() {
    var tableTotals = {
      goods: 0,
      vat: 0,
      gross: 0  
    };

    var rateValues = {};

    var rateTotals = {
        goods: 0,
        vat: 0,
        gross: 0
    };
    
    $('.data-table-row').each(function() {
        var row = $(this);
        var goods = parseFloat($('input[name="goods[]"]', row).val().replace(/,/g, ""));
        var taxRateId = $('select[name="tax_rate[]"]', row).val();
        var rate = parseFloat($('#tax_row_' + taxRateId).data('rate'));
        
        if (isNaN(goods)) {
            goods = 0;
        }
        
        $('input[name="goods[]"]', row).val(addCommas(goods.toFixed(2)));

        tableTotals.goods += goods;
        
        if (!isNaN(rate)) {
            var vat = goods * rate / 100;
            var  gross = goods + goods * rate / 100;
            
            $('input[name="vat[]"]', row).val(addCommas(vat.toFixed(2)));
            $('input[name="gross[]"]', row).val(addCommas(gross.toFixed(2)));
            
            // Rate values
            if (!rateValues[taxRateId]) {
                rateValues[taxRateId] = {
                    goods: 0,
                    vat: 0,
                    gross: 0
                } 
            }

            rateValues[taxRateId].goods += goods;
            rateValues[taxRateId].vat += vat;
            rateValues[taxRateId].gross += gross;

            // Table totals
            tableTotals.vat += vat;
            tableTotals.gross += gross;

            // Rate totals
            rateTotals.goods += goods;
            rateTotals.vat += vat;
            rateTotals.gross += gross;
        }
    });

    // Table totals
    $('#goods_total').val(addCommas(tableTotals.goods.toFixed(2)));
    $('#vat_total').val(addCommas(tableTotals.vat.toFixed(2)));
    $('#gross_total').val(addCommas(tableTotals.gross.toFixed(2)));

    // Tax rates
    $.each(rateValues, function(index, total) {
        var taxRow = $('#tax_row_' + index);
        
        $('input[name="analysis_goods[]"]', taxRow).val(addCommas(total.goods.toFixed(2)));
        $('input[name="analysis_vat[]"]', taxRow).val(addCommas(total.vat.toFixed(2)));
        $('input[name="analysis_gross[]"]', taxRow).val(addCommas(total.gross.toFixed(2)));
    })

    // Tax rates total
    $('#analysis_goods_total').val(addCommas(rateTotals.goods.toFixed(2)));
    $('#analysis_vat_total').val(addCommas(rateTotals.vat.toFixed(2)));
    $('#analysis_gross_total').val(addCommas(rateTotals.gross.toFixed(2)));
    
    // Invoice total
    if(rateTotals.gross > 0) {
        $('.div_invoice_total').slideDown("slow");
        $("#invoice_total").html(addCommas(tableTotals.gross.toFixed(2)));
    } else {
        $('.div_invoice_total').slideUp("slow");
    }
}

$(document).ready(function () {
    // Check if unit is open
    unitCloseCheck();
    
    // Currency
    if ($('#purch_type').val() === 'cash') {
        getCurrency()
    }
    
    // Run calculations
    calculations();
    
    // Set tab index
    setTabbing();

    // Unit actions: get suppliers and check fo close
    $("#unit_id").change(function () {
        $('.error_message').remove();
        
        unitCloseCheck();

        // Only credit purchases has supplier
        if ($('#purch_type').val() == 'credit') {
            getSuppliers();
        }
    });

    // Supplier actions: get supplier currency
    $("#supplier_id").change(function () {
        getSupplierCurrency();
    });
    
    $('select[name="currency_id"]').on('change', function() {
        getCurrency();
    });
    
    // Date actions
    var d = new Date();
    (d.setMonth(d.getMonth() - 12)) + (d.setDate(d.getDate() + 1));

    $('#receipt_date').datepicker({
        format: 'dd-mm-yyyy',
        startDate: new Date(d),
        endDate: '+0d',
        autoclose: true
    }).on('changeDate', function (e) {
        unitCloseCheck();
        
        $('#reference_number').focus();
    });

    $('#invoice_date').datepicker({
        format: 'dd-mm-yyyy',
        startDate: new Date(d),
        endDate: '+0d',
        autoclose: true
    }).on('changeDate', function (e) {
        unitCloseCheck();

        if ($('#supplier_id').hasClass('hidden')) {
            $('#supplier').focus();
        } else {
            $('#supplier_id').focus();
        }
    });

    $('#receipt_date_icon').click(function () {
        $(document).ready(function () {
            $("#receipt_date").datepicker().focus();
        });
    });

    $('#invoice_date_icon').click(function () {
        $(document).ready(function () {
            $("#invoice_date").datepicker().focus();
        });
    });

    // Form actions
    $('#add_line').on('click', function() {
        addRow();
    });

    $(document).on('click', '.delete-line', function(e) {
        e.preventDefault();
        
        if ($('.data-table-row').length === 1) {
            alert('Cannot delete all the row.');
            return;
        }
        
        $(this).parents('.data-table-row').remove();

        setTabbing();
        calculations();
    });
    
    $("#form_purchase").on("submit", function () {
        return validation();
    });

    $("#unit_id").on("change", function () {
        let currencies = $(this).attr('currencies');
        let val = $(this).val();
        currencies = JSON.parse(currencies);
        if (typeof currencies[val] !== 'undefined' && typeof currencies[val][0] !== 'undefined'){
            $("#currency_id").val(currencies[val][0]).trigger('change');
            if (currencies[val].length === 1){
                $("#currency_id").attr('disabled','disabled');
            } else {
                $("#currency_id").removeAttr('disabled');
            }
        }
    });
    $("#currency_id").on("change", function () {
        let purchType =  $("#unit_id").attr('purchType');
        let val = $(this).val();
        $.ajax({
            type: "GET",
            url: "/sheets/get-tax-codes-by-currency",
            data: {
                currency_id: val,
                purchType: purchType,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (result) {
                let $el = $(".tax_rate");
                $el.each(function (){
                    let thisV = $(this);
                    $(this).empty(); // remove old options
                    $.each(result, function(key,value) {
                        console.log(value,thisV);
                        thisV.append($("<option></option>")
                            .attr("value", value.tax_code_ID).text(value.tax_code_display_rate));
                    });
                });
            },
            async: false
        });
    });


});