@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Lodgement Information</strong>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            @if(Session::has('error_message'))
                <div class="alert alert-danger"><em> {!! session('error_message') !!}</em></div>
            @endif

            {!! Form::open(['url' => 'sheets/lodgements/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'lodgements']) !!}
            {{ Form::hidden('lodgement_id', $lodgementId, array('id' => 'lodgement_id')) }}
            {{ Form::hidden('currency_id', $currencyId, array('id' => 'currency_id')) }}
            {{ Form::hidden('currencies_count', count($currencies) - 1, array('id' => 'currencies_count')) }}

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Unit Name:</label>
                <div class="col-xs-12  col-sm-9 col-md-4">
                    {!! Form::select('unit_id', $userUnits, $selectedUnit, ['id' => 'unit_id', 'class'=>'form-control margin-bottom-15', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus']) !!}
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Date:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon cursor-pointer" id="date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        {{ Form::text('date', $date, array('id' => 'date', 'class' => 'form-control text-right cursor-pointer', 'readonly' => '','tabindex' => 2)) }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Slip No.:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('slip_number', $slipNumber, array('class' => 'form-control margin-bottom-15 text-right', 'tabindex' => 3, 'id' => 'lodge_number')) }}
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">G4S Bag Number:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('bag_number', $bagNumber, array('class' => 'form-control text-right', 'tabindex' => 4, 'id' => 'g4s_bag')) }}
                </div>
            </div>

            <div class="form-group hidden">
                <input type="hidden" id="selected_cash_sales" value="{{ $selectedCashSales }}">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Cash Sales:</label>
                <div class="col-xs-12 col-sm-9 col-md-4" id="cash_sales"></div>
            </div>

            <div class="form-group hidden">
                <input type="hidden" id="selected_vending_sales" value="{{ $selectedVendingSales }}">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Vending Sales:</label>
                <div class="col-xs-12 col-sm-9 col-md-4" id="vending_sales"></div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Remarks:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    {{ Form::textarea('remarks', $remarks, array('class' => 'form-control', 'rows' => 2, 'tabindex' => 5)) }}
                </div>
            </div>

            <div class="form-group margin-left-0 margin-right-0 margin-top-35">
                <div class="col-md-12 padding-left-0 padding-right-0">
                    <div class="responsive-content">
                        <table class="table table-bordered table-striped table-small">
                            <thead>
                            <tr>
                                <th width="25%" class="text-center"><span class="tax_rate_label">Currency</span></th>
                                <th width="35%" class="text-center"><span class="net_ext_label">Cash</span></th>
                                <th width="35%" class="text-center"><span class="goods_label">Coin</span></th>
                                <th width="5%">&nbsp</th>
                            </tr>
                            </thead>
                        </table>

                        <table id="datatable" class="table table-bordered table-striped table-small">
                            @forelse ($lodgementCosts as $lodgement)
                                <tr class="data-table-row">
                                    <td width="25%">
                                        {!! Form::select('currency[]', $currencies, $lodgement['currency'], ['class'=>'form-control', 'placeholder' => 'Choose', 'dir' => 'rtl']) !!}
                                    </td>
                                    <td width="35%">
                                        <div class="input-group">
                                            <span class="input-group-addon custom-symbol">{{ $currencySymbol }}</span>
                                            {{ Form::text('cash[]', $lodgement['cash'], array('class' => 'form-control text-right currencyFields')) }}
                                        </div>
                                    </td>
                                    <td width="35%">
                                        <div class="input-group">
                                            <span class="input-group-addon custom-symbol">{{ $currencySymbol }}</span>
                                            {{ Form::text('coin[]', $lodgement['coin'], array('class' => 'form-control text-right currencyFields')) }}
                                        </div>
                                    </td>
                                    <td width="5%" id="b_drop_td">
                                        <a href="" class="delete-line">{!! Html::image('/img/b_drop.png', '', array('id' => 'b_drop')) !!}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="data-table-row">
                                    <td width="25%">
                                        {!! Form::select('currency[]', $currencies, $currencyId, ['class'=>'form-control', 'placeholder' => 'Choose', 'dir' => 'rtl']) !!}
                                    </td>
                                    <td width="35%">
                                        <div class="input-group">
                                            <span class="input-group-addon custom-symbol">{{ $currencySymbol }}</span>
                                            {{ Form::text('cash[]', '0.00', array('class' => 'form-control text-right currencyFields')) }}
                                        </div>
                                    </td>
                                    <td width="35%">
                                        <div class="input-group">
                                            <span class="input-group-addon custom-symbol">{{ $currencySymbol }}</span>
                                            {{ Form::text('coin[]', '0.00', array('class' => 'form-control text-right currencyFields')) }}
                                        </div>
                                    <td width="5%" id="b_drop_td">
                                        <a href="" class="delete-line">{!! Html::image('/img/b_drop.png', '', array('id' => 'b_drop')) !!}</a>
                                    </td>
                                </tr>
                            @endforelse
                        </table>

                        <table class="table table-bordered table-striped table-small">
                            <tr>
                                <td width="25%">
                                    <input id="add_line" class="btn btn-primary" type="button" value="add line"/>
                                </td>
                                <td width="35%">
                                    <div class="input-group"><span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                        <input id="cash_total" name="cash_total" type="text" class="auto_calc text-right form-control" value="0.00"
                                               readonly="readonly"/>
                                    </div>
                                </td>
                                <td width="35%">
                                    <div class="input-group">
                                        <span class="input-group-addon currency-symbol">{{ $currencySymbol }}</span>
                                        <input id="coin_total" name="coin_total" type="text" class="auto_calc text-right form-control" value="0.00"
                                               readonly="readonly"/>
                                    </div>
                                </td>
                                <td width="5%"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="form-group margin-left-0 margin-right-0">
                <div class="table-responsive col-md-8 div_lodgement_total hidden_element padding-left-0 padding-right-0">
                    <table class="table">
                        <tr>
                            <td class="border-top-0 padding-0"><h2>Lodgement Total</h2></td>
                            <td class="border-top-0 padding-0" align="right">
                                <h2>
                                    <span class="currency-symbol">{{ $currencySymbol }}</span>
                                    <span id="lodgement_total"></span>
                                </h2>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="form-group set-margin-left-0 set-margin-right-0">
                <input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-25" name='submit' value='Add Lodgement'/>
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
    <script src="{{ elixir('js/lodgements.js') }}"></script>
@stop