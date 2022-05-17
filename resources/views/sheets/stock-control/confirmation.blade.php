@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Stock Control Confirmation</strong>
        </header>

        <section class="dataTables-padding">
            {!! Form::open(['url' => 'sheets/stock-control/post', 'class' => 'form-horizontal form-bordered']) !!}
            <div class="form-group margin-bottom-0 margin-left-0 margin-right-0">
                <div class="clearfix"></div>
                <div class="col-md-12 padding-left-0 padding-right-0 border-top-0">
                    <div class="responsive-content">
                        <table id="cash_purchases_tbl" class="table table-bordered table-striped table-small">
                            <tr>
                                <td>
                                    <label>Unit Name:</label>
                                    {{ Form::text('unit_name_text', $unitName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                    {{ Form::hidden('unit_id', $unitId) }}
                                    {{ Form::hidden('supervisor_id', $userId) }}
                                    {{ Form::hidden('supervisor_name', $userName) }}
                                </td>
                                <td>
                                    <label>Stock Date:</label>
                                    {{ Form::text('stock_take_date', $stockDate, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Total (Foods, Minerals, Snacks, Vending):</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">&euro;</span>
                                        {{ Form::text('total_fmsv', $foodsPlusMinerals, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                                <td>
                                    <label>Total (Chemicals, Disposables, Free Issues):</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">&euro;</span>
                                        {{ Form::text('total_cdf', $totalChemicalsCleanDispFreeIssues, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label>Total:</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">&euro;</span>
                                        {{ Form::text('totals', $total, array('class' => 'form-control auto_calc text-right', 'readonly' => 'readonly')) }}
                                    </div>
                                </td>
                                <td>
                                    <label>Comments:</label>
                                    {{ Form::text('comments', $comments, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    {{ Form::hidden('foods', $foods) }}
                    {{ Form::hidden('minerals', $minerals) }}
                    {{ Form::hidden('chemicals', $chemicals) }}
                    {{ Form::hidden('clean_disp', $cleanDisp) }}

                    {{ Form::hidden('choc_snacks', $chocSnacks) }}
                    {{ Form::hidden('vending', $vending) }}
                    {{ Form::hidden('free_issues', $freeIssues) }}

                    {{ Form::hidden('foods_delta', $foodsDelta) }}
                    {{ Form::hidden('minerals_delta', $mineralsDelta) }}
                    {{ Form::hidden('choc_snacks_delta', $chocSnacksDelta) }}
                    {{ Form::hidden('vending_delta', $vendingDelta) }}
                    {{ Form::hidden('chemicals_delta', $chemicalsDelta) }}
                    {{ Form::hidden('clean_disp_delta', $cleanDispDelta) }}
                    {{ Form::hidden('free_issues_delta', $freeIssuesDelta) }}
                    {{ Form::hidden('total_delta', $totalDelta) }}

                    {{ Form::hidden('total', $overallTotal) }}

                    <h5>Are you sure you want to add this stock?</h5>
                    <input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm Stock'/>
                </div>
            </div>
            {!!Form::close()!!}

            {!! Form::open(['url' => 'sheets/stock-control', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
                {{ Form::hidden('return_from', 'confirm') }}
                {{ Form::hidden('unit_id', $unitId) }}
                {{ Form::hidden('unit_name', $unitName) }}

                {{ Form::hidden('foods', $foods) }}
                {{ Form::hidden('minerals', $minerals) }}
                {{ Form::hidden('choc_snacks', $chocSnacks) }}
                {{ Form::hidden('vending', $vending) }}
                {{ Form::hidden('foods_plus_minerals', $foodsPlusMinerals) }}
                {{ Form::hidden('chemicals', $chemicals) }}
                {{ Form::hidden('clean_disp', $cleanDisp) }}
                {{ Form::hidden('free_issues', $freeIssues) }}
                {{ Form::hidden('total_chemicals_clean_disp_free_issues', $totalChemicalsCleanDispFreeIssues) }}
                {{ Form::hidden('total', $total) }}
                {{ Form::hidden('comments', $comments) }}
            {!!Form::close()!!}

            <p>
                <a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter stock control</a>
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