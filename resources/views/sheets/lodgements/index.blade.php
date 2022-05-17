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
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Cash:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group margin-bottom-15">
                        <span class="input-group-addon">&euro;</span>
                        {{ Form::text('cash', $cash, array('class' => 'form-control text-right currencyFields', 'tabindex' => 3, 'id' => 'lodge_cash')) }}
                    </div>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Slip No.:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('slip_number', $slipNumber, array('class' => 'form-control text-right', 'tabindex' => 4, 'id' => 'lodge_number')) }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Coin:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group margin-bottom-15">
                        <span class="input-group-addon">&euro;</span>
                        {{ Form::text('coin', $coin, array('class' => 'form-control text-right currencyFields', 'tabindex' => 5, 'id' => 'lodge_coin')) }}
                    </div>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">G4S Bag Number:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('bag_number', $bagNumber, array('class' => 'form-control text-right', 'tabindex' => 6, 'id' => 'g4s_bag')) }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Total:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon">&euro;</span>
                        {{ Form::text('total', '0.00', array('id' => 'total', 'class' => 'form-control text-right auto_calc', 'readonly' => 'readonly')) }}
                    </div>
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
                    {{ Form::textarea('remarks', $remarks, array('class' => 'form-control', 'rows' => 2, 'tabindex' => 7)) }}
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
        }
    </style>

    <script src="{{ elixir('js/format_number.js') }}"></script>
    <script src="{{ elixir('js/lodgements.js') }}"></script>
@stop