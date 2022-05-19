@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<strong>Phased Budget Confirmation</strong>
		</header>

		<section class="dataTables-padding">
			{!! Form::open(['url' => 'sheets/phased-budget/post', 'class' => 'form-horizontal form-bordered']) !!}
			<div class="form-group margin-bottom-0 margin-left-0 margin-right-0">
				<div class="clearfix"></div>
				<div class="col-md-12 padding-left-0 padding-right-0 border-top-0">
					<div class="responsive-content">
						<table id="cash_purchases_tbl" class="table table-bordered table-striped table-small">
							<tr>
								<td>
									<label>Unit Name:</label>
									{{ Form::text('unit_name_1', $unitName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
									{{ Form::hidden('unit_id', $unitId) }}
									{{ Form::hidden('supervisor_id', $userId) }}
									{{ Form::hidden('supervisor_name', $userName) }}
									{{ Form::hidden('budget_start_date', $budgetStartDate) }}
									{{ Form::hidden('budget_end_date', $budgetEndDate) }}
								</td>
								<td>
									<label>Budget Year:</label>
									{{ Form::text('budget_year', $budgetYear, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
								<td class="hidden">
									<label>Head Count:</label>
									{{ Form::text('head_count_totals', $headCountTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('head_count_month_1', $headCountMonth1) }}
									{{ Form::hidden('head_count_month_2', $headCountMonth2) }}
									{{ Form::hidden('head_count_month_3', $headCountMonth3) }}
									{{ Form::hidden('head_count_month_4', $headCountMonth4) }}
									{{ Form::hidden('head_count_month_5', $headCountMonth5) }}
									{{ Form::hidden('head_count_month_6', $headCountMonth6) }}
									{{ Form::hidden('head_count_month_7', $headCountMonth7) }}
									{{ Form::hidden('head_count_month_8', $headCountMonth8) }}
									{{ Form::hidden('head_count_month_9', $headCountMonth9) }}
									{{ Form::hidden('head_count_month_10', $headCountMonth10) }}
									{{ Form::hidden('head_count_month_11', $headCountMonth11) }}
									{{ Form::hidden('head_count_month_12', $headCountMonth12) }}
								</td>
							</tr>
							<tr>
								<td>
									<label># trading days:</label>
									{{ Form::text('num_trading_days_totals', $numTradingDaysTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('num_trading_days_month_1', $numTradingDaysMonth1) }}
									{{ Form::hidden('num_trading_days_month_2', $numTradingDaysMonth2) }}
									{{ Form::hidden('num_trading_days_month_3', $numTradingDaysMonth3) }}
									{{ Form::hidden('num_trading_days_month_4', $numTradingDaysMonth4) }}
									{{ Form::hidden('num_trading_days_month_5', $numTradingDaysMonth5) }}
									{{ Form::hidden('num_trading_days_month_6', $numTradingDaysMonth6) }}
									{{ Form::hidden('num_trading_days_month_7', $numTradingDaysMonth7) }}
									{{ Form::hidden('num_trading_days_month_8', $numTradingDaysMonth8) }}
									{{ Form::hidden('num_trading_days_month_9', $numTradingDaysMonth9) }}
									{{ Form::hidden('num_trading_days_month_10', $numTradingDaysMonth10) }}
									{{ Form::hidden('num_trading_days_month_11', $numTradingDaysMonth11) }}
									{{ Form::hidden('num_trading_days_month_12', $numTradingDaysMonth12) }}
								</td>
								<td>
									<label># of weeks:</label>
									{{ Form::text('num_of_weeks_totals', $numOfWeeksTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('num_of_weeks_month_1', $numOfWeeksMonth1) }}
									{{ Form::hidden('num_of_weeks_month_2', $numOfWeeksMonth2) }}
									{{ Form::hidden('num_of_weeks_month_3', $numOfWeeksMonth3) }}
									{{ Form::hidden('num_of_weeks_month_4', $numOfWeeksMonth4) }}
									{{ Form::hidden('num_of_weeks_month_5', $numOfWeeksMonth5) }}
									{{ Form::hidden('num_of_weeks_month_6', $numOfWeeksMonth6) }}
									{{ Form::hidden('num_of_weeks_month_7', $numOfWeeksMonth7) }}
									{{ Form::hidden('num_of_weeks_month_8', $numOfWeeksMonth8) }}
									{{ Form::hidden('num_of_weeks_month_9', $numOfWeeksMonth9) }}
									{{ Form::hidden('num_of_weeks_month_10', $numOfWeeksMonth10) }}
									{{ Form::hidden('num_of_weeks_month_11', $numOfWeeksMonth11) }}
									{{ Form::hidden('num_of_weeks_month_12', $numOfWeeksMonth12) }}
								</td>
								<td>
									<label>Labour Hours:</label>
									{{ Form::text('labour_hours_totals', $labourHoursTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('labour_hours_month_1', $labourHoursMonth1) }}
									{{ Form::hidden('labour_hours_month_2', $labourHoursMonth2) }}
									{{ Form::hidden('labour_hours_month_3', $labourHoursMonth3) }}
									{{ Form::hidden('labour_hours_month_4', $labourHoursMonth4) }}
									{{ Form::hidden('labour_hours_month_5', $labourHoursMonth5) }}
									{{ Form::hidden('labour_hours_month_6', $labourHoursMonth6) }}
									{{ Form::hidden('labour_hours_month_7', $labourHoursMonth7) }}
									{{ Form::hidden('labour_hours_month_8', $labourHoursMonth8) }}
									{{ Form::hidden('labour_hours_month_9', $labourHoursMonth9) }}
									{{ Form::hidden('labour_hours_month_10', $labourHoursMonth10) }}
									{{ Form::hidden('labour_hours_month_11', $labourHoursMonth11) }}
									{{ Form::hidden('labour_hours_month_12', $labourHoursMonth12) }}
								</td>
							</tr>
							<tr>
								<td>
									<label>Gross Sales:</label>
									{{ Form::text('gross_sales_totals', $grossSalesTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('gross_sales_month_1', $grossSalesMonth1) }}
									{{ Form::hidden('gross_sales_month_2', $grossSalesMonth2) }}
									{{ Form::hidden('gross_sales_month_3', $grossSalesMonth3) }}
									{{ Form::hidden('gross_sales_month_4', $grossSalesMonth4) }}
									{{ Form::hidden('gross_sales_month_5', $grossSalesMonth5) }}
									{{ Form::hidden('gross_sales_month_6', $grossSalesMonth6) }}
									{{ Form::hidden('gross_sales_month_7', $grossSalesMonth7) }}
									{{ Form::hidden('gross_sales_month_8', $grossSalesMonth8) }}
									{{ Form::hidden('gross_sales_month_9', $grossSalesMonth9) }}
									{{ Form::hidden('gross_sales_month_10', $grossSalesMonth10) }}
									{{ Form::hidden('gross_sales_month_11', $grossSalesMonth11) }}
									{{ Form::hidden('gross_sales_month_12', $grossSalesMonth12) }}
								</td>
								<td>
									<label>Net Sales:</label>
									{{ Form::text('net_sales_totals', $netSalesTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('net_sales_month_1', $netSalesMonth1) }}
									{{ Form::hidden('net_sales_month_2', $netSalesMonth2) }}
									{{ Form::hidden('net_sales_month_3', $netSalesMonth3) }}
									{{ Form::hidden('net_sales_month_4', $netSalesMonth4) }}
									{{ Form::hidden('net_sales_month_5', $netSalesMonth5) }}
									{{ Form::hidden('net_sales_month_6', $netSalesMonth6) }}
									{{ Form::hidden('net_sales_month_7', $netSalesMonth7) }}
									{{ Form::hidden('net_sales_month_8', $netSalesMonth8) }}
									{{ Form::hidden('net_sales_month_9', $netSalesMonth9) }}
									{{ Form::hidden('net_sales_month_10', $netSalesMonth10) }}
									{{ Form::hidden('net_sales_month_11', $netSalesMonth11) }}
									{{ Form::hidden('net_sales_month_12', $netSalesMonth12) }}
								</td>
								<td>&nbsp;
									<label>Contract Type:</label>
									{{ Form::text('', $contractTypeLegend, array('class' => 'form-control', 'readonly' => 'readonly')) }}
									{{ Form::hidden('contract_type', $contractType) }}
								</td>
							</tr>
							<tr>
								<td>
									<label>Cost of Sales:</label>
									{{ Form::text('cost_of_sales_totals', $costOfSalesTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('cost_of_sales_month_1', $costOfSalesMonth1) }}
									{{ Form::hidden('cost_of_sales_month_2', $costOfSalesMonth2) }}
									{{ Form::hidden('cost_of_sales_month_3', $costOfSalesMonth3) }}
									{{ Form::hidden('cost_of_sales_month_4', $costOfSalesMonth4) }}
									{{ Form::hidden('cost_of_sales_month_5', $costOfSalesMonth5) }}
									{{ Form::hidden('cost_of_sales_month_6', $costOfSalesMonth6) }}
									{{ Form::hidden('cost_of_sales_month_7', $costOfSalesMonth7) }}
									{{ Form::hidden('cost_of_sales_month_8', $costOfSalesMonth8) }}
									{{ Form::hidden('cost_of_sales_month_9', $costOfSalesMonth9) }}
									{{ Form::hidden('cost_of_sales_month_10', $costOfSalesMonth10) }}
									{{ Form::hidden('cost_of_sales_month_11', $costOfSalesMonth11) }}
									{{ Form::hidden('cost_of_sales_month_12', $costOfSalesMonth12) }}
								</td>
								<td>
									<label>VAT:</label>
									{{ Form::text('vat_totals', $vatTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('vat_month_1', $vatMonth1) }}
									{{ Form::hidden('vat_month_2', $vatMonth2) }}
									{{ Form::hidden('vat_month_3', $vatMonth3) }}
									{{ Form::hidden('vat_month_4', $vatMonth4) }}
									{{ Form::hidden('vat_month_5', $vatMonth5) }}
									{{ Form::hidden('vat_month_6', $vatMonth6) }}
									{{ Form::hidden('vat_month_7', $vatMonth7) }}
									{{ Form::hidden('vat_month_8', $vatMonth8) }}
									{{ Form::hidden('vat_month_9', $vatMonth9) }}
									{{ Form::hidden('vat_month_10', $vatMonth10) }}
									{{ Form::hidden('vat_month_11', $vatMonth11) }}
									{{ Form::hidden('vat_month_12', $vatMonth12) }}
								</td>
								<td>&nbsp;
									<label>GP Type:</label>
									{{ Form::text('budget_type_legend', $budgetTypeLegend, array('class' => 'form-control', 'readonly' => 'readonly')) }}
									{{ Form::hidden('budget_type', $budgetType) }}
								</td>
							</tr>
							<tr>
								<td class="{{ $budgetType == \App\BudgetType::BUDGET_TYPE_GP ? '' : 'hidden' }}">
									<label>Gross Profit (Gross):</label>
									{{ Form::text('gross_profit_totals', $grossProfitTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('gross_profit_month_1', $grossProfitMonth1) }}
									{{ Form::hidden('gross_profit_month_2', $grossProfitMonth2) }}
									{{ Form::hidden('gross_profit_month_3', $grossProfitMonth3) }}
									{{ Form::hidden('gross_profit_month_4', $grossProfitMonth4) }}
									{{ Form::hidden('gross_profit_month_5', $grossProfitMonth5) }}
									{{ Form::hidden('gross_profit_month_6', $grossProfitMonth6) }}
									{{ Form::hidden('gross_profit_month_7', $grossProfitMonth7) }}
									{{ Form::hidden('gross_profit_month_8', $grossProfitMonth8) }}
									{{ Form::hidden('gross_profit_month_9', $grossProfitMonth9) }}
									{{ Form::hidden('gross_profit_month_10', $grossProfitMonth10) }}
									{{ Form::hidden('gross_profit_month_11', $grossProfitMonth11) }}
									{{ Form::hidden('gross_profit_month_12', $grossProfitMonth12) }}
								</td>
								<td class="{{ $budgetType == \App\BudgetType::BUDGET_TYPE_NET ? '' : 'hidden' }}">
									<label>Gross Profit (Net):</label>
									{{ Form::text('gross_profit_net_totals', $grossProfitNetTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('gross_profit_net_month_1', $grossProfitNetMonth1) }}
									{{ Form::hidden('gross_profit_net_month_2', $grossProfitNetMonth2) }}
									{{ Form::hidden('gross_profit_net_month_3', $grossProfitNetMonth3) }}
									{{ Form::hidden('gross_profit_net_month_4', $grossProfitNetMonth4) }}
									{{ Form::hidden('gross_profit_net_month_5', $grossProfitNetMonth5) }}
									{{ Form::hidden('gross_profit_net_month_6', $grossProfitNetMonth6) }}
									{{ Form::hidden('gross_profit_net_month_7', $grossProfitNetMonth7) }}
									{{ Form::hidden('gross_profit_net_month_8', $grossProfitNetMonth8) }}
									{{ Form::hidden('gross_profit_net_month_9', $grossProfitNetMonth9) }}
									{{ Form::hidden('gross_profit_net_month_10', $grossProfitNetMonth10) }}
									{{ Form::hidden('gross_profit_net_month_11', $grossProfitNetMonth11) }}
									{{ Form::hidden('gross_profit_net_month_12', $grossProfitNetMonth12) }}
								</td>
							</tr>
							<tr>
								<td class="{{ $budgetType == \App\BudgetType::BUDGET_TYPE_GP ? '' : 'hidden' }}">
									<label>G.P.% on Gross Sales:</label>
									{{ Form::text('gpp_on_gross_sales_totals', $gppOnGrossSalesTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('gpp_on_gross_sales_month_1', $gppOnGrossSalesMonth1) }}
									{{ Form::hidden('gpp_on_gross_sales_month_2', $gppOnGrossSalesMonth2) }}
									{{ Form::hidden('gpp_on_gross_sales_month_3', $gppOnGrossSalesMonth3) }}
									{{ Form::hidden('gpp_on_gross_sales_month_4', $gppOnGrossSalesMonth4) }}
									{{ Form::hidden('gpp_on_gross_sales_month_5', $gppOnGrossSalesMonth5) }}
									{{ Form::hidden('gpp_on_gross_sales_month_6', $gppOnGrossSalesMonth6) }}
									{{ Form::hidden('gpp_on_gross_sales_month_7', $gppOnGrossSalesMonth7) }}
									{{ Form::hidden('gpp_on_gross_sales_month_8', $gppOnGrossSalesMonth8) }}
									{{ Form::hidden('gpp_on_gross_sales_month_9', $gppOnGrossSalesMonth9) }}
									{{ Form::hidden('gpp_on_gross_sales_month_10', $gppOnGrossSalesMonth10) }}
									{{ Form::hidden('gpp_on_gross_sales_month_11', $gppOnGrossSalesMonth11) }}
									{{ Form::hidden('gpp_on_gross_sales_month_12', $gppOnGrossSalesMonth12) }}
								</td>
								<td class="{{ $budgetType == \App\BudgetType::BUDGET_TYPE_NET ? '' : 'hidden' }}">
									<label>G.P.% on Net Sales:</label>
									{{ Form::text('gpp_on_net_sales_totals', $gppOnNetSalesTotals, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
									{{ Form::hidden('gpp_on_net_sales_month_1', $gppOnNetSalesMonth1) }}
									{{ Form::hidden('gpp_on_net_sales_month_2', $gppOnNetSalesMonth2) }}
									{{ Form::hidden('gpp_on_net_sales_month_3', $gppOnNetSalesMonth3) }}
									{{ Form::hidden('gpp_on_net_sales_month_4', $gppOnNetSalesMonth4) }}
									{{ Form::hidden('gpp_on_net_sales_month_5', $gppOnNetSalesMonth5) }}
									{{ Form::hidden('gpp_on_net_sales_month_6', $gppOnNetSalesMonth6) }}
									{{ Form::hidden('gpp_on_net_sales_month_7', $gppOnNetSalesMonth7) }}
									{{ Form::hidden('gpp_on_net_sales_month_8', $gppOnNetSalesMonth8) }}
									{{ Form::hidden('gpp_on_net_sales_month_9', $gppOnNetSalesMonth9) }}
									{{ Form::hidden('gpp_on_net_sales_month_10', $gppOnNetSalesMonth10) }}
									{{ Form::hidden('gpp_on_net_sales_month_11', $gppOnNetSalesMonth11) }}
									{{ Form::hidden('gpp_on_net_sales_month_12', $gppOnNetSalesMonth12) }}
								</td>
							</tr>
							<tr>
								<td>
									<label>Total:</label>
									{{ Form::text('budget_total', $budgetTotal, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
								<td>
									<label>Net Subsidy:</label>
									{{ Form::text('net_sub', $netSub, array('class' => 'form-control text-right', 'readonly' => 'readonly')) }}
								</td>
							</tr>
						</table>
					</div>					
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-12">

					{{ Form::hidden('labour_totals', $labourTotals) }}
					{{ Form::hidden('labour_month_1', $labourMonth1) }}
					{{ Form::hidden('labour_month_2', $labourMonth2) }}
					{{ Form::hidden('labour_month_3', $labourMonth3) }}
					{{ Form::hidden('labour_month_4', $labourMonth4) }}
					{{ Form::hidden('labour_month_5', $labourMonth5) }}
					{{ Form::hidden('labour_month_6', $labourMonth6) }}
					{{ Form::hidden('labour_month_7', $labourMonth7) }}
					{{ Form::hidden('labour_month_8', $labourMonth8) }}
					{{ Form::hidden('labour_month_9', $labourMonth9) }}
					{{ Form::hidden('labour_month_10', $labourMonth10) }}
					{{ Form::hidden('labour_month_11', $labourMonth11) }}
					{{ Form::hidden('labour_month_12', $labourMonth12) }}

					{{ Form::hidden('training_totals', $trainingTotals) }}
					{{ Form::hidden('training_month_1', $trainingMonth1) }}
					{{ Form::hidden('training_month_2', $trainingMonth2) }}
					{{ Form::hidden('training_month_3', $trainingMonth3) }}
					{{ Form::hidden('training_month_4', $trainingMonth4) }}
					{{ Form::hidden('training_month_5', $trainingMonth5) }}
					{{ Form::hidden('training_month_6', $trainingMonth6) }}
					{{ Form::hidden('training_month_7', $trainingMonth7) }}
					{{ Form::hidden('training_month_8', $trainingMonth8) }}
					{{ Form::hidden('training_month_9', $trainingMonth9) }}
					{{ Form::hidden('training_month_10', $trainingMonth10) }}
					{{ Form::hidden('training_month_11', $trainingMonth11) }}
					{{ Form::hidden('training_month_12', $trainingMonth12) }}

					{{ Form::hidden('cleaning_totals', $cleaningTotals) }}
					{{ Form::hidden('cleaning_month_1', $cleaningMonth1) }}
					{{ Form::hidden('cleaning_month_2', $cleaningMonth2) }}
					{{ Form::hidden('cleaning_month_3', $cleaningMonth3) }}
					{{ Form::hidden('cleaning_month_4', $cleaningMonth4) }}
					{{ Form::hidden('cleaning_month_5', $cleaningMonth5) }}
					{{ Form::hidden('cleaning_month_6', $cleaningMonth6) }}
					{{ Form::hidden('cleaning_month_7', $cleaningMonth7) }}
					{{ Form::hidden('cleaning_month_8', $cleaningMonth8) }}
					{{ Form::hidden('cleaning_month_9', $cleaningMonth9) }}
					{{ Form::hidden('cleaning_month_10', $cleaningMonth10) }}
					{{ Form::hidden('cleaning_month_11', $cleaningMonth11) }}
					{{ Form::hidden('cleaning_month_12', $cleaningMonth12) }}

					{{ Form::hidden('disposables_totals', $disposablesTotals) }}
					{{ Form::hidden('disposables_month_1', $disposablesMonth1) }}
					{{ Form::hidden('disposables_month_2', $disposablesMonth2) }}
					{{ Form::hidden('disposables_month_3', $disposablesMonth3) }}
					{{ Form::hidden('disposables_month_4', $disposablesMonth4) }}
					{{ Form::hidden('disposables_month_5', $disposablesMonth5) }}
					{{ Form::hidden('disposables_month_6', $disposablesMonth6) }}
					{{ Form::hidden('disposables_month_7', $disposablesMonth7) }}
					{{ Form::hidden('disposables_month_8', $disposablesMonth8) }}
					{{ Form::hidden('disposables_month_9', $disposablesMonth9) }}
					{{ Form::hidden('disposables_month_10', $disposablesMonth10) }}
					{{ Form::hidden('disposables_month_11', $disposablesMonth11) }}
					{{ Form::hidden('disposables_month_12', $disposablesMonth12) }}

					{{ Form::hidden('uniform_totals', $uniformTotals) }}
					{{ Form::hidden('uniform_month_1', $uniformMonth1) }}
					{{ Form::hidden('uniform_month_2', $uniformMonth2) }}
					{{ Form::hidden('uniform_month_3', $uniformMonth3) }}
					{{ Form::hidden('uniform_month_4', $uniformMonth4) }}
					{{ Form::hidden('uniform_month_5', $uniformMonth5) }}
					{{ Form::hidden('uniform_month_6', $uniformMonth6) }}
					{{ Form::hidden('uniform_month_7', $uniformMonth7) }}
					{{ Form::hidden('uniform_month_8', $uniformMonth8) }}
					{{ Form::hidden('uniform_month_9', $uniformMonth9) }}
					{{ Form::hidden('uniform_month_10', $uniformMonth10) }}
					{{ Form::hidden('uniform_month_11', $uniformMonth11) }}
					{{ Form::hidden('uniform_month_12', $uniformMonth12) }}

					{{ Form::hidden('delph_and_cutlery_totals', $delphAndCutleryTotals) }}
					{{ Form::hidden('delph_and_cutlery_month_1', $delphAndCutleryMonth1) }}
					{{ Form::hidden('delph_and_cutlery_month_2', $delphAndCutleryMonth2) }}
					{{ Form::hidden('delph_and_cutlery_month_3', $delphAndCutleryMonth3) }}
					{{ Form::hidden('delph_and_cutlery_month_4', $delphAndCutleryMonth4) }}
					{{ Form::hidden('delph_and_cutlery_month_5', $delphAndCutleryMonth5) }}
					{{ Form::hidden('delph_and_cutlery_month_6', $delphAndCutleryMonth6) }}
					{{ Form::hidden('delph_and_cutlery_month_7', $delphAndCutleryMonth7) }}
					{{ Form::hidden('delph_and_cutlery_month_8', $delphAndCutleryMonth8) }}
					{{ Form::hidden('delph_and_cutlery_month_9', $delphAndCutleryMonth9) }}
					{{ Form::hidden('delph_and_cutlery_month_10', $delphAndCutleryMonth10) }}
					{{ Form::hidden('delph_and_cutlery_month_11', $delphAndCutleryMonth11) }}
					{{ Form::hidden('delph_and_cutlery_month_12', $delphAndCutleryMonth12) }}

					{{ Form::hidden('bank_charges_totals', $bankChargesTotals) }}
					{{ Form::hidden('bank_charges_month_1', $bankChargesMonth1) }}
					{{ Form::hidden('bank_charges_month_2', $bankChargesMonth2) }}
					{{ Form::hidden('bank_charges_month_3', $bankChargesMonth3) }}
					{{ Form::hidden('bank_charges_month_4', $bankChargesMonth4) }}
					{{ Form::hidden('bank_charges_month_5', $bankChargesMonth5) }}
					{{ Form::hidden('bank_charges_month_6', $bankChargesMonth6) }}
					{{ Form::hidden('bank_charges_month_7', $bankChargesMonth7) }}
					{{ Form::hidden('bank_charges_month_8', $bankChargesMonth8) }}
					{{ Form::hidden('bank_charges_month_9', $bankChargesMonth9) }}
					{{ Form::hidden('bank_charges_month_10', $bankChargesMonth10) }}
					{{ Form::hidden('bank_charges_month_11', $bankChargesMonth11) }}
					{{ Form::hidden('bank_charges_month_12', $bankChargesMonth12) }}

					{{ Form::hidden('investment_totals', $investmentTotals) }}
					{{ Form::hidden('investment_month_1', $investmentMonth1) }}
					{{ Form::hidden('investment_month_2', $investmentMonth1) }}
					{{ Form::hidden('investment_month_3', $investmentMonth1) }}
					{{ Form::hidden('investment_month_4', $investmentMonth1) }}
					{{ Form::hidden('investment_month_5', $investmentMonth1) }}
					{{ Form::hidden('investment_month_6', $investmentMonth1) }}
					{{ Form::hidden('investment_month_7', $investmentMonth1) }}
					{{ Form::hidden('investment_month_8', $investmentMonth1) }}
					{{ Form::hidden('investment_month_9', $investmentMonth1) }}
					{{ Form::hidden('investment_month_10', $investmentMonth1) }}
					{{ Form::hidden('investment_month_11', $investmentMonth1) }}
					{{ Form::hidden('investment_month_12', $investmentMonth1) }}

					{{ Form::hidden('management_fee_totals', $managementFeeTotals) }}
					{{ Form::hidden('management_fee_month_1', $managementFeeMonth1) }}
					{{ Form::hidden('management_fee_month_2', $managementFeeMonth2) }}
					{{ Form::hidden('management_fee_month_3', $managementFeeMonth3) }}
					{{ Form::hidden('management_fee_month_4', $managementFeeMonth4) }}
					{{ Form::hidden('management_fee_month_5', $managementFeeMonth5) }}
					{{ Form::hidden('management_fee_month_6', $managementFeeMonth6) }}
					{{ Form::hidden('management_fee_month_7', $managementFeeMonth7) }}
					{{ Form::hidden('management_fee_month_8', $managementFeeMonth8) }}
					{{ Form::hidden('management_fee_month_9', $managementFeeMonth9) }}
					{{ Form::hidden('management_fee_month_10', $managementFeeMonth10) }}
					{{ Form::hidden('management_fee_month_11', $managementFeeMonth11) }}
					{{ Form::hidden('management_fee_month_12', $managementFeeMonth12) }}

					{{ Form::hidden('management_fee_totals', $managementFeeTotals) }}
					{{ Form::hidden('management_fee_month_1', $managementFeeMonth1) }}
					{{ Form::hidden('management_fee_month_2', $managementFeeMonth2) }}
					{{ Form::hidden('management_fee_month_3', $managementFeeMonth3) }}
					{{ Form::hidden('management_fee_month_4', $managementFeeMonth4) }}
					{{ Form::hidden('management_fee_month_5', $managementFeeMonth5) }}
					{{ Form::hidden('management_fee_month_6', $managementFeeMonth6) }}
					{{ Form::hidden('management_fee_month_7', $managementFeeMonth7) }}
					{{ Form::hidden('management_fee_month_8', $managementFeeMonth8) }}
					{{ Form::hidden('management_fee_month_9', $managementFeeMonth9) }}
					{{ Form::hidden('management_fee_month_10', $managementFeeMonth10) }}
					{{ Form::hidden('management_fee_month_11', $managementFeeMonth11) }}
					{{ Form::hidden('management_fee_month_12', $managementFeeMonth12) }}

					{{ Form::hidden('insurance_and_related_costs_totals', $insuranceAndRelatedCostsTotals) }}
					{{ Form::hidden('insurance_and_related_costs_month_1', $insuranceAndRelatedCostsMonth1) }}
					{{ Form::hidden('insurance_and_related_costs_month_2', $insuranceAndRelatedCostsMonth2) }}
					{{ Form::hidden('insurance_and_related_costs_month_3', $insuranceAndRelatedCostsMonth3) }}
					{{ Form::hidden('insurance_and_related_costs_month_4', $insuranceAndRelatedCostsMonth4) }}
					{{ Form::hidden('insurance_and_related_costs_month_5', $insuranceAndRelatedCostsMonth5) }}
					{{ Form::hidden('insurance_and_related_costs_month_6', $insuranceAndRelatedCostsMonth6) }}
					{{ Form::hidden('insurance_and_related_costs_month_7', $insuranceAndRelatedCostsMonth7) }}
					{{ Form::hidden('insurance_and_related_costs_month_8', $insuranceAndRelatedCostsMonth8) }}
					{{ Form::hidden('insurance_and_related_costs_month_9', $insuranceAndRelatedCostsMonth9) }}
					{{ Form::hidden('insurance_and_related_costs_month_10', $insuranceAndRelatedCostsMonth10) }}
					{{ Form::hidden('insurance_and_related_costs_month_11', $insuranceAndRelatedCostsMonth11) }}
					{{ Form::hidden('insurance_and_related_costs_month_12', $insuranceAndRelatedCostsMonth12) }}

					{{ Form::hidden('coffee_machine_rental_totals', $coffeeMachineRentalTotals) }}
					{{ Form::hidden('coffee_machine_rental_month_1', $coffeeMachineRentalMonth1) }}
					{{ Form::hidden('coffee_machine_rental_month_2', $coffeeMachineRentalMonth2) }}
					{{ Form::hidden('coffee_machine_rental_month_3', $coffeeMachineRentalMonth3) }}
					{{ Form::hidden('coffee_machine_rental_month_4', $coffeeMachineRentalMonth4) }}
					{{ Form::hidden('coffee_machine_rental_month_5', $coffeeMachineRentalMonth5) }}
					{{ Form::hidden('coffee_machine_rental_month_6', $coffeeMachineRentalMonth6) }}
					{{ Form::hidden('coffee_machine_rental_month_7', $coffeeMachineRentalMonth7) }}
					{{ Form::hidden('coffee_machine_rental_month_8', $coffeeMachineRentalMonth8) }}
					{{ Form::hidden('coffee_machine_rental_month_9', $coffeeMachineRentalMonth9) }}
					{{ Form::hidden('coffee_machine_rental_month_10', $coffeeMachineRentalMonth10) }}
					{{ Form::hidden('coffee_machine_rental_month_11', $coffeeMachineRentalMonth11) }}
					{{ Form::hidden('coffee_machine_rental_month_12', $coffeeMachineRentalMonth12) }}

					{{ Form::hidden('other_rental_totals', $otherRentalTotals) }}
					{{ Form::hidden('other_rental_month_1', $otherRentalMonth1) }}
					{{ Form::hidden('other_rental_month_2', $otherRentalMonth2) }}
					{{ Form::hidden('other_rental_month_3', $otherRentalMonth3) }}
					{{ Form::hidden('other_rental_month_4', $otherRentalMonth4) }}
					{{ Form::hidden('other_rental_month_5', $otherRentalMonth5) }}
					{{ Form::hidden('other_rental_month_6', $otherRentalMonth6) }}
					{{ Form::hidden('other_rental_month_7', $otherRentalMonth7) }}
					{{ Form::hidden('other_rental_month_8', $otherRentalMonth8) }}
					{{ Form::hidden('other_rental_month_9', $otherRentalMonth9) }}
					{{ Form::hidden('other_rental_month_10', $otherRentalMonth10) }}
					{{ Form::hidden('other_rental_month_11', $otherRentalMonth11) }}
					{{ Form::hidden('other_rental_month_12', $otherRentalMonth12) }}

					{{ Form::hidden('it_support_totals', $itSupportTotals) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth1) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth2) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth3) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth4) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth5) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth6) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth7) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth8) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth9) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth10) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth11) }}
					{{ Form::hidden('it_support_month_1', $itSupportMonth12) }}

					{{ Form::hidden('free_issues_totals', $freeIssuesTotals) }}
					{{ Form::hidden('free_issues_month_1', $freeIssuesMonth1) }}
					{{ Form::hidden('free_issues_month_2', $freeIssuesMonth2) }}
					{{ Form::hidden('free_issues_month_3', $freeIssuesMonth3) }}
					{{ Form::hidden('free_issues_month_4', $freeIssuesMonth4) }}
					{{ Form::hidden('free_issues_month_5', $freeIssuesMonth5) }}
					{{ Form::hidden('free_issues_month_6', $freeIssuesMonth6) }}
					{{ Form::hidden('free_issues_month_7', $freeIssuesMonth7) }}
					{{ Form::hidden('free_issues_month_8', $freeIssuesMonth8) }}
					{{ Form::hidden('free_issues_month_9', $freeIssuesMonth9) }}
					{{ Form::hidden('free_issues_month_10', $freeIssuesMonth10) }}
					{{ Form::hidden('free_issues_month_11', $freeIssuesMonth11) }}
					{{ Form::hidden('free_issues_month_12', $freeIssuesMonth12) }}

					{{ Form::hidden('marketing_totals', $marketingTotals) }}
					{{ Form::hidden('marketing_month_1', $marketingMonth1) }}
					{{ Form::hidden('marketing_month_2', $marketingMonth2) }}
					{{ Form::hidden('marketing_month_3', $marketingMonth3) }}
					{{ Form::hidden('marketing_month_4', $marketingMonth4) }}
					{{ Form::hidden('marketing_month_5', $marketingMonth5) }}
					{{ Form::hidden('marketing_month_6', $marketingMonth6) }}
					{{ Form::hidden('marketing_month_7', $marketingMonth7) }}
					{{ Form::hidden('marketing_month_8', $marketingMonth8) }}
					{{ Form::hidden('marketing_month_9', $marketingMonth9) }}
					{{ Form::hidden('marketing_month_10', $marketingMonth10) }}
					{{ Form::hidden('marketing_month_11', $marketingMonth11) }}
					{{ Form::hidden('marketing_month_12', $marketingMonth12) }}

					{{ Form::hidden('set_up_costs_totals', $setUpCostsTotals) }}
					{{ Form::hidden('set_up_costs_month_1', $setUpCostsMonth1) }}
					{{ Form::hidden('set_up_costs_month_2', $setUpCostsMonth2) }}
					{{ Form::hidden('set_up_costs_month_3', $setUpCostsMonth3) }}
					{{ Form::hidden('set_up_costs_month_4', $setUpCostsMonth4) }}
					{{ Form::hidden('set_up_costs_month_5', $setUpCostsMonth5) }}
					{{ Form::hidden('set_up_costs_month_6', $setUpCostsMonth6) }}
					{{ Form::hidden('set_up_costs_month_7', $setUpCostsMonth7) }}
					{{ Form::hidden('set_up_costs_month_8', $setUpCostsMonth8) }}
					{{ Form::hidden('set_up_costs_month_9', $setUpCostsMonth9) }}
					{{ Form::hidden('set_up_costs_month_10', $setUpCostsMonth10) }}
					{{ Form::hidden('set_up_costs_month_11', $setUpCostsMonth11) }}
					{{ Form::hidden('set_up_costs_month_12', $setUpCostsMonth12) }}

					{{ Form::hidden('credit_card_machines_totals', $creditCardMachinesTotals) }}
					{{ Form::hidden('credit_card_machines_month_1', $creditCardMachinesMonth1) }}
					{{ Form::hidden('credit_card_machines_month_2', $creditCardMachinesMonth2) }}
					{{ Form::hidden('credit_card_machines_month_3', $creditCardMachinesMonth3) }}
					{{ Form::hidden('credit_card_machines_month_4', $creditCardMachinesMonth4) }}
					{{ Form::hidden('credit_card_machines_month_5', $creditCardMachinesMonth5) }}
					{{ Form::hidden('credit_card_machines_month_6', $creditCardMachinesMonth6) }}
					{{ Form::hidden('credit_card_machines_month_7', $creditCardMachinesMonth7) }}
					{{ Form::hidden('credit_card_machines_month_8', $creditCardMachinesMonth8) }}
					{{ Form::hidden('credit_card_machines_month_9', $creditCardMachinesMonth9) }}
					{{ Form::hidden('credit_card_machines_month_10', $creditCardMachinesMonth10) }}
					{{ Form::hidden('credit_card_machines_month_11', $creditCardMachinesMonth11) }}
					{{ Form::hidden('credit_card_machines_month_12', $creditCardMachinesMonth12) }}

					{{ Form::hidden('bizimply_cost_totals', $bizimplyCostTotals) }}
					{{ Form::hidden('bizimply_cost_month_1', $bizimplyCostMonth1) }}
					{{ Form::hidden('bizimply_cost_month_2', $bizimplyCostMonth2) }}
					{{ Form::hidden('bizimply_cost_month_3', $bizimplyCostMonth3) }}
					{{ Form::hidden('bizimply_cost_month_4', $bizimplyCostMonth4) }}
					{{ Form::hidden('bizimply_cost_month_5', $bizimplyCostMonth5) }}
					{{ Form::hidden('bizimply_cost_month_6', $bizimplyCostMonth6) }}
					{{ Form::hidden('bizimply_cost_month_7', $bizimplyCostMonth7) }}
					{{ Form::hidden('bizimply_cost_month_8', $bizimplyCostMonth8) }}
					{{ Form::hidden('bizimply_cost_month_9', $bizimplyCostMonth9) }}
					{{ Form::hidden('bizimply_cost_month_10', $bizimplyCostMonth10) }}
					{{ Form::hidden('bizimply_cost_month_11', $bizimplyCostMonth11) }}
					{{ Form::hidden('bizimply_cost_month_12', $bizimplyCostMonth12) }}

					{{ Form::hidden('kitchtech_totals', $kitchtechTotals) }}
					{{ Form::hidden('kitchtech_month_1', $kitchtechMonth1) }}
					{{ Form::hidden('kitchtech_month_2', $kitchtechMonth2) }}
					{{ Form::hidden('kitchtech_month_3', $kitchtechMonth3) }}
					{{ Form::hidden('kitchtech_month_4', $kitchtechMonth4) }}
					{{ Form::hidden('kitchtech_month_5', $kitchtechMonth5) }}
					{{ Form::hidden('kitchtech_month_6', $kitchtechMonth6) }}
					{{ Form::hidden('kitchtech_month_7', $kitchtechMonth7) }}
					{{ Form::hidden('kitchtech_month_8', $kitchtechMonth8) }}
					{{ Form::hidden('kitchtech_month_9', $kitchtechMonth9) }}
					{{ Form::hidden('kitchtech_month_10', $kitchtechMonth10) }}
					{{ Form::hidden('kitchtech_month_11', $kitchtechMonth11) }}
					{{ Form::hidden('kitchtech_month_12', $kitchtechMonth12) }}

					{{ Form::hidden('entered_by', $enteredBy) }}
					{{ Form::hidden('approved_by', $approvedBy) }}

					<h5>Are you sure you want to confirm this Phased Budget?</h5>
					<input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm Phased Budget'/>
				</div>
			</div>
			{!!Form::close()!!}

			{!! Form::open(['url' => 'sheets/phased-budget', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
				{{ Form::hidden('return_from', 'confirm') }}
				{{ Form::hidden('unit_id', $unitId) }}
				{{ Form::hidden('unit_name', $unitName) }}

				{{ Form::hidden('budget_start_date', $budgetStartDate) }}
				{{ Form::hidden('budget_end_date', $budgetEndDate) }}
				{{ Form::hidden('entered_by', $enteredBy) }}
				{{ Form::hidden('approved_by', $approvedBy) }}
				{{ Form::hidden('budget_year', $budgetYear) }}
				{{ Form::hidden('contract_type', $contractType) }}
				{{ Form::hidden('budget_type', $budgetType) }}

				{{ Form::hidden('head_count_month_1', $headCountMonth1) }}
				{{ Form::hidden('head_count_month_1', $headCountMonth1) }}
				{{ Form::hidden('head_count_month_2', $headCountMonth2) }}
				{{ Form::hidden('head_count_month_3', $headCountMonth3) }}
				{{ Form::hidden('head_count_month_4', $headCountMonth4) }}
				{{ Form::hidden('head_count_month_5', $headCountMonth5) }}
				{{ Form::hidden('head_count_month_6', $headCountMonth6) }}
				{{ Form::hidden('head_count_month_7', $headCountMonth7) }}
				{{ Form::hidden('head_count_month_8', $headCountMonth8) }}
				{{ Form::hidden('head_count_month_9', $headCountMonth9) }}
				{{ Form::hidden('head_count_month_10', $headCountMonth10) }}
				{{ Form::hidden('head_count_month_11', $headCountMonth11) }}
				{{ Form::hidden('head_count_month_12', $headCountMonth12) }}

				{{ Form::hidden('month_1_header', $month1Header) }}
				{{ Form::hidden('month_2_header', $month2Header) }}
				{{ Form::hidden('month_3_header', $month3Header) }}
				{{ Form::hidden('month_4_header', $month4Header) }}
				{{ Form::hidden('month_5_header', $month5Header) }}
				{{ Form::hidden('month_6_header', $month6Header) }}
				{{ Form::hidden('month_7_header', $month7Header) }}
				{{ Form::hidden('month_8_header', $month8Header) }}
				{{ Form::hidden('month_9_header', $month9Header) }}
				{{ Form::hidden('month_10_header', $month10Header) }}
				{{ Form::hidden('month_11_header', $month11Header) }}
				{{ Form::hidden('month_12_header', $month12Header) }}

				{{ Form::hidden('num_trading_days_totals', $numTradingDaysTotals) }}
				{{ Form::hidden('num_trading_days_month_1', $numTradingDaysMonth1) }}
				{{ Form::hidden('num_trading_days_month_2', $numTradingDaysMonth2) }}
				{{ Form::hidden('num_trading_days_month_3', $numTradingDaysMonth3) }}
				{{ Form::hidden('num_trading_days_month_4', $numTradingDaysMonth4) }}
				{{ Form::hidden('num_trading_days_month_5', $numTradingDaysMonth5) }}
				{{ Form::hidden('num_trading_days_month_6', $numTradingDaysMonth6) }}
				{{ Form::hidden('num_trading_days_month_7', $numTradingDaysMonth7) }}
				{{ Form::hidden('num_trading_days_month_8', $numTradingDaysMonth8) }}
				{{ Form::hidden('num_trading_days_month_9', $numTradingDaysMonth9) }}
				{{ Form::hidden('num_trading_days_month_10', $numTradingDaysMonth10) }}
				{{ Form::hidden('num_trading_days_month_11', $numTradingDaysMonth11) }}
				{{ Form::hidden('num_trading_days_month_12', $numTradingDaysMonth12) }}

				{{ Form::hidden('num_of_weeks_totals', $numOfWeeksTotals) }}
				{{ Form::hidden('num_of_weeks_month_1', $numOfWeeksMonth1) }}
				{{ Form::hidden('num_of_weeks_month_2', $numOfWeeksMonth2) }}
				{{ Form::hidden('num_of_weeks_month_3', $numOfWeeksMonth3) }}
				{{ Form::hidden('num_of_weeks_month_4', $numOfWeeksMonth4) }}
				{{ Form::hidden('num_of_weeks_month_5', $numOfWeeksMonth5) }}
				{{ Form::hidden('num_of_weeks_month_6', $numOfWeeksMonth6) }}
				{{ Form::hidden('num_of_weeks_month_7', $numOfWeeksMonth7) }}
				{{ Form::hidden('num_of_weeks_month_8', $numOfWeeksMonth8) }}
				{{ Form::hidden('num_of_weeks_month_9', $numOfWeeksMonth9) }}
				{{ Form::hidden('num_of_weeks_month_10', $numOfWeeksMonth10) }}
				{{ Form::hidden('num_of_weeks_month_11', $numOfWeeksMonth11) }}
				{{ Form::hidden('num_of_weeks_month_12', $numOfWeeksMonth12) }}

				{{ Form::hidden('gross_sales_totals', $grossSalesTotals) }}
				{{ Form::hidden('gross_sales_month_1', $grossSalesMonth1) }}
				{{ Form::hidden('gross_sales_month_2', $grossSalesMonth2) }}
				{{ Form::hidden('gross_sales_month_3', $grossSalesMonth3) }}
				{{ Form::hidden('gross_sales_month_4', $grossSalesMonth4) }}
				{{ Form::hidden('gross_sales_month_5', $grossSalesMonth5) }}
				{{ Form::hidden('gross_sales_month_6', $grossSalesMonth6) }}
				{{ Form::hidden('gross_sales_month_7', $grossSalesMonth7) }}
				{{ Form::hidden('gross_sales_month_8', $grossSalesMonth8) }}
				{{ Form::hidden('gross_sales_month_9', $grossSalesMonth9) }}
				{{ Form::hidden('gross_sales_month_10', $grossSalesMonth10) }}
				{{ Form::hidden('gross_sales_month_11', $grossSalesMonth11) }}
				{{ Form::hidden('gross_sales_month_12', $grossSalesMonth12) }}

				{{ Form::hidden('vat_totals', $vatTotals) }}
				{{ Form::hidden('vat_month_1', $vatMonth1) }}
				{{ Form::hidden('vat_month_2', $vatMonth2) }}
				{{ Form::hidden('vat_month_3', $vatMonth3) }}
				{{ Form::hidden('vat_month_4', $vatMonth4) }}
				{{ Form::hidden('vat_month_5', $vatMonth5) }}
				{{ Form::hidden('vat_month_6', $vatMonth6) }}
				{{ Form::hidden('vat_month_7', $vatMonth7) }}
				{{ Form::hidden('vat_month_8', $vatMonth8) }}
				{{ Form::hidden('vat_month_9', $vatMonth9) }}
				{{ Form::hidden('vat_month_10', $vatMonth10) }}
				{{ Form::hidden('vat_month_11', $vatMonth11) }}
				{{ Form::hidden('vat_month_12', $vatMonth12) }}

				{{ Form::hidden('net_sales_totals', $netSalesTotals) }}
				{{ Form::hidden('net_sales_month_1', $netSalesMonth1) }}
				{{ Form::hidden('net_sales_month_2', $netSalesMonth2) }}
				{{ Form::hidden('net_sales_month_3', $netSalesMonth3) }}
				{{ Form::hidden('net_sales_month_4', $netSalesMonth4) }}
				{{ Form::hidden('net_sales_month_5', $netSalesMonth5) }}
				{{ Form::hidden('net_sales_month_6', $netSalesMonth6) }}
				{{ Form::hidden('net_sales_month_7', $netSalesMonth7) }}
				{{ Form::hidden('net_sales_month_8', $netSalesMonth8) }}
				{{ Form::hidden('net_sales_month_9', $netSalesMonth9) }}
				{{ Form::hidden('net_sales_month_10', $netSalesMonth10) }}
				{{ Form::hidden('net_sales_month_11', $netSalesMonth11) }}
				{{ Form::hidden('net_sales_month_12', $netSalesMonth12) }}

				{{ Form::hidden('cost_of_sales_totals', $costOfSalesTotals) }}
				{{ Form::hidden('cost_of_sales_month_1', $costOfSalesMonth1) }}
				{{ Form::hidden('cost_of_sales_month_2', $costOfSalesMonth2) }}
				{{ Form::hidden('cost_of_sales_month_3', $costOfSalesMonth3) }}
				{{ Form::hidden('cost_of_sales_month_4', $costOfSalesMonth4) }}
				{{ Form::hidden('cost_of_sales_month_5', $costOfSalesMonth5) }}
				{{ Form::hidden('cost_of_sales_month_6', $costOfSalesMonth6) }}
				{{ Form::hidden('cost_of_sales_month_7', $costOfSalesMonth7) }}
				{{ Form::hidden('cost_of_sales_month_8', $costOfSalesMonth8) }}
				{{ Form::hidden('cost_of_sales_month_9', $costOfSalesMonth9) }}
				{{ Form::hidden('cost_of_sales_month_10', $costOfSalesMonth10) }}
				{{ Form::hidden('cost_of_sales_month_11', $costOfSalesMonth11) }}
				{{ Form::hidden('cost_of_sales_month_12', $costOfSalesMonth12) }}

				{{ Form::hidden('gross_profit_totals', $grossProfitTotals) }}
				{{ Form::hidden('gross_profit_month_1', $grossProfitMonth1) }}
				{{ Form::hidden('gross_profit_month_2', $grossProfitMonth2) }}
				{{ Form::hidden('gross_profit_month_3', $grossProfitMonth3) }}
				{{ Form::hidden('gross_profit_month_4', $grossProfitMonth4) }}
				{{ Form::hidden('gross_profit_month_5', $grossProfitMonth5) }}
				{{ Form::hidden('gross_profit_month_6', $grossProfitMonth6) }}
				{{ Form::hidden('gross_profit_month_7', $grossProfitMonth7) }}
				{{ Form::hidden('gross_profit_month_8', $grossProfitMonth8) }}
				{{ Form::hidden('gross_profit_month_9', $grossProfitMonth9) }}
				{{ Form::hidden('gross_profit_month_10', $grossProfitMonth10) }}
				{{ Form::hidden('gross_profit_month_11', $grossProfitMonth11) }}
				{{ Form::hidden('gross_profit_month_12', $grossProfitMonth12) }}

				{{ Form::hidden('gross_profit_net_totals', $grossProfitNetTotals) }}
				{{ Form::hidden('gross_profit_net_month_1', $grossProfitNetMonth1) }}
				{{ Form::hidden('gross_profit_net_month_2', $grossProfitNetMonth2) }}
				{{ Form::hidden('gross_profit_net_month_3', $grossProfitNetMonth3) }}
				{{ Form::hidden('gross_profit_net_month_4', $grossProfitNetMonth4) }}
				{{ Form::hidden('gross_profit_net_month_5', $grossProfitNetMonth5) }}
				{{ Form::hidden('gross_profit_net_month_6', $grossProfitNetMonth6) }}
				{{ Form::hidden('gross_profit_net_month_7', $grossProfitNetMonth7) }}
				{{ Form::hidden('gross_profit_net_month_8', $grossProfitNetMonth8) }}
				{{ Form::hidden('gross_profit_net_month_9', $grossProfitNetMonth9) }}
				{{ Form::hidden('gross_profit_net_month_10', $grossProfitNetMonth10) }}
				{{ Form::hidden('gross_profit_net_month_11', $grossProfitNetMonth11) }}
				{{ Form::hidden('gross_profit_net_month_12', $grossProfitNetMonth12) }}

				{{ Form::hidden('gpp_on_gross_sales_totals', $gppOnGrossSalesTotals) }}
				{{ Form::hidden('gpp_on_gross_sales_month_1', $gppOnGrossSalesMonth1) }}
				{{ Form::hidden('gpp_on_gross_sales_month_2', $gppOnGrossSalesMonth2) }}
				{{ Form::hidden('gpp_on_gross_sales_month_3', $gppOnGrossSalesMonth3) }}
				{{ Form::hidden('gpp_on_gross_sales_month_4', $gppOnGrossSalesMonth4) }}
				{{ Form::hidden('gpp_on_gross_sales_month_5', $gppOnGrossSalesMonth5) }}
				{{ Form::hidden('gpp_on_gross_sales_month_6', $gppOnGrossSalesMonth6) }}
				{{ Form::hidden('gpp_on_gross_sales_month_7', $gppOnGrossSalesMonth7) }}
				{{ Form::hidden('gpp_on_gross_sales_month_8', $gppOnGrossSalesMonth8) }}
				{{ Form::hidden('gpp_on_gross_sales_month_9', $gppOnGrossSalesMonth9) }}
				{{ Form::hidden('gpp_on_gross_sales_month_10', $gppOnGrossSalesMonth10) }}
				{{ Form::hidden('gpp_on_gross_sales_month_11', $gppOnGrossSalesMonth11) }}
				{{ Form::hidden('gpp_on_gross_sales_month_12', $gppOnGrossSalesMonth12) }}

				{{ Form::hidden('gpp_on_net_sales_totals', $gppOnNetSalesTotals) }}
				{{ Form::hidden('gpp_on_net_sales_month_1', $gppOnNetSalesMonth1) }}
				{{ Form::hidden('gpp_on_net_sales_month_2', $gppOnNetSalesMonth2) }}
				{{ Form::hidden('gpp_on_net_sales_month_3', $gppOnNetSalesMonth3) }}
				{{ Form::hidden('gpp_on_net_sales_month_4', $gppOnNetSalesMonth4) }}
				{{ Form::hidden('gpp_on_net_sales_month_5', $gppOnNetSalesMonth5) }}
				{{ Form::hidden('gpp_on_net_sales_month_6', $gppOnNetSalesMonth6) }}
				{{ Form::hidden('gpp_on_net_sales_month_7', $gppOnNetSalesMonth7) }}
				{{ Form::hidden('gpp_on_net_sales_month_8', $gppOnNetSalesMonth8) }}
				{{ Form::hidden('gpp_on_net_sales_month_9', $gppOnNetSalesMonth9) }}
				{{ Form::hidden('gpp_on_net_sales_month_10', $gppOnNetSalesMonth10) }}
				{{ Form::hidden('gpp_on_net_sales_month_11', $gppOnNetSalesMonth11) }}
				{{ Form::hidden('gpp_on_net_sales_month_12', $gppOnNetSalesMonth12) }}

				{{ Form::hidden('labour_hours_totals', $labourHoursTotals) }}
				{{ Form::hidden('labour_hours_month_1', $labourHoursMonth1) }}
				{{ Form::hidden('labour_hours_month_2', $labourHoursMonth2) }}
				{{ Form::hidden('labour_hours_month_3', $labourHoursMonth3) }}
				{{ Form::hidden('labour_hours_month_4', $labourHoursMonth4) }}
				{{ Form::hidden('labour_hours_month_5', $labourHoursMonth5) }}
				{{ Form::hidden('labour_hours_month_6', $labourHoursMonth6) }}
				{{ Form::hidden('labour_hours_month_7', $labourHoursMonth7) }}
				{{ Form::hidden('labour_hours_month_8', $labourHoursMonth8) }}
				{{ Form::hidden('labour_hours_month_9', $labourHoursMonth9) }}
				{{ Form::hidden('labour_hours_month_10', $labourHoursMonth10) }}
				{{ Form::hidden('labour_hours_month_11', $labourHoursMonth11) }}
				{{ Form::hidden('labour_hours_month_12', $labourHoursMonth12) }}

				{{ Form::hidden('labour_totals', $labourTotals) }}
				{{ Form::hidden('labour_month_1', $labourMonth1) }}
				{{ Form::hidden('labour_month_2', $labourMonth2) }}
				{{ Form::hidden('labour_month_3', $labourMonth3) }}
				{{ Form::hidden('labour_month_4', $labourMonth4) }}
				{{ Form::hidden('labour_month_5', $labourMonth5) }}
				{{ Form::hidden('labour_month_6', $labourMonth6) }}
				{{ Form::hidden('labour_month_7', $labourMonth7) }}
				{{ Form::hidden('labour_month_8', $labourMonth8) }}
				{{ Form::hidden('labour_month_9', $labourMonth9) }}
				{{ Form::hidden('labour_month_10', $labourMonth10) }}
				{{ Form::hidden('labour_month_11', $labourMonth11) }}
				{{ Form::hidden('labour_month_12', $labourMonth12) }}

				{{ Form::hidden('training_totals', $trainingTotals) }}
				{{ Form::hidden('training_month_1', $trainingMonth1) }}
				{{ Form::hidden('training_month_2', $trainingMonth2) }}
				{{ Form::hidden('training_month_3', $trainingMonth3) }}
				{{ Form::hidden('training_month_4', $trainingMonth4) }}
				{{ Form::hidden('training_month_5', $trainingMonth5) }}
				{{ Form::hidden('training_month_6', $trainingMonth6) }}
				{{ Form::hidden('training_month_7', $trainingMonth7) }}
				{{ Form::hidden('training_month_8', $trainingMonth8) }}
				{{ Form::hidden('training_month_9', $trainingMonth9) }}
				{{ Form::hidden('training_month_10', $trainingMonth10) }}
				{{ Form::hidden('training_month_11', $trainingMonth11) }}
				{{ Form::hidden('training_month_12', $trainingMonth12) }}

				{{ Form::hidden('cleaning_totals', $cleaningTotals) }}
				{{ Form::hidden('cleaning_month_1', $cleaningMonth1) }}
				{{ Form::hidden('cleaning_month_2', $cleaningMonth2) }}
				{{ Form::hidden('cleaning_month_3', $cleaningMonth3) }}
				{{ Form::hidden('cleaning_month_4', $cleaningMonth4) }}
				{{ Form::hidden('cleaning_month_5', $cleaningMonth5) }}
				{{ Form::hidden('cleaning_month_6', $cleaningMonth6) }}
				{{ Form::hidden('cleaning_month_7', $cleaningMonth7) }}
				{{ Form::hidden('cleaning_month_8', $cleaningMonth8) }}
				{{ Form::hidden('cleaning_month_9', $cleaningMonth9) }}
				{{ Form::hidden('cleaning_month_10', $cleaningMonth10) }}
				{{ Form::hidden('cleaning_month_11', $cleaningMonth11) }}
				{{ Form::hidden('cleaning_month_12', $cleaningMonth12) }}

				{{ Form::hidden('disposables_totals', $disposablesTotals) }}
				{{ Form::hidden('disposables_month_1', $disposablesMonth1) }}
				{{ Form::hidden('disposables_month_2', $disposablesMonth2) }}
				{{ Form::hidden('disposables_month_3', $disposablesMonth3) }}
				{{ Form::hidden('disposables_month_4', $disposablesMonth4) }}
				{{ Form::hidden('disposables_month_5', $disposablesMonth5) }}
				{{ Form::hidden('disposables_month_6', $disposablesMonth6) }}
				{{ Form::hidden('disposables_month_7', $disposablesMonth7) }}
				{{ Form::hidden('disposables_month_8', $disposablesMonth8) }}
				{{ Form::hidden('disposables_month_9', $disposablesMonth9) }}
				{{ Form::hidden('disposables_month_10', $disposablesMonth10) }}
				{{ Form::hidden('disposables_month_11', $disposablesMonth11) }}
				{{ Form::hidden('disposables_month_12', $disposablesMonth12) }}

				{{ Form::hidden('uniform_totals', $uniformTotals) }}
				{{ Form::hidden('uniform_month_1', $uniformMonth1) }}
				{{ Form::hidden('uniform_month_2', $uniformMonth2) }}
				{{ Form::hidden('uniform_month_3', $uniformMonth3) }}
				{{ Form::hidden('uniform_month_4', $uniformMonth4) }}
				{{ Form::hidden('uniform_month_5', $uniformMonth5) }}
				{{ Form::hidden('uniform_month_6', $uniformMonth6) }}
				{{ Form::hidden('uniform_month_7', $uniformMonth7) }}
				{{ Form::hidden('uniform_month_8', $uniformMonth8) }}
				{{ Form::hidden('uniform_month_9', $uniformMonth9) }}
				{{ Form::hidden('uniform_month_10', $uniformMonth10) }}
				{{ Form::hidden('uniform_month_11', $uniformMonth11) }}
				{{ Form::hidden('uniform_month_12', $uniformMonth12) }}

				{{ Form::hidden('delph_and_cutlery_totals', $delphAndCutleryTotals) }}
				{{ Form::hidden('delph_and_cutlery_month_1', $delphAndCutleryMonth1) }}
				{{ Form::hidden('delph_and_cutlery_month_2', $delphAndCutleryMonth2) }}
				{{ Form::hidden('delph_and_cutlery_month_3', $delphAndCutleryMonth3) }}
				{{ Form::hidden('delph_and_cutlery_month_4', $delphAndCutleryMonth4) }}
				{{ Form::hidden('delph_and_cutlery_month_5', $delphAndCutleryMonth5) }}
				{{ Form::hidden('delph_and_cutlery_month_6', $delphAndCutleryMonth6) }}
				{{ Form::hidden('delph_and_cutlery_month_7', $delphAndCutleryMonth7) }}
				{{ Form::hidden('delph_and_cutlery_month_8', $delphAndCutleryMonth8) }}
				{{ Form::hidden('delph_and_cutlery_month_9', $delphAndCutleryMonth9) }}
				{{ Form::hidden('delph_and_cutlery_month_10', $delphAndCutleryMonth10) }}
				{{ Form::hidden('delph_and_cutlery_month_11', $delphAndCutleryMonth11) }}
				{{ Form::hidden('delph_and_cutlery_month_12', $delphAndCutleryMonth12) }}

				{{ Form::hidden('bank_charges_totals', $bankChargesTotals) }}
				{{ Form::hidden('bank_charges_month_1', $bankChargesMonth1) }}
				{{ Form::hidden('bank_charges_month_2', $bankChargesMonth2) }}
				{{ Form::hidden('bank_charges_month_3', $bankChargesMonth3) }}
				{{ Form::hidden('bank_charges_month_4', $bankChargesMonth4) }}
				{{ Form::hidden('bank_charges_month_5', $bankChargesMonth5) }}
				{{ Form::hidden('bank_charges_month_6', $bankChargesMonth6) }}
				{{ Form::hidden('bank_charges_month_7', $bankChargesMonth7) }}
				{{ Form::hidden('bank_charges_month_8', $bankChargesMonth8) }}
				{{ Form::hidden('bank_charges_month_9', $bankChargesMonth9) }}
				{{ Form::hidden('bank_charges_month_10', $bankChargesMonth10) }}
				{{ Form::hidden('bank_charges_month_11', $bankChargesMonth11) }}
				{{ Form::hidden('bank_charges_month_12', $bankChargesMonth12) }}

				{{ Form::hidden('investment_totals', $investmentTotals) }}
				{{ Form::hidden('investment_month_1', $investmentMonth1) }}
				{{ Form::hidden('investment_month_2', $investmentMonth1) }}
				{{ Form::hidden('investment_month_3', $investmentMonth1) }}
				{{ Form::hidden('investment_month_4', $investmentMonth1) }}
				{{ Form::hidden('investment_month_5', $investmentMonth1) }}
				{{ Form::hidden('investment_month_6', $investmentMonth1) }}
				{{ Form::hidden('investment_month_7', $investmentMonth1) }}
				{{ Form::hidden('investment_month_8', $investmentMonth1) }}
				{{ Form::hidden('investment_month_9', $investmentMonth1) }}
				{{ Form::hidden('investment_month_10', $investmentMonth1) }}
				{{ Form::hidden('investment_month_11', $investmentMonth1) }}
				{{ Form::hidden('investment_month_12', $investmentMonth1) }}

				{{ Form::hidden('management_fee_totals', $managementFeeTotals) }}
				{{ Form::hidden('management_fee_month_1', $managementFeeMonth1) }}
				{{ Form::hidden('management_fee_month_2', $managementFeeMonth2) }}
				{{ Form::hidden('management_fee_month_3', $managementFeeMonth3) }}
				{{ Form::hidden('management_fee_month_4', $managementFeeMonth4) }}
				{{ Form::hidden('management_fee_month_5', $managementFeeMonth5) }}
				{{ Form::hidden('management_fee_month_6', $managementFeeMonth6) }}
				{{ Form::hidden('management_fee_month_7', $managementFeeMonth7) }}
				{{ Form::hidden('management_fee_month_8', $managementFeeMonth8) }}
				{{ Form::hidden('management_fee_month_9', $managementFeeMonth9) }}
				{{ Form::hidden('management_fee_month_10', $managementFeeMonth10) }}
				{{ Form::hidden('management_fee_month_11', $managementFeeMonth11) }}
				{{ Form::hidden('management_fee_month_12', $managementFeeMonth12) }}

				{{ Form::hidden('management_fee_totals', $managementFeeTotals) }}
				{{ Form::hidden('management_fee_month_1', $managementFeeMonth1) }}
				{{ Form::hidden('management_fee_month_2', $managementFeeMonth2) }}
				{{ Form::hidden('management_fee_month_3', $managementFeeMonth3) }}
				{{ Form::hidden('management_fee_month_4', $managementFeeMonth4) }}
				{{ Form::hidden('management_fee_month_5', $managementFeeMonth5) }}
				{{ Form::hidden('management_fee_month_6', $managementFeeMonth6) }}
				{{ Form::hidden('management_fee_month_7', $managementFeeMonth7) }}
				{{ Form::hidden('management_fee_month_8', $managementFeeMonth8) }}
				{{ Form::hidden('management_fee_month_9', $managementFeeMonth9) }}
				{{ Form::hidden('management_fee_month_10', $managementFeeMonth10) }}
				{{ Form::hidden('management_fee_month_11', $managementFeeMonth11) }}
				{{ Form::hidden('management_fee_month_12', $managementFeeMonth12) }}

				{{ Form::hidden('insurance_and_related_costs_totals', $insuranceAndRelatedCostsTotals) }}
				{{ Form::hidden('insurance_and_related_costs_month_1', $insuranceAndRelatedCostsMonth1) }}
				{{ Form::hidden('insurance_and_related_costs_month_2', $insuranceAndRelatedCostsMonth2) }}
				{{ Form::hidden('insurance_and_related_costs_month_3', $insuranceAndRelatedCostsMonth3) }}
				{{ Form::hidden('insurance_and_related_costs_month_4', $insuranceAndRelatedCostsMonth4) }}
				{{ Form::hidden('insurance_and_related_costs_month_5', $insuranceAndRelatedCostsMonth5) }}
				{{ Form::hidden('insurance_and_related_costs_month_6', $insuranceAndRelatedCostsMonth6) }}
				{{ Form::hidden('insurance_and_related_costs_month_7', $insuranceAndRelatedCostsMonth7) }}
				{{ Form::hidden('insurance_and_related_costs_month_8', $insuranceAndRelatedCostsMonth8) }}
				{{ Form::hidden('insurance_and_related_costs_month_9', $insuranceAndRelatedCostsMonth9) }}
				{{ Form::hidden('insurance_and_related_costs_month_10', $insuranceAndRelatedCostsMonth10) }}
				{{ Form::hidden('insurance_and_related_costs_month_11', $insuranceAndRelatedCostsMonth11) }}
				{{ Form::hidden('insurance_and_related_costs_month_12', $insuranceAndRelatedCostsMonth12) }}

				{{ Form::hidden('coffee_machine_rental_totals', $coffeeMachineRentalTotals) }}
				{{ Form::hidden('coffee_machine_rental_month_1', $coffeeMachineRentalMonth1) }}
				{{ Form::hidden('coffee_machine_rental_month_2', $coffeeMachineRentalMonth2) }}
				{{ Form::hidden('coffee_machine_rental_month_3', $coffeeMachineRentalMonth3) }}
				{{ Form::hidden('coffee_machine_rental_month_4', $coffeeMachineRentalMonth4) }}
				{{ Form::hidden('coffee_machine_rental_month_5', $coffeeMachineRentalMonth5) }}
				{{ Form::hidden('coffee_machine_rental_month_6', $coffeeMachineRentalMonth6) }}
				{{ Form::hidden('coffee_machine_rental_month_7', $coffeeMachineRentalMonth7) }}
				{{ Form::hidden('coffee_machine_rental_month_8', $coffeeMachineRentalMonth8) }}
				{{ Form::hidden('coffee_machine_rental_month_9', $coffeeMachineRentalMonth9) }}
				{{ Form::hidden('coffee_machine_rental_month_10', $coffeeMachineRentalMonth10) }}
				{{ Form::hidden('coffee_machine_rental_month_11', $coffeeMachineRentalMonth11) }}
				{{ Form::hidden('coffee_machine_rental_month_12', $coffeeMachineRentalMonth12) }}

				{{ Form::hidden('other_rental_totals', $otherRentalTotals) }}
				{{ Form::hidden('other_rental_month_1', $otherRentalMonth1) }}
				{{ Form::hidden('other_rental_month_2', $otherRentalMonth2) }}
				{{ Form::hidden('other_rental_month_3', $otherRentalMonth3) }}
				{{ Form::hidden('other_rental_month_4', $otherRentalMonth4) }}
				{{ Form::hidden('other_rental_month_5', $otherRentalMonth5) }}
				{{ Form::hidden('other_rental_month_6', $otherRentalMonth6) }}
				{{ Form::hidden('other_rental_month_7', $otherRentalMonth7) }}
				{{ Form::hidden('other_rental_month_8', $otherRentalMonth8) }}
				{{ Form::hidden('other_rental_month_9', $otherRentalMonth9) }}
				{{ Form::hidden('other_rental_month_10', $otherRentalMonth10) }}
				{{ Form::hidden('other_rental_month_11', $otherRentalMonth11) }}
				{{ Form::hidden('other_rental_month_12', $otherRentalMonth12) }}

				{{ Form::hidden('it_support_totals', $itSupportTotals) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth1) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth2) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth3) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth4) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth5) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth6) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth7) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth8) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth9) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth10) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth11) }}
				{{ Form::hidden('it_support_month_1', $itSupportMonth12) }}

				{{ Form::hidden('free_issues_totals', $freeIssuesTotals) }}
				{{ Form::hidden('free_issues_month_1', $freeIssuesMonth1) }}
				{{ Form::hidden('free_issues_month_2', $freeIssuesMonth2) }}
				{{ Form::hidden('free_issues_month_3', $freeIssuesMonth3) }}
				{{ Form::hidden('free_issues_month_4', $freeIssuesMonth4) }}
				{{ Form::hidden('free_issues_month_5', $freeIssuesMonth5) }}
				{{ Form::hidden('free_issues_month_6', $freeIssuesMonth6) }}
				{{ Form::hidden('free_issues_month_7', $freeIssuesMonth7) }}
				{{ Form::hidden('free_issues_month_8', $freeIssuesMonth8) }}
				{{ Form::hidden('free_issues_month_9', $freeIssuesMonth9) }}
				{{ Form::hidden('free_issues_month_10', $freeIssuesMonth10) }}
				{{ Form::hidden('free_issues_month_11', $freeIssuesMonth11) }}
				{{ Form::hidden('free_issues_month_12', $freeIssuesMonth12) }}

				{{ Form::hidden('marketing_totals', $marketingTotals) }}
				{{ Form::hidden('marketing_month_1', $marketingMonth1) }}
				{{ Form::hidden('marketing_month_2', $marketingMonth2) }}
				{{ Form::hidden('marketing_month_3', $marketingMonth3) }}
				{{ Form::hidden('marketing_month_4', $marketingMonth4) }}
				{{ Form::hidden('marketing_month_5', $marketingMonth5) }}
				{{ Form::hidden('marketing_month_6', $marketingMonth6) }}
				{{ Form::hidden('marketing_month_7', $marketingMonth7) }}
				{{ Form::hidden('marketing_month_8', $marketingMonth8) }}
				{{ Form::hidden('marketing_month_9', $marketingMonth9) }}
				{{ Form::hidden('marketing_month_10', $marketingMonth10) }}
				{{ Form::hidden('marketing_month_11', $marketingMonth11) }}
				{{ Form::hidden('marketing_month_12', $marketingMonth12) }}

				{{ Form::hidden('set_up_costs_totals', $setUpCostsTotals) }}
				{{ Form::hidden('set_up_costs_month_1', $setUpCostsMonth1) }}
				{{ Form::hidden('set_up_costs_month_2', $setUpCostsMonth2) }}
				{{ Form::hidden('set_up_costs_month_3', $setUpCostsMonth3) }}
				{{ Form::hidden('set_up_costs_month_4', $setUpCostsMonth4) }}
				{{ Form::hidden('set_up_costs_month_5', $setUpCostsMonth5) }}
				{{ Form::hidden('set_up_costs_month_6', $setUpCostsMonth6) }}
				{{ Form::hidden('set_up_costs_month_7', $setUpCostsMonth7) }}
				{{ Form::hidden('set_up_costs_month_8', $setUpCostsMonth8) }}
				{{ Form::hidden('set_up_costs_month_9', $setUpCostsMonth9) }}
				{{ Form::hidden('set_up_costs_month_10', $setUpCostsMonth10) }}
				{{ Form::hidden('set_up_costs_month_11', $setUpCostsMonth11) }}
				{{ Form::hidden('set_up_costs_month_12', $setUpCostsMonth12) }}

				{{ Form::hidden('credit_card_machines_totals', $creditCardMachinesTotals) }}
				{{ Form::hidden('credit_card_machines_month_1', $creditCardMachinesMonth1) }}
				{{ Form::hidden('credit_card_machines_month_2', $creditCardMachinesMonth2) }}
				{{ Form::hidden('credit_card_machines_month_3', $creditCardMachinesMonth3) }}
				{{ Form::hidden('credit_card_machines_month_4', $creditCardMachinesMonth4) }}
				{{ Form::hidden('credit_card_machines_month_5', $creditCardMachinesMonth5) }}
				{{ Form::hidden('credit_card_machines_month_6', $creditCardMachinesMonth6) }}
				{{ Form::hidden('credit_card_machines_month_7', $creditCardMachinesMonth7) }}
				{{ Form::hidden('credit_card_machines_month_8', $creditCardMachinesMonth8) }}
				{{ Form::hidden('credit_card_machines_month_9', $creditCardMachinesMonth9) }}
				{{ Form::hidden('credit_card_machines_month_10', $creditCardMachinesMonth10) }}
				{{ Form::hidden('credit_card_machines_month_11', $creditCardMachinesMonth11) }}
				{{ Form::hidden('credit_card_machines_month_12', $creditCardMachinesMonth12) }}

				{{ Form::hidden('bizimply_cost_totals', $bizimplyCostTotals) }}
				{{ Form::hidden('bizimply_cost_month_1', $bizimplyCostMonth1) }}
				{{ Form::hidden('bizimply_cost_month_2', $bizimplyCostMonth2) }}
				{{ Form::hidden('bizimply_cost_month_3', $bizimplyCostMonth3) }}
				{{ Form::hidden('bizimply_cost_month_4', $bizimplyCostMonth4) }}
				{{ Form::hidden('bizimply_cost_month_5', $bizimplyCostMonth5) }}
				{{ Form::hidden('bizimply_cost_month_6', $bizimplyCostMonth6) }}
				{{ Form::hidden('bizimply_cost_month_7', $bizimplyCostMonth7) }}
				{{ Form::hidden('bizimply_cost_month_8', $bizimplyCostMonth8) }}
				{{ Form::hidden('bizimply_cost_month_9', $bizimplyCostMonth9) }}
				{{ Form::hidden('bizimply_cost_month_10', $bizimplyCostMonth10) }}
				{{ Form::hidden('bizimply_cost_month_11', $bizimplyCostMonth11) }}
				{{ Form::hidden('bizimply_cost_month_12', $bizimplyCostMonth12) }}

				{{ Form::hidden('kitchtech_totals', $kitchtechTotals) }}
				{{ Form::hidden('kitchtech_month_1', $kitchtechMonth1) }}
				{{ Form::hidden('kitchtech_month_2', $kitchtechMonth2) }}
				{{ Form::hidden('kitchtech_month_3', $kitchtechMonth3) }}
				{{ Form::hidden('kitchtech_month_4', $kitchtechMonth4) }}
				{{ Form::hidden('kitchtech_month_5', $kitchtechMonth5) }}
				{{ Form::hidden('kitchtech_month_6', $kitchtechMonth6) }}
				{{ Form::hidden('kitchtech_month_7', $kitchtechMonth7) }}
				{{ Form::hidden('kitchtech_month_8', $kitchtechMonth8) }}
				{{ Form::hidden('kitchtech_month_9', $kitchtechMonth9) }}
				{{ Form::hidden('kitchtech_month_10', $kitchtechMonth10) }}
				{{ Form::hidden('kitchtech_month_11', $kitchtechMonth11) }}
				{{ Form::hidden('kitchtech_month_12', $kitchtechMonth12) }}
			{!!Form::close()!!}

			<p>
				<a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter phased budget
					information</a>
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