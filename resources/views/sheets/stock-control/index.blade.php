@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Stock Control</strong>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            {!! Form::open(['url' => 'sheets/stock-control/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'stock_control_form']) !!}
            <div class="form-group">
                <label class="col-xs-12 col-sm-2 col-lg-2 control-label custom-labels">Unit Name:</label>
                <div class="col-xs-12 col-sm-7 col-lg-3">
                    {!! Form::select('unit_name', $userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control margin-bottom-15', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus']) !!}
                    <span id="unit_name_span" class="error_message"></span>
                </div>
                <div class="col-xs-12 col-sm-3 col-lg-4">
                    <span id="closed_unit" class="error_message" style="display: none;"></span>
                </div>
            </div>

            <div class="responsive-content margin-top-25">
                <div style="min-width: 990px">
                    <div class="form-group margin-top-25">
                        <label class="col-xs-5 custom-labels text-center padding-left-18"><h4>Previous Stock:</h4></label>
                        <label class="col-xs-4 custom-labels text-center"><h4>Current Stock:</h4></label>
                        <label class="col-xs-3 custom-labels text-center"><h4>Stock +/-:</h4></label>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels">Stock Date:</label>
                        <div class="col-xs-3">
                            {{ Form::text('stock_take_date_prev', null, array('class' => 'form-control', 'readonly' => 'readonly', 'id' => 'stock_take_date_prev','lessthan'=>'#stock_take_date')) }}
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                {{ Form::text('stock_take_date', $stockTakeDate ? $stockTakeDate : $todayDate, array('id' => 'stock_take_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 2, 'readonly' => '')) }}
                                <span class="input-group-addon cursor-pointer" id="stock_take_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels">Foods:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('foods_prev', '0.00', array('class' => 'form-control text-right', 'readonly' => 'readonly', 'id' => 'foods_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('foods', $foods ?: '0.00', array('id' => 'foods', 'class' => 'form-control text-right currencyFields', 'tabindex' => 3)) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('foods_delta', '0.00', array('id' => 'foods_delta', 'class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels">Min./Alc.:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('minerals_prev', '0.00', array('class' => 'form-control text-right', 'readonly' => 'readonly', 'id' => 'minerals_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('minerals', $minerals ?: '0.00', array('id' => 'minerals', 'class' => 'form-control text-right currencyFields', 'tabindex' => 4)) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('minerals_delta', '0.00', array('id' => 'minerals_delta', 'class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels">Snacks:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('choc_snacks_prev', '0.00', array('class' => 'form-control text-right', 'readonly' => 'readonly', 'id' => 'choc_snacks_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('choc_snacks', $choc_snacks ?: '0.00', array('id' => 'choc_snacks', 'class' => 'form-control text-right currencyFields', 'tabindex' => 5)) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('choc_snacks_delta', '0.00', array('id' => 'choc_snacks_delta', 'class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels">Vending:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('vending_prev', '0.00', array('class' => 'form-control text-right', 'readonly' => 'readonly', 'id' => 'vending_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('vending', $vending ?: '0.00', array('id' => 'vending', 'class' => 'form-control text-right currencyFields', 'tabindex' => 6)) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('vending_delta', '0.00', array('id' => 'vending_delta', 'class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels font-size-16">Food / Min. Total:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('foods_plus_minerals_prev', '0.00', array('class' => 'form-control text-right auto_calc', 'readonly' => 'readonly', 'id' => 'foods_plus_minerals_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('foods_plus_minerals', $foodsPlusMinerals ?: '0.00', array('id' => 'foods_plus_minerals', 'class' => 'form-control text-right auto_calc')) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('foods_plus_minerals_delta', '0.00', array('id' => 'foods_plus_minerals_delta', 'class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels">Chemicals:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('chemicals_prev', '0.00', array('class' => 'form-control text-right', 'readonly' => 'readonly', 'id' => 'chemicals_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('chemicals', $chemicals ?: '0.00', array('id' => 'chemicals', 'class' => 'form-control text-right currencyFields', 'tabindex' => 7)) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('chemicals_delta', '0.00', array('id' => 'chemicals_delta', 'class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels">Disposables:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('clean_disp_prev', '0.00', array('class' => 'form-control text-right', 'readonly' => 'readonly', 'id' => 'clean_disp_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('clean_disp', $cleanDisp ?: '0.00', array('id' => 'clean_disp', 'class' => 'form-control text-right currencyFields', 'tabindex' => 8)) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('clean_disp_delta', '0.00', array('id' => 'clean_disp_delta', 'class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels">Free Issues:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('free_issues_prev', '0.00', array('class' => 'form-control text-right', 'readonly' => 'readonly', 'id' => 'free_issues_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('free_issues', $freeIssues ?: '0.00', array('id' => 'free_issues', 'class' => 'form-control text-right currencyFields', 'tabindex' => 9)) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('free_issues_delta', '0.00', array('id' => 'free_issues_delta', 'class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels font-size-16">Chem / Disp. / F.I. Total:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('total_chemicals_clean_disp_free_issues_prev', '0.00', array('class' => 'form-control text-right auto_calc', 'readonly' => 'readonly', 'id' => 'total_chemicals_clean_disp_free_issues_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('total_chemicals_clean_disp_free_issues', $totalChemicalsCleanDispFreeIssues ?: '0.00', array('id' => 'total_chemicals_clean_disp_free_issues', 'class' => 'form-control text-right auto_calc')) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('total_chemicals_clean_disp_free_issues_delta', '0.00', array('id' => 'total_chemicals_clean_disp_free_issues_delta', 'class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels font-size-16">Overall Total:</label>
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('total_prev', '0.00', array('class' => 'form-control text-right auto_calc', 'readonly' => 'readonly', 'id' => 'total_prev')) }}
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('total', $total ?: '0.00', array('id' => 'total', 'class' => 'form-control text-right auto_calc')) }}
                            </div>
                        </div>

                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                {{ Form::text('total_delta', '0.00', array('id' => 'total_delta', 'class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-2 control-label custom-labels">Comments:</label>
                        <div class="col-xs-3">
                            <textarea name="comments_prev" id="comments_prev" class="form-control" readonly=""></textarea>
                        </div>

                        <div class="col-xs-4">
                            <textarea name="comments" id="comments" class="form-control" tabindex="10">{{ $comments }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group set-margin-left-0 set-margin-right-0">
                {{ Form::hidden('hidden_unit_name', $unitName, array('id' => 'hidden_unit_name')) }}
                <input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-35" name='submit' value='Submit'
                       tabindex='11'/>
            </div>
            {!!Form::close()!!}
        </section>
    </section>
@stop

@section('scripts')
    <script src="{{ elixir('js/format_number.js') }}"></script>
    <script src="{{ elixir('js/stock_control_js.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            //
            $('#stock_control_form').validate({
                rules: {
                    unit_name: {
                        required: true
                    }
                },
                messages: {
                    unit_name: {
                        required: "Please select unit name."
                    }
                },
                submitHandler: function (form) {
                    var stock_take_date_prev = $('#stock_take_date_prev').val();
                    var stock_take_date = $('#stock_take_date').val();

                    if (stock_take_date_prev != "") {
                        var prevDate = stock_take_date_prev.split('-');
                        var currDate = stock_take_date.split('-');

                        if ((new Date(prevDate[2], prevDate[1] - 1, prevDate[0])) > (new Date(currDate[2], currDate[1] - 1, currDate[0]))) {
                            $('#closed_unit').text('Current Stock date must be later than previous stock date.');
                            $('#closed_unit').show();
                            $('#stock_take_date').focus();
                            return false;
                        } else {
                            $('#closed_unit').text('');
                            $('#closed_unit').hide();
                            return true;
                        }
                    } else {
                        return true;
                    }
                }
            });

            $.validator.addMethod("lessThan", function (value, element, params) {
                var splitvalue = value.split("-");
                var datevalue = new Date(splitvalue[2], splitvalue[1] - 1, splitvalue[0]);
                if (!/Invalid|NaN/.test(datevalue)) {
                    return datevalue < new Date();
                }
                return isNaN(value) && isNaN($(params).val()) || (Number(value) < Number($(params).val()));
            }, 'Must be less than {0}.');

            $('#stock_take_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            }).on('changeDate', function (e) {
                $('#foods').focus();
                unitCloseCheck();
            });

            $("#unit_name").change(function () {
                unitCloseCheck();
                $('#hidden_unit_name').val($(this).find(':selected').text());
            });

            previousStock($("#unit_name").val());

            getUnitCurrency();

            $("#unit_name").on('change', function () {
                getUnitCurrency();
                previousStock($(this).val());
            });

            $('#stock_take_date_icon').click(function () {
                $(document).ready(function () {
                    $("#stock_take_date").datepicker().focus();
                });
            });
        });

        function unitCloseCheck() {
            var unitNameVal = $("#unit_name").val();
            var date = $("#stock_take_date").datepicker('getDate'),
                //day  = date.getDate(),
                month = date.getMonth() + 1,
                year = date.getFullYear();
            //alert(month + '---' + year);

            if (unitNameVal != 0) {
                $.ajax({
                    type: "GET",
                    url: "{{ url('/unit_close_check/json') }}",
                    data: {unit_name: unitNameVal, month: month, year: year}
                }).done(function (data) {
                    var obj = jQuery.parseJSON(data);

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
                $('#closed_unit').hide("slow");
                $('.button').slideDown("slow");
            }
        }

        function previousStock(selectedValue) {
            if (selectedValue != 0) {
                $.ajax({
                    type: 'GET',
                    url: "{{ url('/previous_stock/json') }}",
                    data: {unit_name: selectedValue}
                }).done(function (data) {

                    var unit_name_temp = $('#unit_name').val();
                    $('#stock_control_form')[0].reset();
                    $('#unit_name').val(unit_name_temp);

                    var obj = jQuery.parseJSON(data);
                    if (obj.stock_control_row != 0 && obj.stock_control_row != undefined) {
                        if (obj.stock_control_row[2]) {
                            var stock_take_date_prev = obj.stock_control_row[2].split('-');
                            $('#stock_take_date_prev').val(stock_take_date_prev[2] + '-' + stock_take_date_prev[1] + '-' + stock_take_date_prev[0]);
                        }

                        var foods_prev1 = parseFloat(obj.stock_control_row[14]).toFixed(2);
                        var minerals_prev1 = parseFloat(obj.stock_control_row[15]).toFixed(2);
                        var choc_snacks_prev1 = parseFloat(obj.stock_control_row[11]).toFixed(2);
                        var vending_prev1 = parseFloat(obj.stock_control_row[19]).toFixed(2);
                        var chemicals_prev1 = parseFloat(obj.stock_control_row[20]).toFixed(2);
                        var clean_disp_prev1 = parseFloat(obj.stock_control_row[16]).toFixed(2);
                        var free_issues_prev1 = parseFloat(obj.stock_control_row[21]).toFixed(2);

                        $('#foods_prev').val(foods_prev1);
                        $('#minerals_prev').val(minerals_prev1);
                        $('#choc_snacks_prev').val(choc_snacks_prev1);
                        $('#vending_prev').val(vending_prev1);
                        $('#chemicals_prev').val(chemicals_prev1);
                        $('#clean_disp_prev').val(clean_disp_prev1);
                        $('#free_issues_prev').val(free_issues_prev1);

                        $('#foods_plus_minerals_prev').val(round((Number(foods_prev1) + Number(minerals_prev1) + Number(choc_snacks_prev1) + Number(vending_prev1)), 2).toFixed(2));

                        $('#total_chemicals_clean_disp_free_issues_prev').val(round((Number(chemicals_prev1) + Number(clean_disp_prev1) + Number(free_issues_prev1)), 2).toFixed(2));

                        $('#total_prev').val(round((Number($('#foods_plus_minerals_prev').val()) + Number($('#total_chemicals_clean_disp_free_issues_prev').val())), 2).toFixed(2));

                        $('#comments_prev').val(obj.stock_control_row[3]);

                        $('#foods_delta').val(round(($('#foods').val() - $('#foods_prev').val()), 2).toFixed(2));

                        $('#minerals_delta').val(round(($('#minerals').val() - $('#minerals_prev').val()), 2).toFixed(2));

                        $('#choc_snacks_delta').val(round(($('#choc_snacks').val() - $('#choc_snacks_prev').val()), 2).toFixed(2));

                        $('#vending_delta').val(round(($('#vending').val() - $('#vending_prev').val()), 2).toFixed(2));

                        $('#foods_plus_minerals_delta').val(round(($('#foods_plus_minerals').val() - $('#foods_plus_minerals_prev').val()), 2).toFixed(2));

                        $('#chemicals_delta').val(round(($('#chemicals').val() - $('#chemicals_prev').val()), 2).toFixed(2));

                        $('#clean_disp_delta').val(round(($('#clean_disp').val() - $('#clean_disp_prev').val()), 2).toFixed(2));

                        $('#free_issues_delta').val(round(($('#free_issues').val() - $('#free_issues_prev').val()), 2).toFixed(2));

                        $('#total_chemicals_clean_disp_free_issues_delta').val(round(($('#total_chemicals_clean_disp_free_issues').val() - $('#total_chemicals_clean_disp_free_issues_prev').val()), 2).toFixed(2));

                        $('#total_delta').val(round((Number($('#foods_plus_minerals_delta').val()) + Number($('#total_chemicals_clean_disp_free_issues_delta').val())), 2).toFixed(2));

                        /*
                        //$('#foods_prev').val(obj.stock_control_row[14].toFixed(2));
                        //$('#minerals_prev').val(obj.stock_control_row[15].toFixed(2));
                        //$('#choc_snacks_prev').val(obj.stock_control_row[11].toFixed(2));
                        //$('#vending_prev').val(obj.stock_control_row[19].toFixed(2));
                        $('#foods_plus_minerals_prev').val(round((Number(obj.stock_control_row[14]) + Number(obj.stock_control_row[15]) + Number(obj.stock_control_row[11]) + Number(obj.stock_control_row[19])), 2).toFixed(2));
                        //$('#chemicals_prev').val(obj.stock_control_row[20].toFixed(2));
                        //$('#clean_disp_prev').val(obj.stock_control_row[16].toFixed(2));
                        //$('#free_issues_prev').val(obj.stock_control_row[21].toFixed(2));
                        $('#total_chemicals_clean_disp_free_issues_prev').val(round((Number(obj.stock_control_row[20]) + Number(obj.stock_control_row[16]) + Number(obj.stock_control_row[21])), 2).toFixed(2));
                        $('#total_prev').val(round((Number($('#foods_plus_minerals_prev').val()) + Number($('#total_chemicals_clean_disp_free_issues_prev').val())), 2).toFixed(2));
                        $('#comments_prev').val(obj.stock_control_row[3]);

                        $('#foods_delta').val(round(($('#foods').val() - $('#foods_prev').val()), 2).toFixed(2));
                        $('#minerals_delta').val(round(($('#minerals').val() - $('#minerals_prev').val()), 2).toFixed(2));
                        $('#choc_snacks_delta').val(round(($('#choc_snacks').val() - $('#choc_snacks_prev').val()), 2).toFixed(2));
                        $('#vending_delta').val(round(($('#vending').val() - $('#vending_prev').val()), 2).toFixed(2));
                        $('#foods_plus_minerals_delta').val(round(($('#foods_plus_minerals').val() - $('#foods_plus_minerals_prev').val()), 2).toFixed(2));
                        $('#chemicals_delta').val(round(($('#chemicals').val() - $('#chemicals_prev').val()), 2).toFixed(2));
                        $('#clean_disp_delta').val(round(($('#clean_disp').val() - $('#clean_disp_prev').val()), 2).toFixed(2));
                        $('#free_issues_delta').val(round(($('#free_issues').val() - $('#free_issues_prev').val()), 2).toFixed(2));
                        $('#total_chemicals_clean_disp_free_issues_delta').val(round(($('#total_chemicals_clean_disp_free_issues').val() - $('#total_chemicals_clean_disp_free_issues_prev').val()), 2).toFixed(2));
                        $('#total_delta').val(round((Number($('#foods_plus_minerals_delta').val()) + Number($('#total_chemicals_clean_disp_free_issues_delta').val())), 2).toFixed(2));
                        */
                    }

                });
            } else {
                var unit_name_temp = '';
                $('#stock_control_form')[0].reset();
                $('#unit_name').val(unit_name_temp);
            }
        }

        function getUnitCurrency() {
            if (!$('#unit_name').val()) {
                return;
            }

            $.ajax({
                type: 'GET',
                url: "/unit_currency/json",
                data: {
                    unit_id: $('#unit_name').val()
                },
                dataType: 'json'
            }).done(function (data) {
                $('.currency-symbol').text(data.currencySymbol)
            });
        }

        $('#foods').change(function () {
            $('#foods_delta').val(round(($('#foods').val() - $('#foods_prev').val()), 2).toFixed(2));
        });

        $('#minerals').change(function () {
            $('#minerals_delta').val(round(($('#minerals').val() - $('#minerals_prev').val()), 2).toFixed(2));
        });

        $('#choc_snacks').change(function () {
            $('#choc_snacks_delta').val(round(($('#choc_snacks').val() - $('#choc_snacks_prev').val()), 2).toFixed(2));
        });

        $('#vending').change(function () {
            $('#vending_delta').val(round(($('#vending').val() - $('#vending_prev').val()), 2).toFixed(2));
        });
        $('#foods, #minerals, #choc_snacks, #vending').change(function () {
            $('#foods_plus_minerals_delta').val(round(($('#foods_plus_minerals').val() - $('#foods_plus_minerals_prev').val()), 2).toFixed(2));
        });
        $('#chemicals').change(function () {
            $('#chemicals_delta').val(round(($('#chemicals').val() - $('#chemicals_prev').val()), 2).toFixed(2));
        });
        $('#clean_disp').change(function () {
            $('#clean_disp_delta').val(round(($('#clean_disp').val() - $('#clean_disp_prev').val()), 2).toFixed(2));
        });
        $('#free_issues').change(function () {
            $('#free_issues_delta').val(round(($('#free_issues').val() - $('#free_issues_prev').val()), 2).toFixed(2));
        });
        $('#chemicals, #clean_disp, #free_issues').change(function () {
            $('#total_chemicals_clean_disp_free_issues_delta').val(round(($('#total_chemicals_clean_disp_free_issues').val() - $('#total_chemicals_clean_disp_free_issues_prev').val()), 2).toFixed(2));
        });
        $('#foods, #minerals, #choc_snacks, #vending, #chemicals, #clean_disp, #free_issues').change(function () {
            $('#total').val(round((Number($('#foods_plus_minerals').val()) + Number($('#total_chemicals_clean_disp_free_issues').val())), 2).toFixed(2));
            $('#total_delta').val(round((Number($('#foods_plus_minerals_delta').val()) + Number($('#total_chemicals_clean_disp_free_issues_delta').val())), 2).toFixed(2));
        });
    </script>
@stop