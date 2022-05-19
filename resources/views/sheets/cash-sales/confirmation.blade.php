@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Cash Sales Confirmation</strong>
        </header>

        <section class="dataTables-padding">
            {!! Form::open(['url' => 'sheets/cash-sales/post', 'class' => 'form-horizontal form-bordered']) !!}
            <div class="form-group margin-bottom-0 margin-left-0 margin-right-0">
                <div class="clearfix"></div>
                <div class="col-md-12 padding-left-0 padding-right-0 border-top-0">
                    <div class="responsive-content">
                        <table id="cash_purchases_tbl" class="table table-bordered table-striped table-small">
                            <tr>
                                <td class="col-md-4">
                                    <label>Unit Name:</label>
                                    {{ Form::text('unit_name_1', $unitName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                    {{ Form::hidden('unit_id', $unitId) }}
                                    {{ Form::hidden('supervisor_id', $userId) }}
                                    {{ Form::hidden('supervisor_name', $userName) }}
                                </td>
                                <td class="col-md-4">
                                    <label>Reg Number:</label>
                                    {{ Form::text('reg_number_1', $regNumber, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                </td>
                                <td class="col-md-4">
                                    <label>Sale Date:</label>
                                    {{ Form::text('sale_date', $saleDate, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Z Number:</label>
                                    {{ Form::text('z_number', $zNumber, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                </td>
                                <td>
                                    <label>Z Food:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('z_food', $zFood, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                                <td>
                                    <label>Z Confectionary Food:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('z_confect_food', $zConfectFood, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Z Fruit Juice:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('z_fruit', $zFruit, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                                <td>
                                    <label>Z Minerals Water:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('z_minerals', $zMinerals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                                <td>
                                    <label>Z Confectionary:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('z_confect', $zConfect, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Total Receipts:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('cash_credit_card', $cashCreditCard, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                                <td>
                                    <label>Z Read:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('z_read', $zRead, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3"><h4>Variance Explanation:</h4></td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Variance:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('variance', $variance, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                                <td>
                                    <label>Petty Cash:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('cash_purchase', $cashPurchase, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                                <td>
                                    <label>Credit Sales:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">{{ $currencySymbol }}</span>
                                        {{ Form::text('credit_sale', $creditSales, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <h5>Are you sure you want to confirm this sale?</h5>
                    {{ Form::hidden('currency_id', $currencyId) }}
                    {{ Form::hidden('cash_count', $cashCount) }}
                    {{ Form::hidden('over_ring', $overRing) }}
                    {{ Form::hidden('sale_details', $saleDetails) }}
                    {{ Form::hidden('credit_card', $creditCard) }}
                    {{ Form::hidden('reg_management_id', $selectedRegNumber) }}
                    {{ Form::hidden('staff_cards', $staffCards) }}
                    {{ Form::hidden('sheet_id', $sheetId) }}
                    <input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm Sale'/>
                </div>
            </div>
            {!!Form::close()!!}

        <!-- If user wants to go back and change the data, this form will take the values back and fill the purchases_sheet.php form [ Start ] -->
            {!! Form::open(['url' => 'sheets/cash-sales', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
                {{ Form::hidden('currency_id', $currencyId) }}
                {{ Form::hidden('return_from', 'confirm') }}
                {{ Form::hidden('unit_id', $unitId) }}
                {{ Form::hidden('unit_name', $unitName) }}

                {{ Form::hidden('sale_date', $saleDate) }}
                {{ Form::hidden('z_number', $zNumber) }}
                {{ Form::hidden('reg_number', $selectedRegNumber) }}
                {{ Form::hidden('z_food', $zFood) }}

                {{ Form::hidden('z_confect_food', $zConfectFood) }}

                {{ Form::hidden('z_fruit', $zFruit) }}
                {{ Form::hidden('z_minerals', $zMinerals) }}
                {{ Form::hidden('z_confect', $zConfect) }}

                {{ Form::hidden('cash_count', $cashCount) }}
                {{ Form::hidden('credit_card', $creditCard) }}
                {{ Form::hidden('staff_cards', $staffCards) }}
                {{ Form::hidden('over_ring', $overRing) }}

                {{ Form::hidden('sale_details', $saleDetails) }}
                {{ Form::hidden('sheet_id', $sheetId) }}
            {!!Form::close()!!}

            <p>
                <a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter cash sales</a>
                <br/>
            </p>
        </section>
    </section>
@stop
@section('scripts')
    <script src="{{asset('js/jquery.backDetect.js')}}"></script>
    <script type="text/javascript">
        $(window).load(function () {
            $('body').backDetect(function () {
                alert('Confirm form resubmission');
                $('#re_enter_frm').submit()
            });
        });

        // Prevent double submit
        $('form').on('submit', function () {
            if ($(this).hasClass('processing')) {
                return false;
            }

            $(this).addClass('processing');
        });
    </script>
@stop