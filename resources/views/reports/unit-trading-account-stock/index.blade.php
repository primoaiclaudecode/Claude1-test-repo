@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Unit Trading / Account Report</strong>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            {!! Form::open(['url' => 'reports/unit-trading-account-stock', 'class' => 'form-horizontal form-bordered', 'id' => 'utas_form']) !!}

            <div class="responsive-content">
                <table class="table simpleTable table-hover table-bordered table-striped margin-bottom-0 table-small">
                    <thead>
                    <tr>
                        <th>Unit Name:</th>
                        <th>From Date:</th>
                        <th>Trading Days:</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{!! Form::select('unit_name', $userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'Select Unit Name', 'tabindex' => 1, 'autofocus']) !!}</td>
                        <td>
                            <div class="input-group">
                                {{ Form::text('from_date', $fromDate, array('id' => 'from_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 2, 'readonly' => '')) }}
                                <span class="input-group-addon cursor-pointer" id="from_date_icon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </td>
                        <td><INPUT id="trading_days" type="text" class="text-right form-control" value="{{$trading_days}}" readonly="readonly"/></td>
                    </tr>
                    <tr>
                        <th>Head Count:</th>
                        <th>To Date:</th>
                        <th>Weeks:</th>
                    </tr>
                    <tr>
                        <td><INPUT id="head_count" type="text" class="text-right form-control" value="{{$head_count}}" readonly="readonly"/></td>
                        <td>
                            <div class="input-group">
                                {{ Form::text('to_date', $toDate, array('id' => 'to_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 4, 'readonly' => '')) }}
                                <span class="input-group-addon cursor-pointer" id="to_date_icon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </td>
                        <td><INPUT id="weeks" type="text" class="text-right form-control" value="{{$weeks}}" readonly="readonly"/></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="btn-toolbar margin-top-25">
                <input type='submit' id="submit_btn" class="btn btn-primary btn-md" name='submit' value='Get Report' tabindex='5'/>
                <input type='button' id="cancel_btn" class="btn btn-primary btn-md" name='cancel' value='Cancel' tabindex='6'
                       onclick="window.location='{{ $backUrl }}'"/>
            </div>

            <div class="responsive-content">
                <table class="margin-top-45 table simpleTable table-hover table-bordered table-striped table-small">
                    <thead>
                    <tr>
                        <th></th>
                        <th class="text-center">Budget</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Variance</th>
                        <th class="text-center">% of Budget</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="vertical-align-middle">Gross Sales</td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_sales_budget}}" readonly="readonly"/></td>
                        {{--<td><INPUT type="text" class="text-right form-control" value="{{$gross_sales_budget_gross_sales_pro_rata}}" readonly="readonly" /></td>--}}
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_sales_actual}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_sales_variance}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_sales_percent_of_budget}}%" readonly="readonly"/></td>
                    </tr>
                    <tr>
                        <td class="vertical-align-middle">Net Sales</td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$net_sales_budget}}" readonly="readonly"/></td>
                        {{--<td><INPUT type="text" class="text-right form-control" value="{{$net_sales_budget_net_sales_pro_rata}}" readonly="readonly" /></td>--}}
                        <td><INPUT type="text" class="text-right form-control" value="{{$net_sales_actual}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$net_sales_variance}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$net_sales_percent_of_budget}}%" readonly="readonly"/></td>
                    </tr>
                    <tr>
                        <td class="vertical-align-middle">Cost of Sales <br/>(Combined)</td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_budget}}" readonly="readonly"/></td>
                        {{--<td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_budget_cost_of_sales_pro_rata}}" readonly="readonly" /></td>--}}
                        <td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_actual}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_variance}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_percent_of_budget}}%" readonly="readonly"/>
                        </td>
                    </tr>
                    <tr class="warning">
                        <td class="vertical-align-middle">Cost of Sales <br/>(Purchases)</td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_budget}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_purchases}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_variance}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_percent_of_budget}}%" readonly="readonly"/>
                        </td>
                    </tr>
                    <tr class="warning">
                        <td class="vertical-align-middle">Cost of Sales <br/>(Stock delta)</td>
                        <td><INPUT type="text" class="text-right form-control" value="" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$cost_of_sales_stock_delta }}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="" readonly="readonly"/></td>
                    </tr>
                    <tr class="{{$budgetType == \App\BudgetType::BUDGET_TYPE_NET ? 'hidden' : '' }}">
                        <td class="vertical-align-middle">Gross Profit (Gross)</td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_gross_budget}}" readonly="readonly"/></td>
                        {{--<td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_budget_gross_profit_pro_rata}}" readonly="readonly" /></td>--}}
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_gross_actual}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_gross_variance}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_gross_percent_of_budget}}%"
                                   readonly="readonly"/>
                        </td>
                    </tr>
                    <tr class="{{$budgetType == \App\BudgetType::BUDGET_TYPE_GP ? 'hidden' : '' }}">
                        <td class="vertical-align-middle">Gross Profit (Net)</td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_net_budget}}" readonly="readonly"/></td>
                        {{--<td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_net_budget_gross_profit_net_pro_rata}}" readonly="readonly" /></td>--}}
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_net_actual}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_net_variance}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gross_profit_net_percent_of_budget}}%" readonly="readonly"/>
                        </td>
                    </tr>
                    <tr class="{{$budgetType == \App\BudgetType::BUDGET_TYPE_NET ? 'hidden' : '' }}">
                        <td class="vertical-align-middle">GP % Gross</td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gp_percent_gross_budget}}" readonly="readonly"/></td>
                        {{--<td><INPUT type="text" class="text-right form-control" value="{{$gpp_on_gross_sales_budget}}" readonly="readonly" /></td>--}}
                        <td><INPUT type="text" class="text-right form-control" value="{{$gp_percent_gross_actual}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gp_percent_gross_variance}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gp_percent_gross_percent_of_budget}}%" readonly="readonly"/>
                        </td>
                    </tr>
                    <tr class="{{$budgetType == \App\BudgetType::BUDGET_TYPE_GP ? 'hidden' : '' }}">
                        <td class="vertical-align-middle">GP % Net</td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gp_percent_net_budget}}" readonly="readonly"/></td>
                        {{--<td><INPUT type="text" class="text-right form-control" value="{{$gpp_on_net_sales_budget}}" readonly="readonly" /></td>--}}
                        <td><INPUT type="text" class="text-right form-control" value="{{$gp_percent_net_actual}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gp_percent_net_variance}}" readonly="readonly"/></td>
                        <td><INPUT type="text" class="text-right form-control" value="{{$gp_percent_net_percent_of_budget}}%" readonly="readonly"/>
                        </td>
                    </tr>
                    @foreach($phasedBudgetRows as $phasedBudgetRow)
                        <tr>
                            <td class="vertical-align-middle">{{ $phasedBudgetRow['title'] }}</td>
                            <td><INPUT type="text" class="text-right form-control" value="{{ $phasedBudgetRow['value'] }}" readonly="readonly"/></td>
                            <td class="text-align-center">N/A</td>
                            <td class="text-align-center">N/A</td>
                            <td class="text-align-center">N/A</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {!!Form::close()!!}
        </section>
    </section>
@stop

@section('scripts')
    <script type="text/javascript">
        $('#from_date').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        }).on('changeDate', function (e) {
            $('#to_date').focus();
        });

        $('#to_date').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        }).on('changeDate', function (e) {
            $('#to_date').focus();
        });

        $(document).ready(function () {
            $('#from_date_icon').click(function () {
                $("#from_date").datepicker().focus();
            });

            $('#to_date_icon').click(function () {
                $("#to_date").datepicker().focus();
            });

            $('#utas_form').on('submit', function () {
                $('.error_message').remove();

                if (!$('#unit_name').val()) {
                    $("#unit_name").focus();
                    $("#unit_name")
                        .after(
                            $('<span />').addClass('error_message').text("Please select a Unit.")
                        )

                    return false;
                }

                return true;
            })
        });
    </script>
@stop
