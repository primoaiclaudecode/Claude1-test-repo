@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Phased Budget</strong>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif

			{!! Form::open(['url' => 'sheets/phased-budget/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'trading_account']) !!}

			<div class="form-group">
				<div class="col-xs-12 col-sm-6 col-lg-4">
					<label class="control-label custom-labels">User Name:</label>
					{{ Form::text('user_name', $userName, array('class' => 'form-control margin-bottom-15', 'readonly' => 'readonly')) }}
				</div>

				<div class="col-xs-12 col-sm-6 col-lg-2">
					<label class="control-label custom-labels">Budget Start:</label>
					<div class="input-group margin-bottom-15">
						{{ Form::text('budget_start_date', !empty($budgetStartDate) ? $budgetStartDate : (!empty($changeLogBudget->budget_start_date) ? Carbon\Carbon::parse($changeLogBudget->budget_start_date)->format('d-m-Y') : $todayDate), array('id' => 'budget_start_date', 'class' => 'form-control text-right cursor-pointer', 'tabindex' => 2, 'onchange' => 'display_months_header(this)', 'readonly' => '')) }}
						<span class="input-group-addon cursor-pointer" id="budget_start_date_icon">
                        <i class="fa fa-calendar"></i>
                    </span>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-lg-2">
					<label class="control-label custom-labels">Budget End:</label>
					<div class="input-group">
						{{ Form::text('budget_end_date', !empty($budgetEndDate) ? $budgetEndDate : (!empty($changeLogBudget->budget_end_date) ? Carbon\Carbon::parse($changeLogBudget->budget_end_date)->format('d-m-Y') : date('d-m-Y', strtotime('+12 months - 1 day'))), array('id' => 'budget_end_date', 'class' => 'form-control text-right cursor-pointer', 'tabindex' => 3, 'readonly' => '')) }}
						<span class="input-group-addon cursor-pointer" id="budget_end_date_icon">
                        <i class="fa fa-calendar"></i>
                    </span>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-xs-12 col-sm-6 col-lg-4">
					<label class="control-label custom-labels">Unit Name:</label>
					<div>
						{!! Form::select('unit_name', $userUnits, !empty($changeLogBudget->unit_id) ? $changeLogBudget->unit_id : $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus', 'onchange' => 'changeLog()']) !!}
						<span id="unit_name_span" class="error_message"></span>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6  col-lg-2">
					<label class="control-label custom-labels">Contract Type:</label>
					<div>
						{!! Form::select('contract_type', $contractTypes, $selectedContractType, ['class'=>'form-control margin-bottom-15', 'id' => 'contract_type', 'placeholder' => 'Select contract type']); !!}
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-xs-12 col-lg-4 budget-type margin-bottom-15">
					<label class="control-label custom-labels">GP Type:</label>

					@foreach($budgetTypes as $id => $title)
						<label class="budget-type-cell">
							{{ $title }}
							<input type="radio" name="budget_type" value="{{ $id }}" {{ $selectedBudgetType == $id ? 'checked' : ''}} />
						</label>
					@endforeach
				</div>

				<div class="col-xs-12 col-lg-8 hidden_element change_log_div">
					<label class="control-label custom-labels" data-toggle="collapse" data-target="#change-log-collapse" aria-expanded="false"
					       aria-controls="change_log_table">
						Change Log <i class="fa fa-caret-down toggle-budgets-visibility"></i>
					</label>

					<div id="change-log-collapse" class="responsive-content collapse">
						<table id="change_log_table" class="table simpleTable table-hover table-bordered table-striped margin-bottom-0 table-no-wrap"></table>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-xs-12 col-sm-6 col-lg-4">
					<label class="control-label custom-labels">Entered By:</label>
					{{ Form::text('entered_by', !empty($enteredBy) ? $enteredBy : (!empty($changeLogBudget->entered_by) ? $changeLogBudget->entered_by : $userName), array('class' => 'form-control text-mobile-right margin-bottom-15', 'tabindex' => 3)) }}
				</div>

				<div class="col-xs-12 col-sm-6 col-lg-2">
					<label class="control-label custom-labels">Approved By:</label>
					{{ Form::text('approved_by', !empty($approvedBy) ? $approvedBy : (!empty($changeLogBudget->approved_by) ? $changeLogBudget->approved_by : ''), array('id' => 'approved_by', 'class' => 'form-control text-mobile-right margin-bottom-15', 'tabindex' => 4)) }}
				</div>

				<div class="col-xs-12 col-sm-6 col-lg-2">
					<label class="control-label custom-labels">Contract Type:</label>
					{{ Form::text('contract_type_legend', $contractTypeLegend, array('id' => 'contract_type_legend', 'class' => 'form-control margin-bottom-15 text-mobile-right', 'tabindex' => 5, 'readonly' => '')) }}
				</div>
			</div>

			<div class="form-group col-xs-12">
				<button type='button' id="change_rows_visibility" class="btn btn-primary" data-toggle="modal" data-target="#unit_rows_visibility">Rows
					visibility
				</button>
			</div>

			<div class="clearfix"></div>

			<div class="responsive-content">
				<table id="trading_account_tbl" class="table-medium full-width">
					<!-- Budget Year [ Starts ] -->
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td width="100%" colspan="9">
							<input name="budget_year" id="budget_year" type="text" class="textbox_budget_year" readonly="readonly"
							       value="{{ $budgetYear}}"/>
						</td>
					</tr>
					<!-- Budget Year [ Ends ] -->

					<tr id="month-headers">
						<td width="18%">&nbsp;</td>
						<td width="10%"><strong>TOTALS</strong></td>
						<td width="6%" id="month_one" align="center">Month 1</td>
						<td width="6%" id="month_two" align="center">Month 2</td>
						<td width="6%" id="month_three" align="center">Month 3</td>
						<td width="6%" id="month_four" align="center">Month 4</td>
						<td width="6%" id="month_five" align="center">Month 5</td>
						<td width="6%" id="month_six" align="center">Month 6</td>
						<td width="6%" id="month_seven" align="center">Month 7</td>
						<td width="6%" id="month_eight" align="center">Month 8</td>
						<td width="6%" id="month_nine" align="center">Month 9</td>
						<td width="6%" id="month_ten" align="center">Month 10</td>
						<td width="6%" id="month_eleven" align="center">Month 11</td>
						<td width="6%" id="month_twelve" align="center">Month 12</td>
					</tr>

					<tr>
						<td>Head Count</td>
						<td>
							<input class="form-control" type="hidden" name="head_count_totals" id="head_count_totals" value=""/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_1" id="head_count_month_1" value="{{ $headCountMonth1 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_2" id="head_count_month_2" value="{{ $headCountMonth2 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_3" id="head_count_month_3" value="{{ $headCountMonth3 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_4" id="head_count_month_4" value="{{ $headCountMonth4 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_5" id="head_count_month_5" value="{{ $headCountMonth5 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_6" id="head_count_month_6" value="{{ $headCountMonth6 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_7" id="head_count_month_7" value="{{ $headCountMonth7 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_8" id="head_count_month_8" value="{{ $headCountMonth8 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_9" id="head_count_month_9" value="{{ $headCountMonth9 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_10" id="head_count_month_10" value="{{ $headCountMonth10 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_11" id="head_count_month_11" value="{{ $headCountMonth11 }}"/>
						</td>
						<td>
							<input class="form-control" type="text" name="head_count_month_12" id="head_count_month_12" value="{{ $headCountMonth12 }}"/>
						</td>
					</tr>

					<tr>
						<td># trading days</td>
						<td><input class="form-control auto_calc" type="text" name="num_trading_days_totals" id="num_trading_days_totals"
						           value="{{ !empty($numTradingDaysTotals) ? $numTradingDaysTotals : (!empty($changeLogBudget->num_trading_days_totals) ? $changeLogBudget->num_trading_days_totals : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_1" id="num_trading_days_month_1"
						           value="{{ !empty($numTradingDaysMonth1) ? $numTradingDaysMonth1 : (!empty($changeLogBudget->num_trading_days_month_1) ? $changeLogBudget->num_trading_days_month_1 : '') }}"
						           tabindex="5"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_2" id="num_trading_days_month_2"
						           value="{{ !empty($numTradingDaysMonth2) ? $numTradingDaysMonth2 : (!empty($changeLogBudget->num_trading_days_month_2) ? $changeLogBudget->num_trading_days_month_2 : '') }}"
						           tabindex="6"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_3" id="num_trading_days_month_3"
						           value="{{ !empty($numTradingDaysMonth3) ? $numTradingDaysMonth3 : (!empty($changeLogBudget->num_trading_days_month_3) ? $changeLogBudget->num_trading_days_month_3 : '') }}"
						           tabindex="7"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_4" id="num_trading_days_month_4"
						           value="{{ !empty($numTradingDaysMonth4) ? $numTradingDaysMonth4 : (!empty($changeLogBudget->num_trading_days_month_4) ? $changeLogBudget->num_trading_days_month_4 : '') }}"
						           tabindex="8"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_5" id="num_trading_days_month_5"
						           value="{{ !empty($numTradingDaysMonth5) ? $numTradingDaysMonth5 : (!empty($changeLogBudget->num_trading_days_month_5) ? $changeLogBudget->num_trading_days_month_5 : '') }}"
						           tabindex="9"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_6" id="num_trading_days_month_6"
						           value="{{ !empty($numTradingDaysMonth6) ? $numTradingDaysMonth6 : (!empty($changeLogBudget->num_trading_days_month_6) ? $changeLogBudget->num_trading_days_month_6 : '') }}"
						           tabindex="10"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_7" id="num_trading_days_month_7"
						           value="{{ !empty($numTradingDaysMonth7) ? $numTradingDaysMonth7 : (!empty($changeLogBudget->num_trading_days_month_7) ? $changeLogBudget->num_trading_days_month_7 : '') }}"
						           tabindex="11"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_8" id="num_trading_days_month_8"
						           value="{{ !empty($numTradingDaysMonth8) ? $numTradingDaysMonth8 : (!empty($changeLogBudget->num_trading_days_month_8) ? $changeLogBudget->num_trading_days_month_8 : '') }}"
						           tabindex="12"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_9" id="num_trading_days_month_9"
						           value="{{ !empty($numTradingDaysMonth9) ? $numTradingDaysMonth9 : (!empty($changeLogBudget->num_trading_days_month_9) ? $changeLogBudget->num_trading_days_month_9 : '') }}"
						           tabindex="13"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_10" id="num_trading_days_month_10"
						           value="{{ !empty($numTradingDaysMonth10) ? $numTradingDaysMonth10 : (!empty($changeLogBudget->num_trading_days_month_10) ? $changeLogBudget->num_trading_days_month_10 : '') }}"
						           tabindex="14"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_11" id="num_trading_days_month_11"
						           value="{{ !empty($numTradingDaysMonth11) ? $numTradingDaysMonth11 : (!empty($changeLogBudget->num_trading_days_month_11) ? $changeLogBudget->num_trading_days_month_11 : '') }}"
						           tabindex="15"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_trading_days_month_12" id="num_trading_days_month_12"
						           value="{{ !empty($numTradingDaysMonth12) ? $numTradingDaysMonth12 : (!empty($changeLogBudget->num_trading_days_month_12) ? $changeLogBudget->num_trading_days_month_12 : '') }}"
						           tabindex="16"/></td>
					</tr>

					<tr>
						<td># of weeks</td>
						<td><input class="form-control auto_calc" type="text" name="num_of_weeks_totals" id="num_of_weeks_totals"
						           value="{{ !empty($numOfWeeksTotals) ? $numOfWeeksTotals : (!empty($changeLogBudget->num_of_weeks_totals) ? $changeLogBudget->num_of_weeks_totals : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_1" id="num_of_weeks_month_1"
						           value="{{ !empty($numOfWeeksMonth1) ? $numOfWeeksMonth1 : (!empty($changeLogBudget->num_of_weeks_month_1) ? $changeLogBudget->num_of_weeks_month_1 : '') }}"
						           tabindex="17"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_2" id="num_of_weeks_month_2"
						           value="{{ !empty($numOfWeeksMonth2) ? $numOfWeeksMonth2 : (!empty($changeLogBudget->num_of_weeks_month_2) ? $changeLogBudget->num_of_weeks_month_2 : '') }}"
						           tabindex="18"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_3" id="num_of_weeks_month_3"
						           value="{{ !empty($numOfWeeksMonth3) ? $numOfWeeksMonth3 : (!empty($changeLogBudget->num_of_weeks_month_3) ? $changeLogBudget->num_of_weeks_month_3 : '') }}"
						           tabindex="19"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_4" id="num_of_weeks_month_4"
						           value="{{ !empty($numOfWeeksMonth4) ? $numOfWeeksMonth4 : (!empty($changeLogBudget->num_of_weeks_month_4) ? $changeLogBudget->num_of_weeks_month_4 : '') }}"
						           tabindex="20"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_5" id="num_of_weeks_month_5"
						           value="{{ !empty($numOfWeeksMonth5) ? $numOfWeeksMonth5 : (!empty($changeLogBudget->num_of_weeks_month_5) ? $changeLogBudget->num_of_weeks_month_5 : '') }}"
						           tabindex="21"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_6" id="num_of_weeks_month_6"
						           value="{{ !empty($numOfWeeksMonth6) ? $numOfWeeksMonth6 : (!empty($changeLogBudget->num_of_weeks_month_6) ? $changeLogBudget->num_of_weeks_month_6 : '') }}"
						           tabindex="22"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_7" id="num_of_weeks_month_7"
						           value="{{ !empty($numOfWeeksMonth7) ? $numOfWeeksMonth7 : (!empty($changeLogBudget->num_of_weeks_month_7) ? $changeLogBudget->num_of_weeks_month_7 : '') }}"
						           tabindex="23"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_8" id="num_of_weeks_month_8"
						           value="{{ !empty($numOfWeeksMonth8) ? $numOfWeeksMonth8 : (!empty($changeLogBudget->num_of_weeks_month_8) ? $changeLogBudget->num_of_weeks_month_8 : '') }}"
						           tabindex="24"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_9" id="num_of_weeks_month_9"
						           value="{{ !empty($numOfWeeksMonth9) ? $numOfWeeksMonth9 : (!empty($changeLogBudget->num_of_weeks_month_9) ? $changeLogBudget->num_of_weeks_month_9 : '') }}"
						           tabindex="25"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_10" id="num_of_weeks_month_10"
						           value="{{ !empty($numOfWeeksMonth10) ? $numOfWeeksMonth10 : (!empty($changeLogBudget->num_of_weeks_month_10) ? $changeLogBudget->num_of_weeks_month_10 : '') }}"
						           tabindex="26"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_11" id="num_of_weeks_month_11"
						           value="{{ !empty($numOfWeeksMonth11) ? $numOfWeeksMonth11 : (!empty($changeLogBudget->num_of_weeks_month_11) ? $changeLogBudget->num_of_weeks_month_11 : '') }}"
						           tabindex="27"/></td>
						<td><input class="form-control currencyFields" type="text" name="num_of_weeks_month_12" id="num_of_weeks_month_12"
						           value="{{ !empty($numOfWeeksMonth12) ? $numOfWeeksMonth12 : (!empty($changeLogBudget->num_of_weeks_month_12) ? $changeLogBudget->num_of_weeks_month_12 : '') }}"
						           tabindex="28"/></td>
					</tr>

					<tr>
						<td>Gross Sales</td>
						<td><input class="form-control auto_calc" type="text" name="gross_sales_totals" id="gross_sales_totals"
						           value="{{ !empty($grossSalesTotals) ? $grossSalesTotals : (!empty($changeLogBudget->gross_sales_totals) ? number_format($changeLogBudget->gross_sales_totals) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_1" id="gross_sales_month_1"
						           value="{{ !empty($grossSalesMonth1) ? $grossSalesMonth1 : (!empty($changeLogBudget->gross_sales_month_1) ? number_format($changeLogBudget->gross_sales_month_1) : '') }}"
						           tabindex="29"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_2" id="gross_sales_month_2"
						           value="{{ !empty($grossSalesMonth2) ? $grossSalesMonth2 : (!empty($changeLogBudget->gross_sales_month_2) ? number_format($changeLogBudget->gross_sales_month_2) : '') }}"
						           tabindex="30"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_3" id="gross_sales_month_3"
						           value="{{ !empty($grossSalesMonth3) ? $grossSalesMonth3 : (!empty($changeLogBudget->gross_sales_month_3) ? number_format($changeLogBudget->gross_sales_month_3) : '') }}"
						           tabindex="31"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_4" id="gross_sales_month_4"
						           value="{{ !empty($grossSalesMonth4) ? $grossSalesMonth4 : (!empty($changeLogBudget->gross_sales_month_4) ? number_format($changeLogBudget->gross_sales_month_4) : '') }}"
						           tabindex="32"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_5" id="gross_sales_month_5"
						           value="{{ !empty($grossSalesMonth5) ? $grossSalesMonth5 : (!empty($changeLogBudget->gross_sales_month_5) ? number_format($changeLogBudget->gross_sales_month_5) : '') }}"
						           tabindex="33"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_6" id="gross_sales_month_6"
						           value="{{ !empty($grossSalesMonth6) ? $grossSalesMonth6 : (!empty($changeLogBudget->gross_sales_month_6) ? number_format($changeLogBudget->gross_sales_month_6) : '') }}"
						           tabindex="34"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_7" id="gross_sales_month_7"
						           value="{{ !empty($grossSalesMonth7) ? $grossSalesMonth7 : (!empty($changeLogBudget->gross_sales_month_7) ? number_format($changeLogBudget->gross_sales_month_7) : '') }}"
						           tabindex="35"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_8" id="gross_sales_month_8"
						           value="{{ !empty($grossSalesMonth8) ? $grossSalesMonth8 : (!empty($changeLogBudget->gross_sales_month_8) ? number_format($changeLogBudget->gross_sales_month_8) : '') }}"
						           tabindex="36"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_9" id="gross_sales_month_9"
						           value="{{ !empty($grossSalesMonth9) ? $grossSalesMonth9 : (!empty($changeLogBudget->gross_sales_month_9) ? number_format($changeLogBudget->gross_sales_month_9) : '') }}"
						           tabindex="37"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_10" id="gross_sales_month_10"
						           value="{{ !empty($grossSalesMonth10) ? $grossSalesMonth10 : (!empty($changeLogBudget->gross_sales_month_10) ? number_format($changeLogBudget->gross_sales_month_10) : '') }}"
						           tabindex="38"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_11" id="gross_sales_month_11"
						           value="{{ !empty($grossSalesMonth11) ? $grossSalesMonth11 : (!empty($changeLogBudget->gross_sales_month_11) ? number_format($changeLogBudget->gross_sales_month_11) : '') }}"
						           tabindex="39"/></td>
						<td><input class="form-control currencyFields" type="text" name="gross_sales_month_12" id="gross_sales_month_12"
						           value="{{ !empty($grossSalesMonth12) ? $grossSalesMonth12 : (!empty($changeLogBudget->gross_sales_month_12) ? number_format($changeLogBudget->gross_sales_month_12) : '') }}"
						           tabindex="40"/></td>
					</tr>

					<tr>
						<td>VAT</td>
						<td><input class="form-control auto_calc" type="text" name="vat_totals" id="vat_totals"
						           value="{{ !empty($vatTotals) ? $vatTotals : (!empty($changeLogBudget->vat_totals) ? number_format($changeLogBudget->vat_totals) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_1" id="vat_month_1"
						           value="{{ !empty($vatMonth1) ? $vatMonth1 : (!empty($changeLogBudget->vat_month_1) ? number_format($changeLogBudget->vat_month_1) : '') }}"
						           tabindex="41"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_2" id="vat_month_2"
						           value="{{ !empty($vatMonth2) ? $vatMonth2 : (!empty($changeLogBudget->vat_month_2) ? number_format($changeLogBudget->vat_month_2) : '') }}"
						           tabindex="42"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_3" id="vat_month_3"
						           value="{{ !empty($vatMonth3) ? $vatMonth3 : (!empty($changeLogBudget->vat_month_3) ? number_format($changeLogBudget->vat_month_3) : '') }}"
						           tabindex="43"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_4" id="vat_month_4"
						           value="{{ !empty($vatMonth4) ? $vatMonth4 : (!empty($changeLogBudget->vat_month_4) ? number_format($changeLogBudget->vat_month_4) : '') }}"
						           tabindex="44"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_5" id="vat_month_5"
						           value="{{ !empty($vatMonth5) ? $vatMonth5 : (!empty($changeLogBudget->vat_month_5) ? number_format($changeLogBudget->vat_month_5) : '') }}"
						           tabindex="45"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_6" id="vat_month_6"
						           value="{{ !empty($vatMonth6) ? $vatMonth6 : (!empty($changeLogBudget->vat_month_6) ? number_format($changeLogBudget->vat_month_6) : '') }}"
						           tabindex="46"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_7" id="vat_month_7"
						           value="{{ !empty($vatMonth7) ? $vatMonth7 : (!empty($changeLogBudget->vat_month_7) ? number_format($changeLogBudget->vat_month_7) : '') }}"
						           tabindex="47"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_8" id="vat_month_8"
						           value="{{ !empty($vatMonth8) ? $vatMonth8 : (!empty($changeLogBudget->vat_month_8) ? number_format($changeLogBudget->vat_month_8) : '') }}"
						           tabindex="48"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_9" id="vat_month_9"
						           value="{{ !empty($vatMonth9) ? $vatMonth9 : (!empty($changeLogBudget->vat_month_9) ? number_format($changeLogBudget->vat_month_9) : '') }}"
						           tabindex="49"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_10" id="vat_month_10"
						           value="{{ !empty($vatMonth10) ? $vatMonth10 : (!empty($changeLogBudget->vat_month_10) ? number_format($changeLogBudget->vat_month_10) : '') }}"
						           tabindex="50"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_11" id="vat_month_11"
						           value="{{ !empty($vatMonth11) ? $vatMonth11 : (!empty($changeLogBudget->vat_month_11) ? number_format($changeLogBudget->vat_month_11) : '') }}"
						           tabindex="51"/></td>
						<td><input class="form-control currencyFields" type="text" name="vat_month_12" id="vat_month_12"
						           value="{{ !empty($vatMonth12) ? $vatMonth12 : (!empty($changeLogBudget->vat_month_12) ? number_format($changeLogBudget->vat_month_12) : '') }}"
						           tabindex="52"/></td>
					</tr>

					<tr>
						<td>Net Sales</td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_totals" id="net_sales_totals"
						           value="{{ !empty($netSalesTotals) ? $netSalesTotals : (!empty($changeLogBudget->net_sales_totals) ? number_format($changeLogBudget->net_sales_totals) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_1" id="net_sales_month_1"
						           value="{{ !empty($netSalesMonth1) ? $netSalesMonth1 : (!empty($changeLogBudget->net_sales_month_1) ? number_format($changeLogBudget->net_sales_month_1) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_2" id="net_sales_month_2"
						           value="{{ !empty($netSalesMonth2) ? $netSalesMonth2 : (!empty($changeLogBudget->net_sales_month_2) ? number_format($changeLogBudget->net_sales_month_2) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_3" id="net_sales_month_3"
						           value="{{ !empty($netSalesMonth3) ? $netSalesMonth3 : (!empty($changeLogBudget->net_sales_month_3) ? number_format($changeLogBudget->net_sales_month_3) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_4" id="net_sales_month_4"
						           value="{{ !empty($netSalesMonth4) ? $netSalesMonth4 : (!empty($changeLogBudget->net_sales_month_4) ? number_format($changeLogBudget->net_sales_month_4) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_5" id="net_sales_month_5"
						           value="{{ !empty($netSalesMonth5) ? $netSalesMonth5 : (!empty($changeLogBudget->net_sales_month_5) ? number_format($changeLogBudget->net_sales_month_5) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_6" id="net_sales_month_6"
						           value="{{ !empty($netSalesMonth6) ? $netSalesMonth6 : (!empty($changeLogBudget->net_sales_month_6) ? number_format($changeLogBudget->net_sales_month_6) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_7" id="net_sales_month_7"
						           value="{{ !empty($netSalesMonth7) ? $netSalesMonth7 : (!empty($changeLogBudget->net_sales_month_7) ? number_format($changeLogBudget->net_sales_month_7) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_8" id="net_sales_month_8"
						           value="{{ !empty($netSalesMonth8) ? $netSalesMonth8 : (!empty($changeLogBudget->net_sales_month_8) ? number_format($changeLogBudget->net_sales_month_8) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_9" id="net_sales_month_9"
						           value="{{ !empty($netSalesMonth9) ? $netSalesMonth9 : (!empty($changeLogBudget->net_sales_month_9) ? number_format($changeLogBudget->net_sales_month_9) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_10" id="net_sales_month_10"
						           value="{{ !empty($netSalesMonth10) ? $netSalesMonth10 : (!empty($changeLogBudget->net_sales_month_10) ? number_format($changeLogBudget->net_sales_month_10) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_11" id="net_sales_month_11"
						           value="{{ !empty($netSalesMonth11) ? $netSalesMonth11 : (!empty($changeLogBudget->net_sales_month_11) ? number_format($changeLogBudget->net_sales_month_11) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="net_sales_month_12" id="net_sales_month_12"
						           value="{{ !empty($netSalesMonth12) ? $netSalesMonth12 : (!empty($changeLogBudget->net_sales_month_12) ? number_format($changeLogBudget->net_sales_month_12) : '') }}"
						           readonly="readonly"/></td>
					</tr>

					<tr>
						<td>Cost of Sales</td>
						<td><input class="form-control auto_calc" type="text" name="cost_of_sales_totals" id="cost_of_sales_totals"
						           value="{{ !empty($costOfSalesTotals) ? $costOfSalesTotals : (!empty($changeLogBudget->cost_of_sales_totals) ? number_format($changeLogBudget->cost_of_sales_totals) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_1" id="cost_of_sales_month_1"
						           value="{{ !empty($costOfSalesMonth1) ? $costOfSalesMonth1 : (!empty($changeLogBudget->cost_of_sales_month_1) ? number_format($changeLogBudget->cost_of_sales_month_1) : '') }}"
						           tabindex="53"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_2" id="cost_of_sales_month_2"
						           value="{{ !empty($costOfSalesMonth2) ? $costOfSalesMonth2 : (!empty($changeLogBudget->cost_of_sales_month_2) ? number_format($changeLogBudget->cost_of_sales_month_2) : '') }}"
						           tabindex="54"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_3" id="cost_of_sales_month_3"
						           value="{{ !empty($costOfSalesMonth3) ? $costOfSalesMonth3 : (!empty($changeLogBudget->cost_of_sales_month_3) ? number_format($changeLogBudget->cost_of_sales_month_3) : '') }}"
						           tabindex="55"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_4" id="cost_of_sales_month_4"
						           value="{{ !empty($costOfSalesMonth4) ? $costOfSalesMonth4 : (!empty($changeLogBudget->cost_of_sales_month_4) ? number_format($changeLogBudget->cost_of_sales_month_4) : '') }}"
						           tabindex="56"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_5" id="cost_of_sales_month_5"
						           value="{{ !empty($costOfSalesMonth5) ? $costOfSalesMonth5 : (!empty($changeLogBudget->cost_of_sales_month_5) ? number_format($changeLogBudget->cost_of_sales_month_5) : '') }}"
						           tabindex="57"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_6" id="cost_of_sales_month_6"
						           value="{{ !empty($costOfSalesMonth6) ? $costOfSalesMonth6 : (!empty($changeLogBudget->cost_of_sales_month_6) ? number_format($changeLogBudget->cost_of_sales_month_6) : '') }}"
						           tabindex="58"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_7" id="cost_of_sales_month_7"
						           value="{{ !empty($costOfSalesMonth7) ? $costOfSalesMonth7 : (!empty($changeLogBudget->cost_of_sales_month_7) ? number_format($changeLogBudget->cost_of_sales_month_7) : '') }}"
						           tabindex="59"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_8" id="cost_of_sales_month_8"
						           value="{{ !empty($costOfSalesMonth8) ? $costOfSalesMonth8 : (!empty($changeLogBudget->cost_of_sales_month_8) ? number_format($changeLogBudget->cost_of_sales_month_8) : '') }}"
						           tabindex="60"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_9" id="cost_of_sales_month_9"
						           value="{{ !empty($costOfSalesMonth9) ? $costOfSalesMonth9 : (!empty($changeLogBudget->cost_of_sales_month_9) ? number_format($changeLogBudget->cost_of_sales_month_9) : '') }}"
						           tabindex="61"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_10" id="cost_of_sales_month_10"
						           value="{{ !empty($costOfSalesMonth10) ? $costOfSalesMonth10 : (!empty($changeLogBudget->cost_of_sales_month_10) ? number_format($changeLogBudget->cost_of_sales_month_10) : '') }}"
						           tabindex="62"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_11" id="cost_of_sales_month_11"
						           value="{{ !empty($costOfSalesMonth11) ? $costOfSalesMonth11 : (!empty($changeLogBudget->cost_of_sales_month_11) ? number_format($changeLogBudget->cost_of_sales_month_11) : '') }}"
						           tabindex="63"/></td>
						<td><input class="form-control currencyFields" type="text" name="cost_of_sales_month_12" id="cost_of_sales_month_12"
						           value="{{ !empty($costOfSalesMonth12) ? $costOfSalesMonth12 : (!empty($changeLogBudget->cost_of_sales_month_12) ? number_format($changeLogBudget->cost_of_sales_month_12) : '') }}"
						           tabindex="64"/></td>
					</tr>

					<tr class="budget-type-row budget-type-{{ \App\BudgetType::BUDGET_TYPE_GP}} {{ $selectedBudgetType == \App\BudgetType::BUDGET_TYPE_NET ? 'hidden' : '' }}">
						<td>Gross Profit (Gross)</td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_totals" id="gross_profit_totals"
						           value="{{ !empty($grossProfitTotals) ? $grossProfitTotals : (!empty($changeLogBudget->gross_profit_totals) ? number_format($changeLogBudget->gross_profit_totals) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_1" id="gross_profit_month_1"
						           value="{{ !empty($grossProfitMonth1) ? $grossProfitMonth1 : (!empty($changeLogBudget->gross_profit_month_1) ? number_format($changeLogBudget->gross_profit_month_1) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_2" id="gross_profit_month_2"
						           value="{{ !empty($grossProfitMonth2) ? $grossProfitMonth2 : (!empty($changeLogBudget->gross_profit_month_2) ? number_format($changeLogBudget->gross_profit_month_2) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_3" id="gross_profit_month_3"
						           value="{{ !empty($grossProfitMonth3) ? $grossProfitMonth3 : (!empty($changeLogBudget->gross_profit_month_3) ? number_format($changeLogBudget->gross_profit_month_3) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_4" id="gross_profit_month_4"
						           value="{{ !empty($grossProfitMonth4) ? $grossProfitMonth4 : (!empty($changeLogBudget->gross_profit_month_4) ? number_format($changeLogBudget->gross_profit_month_4) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_5" id="gross_profit_month_5"
						           value="{{ !empty($grossProfitMonth5) ? $grossProfitMonth5 : (!empty($changeLogBudget->gross_profit_month_5) ? number_format($changeLogBudget->gross_profit_month_5) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_6" id="gross_profit_month_6"
						           value="{{ !empty($grossProfitMonth6) ? $grossProfitMonth6 : (!empty($changeLogBudget->gross_profit_month_6) ? number_format($changeLogBudget->gross_profit_month_6) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_7" id="gross_profit_month_7"
						           value="{{ !empty($grossProfitMonth7) ? $grossProfitMonth7 : (!empty($changeLogBudget->gross_profit_month_7) ? number_format($changeLogBudget->gross_profit_month_7) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_8" id="gross_profit_month_8"
						           value="{{ !empty($grossProfitMonth8) ? $grossProfitMonth8 : (!empty($changeLogBudget->gross_profit_month_8) ? number_format($changeLogBudget->gross_profit_month_8) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_9" id="gross_profit_month_9"
						           value="{{ !empty($grossProfitMonth9) ? $grossProfitMonth9 : (!empty($changeLogBudget->gross_profit_month_9) ? number_format($changeLogBudget->gross_profit_month_9) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_10" id="gross_profit_month_10"
						           value="{{ !empty($grossProfitMonth10) ? $grossProfitMonth10 : (!empty($changeLogBudget->gross_profit_month_10) ? number_format($changeLogBudget->gross_profit_month_10) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_11" id="gross_profit_month_11"
						           value="{{ !empty($grossProfitMonth11) ? $grossProfitMonth11 : (!empty($changeLogBudget->gross_profit_month_11) ? number_format($changeLogBudget->gross_profit_month_11) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_month_12" id="gross_profit_month_12"
						           value="{{ !empty($grossProfitMonth12) ? $grossProfitMonth12 : (!empty($changeLogBudget->gross_profit_month_12) ? number_format($changeLogBudget->gross_profit_month_12) : '') }}"
						           readonly/></td>
					</tr>

					<tr class="budget-type-row budget-type-{{ \App\BudgetType::BUDGET_TYPE_NET}} {{ $selectedBudgetType == \App\BudgetType::BUDGET_TYPE_GP ? 'hidden' : '' }}">
						<td>Gross Profit (Net)</td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_totals" id="gross_profit_net_totals"
						           value="{{ !empty($grossProfitNetTotals) ? $grossProfitNetTotals : (!empty($changeLogBudget->gross_profit_net_totals) ? number_format($changeLogBudget->gross_profit_net_totals) : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_1" id="gross_profit_net_month_1"
						           value="{{ !empty($grossProfitNetMonth1) ? $grossProfitNetMonth1 : (!empty($changeLogBudget->gross_profit_net_month_1) ? number_format($changeLogBudget->gross_profit_net_month_1) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_2" id="gross_profit_net_month_2"
						           value="{{ !empty($grossProfitNetMonth2) ? $grossProfitNetMonth2 : (!empty($changeLogBudget->gross_profit_net_month_2) ? number_format($changeLogBudget->gross_profit_net_month_2) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_3" id="gross_profit_net_month_3"
						           value="{{ !empty($grossProfitNetMonth3) ? $grossProfitNetMonth3 : (!empty($changeLogBudget->gross_profit_net_month_3) ? number_format($changeLogBudget->gross_profit_net_month_3) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_4" id="gross_profit_net_month_4"
						           value="{{ !empty($grossProfitNetMonth4) ? $grossProfitNetMonth4 : (!empty($changeLogBudget->gross_profit_net_month_4) ? number_format($changeLogBudget->gross_profit_net_month_4) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_5" id="gross_profit_net_month_5"
						           value="{{ !empty($grossProfitNetMonth5) ? $grossProfitNetMonth5 : (!empty($changeLogBudget->gross_profit_net_month_5) ? number_format($changeLogBudget->gross_profit_net_month_5) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_6" id="gross_profit_net_month_6"
						           value="{{ !empty($grossProfitNetMonth6) ? $grossProfitNetMonth6 : (!empty($changeLogBudget->gross_profit_net_month_6) ? number_format($changeLogBudget->gross_profit_net_month_6) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_7" id="gross_profit_net_month_7"
						           value="{{ !empty($grossProfitNetMonth7) ? $grossProfitNetMonth7 : (!empty($changeLogBudget->gross_profit_net_month_7) ? number_format($changeLogBudget->gross_profit_net_month_7) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_8" id="gross_profit_net_month_8"
						           value="{{ !empty($grossProfitNetMonth8) ? $grossProfitNetMonth8 : (!empty($changeLogBudget->gross_profit_net_month_8) ? number_format($changeLogBudget->gross_profit_net_month_8) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_9" id="gross_profit_net_month_9"
						           value="{{ !empty($grossProfitNetMonth9) ? $grossProfitNetMonth9 : (!empty($changeLogBudget->gross_profit_net_month_9) ? number_format($changeLogBudget->gross_profit_net_month_9) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_10" id="gross_profit_net_month_10"
						           value="{{ !empty($grossProfitNetMonth10) ? $grossProfitNetMonth10 : (!empty($changeLogBudget->gross_profit_net_month_10) ? number_format($changeLogBudget->gross_profit_net_month_10) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_11" id="gross_profit_net_month_11"
						           value="{{ !empty($grossProfitNetMonth11) ? $grossProfitNetMonth11 : (!empty($changeLogBudget->gross_profit_net_month_11) ? number_format($changeLogBudget->gross_profit_net_month_11) : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gross_profit_net_month_12" id="gross_profit_net_month_12"
						           value="{{ !empty($grossProfitNetMonth12) ? $grossProfitNetMonth12 : (!empty($changeLogBudget->gross_profit_net_month_12) ? number_format($changeLogBudget->gross_profit_net_month_12) : '') }}"
						           readonly/></td>
					</tr>

					<tr class="budget-type-row budget-type-{{ \App\BudgetType::BUDGET_TYPE_GP}} {{ $selectedBudgetType == \App\BudgetType::BUDGET_TYPE_NET ? 'hidden' : '' }}">
						<td>G.P.% on Gross Sales</td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_totals" id="gpp_on_gross_sales_totals"
						           value="{{ !empty($gppOnGrossSalesTotals) ? $gppOnGrossSalesTotals : (!empty($changeLogBudget->gpp_on_gross_sales_totals) ? $changeLogBudget->gpp_on_gross_sales_totals.'%' : '') }}"
						           readonly/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_1" id="gpp_on_gross_sales_month_1"
						           value="{{ !empty($gppOnGrossSalesMonth1) ? $gppOnGrossSalesMonth1 : (!empty($changeLogBudget->gpp_on_gross_sales_month_1) ? $changeLogBudget->gpp_on_gross_sales_month_1.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_2" id="gpp_on_gross_sales_month_2"
						           value="{{ !empty($gppOnGrossSalesMonth2) ? $gppOnGrossSalesMonth2 : (!empty($changeLogBudget->gpp_on_gross_sales_month_2) ? $changeLogBudget->gpp_on_gross_sales_month_2.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_3" id="gpp_on_gross_sales_month_3"
						           value="{{ !empty($gppOnGrossSalesMonth3) ? $gppOnGrossSalesMonth3 : (!empty($changeLogBudget->gpp_on_gross_sales_month_3) ? $changeLogBudget->gpp_on_gross_sales_month_3.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_4" id="gpp_on_gross_sales_month_4"
						           value="{{ !empty($gppOnGrossSalesMonth4) ? $gppOnGrossSalesMonth4 : (!empty($changeLogBudget->gpp_on_gross_sales_month_4) ? $changeLogBudget->gpp_on_gross_sales_month_4.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_5" id="gpp_on_gross_sales_month_5"
						           value="{{ !empty($gppOnGrossSalesMonth5) ? $gppOnGrossSalesMonth5 : (!empty($changeLogBudget->gpp_on_gross_sales_month_5) ? $changeLogBudget->gpp_on_gross_sales_month_5.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_6" id="gpp_on_gross_sales_month_6"
						           value="{{ !empty($gppOnGrossSalesMonth6) ? $gppOnGrossSalesMonth6 : (!empty($changeLogBudget->gpp_on_gross_sales_month_6) ? $changeLogBudget->gpp_on_gross_sales_month_6.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_7" id="gpp_on_gross_sales_month_7"
						           value="{{ !empty($gppOnGrossSalesMonth7) ? $gppOnGrossSalesMonth7 : (!empty($changeLogBudget->gpp_on_gross_sales_month_7) ? $changeLogBudget->gpp_on_gross_sales_month_7.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_8" id="gpp_on_gross_sales_month_8"
						           value="{{ !empty($gppOnGrossSalesMonth8) ? $gppOnGrossSalesMonth8 : (!empty($changeLogBudget->gpp_on_gross_sales_month_8) ? $changeLogBudget->gpp_on_gross_sales_month_8.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_9" id="gpp_on_gross_sales_month_9"
						           value="{{ !empty($gppOnGrossSalesMonth9) ? $gppOnGrossSalesMonth9 : (!empty($changeLogBudget->gpp_on_gross_sales_month_9) ? $changeLogBudget->gpp_on_gross_sales_month_9.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_10" id="gpp_on_gross_sales_month_10"
						           value="{{ !empty($gppOnGrossSalesMonth10) ? $gppOnGrossSalesMonth10 : (!empty($changeLogBudget->gpp_on_gross_sales_month_10) ? $changeLogBudget->gpp_on_gross_sales_month_10.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_11" id="gpp_on_gross_sales_month_11"
						           value="{{ !empty($gppOnGrossSalesMonth11) ? $gppOnGrossSalesMonth11 : (!empty($changeLogBudget->gpp_on_gross_sales_month_11) ? $changeLogBudget->gpp_on_gross_sales_month_11.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_gross_sales_month_12" id="gpp_on_gross_sales_month_12"
						           value="{{ !empty($gppOnGrossSalesMonth12) ? $gppOnGrossSalesMonth12 : (!empty($changeLogBudget->gpp_on_gross_sales_month_12) ? $changeLogBudget->gpp_on_gross_sales_month_12.'%' : '') }}"
						           readonly="readonly"/></td>
					</tr>

					<tr class="budget-type-row budget-type-{{ \App\BudgetType::BUDGET_TYPE_NET}} {{ $selectedBudgetType == \App\BudgetType::BUDGET_TYPE_GP ? 'hidden' : '' }}">
						<td>G.P.% on Net Sales</td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_totals" id="gpp_on_net_sales_totals"
						           value="{{ !empty($gppOnNetSalesTotals) ? $gppOnNetSalesTotals : (!empty($changeLogBudget->gpp_on_net_sales_totals) ? $changeLogBudget->gpp_on_net_sales_totals.'%' : '') }}"/>
						</td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_1" id="gpp_on_net_sales_month_1"
						           value="{{ !empty($gppOnNetSalesMonth1) ? $gppOnNetSalesMonth1 : (!empty($changeLogBudget->gpp_on_net_sales_month_1) ? $changeLogBudget->gpp_on_net_sales_month_1.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_2" id="gpp_on_net_sales_month_2"
						           value="{{ !empty($gppOnNetSalesMonth2) ? $gppOnNetSalesMonth2 : (!empty($changeLogBudget->gpp_on_net_sales_month_2) ? $changeLogBudget->gpp_on_net_sales_month_2.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_3" id="gpp_on_net_sales_month_3"
						           value="{{ !empty($gppOnNetSalesMonth3) ? $gppOnNetSalesMonth3 : (!empty($changeLogBudget->gpp_on_net_sales_month_3) ? $changeLogBudget->gpp_on_net_sales_month_3.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_4" id="gpp_on_net_sales_month_4"
						           value="{{ !empty($gppOnNetSalesMonth4) ? $gppOnNetSalesMonth4 : (!empty($changeLogBudget->gpp_on_net_sales_month_4) ? $changeLogBudget->gpp_on_net_sales_month_4.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_5" id="gpp_on_net_sales_month_5"
						           value="{{ !empty($gppOnNetSalesMonth5) ? $gppOnNetSalesMonth5 : (!empty($changeLogBudget->gpp_on_net_sales_month_5) ? $changeLogBudget->gpp_on_net_sales_month_5.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_6" id="gpp_on_net_sales_month_6"
						           value="{{ !empty($gppOnNetSalesMonth6) ? $gppOnNetSalesMonth6 : (!empty($changeLogBudget->gpp_on_net_sales_month_6) ? $changeLogBudget->gpp_on_net_sales_month_6.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_7" id="gpp_on_net_sales_month_7"
						           value="{{ !empty($gppOnNetSalesMonth7) ? $gppOnNetSalesMonth7 : (!empty($changeLogBudget->gpp_on_net_sales_month_7) ? $changeLogBudget->gpp_on_net_sales_month_7.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_8" id="gpp_on_net_sales_month_8"
						           value="{{ !empty($gppOnNetSalesMonth8) ? $gppOnNetSalesMonth8 : (!empty($changeLogBudget->gpp_on_net_sales_month_8) ? $changeLogBudget->gpp_on_net_sales_month_8.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_9" id="gpp_on_net_sales_month_9"
						           value="{{ !empty($gppOnNetSalesMonth9) ? $gppOnNetSalesMonth9 : (!empty($changeLogBudget->gpp_on_net_sales_month_9) ? $changeLogBudget->gpp_on_net_sales_month_9.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_10" id="gpp_on_net_sales_month_10"
						           value="{{ !empty($gppOnNetSalesMonth10) ? $gppOnNetSalesMonth10 : (!empty($changeLogBudget->gpp_on_net_sales_month_10) ? $changeLogBudget->gpp_on_net_sales_month_10.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_11" id="gpp_on_net_sales_month_11"
						           value="{{ !empty($gppOnNetSalesMonth11) ? $gppOnNetSalesMonth11 : (!empty($changeLogBudget->gpp_on_net_sales_month_11) ? $changeLogBudget->gpp_on_net_sales_month_11.'%' : '') }}"
						           readonly="readonly"/></td>
						<td><input class="form-control auto_calc" type="text" name="gpp_on_net_sales_month_12" id="gpp_on_net_sales_month_12"
						           value="{{ !empty($gppOnNetSalesMonth12) ? $gppOnNetSalesMonth12 : (!empty($changeLogBudget->gpp_on_net_sales_month_12) ? $changeLogBudget->gpp_on_net_sales_month_12.'%' : '') }}"
						           readonly="readonly"/></td>
					</tr>

					<tr id="labour_row" class="{{ $unitRows['labour']['hidden'] ? 'hidden' : '' }}">
						<td>Labour</td>
						<td><input class="form-control auto_calc" type="text" name="labour_totals" value="{{ $labourTotals ? $labourTotals : 0 }}"
						           id="labour_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_1"
						           value="{{ $labourMonth1 ? $labourMonth1 : 0 }}"
						           id="labour_month_1" tabindex="65"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_2"
						           value="{{ $labourMonth2 ? $labourMonth2 : 0 }}"
						           id="labour_month_2" tabindex="66"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_3"
						           value="{{ $labourMonth3 ? $labourMonth3 : 0 }}"
						           id="labour_month_3" tabindex="67"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_4"
						           value="{{ $labourMonth4 ? $labourMonth4 : 0 }}"
						           id="labour_month_4" tabindex="68"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_5"
						           value="{{ $labourMonth5 ? $labourMonth5 : 0 }}"
						           id="labour_month_5" tabindex="69"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_6"
						           value="{{ $labourMonth6 ? $labourMonth6 : 0 }}"
						           id="labour_month_6" tabindex="70"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_7"
						           value="{{ $labourMonth7 ? $labourMonth7 : 0 }}"
						           id="labour_month_7" tabindex="71"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_8"
						           value="{{ $labourMonth8 ? $labourMonth8 : 0 }}"
						           id="labour_month_8" tabindex="72"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_9"
						           value="{{ $labourMonth9 ? $labourMonth9 : 0 }}"
						           id="labour_month_9" tabindex="73"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_10"
						           value="{{ $labourMonth10 ? $labourMonth10 : 0 }}" id="labour_month_10" tabindex="74"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_11"
						           value="{{ $labourMonth11 ? $labourMonth11 : 0 }}" id="labour_month_11" tabindex="75"/></td>
						<td><input class="form-control currencyFields" type="text" name="labour_month_12"
						           value="{{ $labourMonth12 ? $labourMonth12 : 0 }}" id="labour_month_12" tabindex="76"/></td>
					</tr>

					<tr id="training_row" class="{{ $unitRows['training']['hidden'] ? 'hidden' : '' }}">
						<td>Training</td>
						<td><input class="form-control auto_calc" type="text" name="training_totals"
						           value="{{ $trainingTotals ? $trainingTotals : 0 }}"
						           id="training_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_1"
						           value="{{ $trainingMonth1 ? $trainingMonth1 : 0 }}" id="training_month_1" tabindex="77"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_2"
						           value="{{ $trainingMonth2 ? $trainingMonth2 : 0 }}" id="training_month_2" tabindex="78"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_3"
						           value="{{ $trainingMonth3 ? $trainingMonth3 : 0 }}" id="training_month_3" tabindex="79"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_4"
						           value="{{ $trainingMonth4 ? $trainingMonth4 : 0 }}" id="training_month_4" tabindex="80"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_5"
						           value="{{ $trainingMonth5 ? $trainingMonth5 : 0 }}" id="training_month_5" tabindex="81"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_6"
						           value="{{ $trainingMonth6 ? $trainingMonth6 : 0 }}" id="training_month_6" tabindex="82"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_7"
						           value="{{ $trainingMonth7 ? $trainingMonth7 : 0 }}" id="training_month_7" tabindex="83"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_8"
						           value="{{ $trainingMonth8 ? $trainingMonth8 : 0 }}" id="training_month_8" tabindex="84"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_9"
						           value="{{ $trainingMonth9 ? $trainingMonth9 : 0 }}" id="training_month_9" tabindex="85"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_10"
						           value="{{ $trainingMonth10 ? $trainingMonth10 : 0 }}" id="training_month_10" tabindex="86"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_11"
						           value="{{ $trainingMonth11 ? $trainingMonth11 : 0 }}" id="training_month_11" tabindex="87"/></td>
						<td><input class="form-control currencyFields" type="text" name="training_month_12"
						           value="{{ $trainingMonth12 ? $trainingMonth12 : 0 }}" id="training_month_12" tabindex="88"/></td>
					</tr>

					<tr id="cleaning_row" class="{{ $unitRows['cleaning']['hidden'] ? 'hidden' : '' }}">
						<td>Cleaning</td>
						<td><input class="form-control auto_calc" type="text" name="cleaning_totals"
						           value="{{ $cleaningTotals ? $cleaningTotals : 0 }}"
						           id="cleaning_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_1"
						           value="{{ $cleaningMonth1 ? $cleaningMonth1 : 0 }}" id="cleaning_month_1" tabindex="89"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_2"
						           value="{{ $cleaningMonth2 ? $cleaningMonth2 : 0 }}" id="cleaning_month_2" tabindex="90"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_3"
						           value="{{ $cleaningMonth3 ? $cleaningMonth3 : 0 }}" id="cleaning_month_3" tabindex="91"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_4"
						           value="{{ $cleaningMonth4 ? $cleaningMonth4 : 0 }}" id="cleaning_month_4" tabindex="92"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_5"
						           value="{{ $cleaningMonth5 ? $cleaningMonth5 : 0 }}" id="cleaning_month_5" tabindex="93"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_6"
						           value="{{ $cleaningMonth6 ? $cleaningMonth6 : 0 }}" id="cleaning_month_6" tabindex="94"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_7"
						           value="{{ $cleaningMonth7 ? $cleaningMonth7 : 0 }}" id="cleaning_month_7" tabindex="95"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_8"
						           value="{{ $cleaningMonth8 ? $cleaningMonth8 : 0 }}" id="cleaning_month_8" tabindex="96"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_9"
						           value="{{ $cleaningMonth9 ? $cleaningMonth9 : 0 }}" id="cleaning_month_9" tabindex="97"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_10"
						           value="{{ $cleaningMonth10 ? $cleaningMonth10 : 0 }}" id="cleaning_month_10" tabindex="98"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_11"
						           value="{{ $cleaningMonth11 ? $cleaningMonth11 : 0 }}" id="cleaning_month_11" tabindex="99"/></td>
						<td><input class="form-control currencyFields" type="text" name="cleaning_month_12"
						           value="{{ $cleaningMonth12 ? $cleaningMonth12 : 0 }}" id="cleaning_month_12" tabindex="100"/></td>
					</tr>

					<tr id="disposables_row" class="{{ $unitRows['disposables']['hidden'] ? 'hidden' : '' }}">
						<td>Disposables</td>
						<td><input class="form-control auto_calc" type="text" name="disposables_totals"
						           value="{{ $disposablesTotals ? $disposablesTotals : 0 }}" id="disposables_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_1"
						           value="{{ $disposablesMonth1 ? $disposablesMonth1 : 0 }}" id="disposables_month_1" tabindex="101"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_2"
						           value="{{ $disposablesMonth2 ? $disposablesMonth2 : 0 }}" id="disposables_month_2" tabindex="102"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_3"
						           value="{{ $disposablesMonth3 ? $disposablesMonth3 : 0 }}" id="disposables_month_3" tabindex="103"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_4"
						           value="{{ $disposablesMonth4 ? $disposablesMonth4 : 0 }}" id="disposables_month_4" tabindex="104"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_5"
						           value="{{ $disposablesMonth5 ? $disposablesMonth5 : 0 }}" id="disposables_month_5" tabindex="105"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_6"
						           value="{{ $disposablesMonth6 ? $disposablesMonth6 : 0 }}" id="disposables_month_6" tabindex="106"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_7"
						           value="{{ $disposablesMonth7 ? $disposablesMonth7 : 0 }}" id="disposables_month_7" tabindex="107"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_8"
						           value="{{ $disposablesMonth8 ? $disposablesMonth8 : 0 }}" id="disposables_month_8" tabindex="108"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_9"
						           value="{{ $disposablesMonth9 ? $disposablesMonth9 : 0 }}" id="disposables_month_9" tabindex="109"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_10"
						           value="{{ $disposablesMonth10 ? $disposablesMonth10 : 0 }}" id="disposables_month_10" tabindex="110"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_11"
						           value="{{ $disposablesMonth11 ? $disposablesMonth11 : 0 }}" id="disposables_month_11" tabindex="111"/></td>
						<td><input class="form-control currencyFields" type="text" name="disposables_month_12"
						           value="{{ $disposablesMonth12 ? $disposablesMonth12 : 0 }}" id="disposables_month_12" tabindex="112"/></td>
					</tr>

					<tr id="uniform_row" class="{{ $unitRows['uniform']['hidden'] ? 'hidden' : '' }}">
						<td>Uniform</td>
						<td><input class="form-control auto_calc" type="text" name="uniform_totals"
						           value="{{ $uniformTotals ? $uniformTotals : 0 }}"
						           id="uniform_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_1"
						           value="{{ $uniformMonth1 ? $uniformMonth1 : 0 }}" id="uniform_month_1" tabindex="113"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_2"
						           value="{{ $uniformMonth2 ? $uniformMonth2 : 0 }}" id="uniform_month_2" tabindex="114"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_3"
						           value="{{ $uniformMonth3 ? $uniformMonth3 : 0 }}" id="uniform_month_3" tabindex="115"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_4"
						           value="{{ $uniformMonth4 ? $uniformMonth4 : 0 }}" id="uniform_month_4" tabindex="116"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_5"
						           value="{{ $uniformMonth5 ? $uniformMonth5 : 0 }}" id="uniform_month_5" tabindex="117"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_6"
						           value="{{ $uniformMonth6 ? $uniformMonth6 : 0 }}" id="uniform_month_6" tabindex="118"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_7"
						           value="{{ $uniformMonth7 ? $uniformMonth7 : 0 }}" id="uniform_month_7" tabindex="119"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_8"
						           value="{{ $uniformMonth8 ? $uniformMonth8 : 0 }}" id="uniform_month_8" tabindex="120"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_9"
						           value="{{ $uniformMonth9 ? $uniformMonth9 : 0 }}" id="uniform_month_9" tabindex="121"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_10"
						           value="{{ $uniformMonth10 ? $uniformMonth10 : 0 }}" id="uniform_month_10" tabindex="122"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_11"
						           value="{{ $uniformMonth11 ? $uniformMonth11 : 0 }}" id="uniform_month_11" tabindex="123"/></td>
						<td><input class="form-control currencyFields" type="text" name="uniform_month_12"
						           value="{{ $uniformMonth12 ? $uniformMonth12 : 0 }}" id="uniform_month_12" tabindex="124"/></td>
					</tr>

					<tr id="delph_and_cutlery_row" class="{{ $unitRows['delph_and_cutlery']['hidden'] ? 'hidden' : '' }}">
						<td>Delph &amp; Cutlery</td>
						<td><input class="form-control auto_calc" type="text" name="delph_and_cutlery_totals"
						           value="{{ $delphAndCutleryTotals ? $delphAndCutleryTotals : 0 }}" id="delph_and_cutlery_totals"
						           readonly="readonly"/>
						</td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_1"
						           value="{{ $delphAndCutleryMonth1 ? $delphAndCutleryMonth1 : 0 }}" id="delph_and_cutlery_month_1"
						           tabindex="125"/></td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_2"
						           value="{{ $delphAndCutleryMonth2 ? $delphAndCutleryMonth2 : 0 }}" id="delph_and_cutlery_month_2"
						           tabindex="126"/></td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_3"
						           value="{{ $delphAndCutleryMonth3 ? $delphAndCutleryMonth3 : 0 }}" id="delph_and_cutlery_month_3"
						           tabindex="127"/></td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_4"
						           value="{{ $delphAndCutleryMonth4 ? $delphAndCutleryMonth4 : 0 }}" id="delph_and_cutlery_month_4"
						           tabindex="128"/></td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_5"
						           value="{{ $delphAndCutleryMonth5 ? $delphAndCutleryMonth5 : 0 }}" id="delph_and_cutlery_month_5"
						           tabindex="129"/></td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_6"
						           value="{{ $delphAndCutleryMonth6 ? $delphAndCutleryMonth6 : 0 }}" id="delph_and_cutlery_month_6"
						           tabindex="130"/></td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_7"
						           value="{{ $delphAndCutleryMonth7 ? $delphAndCutleryMonth7 : 0 }}" id="delph_and_cutlery_month_7"
						           tabindex="131"/></td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_8"
						           value="{{ $delphAndCutleryMonth8 ? $delphAndCutleryMonth8 : 0 }}" id="delph_and_cutlery_month_8"
						           tabindex="132"/></td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_9"
						           value="{{ $delphAndCutleryMonth9 ? $delphAndCutleryMonth9 : 0 }}" id="delph_and_cutlery_month_9"
						           tabindex="133"/></td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_10"
						           value="{{ $delphAndCutleryMonth10 ? $delphAndCutleryMonth10 : 0 }}" id="delph_and_cutlery_month_10"
						           tabindex="134"/>
						</td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_11"
						           value="{{ $delphAndCutleryMonth11 ? $delphAndCutleryMonth11 : 0 }}" id="delph_and_cutlery_month_11"
						           tabindex="135"/>
						</td>
						<td><input class="form-control currencyFields" type="text" name="delph_and_cutlery_month_12"
						           value="{{ $delphAndCutleryMonth12 ? $delphAndCutleryMonth12 : 0 }}" id="delph_and_cutlery_month_12"
						           tabindex="136"/>
						</td>
					</tr>

					<tr id="bank_charges_row" class="{{ $unitRows['bank_charges']['hidden'] ? 'hidden' : '' }}">
						<td>Bank Charges</td>
						<td><input class="form-control auto_calc" type="text" name="bank_charges_totals"
						           value="{{ $bankChargesTotals ? $bankChargesTotals : 0 }}" id="bank_charges_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_1"
						           value="{{ $bankChargesMonth1 ? $bankChargesMonth1 : 0 }}" id="bank_charges_month_1" tabindex="137"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_2"
						           value="{{ $bankChargesMonth2 ? $bankChargesMonth2 : 0 }}" id="bank_charges_month_2" tabindex="138"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_3"
						           value="{{ $bankChargesMonth3 ? $bankChargesMonth3 : 0 }}" id="bank_charges_month_3" tabindex="139"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_4"
						           value="{{ $bankChargesMonth4 ? $bankChargesMonth4 : 0 }}" id="bank_charges_month_4" tabindex="140"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_5"
						           value="{{ $bankChargesMonth5 ? $bankChargesMonth5 : 0 }}" id="bank_charges_month_5" tabindex="141"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_6"
						           value="{{ $bankChargesMonth6 ? $bankChargesMonth6 : 0 }}" id="bank_charges_month_6" tabindex="142"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_7"
						           value="{{ $bankChargesMonth7 ? $bankChargesMonth7 : 0 }}" id="bank_charges_month_7" tabindex="143"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_8"
						           value="{{ $bankChargesMonth8 ? $bankChargesMonth8 : 0 }}" id="bank_charges_month_8" tabindex="144"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_9"
						           value="{{ $bankChargesMonth9 ? $bankChargesMonth9 : 0 }}" id="bank_charges_month_9" tabindex="145"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_10"
						           value="{{ $bankChargesMonth10 ? $bankChargesMonth10 : 0 }}" id="bank_charges_month_10" tabindex="146"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_11"
						           value="{{ $bankChargesMonth11 ? $bankChargesMonth11 : 0 }}" id="bank_charges_month_11" tabindex="147"/></td>
						<td><input class="form-control currencyFields" type="text" name="bank_charges_month_12"
						           value="{{ $bankChargesMonth12 ? $bankChargesMonth12 : 0 }}" id="bank_charges_month_12" tabindex="148"/></td>
					</tr>

					<tr id="investment_row" class="{{ $unitRows['investment']['hidden'] ? 'hidden' : '' }}">
						<td>Investment</td>
						<td><input class="form-control auto_calc" type="text" name="investment_totals"
						           value="{{ $investmentTotals ? $investmentTotals : 0 }}" id="investment_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_1"
						           value="{{ $investmentMonth1 ? $investmentMonth1 : 0 }}" id="investment_month_1" tabindex="149"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_2"
						           value="{{ $investmentMonth2 ? $investmentMonth2 : 0 }}" id="investment_month_2" tabindex="150"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_3"
						           value="{{ $investmentMonth3 ? $investmentMonth3 : 0 }}" id="investment_month_3" tabindex="151"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_4"
						           value="{{ $investmentMonth4 ? $investmentMonth4 : 0 }}" id="investment_month_4" tabindex="152"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_5"
						           value="{{ $investmentMonth5 ? $investmentMonth5 : 0 }}" id="investment_month_5" tabindex="153"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_6"
						           value="{{ $investmentMonth6 ? $investmentMonth6 : 0 }}" id="investment_month_6" tabindex="154"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_7"
						           value="{{ $investmentMonth7 ? $investmentMonth7 : 0 }}" id="investment_month_7" tabindex="155"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_8"
						           value="{{ $investmentMonth8 ? $investmentMonth8 : 0 }}" id="investment_month_8" tabindex="156"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_9"
						           value="{{ $investmentMonth9 ? $investmentMonth9 : 0 }}" id="investment_month_9" tabindex="157"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_10"
						           value="{{ $investmentMonth10 ? $investmentMonth10 : 0 }}" id="investment_month_10" tabindex="158"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_11"
						           value="{{ $investmentMonth11 ? $investmentMonth11 : 0 }}" id="investment_month_11" tabindex="159"/></td>
						<td><input class="form-control currencyFields" type="text" name="investment_month_12"
						           value="{{ $investmentMonth12 ? $investmentMonth12 : 0 }}" id="investment_month_12" tabindex="160"/></td>
					</tr>

					<tr id="management_fee_row" class="{{ $unitRows['management_fee']['hidden'] ? 'hidden' : '' }}">
						<td>Management fee</td>
						<td><input class="form-control auto_calc" type="text" name="management_fee_totals"
						           value="{{ $managementFeeTotals ? $managementFeeTotals : 0 }}" id="management_fee_totals" readonly="readonly"/>
						</td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_1"
						           value="{{ $managementFeeMonth1 ? $managementFeeMonth1 : 0 }}" id="management_fee_month_1" tabindex="161"/></td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_2"
						           value="{{ $managementFeeMonth2 ? $managementFeeMonth2 : 0 }}" id="management_fee_month_2" tabindex="162"/></td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_3"
						           value="{{ $managementFeeMonth3 ? $managementFeeMonth3 : 0 }}" id="management_fee_month_3" tabindex="163"/></td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_4"
						           value="{{ $managementFeeMonth4 ? $managementFeeMonth4 : 0 }}" id="management_fee_month_4" tabindex="164"/></td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_5"
						           value="{{ $managementFeeMonth5 ? $managementFeeMonth5 : 0 }}" id="management_fee_month_5" tabindex="165"/></td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_6"
						           value="{{ $managementFeeMonth6 ? $managementFeeMonth6 : 0 }}" id="management_fee_month_6" tabindex="166"/></td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_7"
						           value="{{ $managementFeeMonth7 ? $managementFeeMonth7 : 0 }}" id="management_fee_month_7" tabindex="167"/></td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_8"
						           value="{{ $managementFeeMonth8 ? $managementFeeMonth8 : 0 }}" id="management_fee_month_8" tabindex="168"/></td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_9"
						           value="{{ $managementFeeMonth9 ? $managementFeeMonth9 : 0 }}" id="management_fee_month_9" tabindex="169"/></td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_10"
						           value="{{ $managementFeeMonth10 ? $managementFeeMonth10 : 0 }}" id="management_fee_month_10" tabindex="170"/>
						</td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_11"
						           value="{{ $managementFeeMonth11 ? $managementFeeMonth11 : 0 }}" id="management_fee_month_11" tabindex="171"/>
						</td>
						<td><input class="form-control currencyFields" type="text" name="management_fee_month_12"
						           value="{{ $managementFeeMonth12 ? $managementFeeMonth12 : 0 }}" id="management_fee_month_12" tabindex="172"/>
						</td>
					</tr>

					<tr id="insurance_and_related_costs_row" class="{{ $unitRows['insurance_and_related_costs']['hidden'] ? 'hidden' : '' }}">
						<td>Insurance & related costs</td>
						<td><input class="form-control auto_calc" type="text" name="insurance_and_related_costs_totals"
						           value="{{ $insuranceAndRelatedCostsTotals ? $insuranceAndRelatedCostsTotals : 0 }}"
						           id="insurance_and_related_costs_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_1"
						           value="{{ $insuranceAndRelatedCostsMonth1 ? $insuranceAndRelatedCostsMonth1 : 0 }}"
						           id="insurance_and_related_costs_month_1" tabindex="173"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_2"
						           value="{{ $insuranceAndRelatedCostsMonth2 ? $insuranceAndRelatedCostsMonth2 : 0 }}"
						           id="insurance_and_related_costs_month_2" tabindex="174"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_3"
						           value="{{ $insuranceAndRelatedCostsMonth3 ? $insuranceAndRelatedCostsMonth3 : 0 }}"
						           id="insurance_and_related_costs_month_3" tabindex="175"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_4"
						           value="{{ $insuranceAndRelatedCostsMonth4 ? $insuranceAndRelatedCostsMonth4 : 0 }}"
						           id="insurance_and_related_costs_month_4" tabindex="176"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_5"
						           value="{{ $insuranceAndRelatedCostsMonth5 ? $insuranceAndRelatedCostsMonth5 : 0 }}"
						           id="insurance_and_related_costs_month_5" tabindex="177"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_6"
						           value="{{ $insuranceAndRelatedCostsMonth6 ? $insuranceAndRelatedCostsMonth6 : 0 }}"
						           id="insurance_and_related_costs_month_6" tabindex="178"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_7"
						           value="{{ $insuranceAndRelatedCostsMonth7 ? $insuranceAndRelatedCostsMonth7 : 0 }}"
						           id="insurance_and_related_costs_month_7" tabindex="179"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_8"
						           value="{{ $insuranceAndRelatedCostsMonth8 ? $insuranceAndRelatedCostsMonth8 : 0 }}"
						           id="insurance_and_related_costs_month_8" tabindex="180"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_9"
						           value="{{ $insuranceAndRelatedCostsMonth9 ? $insuranceAndRelatedCostsMonth9 : 0 }}"
						           id="insurance_and_related_costs_month_9" tabindex="181"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_10"
						           value="{{ $insuranceAndRelatedCostsMonth10 ? $insuranceAndRelatedCostsMonth10 : 0 }}"
						           id="insurance_and_related_costs_month_10" tabindex="182"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_11"
						           value="{{ $insuranceAndRelatedCostsMonth11 ? $insuranceAndRelatedCostsMonth11 : 0 }}"
						           id="insurance_and_related_costs_month_11" tabindex="183"/></td>
						<td><input class="form-control currencyFields" type="text" name="insurance_and_related_costs_month_12"
						           value="{{ $insuranceAndRelatedCostsMonth12 ? $insuranceAndRelatedCostsMonth12 : 0 }}"
						           id="insurance_and_related_costs_month_12" tabindex="184"/></td>
					</tr>

					<tr id="coffee_machine_rental_row" class="{{ $unitRows['coffee_machine_rental']['hidden'] ? 'hidden' : '' }}">
						<td>Coffee Machine rental</td>
						<td><input class="form-control auto_calc" type="text" name="coffee_machine_rental_totals"
						           value="{{ $coffeeMachineRentalTotals ? $coffeeMachineRentalTotals : 0 }}" id="coffee_machine_rental_totals"
						           readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_1"
						           value="{{ $coffeeMachineRentalMonth1 ? $coffeeMachineRentalMonth1 : 0 }}" id="coffee_machine_rental_month_1"
						           tabindex="185"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_2"
						           value="{{ $coffeeMachineRentalMonth2 ? $coffeeMachineRentalMonth2 : 0 }}" id="coffee_machine_rental_month_2"
						           tabindex="186"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_3"
						           value="{{ $coffeeMachineRentalMonth3 ? $coffeeMachineRentalMonth3 : 0 }}" id="coffee_machine_rental_month_3"
						           tabindex="187"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_4"
						           value="{{ $coffeeMachineRentalMonth4 ? $coffeeMachineRentalMonth4 : 0 }}" id="coffee_machine_rental_month_4"
						           tabindex="188"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_5"
						           value="{{ $coffeeMachineRentalMonth5 ? $coffeeMachineRentalMonth5 : 0 }}" id="coffee_machine_rental_month_5"
						           tabindex="189"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_6"
						           value="{{ $coffeeMachineRentalMonth6 ? $coffeeMachineRentalMonth6 : 0 }}" id="coffee_machine_rental_month_6"
						           tabindex="190"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_7"
						           value="{{ $coffeeMachineRentalMonth7 ? $coffeeMachineRentalMonth7 : 0 }}" id="coffee_machine_rental_month_7"
						           tabindex="191"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_8"
						           value="{{ $coffeeMachineRentalMonth8 ? $coffeeMachineRentalMonth8 : 0 }}" id="coffee_machine_rental_month_8"
						           tabindex="192"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_9"
						           value="{{ $coffeeMachineRentalMonth9 ? $coffeeMachineRentalMonth9 : 0 }}" id="coffee_machine_rental_month_9"
						           tabindex="193"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_10"
						           value="{{ $coffeeMachineRentalMonth10 ? $coffeeMachineRentalMonth10 : 0 }}" id="coffee_machine_rental_month_10"
						           tabindex="194"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_11"
						           value="{{ $coffeeMachineRentalMonth11 ? $coffeeMachineRentalMonth11 : 0 }}" id="coffee_machine_rental_month_11"
						           tabindex="195"/></td>
						<td><input class="form-control currencyFields" type="text" name="coffee_machine_rental_month_12"
						           value="{{ $coffeeMachineRentalMonth12 ? $coffeeMachineRentalMonth12 : 0 }}" id="coffee_machine_rental_month_12"
						           tabindex="196"/></td>
					</tr>

					<tr id="other_rental_row" class="{{ $unitRows['other_rental']['hidden'] ? 'hidden' : '' }}">
						<td>Other Rental</td>
						<td><input class="form-control auto_calc" type="text" name="other_rental_totals"
						           value="{{ $otherRentalTotals ? $otherRentalTotals : 0 }}" id="other_rental_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_1"
						           value="{{ $otherRentalMonth1 ? $otherRentalMonth1 : 0 }}" id="other_rental_month_1" tabindex="197"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_2"
						           value="{{ $otherRentalMonth2 ? $otherRentalMonth2 : 0 }}" id="other_rental_month_2" tabindex="198"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_3"
						           value="{{ $otherRentalMonth3 ? $otherRentalMonth3 : 0 }}" id="other_rental_month_3" tabindex="199"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_4"
						           value="{{ $otherRentalMonth4 ? $otherRentalMonth4 : 0 }}" id="other_rental_month_4" tabindex="200"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_5"
						           value="{{ $otherRentalMonth5 ? $otherRentalMonth5 : 0 }}" id="other_rental_month_5" tabindex="201"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_6"
						           value="{{ $otherRentalMonth6 ? $otherRentalMonth6 : 0 }}" id="other_rental_month_6" tabindex="202"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_7"
						           value="{{ $otherRentalMonth7 ? $otherRentalMonth7 : 0 }}" id="other_rental_month_7" tabindex="203"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_8"
						           value="{{ $otherRentalMonth8 ? $otherRentalMonth8 : 0 }}" id="other_rental_month_8" tabindex="204"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_9"
						           value="{{ $otherRentalMonth9 ? $otherRentalMonth9 : 0 }}" id="other_rental_month_9" tabindex="205"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_10"
						           value="{{ $otherRentalMonth10 ? $otherRentalMonth10 : 0 }}" id="other_rental_month_10" tabindex="206"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_11"
						           value="{{ $otherRentalMonth11 ? $otherRentalMonth11 : 0 }}" id="other_rental_month_11" tabindex="207"/></td>
						<td><input class="form-control currencyFields" type="text" name="other_rental_month_12"
						           value="{{ $otherRentalMonth12 ? $otherRentalMonth12 : 0 }}" id="other_rental_month_12" tabindex="208"/></td>
					</tr>

					<tr id="it_support_row" class="{{ $unitRows['it_support']['hidden'] ? 'hidden' : '' }}">
						<td>IT Support</td>
						<td><input class="form-control auto_calc" type="text" name="it_support_totals"
						           value="{{ $itSupportTotals ? $itSupportTotals : 0 }}" id="it_support_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_1"
						           value="{{ $itSupportMonth1 ? $itSupportMonth1 : 0 }}" id="it_support_month_1" tabindex="209"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_2"
						           value="{{ $itSupportMonth2 ? $itSupportMonth2 : 0 }}" id="it_support_month_2" tabindex="210"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_3"
						           value="{{ $itSupportMonth3 ? $itSupportMonth3 : 0 }}" id="it_support_month_3" tabindex="211"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_4"
						           value="{{ $itSupportMonth4 ? $itSupportMonth4 : 0 }}" id="it_support_month_4" tabindex="212"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_5"
						           value="{{ $itSupportMonth5 ? $itSupportMonth5 : 0 }}" id="it_support_month_5" tabindex="213"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_6"
						           value="{{ $itSupportMonth6 ? $itSupportMonth6 : 0 }}" id="it_support_month_6" tabindex="214"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_7"
						           value="{{ $itSupportMonth7 ? $itSupportMonth7 : 0 }}" id="it_support_month_7" tabindex="215"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_8"
						           value="{{ $itSupportMonth8 ? $itSupportMonth8 : 0 }}" id="it_support_month_8" tabindex="216"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_9"
						           value="{{ $itSupportMonth9 ? $itSupportMonth9 : 0 }}" id="it_support_month_9" tabindex="217"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_10"
						           value="{{ $itSupportMonth10 ? $itSupportMonth10 : 0 }}" id="it_support_month_10" tabindex="218"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_11"
						           value="{{ $itSupportMonth11 ? $itSupportMonth11 : 0 }}" id="it_support_month_11" tabindex="219"/></td>
						<td><input class="form-control currencyFields" type="text" name="it_support_month_12"
						           value="{{ $itSupportMonth12 ? $itSupportMonth12 : 0 }}" id="it_support_month_12" tabindex="220"/></td>
					</tr>

					<tr id="free_issues_row" class="{{ $unitRows['free_issues']['hidden'] ? 'hidden' : '' }}">
						<td>Free Issues</td>
						<td><input class="form-control auto_calc" type="text" name="free_issues_totals"
						           value="{{ $freeIssuesTotals ? $freeIssuesTotals : 0 }}" id="free_issues_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_1"
						           value="{{ $freeIssuesMonth1 ? $freeIssuesMonth1 : 0 }}" id="free_issues_month_1" tabindex="221"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_2"
						           value="{{ $freeIssuesMonth2 ? $freeIssuesMonth2 : 0 }}" id="free_issues_month_2" tabindex="222"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_3"
						           value="{{ $freeIssuesMonth3 ? $freeIssuesMonth3 : 0 }}" id="free_issues_month_3" tabindex="223"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_4"
						           value="{{ $freeIssuesMonth4 ? $freeIssuesMonth4 : 0 }}" id="free_issues_month_4" tabindex="224"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_5"
						           value="{{ $freeIssuesMonth5 ? $freeIssuesMonth5 : 0 }}" id="free_issues_month_5" tabindex="225"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_6"
						           value="{{ $freeIssuesMonth6 ? $freeIssuesMonth6 : 0 }}" id="free_issues_month_6" tabindex="226"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_7"
						           value="{{ $freeIssuesMonth7 ? $freeIssuesMonth7 : 0 }}" id="free_issues_month_7" tabindex="227"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_8"
						           value="{{ $freeIssuesMonth8 ? $freeIssuesMonth8 : 0 }}" id="free_issues_month_8" tabindex="228"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_9"
						           value="{{ $freeIssuesMonth9 ? $freeIssuesMonth9 : 0 }}" id="free_issues_month_9" tabindex="229"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_10"
						           value="{{ $freeIssuesMonth10 ? $freeIssuesMonth10 : 0 }}" id="free_issues_month_10" tabindex="230"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_11"
						           value="{{ $freeIssuesMonth11 ? $freeIssuesMonth11 : 0 }}" id="free_issues_month_11" tabindex="231"/></td>
						<td><input class="form-control currencyFields" type="text" name="free_issues_month_12"
						           value="{{ $freeIssuesMonth12 ? $freeIssuesMonth12 : 0 }}" id="free_issues_month_12" tabindex="232"/></td>
					</tr>

					<tr id="marketing_row" class="{{ $unitRows['marketing']['hidden'] ? 'hidden' : '' }}">
						<td>Marketing</td>
						<td><input class="form-control auto_calc" type="text" name="marketing_totals"
						           value="{{ $marketingTotals ? $marketingTotals : 0 }}" id="marketing_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_1"
						           value="{{ $marketingMonth1 ? $marketingMonth1 : 0 }}" id="marketing_month_1" tabindex="233"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_2"
						           value="{{ $marketingMonth2 ? $marketingMonth2 : 0 }}" id="marketing_month_2" tabindex="234"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_3"
						           value="{{ $marketingMonth3 ? $marketingMonth3 : 0 }}" id="marketing_month_3" tabindex="235"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_4"
						           value="{{ $marketingMonth4 ? $marketingMonth4 : 0 }}" id="marketing_month_4" tabindex="236"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_5"
						           value="{{ $marketingMonth5 ? $marketingMonth5 : 0 }}" id="marketing_month_5" tabindex="237"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_6"
						           value="{{ $marketingMonth6 ? $marketingMonth6 : 0 }}" id="marketing_month_6" tabindex="238"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_7"
						           value="{{ $marketingMonth7 ? $marketingMonth7 : 0 }}" id="marketing_month_7" tabindex="239"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_8"
						           value="{{ $marketingMonth8 ? $marketingMonth8 : 0 }}" id="marketing_month_8" tabindex="240"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_9"
						           value="{{ $marketingMonth9 ? $marketingMonth9 : 0 }}" id="marketing_month_9" tabindex="241"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_10"
						           value="{{ $marketingMonth10 ? $marketingMonth10 : 0 }}" id="marketing_month_10" tabindex="242"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_11"
						           value="{{ $marketingMonth11 ? $marketingMonth11 : 0 }}" id="marketing_month_11" tabindex="243"/></td>
						<td><input class="form-control currencyFields" type="text" name="marketing_month_12"
						           value="{{ $marketingMonth12 ? $marketingMonth12 : 0 }}" id="marketing_month_12" tabindex="244"/></td>
					</tr>

					<tr id="set_up_costs_row" class="{{ $unitRows['set_up_costs']['hidden'] ? 'hidden' : '' }}">
						<td>Set Up costs</td>
						<td><input class="form-control auto_calc" type="text" name="set_up_costs_totals"
						           value="{{ $setUpCostsTotals ? $setUpCostsTotals : 0 }}" id="set_up_costs_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_1"
						           value="{{ $setUpCostsMonth1 ? $setUpCostsMonth1 : 0 }}" id="set_up_costs_month_1" tabindex="245"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_2"
						           value="{{ $setUpCostsMonth2 ? $setUpCostsMonth2 : 0 }}" id="set_up_costs_month_2" tabindex="246"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_3"
						           value="{{ $setUpCostsMonth3 ? $setUpCostsMonth3 : 0 }}" id="set_up_costs_month_3" tabindex="247"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_4"
						           value="{{ $setUpCostsMonth4 ? $setUpCostsMonth4 : 0 }}" id="set_up_costs_month_4" tabindex="248"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_5"
						           value="{{ $setUpCostsMonth5 ? $setUpCostsMonth5 : 0 }}" id="set_up_costs_month_5" tabindex="249"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_6"
						           value="{{ $setUpCostsMonth6 ? $setUpCostsMonth6 : 0 }}" id="set_up_costs_month_6" tabindex="250"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_7"
						           value="{{ $setUpCostsMonth7 ? $setUpCostsMonth7 : 0 }}" id="set_up_costs_month_7" tabindex="251"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_8"
						           value="{{ $setUpCostsMonth8 ? $setUpCostsMonth8 : 0 }}" id="set_up_costs_month_8" tabindex="252"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_9"
						           value="{{ $setUpCostsMonth9 ? $setUpCostsMonth9 : 0 }}" id="set_up_costs_month_9" tabindex="253"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_10"
						           value="{{ $setUpCostsMonth10 ? $setUpCostsMonth10 : 0 }}" id="set_up_costs_month_10" tabindex="254"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_11"
						           value="{{ $setUpCostsMonth11 ? $setUpCostsMonth11 : 0 }}" id="set_up_costs_month_11" tabindex="255"/></td>
						<td><input class="form-control currencyFields" type="text" name="set_up_costs_month_12"
						           value="{{ $setUpCostsMonth12 ? $setUpCostsMonth12 : 0 }}" id="set_up_costs_month_12" tabindex="256"/></td>
					</tr>

					<tr id="credit_card_machines_row" class="{{ $unitRows['credit_card_machines']['hidden'] ? 'hidden' : '' }}">
						<td>Credit Card Machines</td>
						<td><input class="form-control auto_calc" type="text" name="credit_card_machines_totals"
						           value="{{ $creditCardMachinesTotals ? $creditCardMachinesTotals : 0 }}" id="credit_card_machines_totals"
						           readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_1"
						           value="{{ $creditCardMachinesMonth1 ? $creditCardMachinesMonth1 : 0 }}" id="credit_card_machines_month_1"
						           tabindex="257"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_2"
						           value="{{ $creditCardMachinesMonth2 ? $creditCardMachinesMonth2 : 0 }}" id="credit_card_machines_month_2"
						           tabindex="258"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_3"
						           value="{{ $creditCardMachinesMonth3 ? $creditCardMachinesMonth3 : 0 }}" id="credit_card_machines_month_3"
						           tabindex="259"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_4"
						           value="{{ $creditCardMachinesMonth4 ? $creditCardMachinesMonth4 : 0 }}" id="credit_card_machines_month_4"
						           tabindex="260"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_5"
						           value="{{ $creditCardMachinesMonth5 ? $creditCardMachinesMonth5 : 0 }}" id="credit_card_machines_month_5"
						           tabindex="261"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_6"
						           value="{{ $creditCardMachinesMonth6 ? $creditCardMachinesMonth6 : 0 }}" id="credit_card_machines_month_6"
						           tabindex="262"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_7"
						           value="{{ $creditCardMachinesMonth7 ? $creditCardMachinesMonth7 : 0 }}" id="credit_card_machines_month_7"
						           tabindex="263"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_8"
						           value="{{ $creditCardMachinesMonth8 ? $creditCardMachinesMonth8 : 0 }}" id="credit_card_machines_month_8"
						           tabindex="264"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_9"
						           value="{{ $creditCardMachinesMonth9 ? $creditCardMachinesMonth9 : 0 }}" id="credit_card_machines_month_9"
						           tabindex="265"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_10"
						           value="{{ $creditCardMachinesMonth10 ? $creditCardMachinesMonth10 : 0 }}" id="credit_card_machines_month_10"
						           tabindex="266"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_11"
						           value="{{ $creditCardMachinesMonth11 ? $creditCardMachinesMonth11 : 0 }}" id="credit_card_machines_month_11"
						           tabindex="267"/></td>
						<td><input class="form-control currencyFields" type="text" name="credit_card_machines_month_12"
						           value="{{ $creditCardMachinesMonth12 ? $creditCardMachinesMonth12 : 0 }}" id="credit_card_machines_month_12"
						           tabindex="268"/></td>
					</tr>

					<tr id="bizimply_cost_row" class="{{ $unitRows['bizimply_cost']['hidden'] ? 'hidden' : '' }}">
						<td>Bizimply cost</td>
						<td><input class="form-control auto_calc" type="text" name="bizimply_cost_totals"
						           value="{{ $bizimplyCostTotals ? $bizimplyCostTotals : 0 }}" id="bizimply_cost_totals" readonly="readonly"/>
						</td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_1"
						           value="{{ $bizimplyCostMonth1 ? $bizimplyCostMonth1 : 0 }}" id="bizimply_cost_month_1" tabindex="269"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_2"
						           value="{{ $bizimplyCostMonth2 ? $bizimplyCostMonth2 : 0 }}" id="bizimply_cost_month_2" tabindex="270"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_3"
						           value="{{ $bizimplyCostMonth3 ? $bizimplyCostMonth3 : 0 }}" id="bizimply_cost_month_3" tabindex="271"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_4"
						           value="{{ $bizimplyCostMonth4 ? $bizimplyCostMonth4 : 0 }}" id="bizimply_cost_month_4" tabindex="272"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_5"
						           value="{{ $bizimplyCostMonth5 ? $bizimplyCostMonth5 : 0 }}" id="bizimply_cost_month_5" tabindex="273"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_6"
						           value="{{ $bizimplyCostMonth6 ? $bizimplyCostMonth6 : 0 }}" id="bizimply_cost_month_6" tabindex="274"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_7"
						           value="{{ $bizimplyCostMonth7 ? $bizimplyCostMonth7 : 0 }}" id="bizimply_cost_month_7" tabindex="275"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_8"
						           value="{{ $bizimplyCostMonth8 ? $bizimplyCostMonth8 : 0 }}" id="bizimply_cost_month_8" tabindex="276"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_9"
						           value="{{ $bizimplyCostMonth9 ? $bizimplyCostMonth9 : 0 }}" id="bizimply_cost_month_9" tabindex="277"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_10"
						           value="{{ $bizimplyCostMonth10 ? $bizimplyCostMonth10 : 0 }}" id="bizimply_cost_month_10" tabindex="278"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_11"
						           value="{{ $bizimplyCostMonth11 ? $bizimplyCostMonth11 : 0 }}" id="bizimply_cost_month_11" tabindex="279"/></td>
						<td><input class="form-control currencyFields" type="text" name="bizimply_cost_month_12"
						           value="{{ $bizimplyCostMonth12 ? $bizimplyCostMonth12 : 0 }}" id="bizimply_cost_month_12" tabindex="280"/></td>
					</tr>

					<tr id="kitchtech_row" class="{{ $unitRows['kitchtech']['hidden'] ? 'hidden' : '' }}">
						<td>Kitchtech</td>
						<td><input class="form-control auto_calc" type="text" name="kitchtech_totals"
						           value="{{ $kitchtechTotals ? $kitchtechTotals : 0 }}" id="kitchtech_totals" readonly="readonly"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_1"
						           value="{{ $kitchtechMonth1 ? $kitchtechMonth1 : 0 }}" id="kitchtech_month_1" tabindex="281"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_2"
						           value="{{ $kitchtechMonth2 ? $kitchtechMonth2 : 0 }}" id="kitchtech_month_2" tabindex="282"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_3"
						           value="{{ $kitchtechMonth3 ? $kitchtechMonth3 : 0 }}" id="kitchtech_month_3" tabindex="283"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_4"
						           value="{{ $kitchtechMonth4 ? $kitchtechMonth4 : 0 }}" id="kitchtech_month_4" tabindex="284"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_5"
						           value="{{ $kitchtechMonth5 ? $kitchtechMonth5 : 0 }}" id="kitchtech_month_5" tabindex="285"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_6"
						           value="{{ $kitchtechMonth6 ? $kitchtechMonth6 : 0 }}" id="kitchtech_month_6" tabindex="286"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_7"
						           value="{{ $kitchtechMonth7 ? $kitchtechMonth7 : 0 }}" id="kitchtech_month_7" tabindex="287"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_8"
						           value="{{ $kitchtechMonth8 ? $kitchtechMonth8 : 0 }}" id="kitchtech_month_8" tabindex="288"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_9"
						           value="{{ $kitchtechMonth9 ? $kitchtechMonth9 : 0 }}" id="kitchtech_month_9" tabindex="289"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_10"
						           value="{{ $kitchtechMonth10 ? $kitchtechMonth10 : 0 }}" id="kitchtech_month_10" tabindex="290"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_11"
						           value="{{ $kitchtechMonth11 ? $kitchtechMonth11 : 0 }}" id="kitchtech_month_11" tabindex="291"/></td>
						<td><input class="form-control currencyFields" type="text" name="kitchtech_month_12"
						           value="{{ $kitchtechMonth12 ? $kitchtechMonth12 : 0 }}" id="kitchtech_month_12" tabindex="292"/></td>
					</tr>

					<tr id="total_row" class="">
						<td>Total</td>
						<td>
							<input class="form-control" type="text" value="0" name="budget_total_totals" id="budget_total_totals"
							       readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_1" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_2" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_3" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_4" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_5" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_6" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_7" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_8" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_9" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_10" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_11" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="budget_total_month_12" readonly="readonly"/>
						</td>
					</tr>

					<tr id="total_row" class="">
						<td>Net Subsidy</td>
						<td>
							<input class="form-control" type="text" value="0" name="net_sub_totals" id="net_sub_totals" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_1" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_2" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_3" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_4" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_5" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_6" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_7" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_8" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_9" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_10" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_11" readonly="readonly"/>
						</td>
						<td>
							<input class="form-control" type="text" value="0" id="net_sub_month_12" readonly="readonly"/>
						</td>
					</tr>
				</table>
			</div>

			<div class="form-group set-margin-left-0 set-margin-right-0">
				{{ Form::hidden('hidden_unit_name', $unitName, array('id' => 'hidden_unit_name')) }}
				<input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-35" name='submit' value='Submit'
				       tabindex='293'/>
			</div>
			{!!Form::close()!!}
		</section>
	</section>

	<div id="unit_rows_visibility" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Rows visibility</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						@foreach($unitRows as $rowIndex => $row)
							<div class="col col-xs-12 col-md-6 margin-top-10">
								<button id="{{ $rowIndex }}" type="button"
								        class="toggle-row-visibility btn {{ $row['hidden'] ? 'btn-default' : 'btn-primary' }} btn-block">{{ $row['name'] }}</button>
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>

@stop

@section('scripts')
	<script src="{{ elixir('js/format_number.js') }}"></script>
	<script src="{{ elixir('js/trading_account_js.js') }}"></script>

	<script type="text/javascript">
        function changeLog() {
            var unitId = $("#unit_name").val();
            if (unitId) {
                $.ajax({
                    type: "GET",
                    url: "{{ url('/change_log/json') }}",
                    data: {unit_name: unitId}
                }).done(function (data) {
                    var obj = jQuery.parseJSON(data);

                    if (obj.change_log_table) {
                        $('.change_log_div').slideDown("slow");
                        $('table#change_log_table').html(obj.change_log_table);
                    } else {
                        $('.change_log_div').slideUp("slow");
                    }

                    $.each(obj.unit_rows, function (i, rowInfo) {
                        if (rowInfo.hidden) {
                            $('#' + rowInfo.rowIndex).removeClass('btn-primary btn-default').addClass('btn-default');
                            $('#' + rowInfo.rowIndex + '_row').addClass('hidden');
                        } else {
                            $('#' + rowInfo.rowIndex).removeClass('btn-primary btn-default').addClass('btn-primary');
                            $('#' + rowInfo.rowIndex + '_row').removeClass('hidden');
                        }
                    });

                    calculateBudgetTotal();
                });
            } else {
                $('.change_log_div').slideUp("slow");
            }
        }

        $(document).ready(function () {
            changeLog();

            $('#budget_start_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            }).on('changeDate', function (e) {
                var startDate = $('#budget_start_date').val();
                var k = getTomorrow(startDate, 364);
                var myTime = ('0' + k.getDate()).slice(-2) + '-' + ('0' + (k.getMonth() + 1)).slice(-2) + '-' + k.getFullYear();

                // Budget Year
                var fromYear = startDate.substr(6, 4);
                if (fromYear != k.getFullYear())
                    $('#budget_year').val(fromYear + ' - ' + k.getFullYear());
                else
                    $('#budget_year').val(fromYear);

                $('#budget_end_date').datepicker('setDate', myTime);
                $('#budget_end_date').focus();
            });

            $('#budget_end_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            }).on('changeDate', function (e) {
                $('#approved_by').focus();
            });

            $("#unit_name").change(function () {
                $('#hidden_unit_name').val($(this).find(':selected').text());
            });

            $('#budget_start_date_icon').click(function () {
                $(document).ready(function () {
                    $("#budget_start_date").datepicker().focus();
                });
            });

            $('#budget_end_date_icon').click(function () {
                $(document).ready(function () {
                    $("#budget_end_date").datepicker().focus();
                });
            });

            $('.toggle-row-visibility').on('click', function (e) {
                e.preventDefault();

                var button = $(this);
                var rowIndex = button.attr('id');

                $.ajax({
                    type: "post",
                    url: '/sheets/phased-budget/toggle-row-visibility',
                    data: {
                        _token: '{{csrf_token()}}',
                        unitId: $('#unit_name').val(),
                        rowIndex: rowIndex
                    },
                    success: function () {
                        button.toggleClass('btn-primary btn-default');
                        $('#' + rowIndex + '_row').toggleClass('hidden');

                        calculateBudgetTotal();
                    }
                });

            })

            // Budget Types
            $('input[name="budget_type"]').on('change', function () {
                $('.budget-type-row').hide();

                $('.budget-type-row.budget-type-' + $(this).val()).show();
            })

            $('#change-log-collapse')
                .on('show.bs.collapse', function () {
                    $('.toggle-budgets-visibility').removeClass('fa-caret-down').addClass('fa-caret-up');
                })
                .on('hide.bs.collapse', function () {
                    $('.toggle-budgets-visibility').removeClass('fa-caret-up').addClass('fa-caret-down');
                });
        });

	</script>
@stop