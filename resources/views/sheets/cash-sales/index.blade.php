@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Cash Sales</strong>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            {!! Form::open(['url' => 'sheets/cash-sales/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'cash_sales']) !!}
            {{ Form::hidden('currency_id', $selectedCurrency, array('id' => 'currency_id')) }}

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Unit Name:</label>
                    <div class="col-xs-12 col-sm-9 col-md-4">
                        {!! Form::select('unit_name', $userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus']) !!}
                    <span id="unit_name_span" class="error_message"></span>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Sale Date:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        {{ Form::text('sale_date', $saleDate ? $saleDate : $todayDate, array('id' => 'sale_date', 'class' => 'form-control text-right', 'tabindex' => 3, 'readonly' => '')) }}
                        <span class="input-group-addon cursor-pointer" id="sale_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                    <span id="sale_date_span" class="error_message"></span>
                    <span id="closed_unit" class="error_message" style="display: none;"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Reg Number:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div id="reg_num" class="margin-bottom-15">
                        {{ Form::text('reg_number', null, array('id' => 'reg_number', 'class' => 'form-control text-right margin-bottom-15', 'tabindex' => 2)) }}
                    </div>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Z Number:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('z_number', $zNumber, array('id' => 'z_number', 'class' => 'form-control text-right', 'tabindex' => 4)) }}
                    <span id="z_number_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-md-offset-6 control-label custom-labels">Z Food:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('z_food', $zFood ?: "0.00", array('class' => 'form-control text-right currencyFields', 'tabindex' => 5, 'id' => 'z_food')) }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-md-offset-6 control-label custom-labels">Z Confectionary Food:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('z_confect_food', $zConfectFood ?: "0.00", array('class' => 'form-control text-right currencyFields', 'tabindex' => 6, 'id' => 'z_confect_food')) }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Cash Count:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group margin-bottom-15">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('cash_count', $cashCount ?: '0.00', array('class' => 'form-control text-right currencyFields', 'tabindex' => 10, 'id' => 'cash_count')) }}
                    </div>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Z Fruit Juice:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('z_fruit', $zFruit ?: "0.00", array('class' => 'form-control text-right currencyFields', 'tabindex' => 7, 'id' => 'z_fruit')) }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Credit Cards:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group margin-bottom-15">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('credit_card', $creditCard ?: '0.00', array('class' => 'form-control text-right currencyFields', 'tabindex' => 11, 'id' => 'credit_card')) }}
                    </div>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Z Minerals / Water:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('z_minerals', $zMinerals ?: "0.00", array('class' => 'form-control text-right currencyFields', 'tabindex' => 8, 'id' => 'z_minerals')) }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Staff Card:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group margin-bottom-15">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('staff_cards', $staffCards ?: '0.00', array('class' => 'form-control text-right currencyFields', 'tabindex' => 12, 'id' => 'staff_cards')) }}
                    </div>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Z Confectionary:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('z_confect', $zConfect ?: "0.00", array('class' => 'form-control text-right currencyFields', 'tabindex' => 9, 'id' => 'z_confect')) }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Total Receipts:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group margin-bottom-15">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('cash_credit_card', '0.00', array('class' => 'form-control text-right auto_calc', 'id' => 'cash_credit_card', 'readonly' => 'readonly')) }}
                    </div>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Z Read:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('z_read', '0.00', array('class' => 'form-control text-right auto_calc', 'id' => 'z_read', 'readonly' => 'readonly')) }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Variance:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group margin-bottom-15">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('variance', '0.00', array('class' => 'form-control text-right auto_calc', 'id' => 'variance', 'readonly' => 'readonly')) }}
                    </div>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Over-ring:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                        {{ Form::text('over_ring', $overRing, array('class' => 'form-control text-right currencyFields', 'tabindex' => 13, 'id' => 'over_ring')) }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Cash Purchases:</label>
                <div class="col-xs-12 col-sm-9 col-md-4" onclick="calculate_zread_variance()">
                    <span id="cash_purchases_chk_bxs"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Credit Sales:</label>
                <div class="col-xs-12 col-sm-9 col-md-4" onclick="calculate_zread_variance()">
                    <span id="credit_sales_chk_bxs"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Remarks:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    {{ Form::textarea('sale_details', $saleDetails, array('class' => 'form-control', 'rows' => 2, 'tabindex' => 21)) }}
                </div>
            </div>

            <div class="form-group margin-bottom-0">
                <div class="table-responsive margin-top-10 col-md-6 div_sales_total hidden_element padding-left-0 padding-right-0">
                    <table class="table">
                        <tr>
                            <td class="border-top-0 padding-0">
                                <h2>Sales Total</h2>
                            </td>
                            <td class="border-top-0 padding-0" align="right">
                                <h2>
                                    <span class="currency-symbol"></span>
                                    <span id="sales_total"></span>
                                </h2>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="form-group set-margin-left-0 set-margin-right-0">
                {{ Form::hidden('hidden_unit_name', $unitName, array('id' => 'hidden_unit_name')) }}
                {{ Form::hidden('sheet_id', $sheetId,array('id' => 'sheet_id')) }}
                <input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-25" name='submit' value='Add Sales'
                       tabindex='22'/>
            </div>
            {!!Form::close()!!}
        </section>
    </section>
@stop

@section('scripts')
    <style>
        span.currency-symbol {
            margin-left: 5px;
            margin-right: 10px;
        }
    </style>

    <script src="{{ elixir('js/format_number.js') }}"></script>
    <script src="{{ elixir('js/cash_sales_js.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            var unitId = $('#unit_name').val();

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
                $('#z_number').focus();
            });

            $('#lodge_date').datepicker({
                format: 'dd-mm-yyyy',
                forceParse: false,
                autoclose: true
            });/*.on('changeDate',function(e){
                $('#lodge_number').focus();
            });*/

            $("#unit_name").change(function () {
                $('#hidden_unit_name').val($(this).find(':selected').text());
                regNumberCashPurchasesCreditSales();
                unitCloseCheck();
            });
        });

        jQuery(window).on("load", function () {
            regNumberCashPurchasesCreditSales();
            setTimeout(function () {
                unitCloseCheck();
            }, 1000);
        });

        function regNumberCashPurchasesCreditSales() {
            var unitNameVal = $("#unit_name").val();
            var sheet_id = $("#sheet_id").val();

            if (unitNameVal != 0) {
                $.ajax({
                    type: "GET",
                    url: "{{ url('/reg_number_cash_purchases_credit_sales/json') }}",
                    data: {
                        unit_name: unitNameVal, 
	                    sheet_id: sheet_id, 
	                    selected_reg_number: '{{ $selectedRegNumber }}'
                    }
                }).done(function (data) {
                    var obj = jQuery.parseJSON(data);

                    // Reg Number Radio Buttons
                    if (obj.reg_num) {
                        $('#reg_num').html(obj.reg_num);
                        $(".radio label").css("padding-left", 0);
                        $('input[name="reg_number"]:checked').trigger('click')
                    }

                    // Cash Purchases Checkboxes
                    $('#cash_purchases_chk_bxs').html(obj.cash_purchases_data);
                    $(".checkbox label").css("padding-left", 0);


                    // Credit Sales Checkboxes
                    $('#credit_sales_chk_bxs').html(obj.credit_sales_data);
                    $(".checkbox label").css("padding-left", 0);

                });
            } else {
                $('#reg_num').html('{{ Form::text('reg_number', null, array('id' => 'reg_number', 'class' => 'form-control text-right', 'tabindex' => 2)) }}');
                $('#cash_purchases_chk_bxs').html('');
                $('#credit_sales_chk_bxs').html('');
            }
        }

        function unitCloseCheck() {
            var unitNameVal = $("#unit_name").val();
            var date = $("#sale_date").datepicker('getDate'),
                //day  = date.getDate(),
                month = date.getMonth() + 1,
                year = date.getFullYear();
            //alert(month + '---' + year);

            if (unitNameVal != 0) {
                $.ajax({
                    type: "GET",
                    url: "{{ url('/unit_close_check/json') }}",
                    data: {unit_name: unitNameVal, month: month, year: year, selected_reg_number: '{{ $selectedRegNumber }}'}
                }).done(function (data) {
                    var obj = jQuery.parseJSON(data);

                    // Reg Number Radio Buttons
                    if (obj.reg_num) {
                        $('#reg_num').html(obj.reg_num);
                        $(".radio label").css("padding-left", 0);
                    }

                    // Cash Purchases Checkboxes
                    if (obj.cash_purchases_data) {
                        $('#cash_purchases_chk_bxs').html(obj.cash_purchases_data);
                        $(".checkbox label").css("padding-left", 0);
                    }

                    // Credit Sales Checkboxes
                    if (obj.credit_sales_data) {
                        $('#credit_sales_chk_bxs').html(obj.credit_sales_data);
                        $(".checkbox label").css("padding-left", 0);
                    }

                    // Variance
                    //console.log(all);
                    var checktotal = 0;
                    for (var i = 0; i < all.length; i++) {
                        if (all[i].checked) {
                            checktotal += Number(all[i].value.replace(/,/g, ""));
                        }
                    }

                    var zRead = Number($('#z_food').val().replace(/,/g, "")) + Number($('#z_confect_food').val().replace(/,/g, "")) + Number($('#z_minerals').val().replace(/,/g, "")) + Number($('#z_confect').val().replace(/,/g, "")) + Number($('#z_fruit').val().replace(/,/g, ""));
                    var totalReceipts = Number($('#cash_count').val().replace(/,/g, "")) + Number($('#credit_card').val().replace(/,/g, "")) + Number($('#staff_cards').val().replace(/,/g, ""));

                    if (zRead != '' && totalReceipts != '') {
                        variance = totalReceipts + checktotal - zRead;
                        $('#variance').val(addCommas(variance.toFixed(2)));
                    }

                    var unitCloseStr = obj.closedUnitErrorMsg;
                    var unitCloseRes = unitCloseStr.substr(0, 4);
                    if (obj.closedUnitErrorMsg != '' && unitCloseRes == 'This') {//alert(1)
                        $('.button').removeAttr("id");
                        $('#sale_date').focus();
                        $('#closed_unit').show("slow");
                        $('#closed_unit').html(obj.closedUnitErrorMsg);
                        $('.button').hide("slow");
                        // disable enter key if error
                        $('#cash_sales').bind("keyup keypress", function (e) {
                            var code = e.keyCode || e.which;
                            if (code == 13) {
                                e.preventDefault();
                                return false;
                            }
                        });
                    } else {//alert(2)
                        // enable enter key if error
                        $('.button').attr("id", "submit_btn");
                        $('#cash_sales').bind("keyup keypress", function (e) {
                            var code = e.keyCode || e.which;
                            if (code == 13) {
                                e.preventDefault();
                                $('#submit_btn').click();
                            }
                        });
                        $('#closed_unit').hide("slow");
                        $('.button').show("slow");
                    }
                });
            } else {
                $('#reg_num').html('{{ Form::text('reg_number', null, array('id' => 'reg_number', 'class' => 'form-control text-right', 'tabindex' => 2)) }}');
                $('#cash_purchases_chk_bxs').html('');
                $('#credit_sales_chk_bxs').html('');
                $('#closed_unit').hide("slow");
                $('.button').slideDown("slow");
            }
        }
    </script>
@stop