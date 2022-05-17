var budgetSubRows = [
	'labour',
	'training',
	'cleaning',
	'disposables',
	'uniform',
	'delph_and_cutlery',
	'bank_charges',
	'investment',
	'management_fee',
	'insurance_and_related_costs',
	'coffee_machine_rental',
	'other_rental',
	'it_support',
	'free_issues',
	'marketing',
	'set_up_costs',
	'credit_card_machines',
	'bizimply_cost',
	'kitchtech',
];

function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function getTomorrow(d,offset) {
    if (!offset){
        offset = 364;
    }
    if(typeof(d) === "string"){
        var t = d.split("-"); /* splits dd-mm-year */
        d = new Date(t[2],t[1] - 1, t[0]);
    //  d = new Date(t[2],t[1] - 1, t[0] + 2000); /* for dd-mm-yy */
    }
    return new Date(d.setDate(d.getDate() + offset));
}

/* # trading days [ Starts ] */
var month_1_num_trading_days = document.getElementById('num_trading_days_month_1');
var month_2_num_trading_days = document.getElementById('num_trading_days_month_2');
var month_3_num_trading_days = document.getElementById('num_trading_days_month_3');
var month_4_num_trading_days = document.getElementById('num_trading_days_month_4');
var month_5_num_trading_days = document.getElementById('num_trading_days_month_5');
var month_6_num_trading_days = document.getElementById('num_trading_days_month_6');
var month_7_num_trading_days = document.getElementById('num_trading_days_month_7');
var month_8_num_trading_days = document.getElementById('num_trading_days_month_8');
var month_9_num_trading_days = document.getElementById('num_trading_days_month_9');
var month_10_num_trading_days = document.getElementById('num_trading_days_month_10');
var month_11_num_trading_days = document.getElementById('num_trading_days_month_11');
var month_12_num_trading_days = document.getElementById('num_trading_days_month_12');

$('#num_trading_days_month_1, #num_trading_days_month_2, #num_trading_days_month_3, #num_trading_days_month_4, #num_trading_days_month_5, #num_trading_days_month_6, #num_trading_days_month_7, #num_trading_days_month_8, #num_trading_days_month_9, #num_trading_days_month_10, #num_trading_days_month_11, #num_trading_days_month_12').change(function() {
	var totals_num_trading_days = Number(month_1_num_trading_days.value) + Number(month_2_num_trading_days.value) + Number(month_3_num_trading_days.value) + Number(month_4_num_trading_days.value) + Number(month_5_num_trading_days.value) + Number(month_6_num_trading_days.value) + Number(month_7_num_trading_days.value) + Number(month_8_num_trading_days.value) + Number(month_9_num_trading_days.value) + Number(month_10_num_trading_days.value) + Number(month_11_num_trading_days.value) + Number(month_12_num_trading_days.value);
	num_trading_days_totals.value = totals_num_trading_days;
});
/* # trading days [ Ends ] */

/* # of weeks [ Starts ] */
var month_1_num_of_weeks = document.getElementById('num_of_weeks_month_1');
var month_2_num_of_weeks = document.getElementById('num_of_weeks_month_2');
var month_3_num_of_weeks = document.getElementById('num_of_weeks_month_3');
var month_4_num_of_weeks = document.getElementById('num_of_weeks_month_4');
var month_5_num_of_weeks = document.getElementById('num_of_weeks_month_5');
var month_6_num_of_weeks = document.getElementById('num_of_weeks_month_6');
var month_7_num_of_weeks = document.getElementById('num_of_weeks_month_7');
var month_8_num_of_weeks = document.getElementById('num_of_weeks_month_8');
var month_9_num_of_weeks = document.getElementById('num_of_weeks_month_9');
var month_10_num_of_weeks = document.getElementById('num_of_weeks_month_10');
var month_11_num_of_weeks = document.getElementById('num_of_weeks_month_11');
var month_12_num_of_weeks = document.getElementById('num_of_weeks_month_12');

$('#num_of_weeks_month_1, #num_of_weeks_month_2, #num_of_weeks_month_3, #num_of_weeks_month_4, #num_of_weeks_month_5, #num_of_weeks_month_6, #num_of_weeks_month_7, #num_of_weeks_month_8, #num_of_weeks_month_9, #num_of_weeks_month_10, #num_of_weeks_month_11, #num_of_weeks_month_12').change(function() {
	var totals_num_of_weeks = Number(month_1_num_of_weeks.value) + Number(month_2_num_of_weeks.value) + Number(month_3_num_of_weeks.value) + Number(month_4_num_of_weeks.value) + Number(month_5_num_of_weeks.value) + Number(month_6_num_of_weeks.value) + Number(month_7_num_of_weeks.value) + Number(month_8_num_of_weeks.value) + Number(month_9_num_of_weeks.value) + Number(month_10_num_of_weeks.value) + Number(month_11_num_of_weeks.value) + Number(month_12_num_of_weeks.value);
	num_of_weeks_totals.value = totals_num_of_weeks;
});
/* # of weeks [ Ends ] */

/* Gross Sales, VAT, Net Sales [ Starts ] */
var month_1_gross_sales = document.getElementById('gross_sales_month_1');
var month_2_gross_sales = document.getElementById('gross_sales_month_2');
var month_3_gross_sales = document.getElementById('gross_sales_month_3');
var month_4_gross_sales = document.getElementById('gross_sales_month_4');
var month_5_gross_sales = document.getElementById('gross_sales_month_5');
var month_6_gross_sales = document.getElementById('gross_sales_month_6');
var month_7_gross_sales = document.getElementById('gross_sales_month_7');
var month_8_gross_sales = document.getElementById('gross_sales_month_8');
var month_9_gross_sales = document.getElementById('gross_sales_month_9');
var month_10_gross_sales = document.getElementById('gross_sales_month_10');
var month_11_gross_sales = document.getElementById('gross_sales_month_11');
var month_12_gross_sales = document.getElementById('gross_sales_month_12');

var month_1_vat = document.getElementById('vat_month_1');
var month_2_vat = document.getElementById('vat_month_2');
var month_3_vat = document.getElementById('vat_month_3');
var month_4_vat = document.getElementById('vat_month_4');
var month_5_vat = document.getElementById('vat_month_5');
var month_6_vat = document.getElementById('vat_month_6');
var month_7_vat = document.getElementById('vat_month_7');
var month_8_vat = document.getElementById('vat_month_8');
var month_9_vat = document.getElementById('vat_month_9');
var month_10_vat = document.getElementById('vat_month_10');
var month_11_vat = document.getElementById('vat_month_11');
var month_12_vat = document.getElementById('vat_month_12');

var month_1_net_sales = document.getElementById('net_sales_month_1');
var month_2_net_sales = document.getElementById('net_sales_month_2');
var month_3_net_sales = document.getElementById('net_sales_month_3');
var month_4_net_sales = document.getElementById('net_sales_month_4');
var month_5_net_sales = document.getElementById('net_sales_month_5');
var month_6_net_sales = document.getElementById('net_sales_month_6');
var month_7_net_sales = document.getElementById('net_sales_month_7');
var month_8_net_sales = document.getElementById('net_sales_month_8');
var month_9_net_sales = document.getElementById('net_sales_month_9');
var month_10_net_sales = document.getElementById('net_sales_month_10');
var month_11_net_sales = document.getElementById('net_sales_month_11');
var month_12_net_sales = document.getElementById('net_sales_month_12');

var month_1_cost_of_sales = document.getElementById('cost_of_sales_month_1');
var month_2_cost_of_sales = document.getElementById('cost_of_sales_month_2');
var month_3_cost_of_sales = document.getElementById('cost_of_sales_month_3');
var month_4_cost_of_sales = document.getElementById('cost_of_sales_month_4');
var month_5_cost_of_sales = document.getElementById('cost_of_sales_month_5');
var month_6_cost_of_sales = document.getElementById('cost_of_sales_month_6');
var month_7_cost_of_sales = document.getElementById('cost_of_sales_month_7');
var month_8_cost_of_sales = document.getElementById('cost_of_sales_month_8');
var month_9_cost_of_sales = document.getElementById('cost_of_sales_month_9');
var month_10_cost_of_sales = document.getElementById('cost_of_sales_month_10');
var month_11_cost_of_sales = document.getElementById('cost_of_sales_month_11');
var month_12_cost_of_sales = document.getElementById('cost_of_sales_month_12');

var month_1_gross_profit = document.getElementById('gross_profit_month_1');
var month_2_gross_profit = document.getElementById('gross_profit_month_2');
var month_3_gross_profit = document.getElementById('gross_profit_month_3');
var month_4_gross_profit = document.getElementById('gross_profit_month_4');
var month_5_gross_profit = document.getElementById('gross_profit_month_5');
var month_6_gross_profit = document.getElementById('gross_profit_month_6');
var month_7_gross_profit = document.getElementById('gross_profit_month_7');
var month_8_gross_profit = document.getElementById('gross_profit_month_8');
var month_9_gross_profit = document.getElementById('gross_profit_month_9');
var month_10_gross_profit = document.getElementById('gross_profit_month_10');
var month_11_gross_profit = document.getElementById('gross_profit_month_11');
var month_12_gross_profit = document.getElementById('gross_profit_month_12');

$('#gross_sales_month_1, #gross_sales_month_2, #gross_sales_month_3, #gross_sales_month_4, #gross_sales_month_5, #gross_sales_month_6, #gross_sales_month_7, #gross_sales_month_8, #gross_sales_month_9, #gross_sales_month_10, #gross_sales_month_11, #gross_sales_month_12').change(function() {

	month_1_gross_sales.value = addCommas(month_1_gross_sales.value);
	month_2_gross_sales.value = addCommas(month_2_gross_sales.value);
	month_3_gross_sales.value = addCommas(month_3_gross_sales.value);
	month_4_gross_sales.value = addCommas(month_4_gross_sales.value);
	month_5_gross_sales.value = addCommas(month_5_gross_sales.value);
	month_6_gross_sales.value = addCommas(month_6_gross_sales.value);
	month_7_gross_sales.value = addCommas(month_7_gross_sales.value);
	month_8_gross_sales.value = addCommas(month_8_gross_sales.value);
	month_9_gross_sales.value = addCommas(month_9_gross_sales.value);
	month_10_gross_sales.value = addCommas(month_10_gross_sales.value);
	month_11_gross_sales.value = addCommas(month_11_gross_sales.value);
	month_12_gross_sales.value = addCommas(month_12_gross_sales.value);
	var totals_gross_sales = Number(month_1_gross_sales.value.replace(/,/g, "")) + Number(month_2_gross_sales.value.replace(/,/g, "")) + Number(month_3_gross_sales.value.replace(/,/g, "")) + Number(month_4_gross_sales.value.replace(/,/g, "")) + Number(month_5_gross_sales.value.replace(/,/g, "")) + Number(month_6_gross_sales.value.replace(/,/g, "")) + Number(month_7_gross_sales.value.replace(/,/g, "")) + Number(month_8_gross_sales.value.replace(/,/g, "")) + Number(month_9_gross_sales.value.replace(/,/g, "")) + Number(month_10_gross_sales.value.replace(/,/g, "")) + Number(month_11_gross_sales.value.replace(/,/g, "")) + Number(month_12_gross_sales.value.replace(/,/g, ""));
	gross_sales_totals.value = addCommas(totals_gross_sales);

	month_1_net_sales.value = addCommas(Number(month_1_gross_sales.value.replace(/,/g, "")) - Number(month_1_vat.value.replace(/,/g, "")));
	month_2_net_sales.value = addCommas(Number(month_2_gross_sales.value.replace(/,/g, "")) - Number(month_2_vat.value.replace(/,/g, "")));
	month_3_net_sales.value = addCommas(Number(month_3_gross_sales.value.replace(/,/g, "")) - Number(month_3_vat.value.replace(/,/g, "")));
	month_4_net_sales.value = addCommas(Number(month_4_gross_sales.value.replace(/,/g, "")) - Number(month_4_vat.value.replace(/,/g, "")));
	month_5_net_sales.value = addCommas(Number(month_5_gross_sales.value.replace(/,/g, "")) - Number(month_5_vat.value.replace(/,/g, "")));
	month_6_net_sales.value = addCommas(Number(month_6_gross_sales.value.replace(/,/g, "")) - Number(month_6_vat.value.replace(/,/g, "")));
	month_7_net_sales.value = addCommas(Number(month_7_gross_sales.value.replace(/,/g, "")) - Number(month_7_vat.value.replace(/,/g, "")));
	month_8_net_sales.value = addCommas(Number(month_8_gross_sales.value.replace(/,/g, "")) - Number(month_8_vat.value.replace(/,/g, "")));
	month_9_net_sales.value = addCommas(Number(month_9_gross_sales.value.replace(/,/g, "")) - Number(month_9_vat.value.replace(/,/g, "")));
	month_10_net_sales.value = addCommas(Number(month_10_gross_sales.value.replace(/,/g, "")) - Number(month_10_vat.value.replace(/,/g, "")));
	month_11_net_sales.value = addCommas(Number(month_11_gross_sales.value.replace(/,/g, "")) - Number(month_11_vat.value.replace(/,/g, "")));
	month_12_net_sales.value = addCommas(Number(month_12_gross_sales.value.replace(/,/g, "")) - Number(month_12_vat.value.replace(/,/g, "")));

	var totals_net_sales = Number(month_1_net_sales.value.replace(/,/g, "")) + Number(month_2_net_sales.value.replace(/,/g, "")) + Number(month_3_net_sales.value.replace(/,/g, "")) + Number(month_4_net_sales.value.replace(/,/g, "")) + Number(month_5_net_sales.value.replace(/,/g, "")) + Number(month_6_net_sales.value.replace(/,/g, "")) + Number(month_7_net_sales.value.replace(/,/g, "")) + Number(month_8_net_sales.value.replace(/,/g, "")) + Number(month_9_net_sales.value.replace(/,/g, "")) + Number(month_10_net_sales.value.replace(/,/g, "")) + Number(month_11_net_sales.value.replace(/,/g, "")) + Number(month_12_net_sales.value.replace(/,/g, ""));
	net_sales_totals.value = addCommas(totals_net_sales);

	month_1_gross_profit.value = addCommas(Number(month_1_gross_sales.value.replace(/,/g, "")) - Number(month_1_cost_of_sales.value.replace(/,/g, "")));
	month_2_gross_profit.value = addCommas(Number(month_2_gross_sales.value.replace(/,/g, "")) - Number(month_2_cost_of_sales.value.replace(/,/g, "")));
	month_3_gross_profit.value = addCommas(Number(month_3_gross_sales.value.replace(/,/g, "")) - Number(month_3_cost_of_sales.value.replace(/,/g, "")));
	month_4_gross_profit.value = addCommas(Number(month_4_gross_sales.value.replace(/,/g, "")) - Number(month_4_cost_of_sales.value.replace(/,/g, "")));
	month_5_gross_profit.value = addCommas(Number(month_5_gross_sales.value.replace(/,/g, "")) - Number(month_5_cost_of_sales.value.replace(/,/g, "")));
	month_6_gross_profit.value = addCommas(Number(month_6_gross_sales.value.replace(/,/g, "")) - Number(month_6_cost_of_sales.value.replace(/,/g, "")));
	month_7_gross_profit.value = addCommas(Number(month_7_gross_sales.value.replace(/,/g, "")) - Number(month_7_cost_of_sales.value.replace(/,/g, "")));
	month_8_gross_profit.value = addCommas(Number(month_8_gross_sales.value.replace(/,/g, "")) - Number(month_8_cost_of_sales.value.replace(/,/g, "")));
	month_9_gross_profit.value = addCommas(Number(month_9_gross_sales.value.replace(/,/g, "")) - Number(month_9_cost_of_sales.value.replace(/,/g, "")));
	month_10_gross_profit.value = addCommas(Number(month_10_gross_sales.value.replace(/,/g, "")) - Number(month_10_cost_of_sales.value.replace(/,/g, "")));
	month_11_gross_profit.value = addCommas(Number(month_11_gross_sales.value.replace(/,/g, "")) - Number(month_11_cost_of_sales.value.replace(/,/g, "")));
	month_12_gross_profit.value = addCommas(Number(month_12_gross_sales.value.replace(/,/g, "")) - Number(month_12_cost_of_sales.value.replace(/,/g, "")));

	var totals_gross_profit = document.getElementById('gross_profit_totals');
	totals_gross_profit.value = addCommas(Number(document.getElementById('gross_sales_totals').value.replace(/,/g, "")) - Number(document.getElementById('cost_of_sales_totals').value.replace(/,/g, "")));

	month_1_gross_profit_net.value = addCommas(Number(month_1_net_sales.value.replace(/,/g, "")) - Number(month_1_cost_of_sales.value.replace(/,/g, "")));
	month_2_gross_profit_net.value = addCommas(Number(month_2_net_sales.value.replace(/,/g, "")) - Number(month_2_cost_of_sales.value.replace(/,/g, "")));
	month_3_gross_profit_net.value = addCommas(Number(month_3_net_sales.value.replace(/,/g, "")) - Number(month_3_cost_of_sales.value.replace(/,/g, "")));
	month_4_gross_profit_net.value = addCommas(Number(month_4_net_sales.value.replace(/,/g, "")) - Number(month_4_cost_of_sales.value.replace(/,/g, "")));
	month_5_gross_profit_net.value = addCommas(Number(month_5_net_sales.value.replace(/,/g, "")) - Number(month_5_cost_of_sales.value.replace(/,/g, "")));
	month_6_gross_profit_net.value = addCommas(Number(month_6_net_sales.value.replace(/,/g, "")) - Number(month_6_cost_of_sales.value.replace(/,/g, "")));
	month_7_gross_profit_net.value = addCommas(Number(month_7_net_sales.value.replace(/,/g, "")) - Number(month_7_cost_of_sales.value.replace(/,/g, "")));
	month_8_gross_profit_net.value = addCommas(Number(month_8_net_sales.value.replace(/,/g, "")) - Number(month_8_cost_of_sales.value.replace(/,/g, "")));
	month_9_gross_profit_net.value = addCommas(Number(month_9_net_sales.value.replace(/,/g, "")) - Number(month_9_cost_of_sales.value.replace(/,/g, "")));
	month_10_gross_profit_net.value = addCommas(Number(month_10_net_sales.value.replace(/,/g, "")) - Number(month_10_cost_of_sales.value.replace(/,/g, "")));
	month_11_gross_profit_net.value = addCommas(Number(month_11_net_sales.value.replace(/,/g, "")) - Number(month_11_cost_of_sales.value.replace(/,/g, "")));
	month_12_gross_profit_net.value = addCommas(Number(month_12_net_sales.value.replace(/,/g, "")) - Number(month_12_cost_of_sales.value.replace(/,/g, "")));

	var totals_gross_profit_net = document.getElementById('gross_profit_net_totals');
	totals_gross_profit_net.value = addCommas(Number(document.getElementById('net_sales_totals').value.replace(/,/g, "")) - Number(document.getElementById('cost_of_sales_totals').value.replace(/,/g, "")));

	if(month_1_gross_profit.value && month_1_gross_sales.value)
		month_1_gpp_on_gross_sales.value = (Math.round((Number(month_1_gross_profit.value.replace(/,/g, "")) / Number(month_1_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_2_gross_profit.value && month_2_gross_sales.value)
		month_2_gpp_on_gross_sales.value = (Math.round((Number(month_2_gross_profit.value.replace(/,/g, "")) / Number(month_2_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_3_gross_profit.value && month_3_gross_sales.value)
		month_3_gpp_on_gross_sales.value = (Math.round((Number(month_3_gross_profit.value.replace(/,/g, "")) / Number(month_3_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_4_gross_profit.value && month_4_gross_sales.value)
		month_4_gpp_on_gross_sales.value = (Math.round((Number(month_4_gross_profit.value.replace(/,/g, "")) / Number(month_4_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_5_gross_profit.value && month_5_gross_sales.value)
		month_5_gpp_on_gross_sales.value = (Math.round((Number(month_5_gross_profit.value.replace(/,/g, "")) / Number(month_5_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_6_gross_profit.value && month_6_gross_sales.value)
		month_6_gpp_on_gross_sales.value = (Math.round((Number(month_6_gross_profit.value.replace(/,/g, "")) / Number(month_6_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_7_gross_profit.value && month_7_gross_sales.value)
		month_7_gpp_on_gross_sales.value = (Math.round((Number(month_7_gross_profit.value.replace(/,/g, "")) / Number(month_7_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_8_gross_profit.value && month_8_gross_sales.value)
		month_8_gpp_on_gross_sales.value = (Math.round((Number(month_8_gross_profit.value.replace(/,/g, "")) / Number(month_8_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_9_gross_profit.value && month_9_gross_sales.value)
		month_9_gpp_on_gross_sales.value = (Math.round((Number(month_9_gross_profit.value.replace(/,/g, "")) / Number(month_9_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_10_gross_profit.value && month_10_gross_sales.value)
		month_10_gpp_on_gross_sales.value = (Math.round((Number(month_10_gross_profit.value.replace(/,/g, "")) / Number(month_10_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_11_gross_profit.value && month_11_gross_sales.value)
		month_11_gpp_on_gross_sales.value = (Math.round((Number(month_11_gross_profit.value.replace(/,/g, "")) / Number(month_11_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_12_gross_profit.value && month_12_gross_sales.value)
		month_12_gpp_on_gross_sales.value = (Math.round((Number(month_12_gross_profit.value.replace(/,/g, "")) / Number(month_12_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	var totals_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_totals');
	totals_gpp_on_gross_sales.value = (Math.round(Number(document.getElementById('gross_profit_totals').value.replace(/,/g, "")) / Number(document.getElementById('gross_sales_totals').value.replace(/,/g, "")) * 100)) + '%';

	if(month_1_gross_profit_net.value != 0 && month_1_net_sales.value != 0)
		month_1_gpp_on_net_sales.value = (Math.round((Number(month_1_gross_profit_net.value.replace(/,/g, "")) / Number(month_1_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_2_gross_profit_net.value != 0 && month_2_net_sales.value != 0)
		month_2_gpp_on_net_sales.value = (Math.round((Number(month_2_gross_profit_net.value.replace(/,/g, "")) / Number(month_2_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_3_gross_profit_net.value != 0 && month_3_net_sales.value != 0)
		month_3_gpp_on_net_sales.value = (Math.round((Number(month_3_gross_profit_net.value.replace(/,/g, "")) / Number(month_3_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_4_gross_profit_net.value != 0 && month_4_net_sales.value != 0)
		month_4_gpp_on_net_sales.value = (Math.round((Number(month_4_gross_profit_net.value.replace(/,/g, "")) / Number(month_4_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_5_gross_profit_net.value != 0 && month_5_net_sales.value != 0)
		month_5_gpp_on_net_sales.value = (Math.round((Number(month_5_gross_profit_net.value.replace(/,/g, "")) / Number(month_5_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_6_gross_profit_net.value != 0 && month_6_net_sales.value != 0)
		month_6_gpp_on_net_sales.value = (Math.round((Number(month_6_gross_profit_net.value.replace(/,/g, "")) / Number(month_6_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_7_gross_profit_net.value != 0 && month_7_net_sales.value != 0)
		month_7_gpp_on_net_sales.value = (Math.round((Number(month_7_gross_profit_net.value.replace(/,/g, "")) / Number(month_7_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_8_gross_profit_net.value != 0 && month_8_net_sales.value != 0)
		month_8_gpp_on_net_sales.value = (Math.round((Number(month_8_gross_profit_net.value.replace(/,/g, "")) / Number(month_8_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_9_gross_profit_net.value != 0 && month_9_net_sales.value != 0)
		month_9_gpp_on_net_sales.value = (Math.round((Number(month_9_gross_profit_net.value.replace(/,/g, "")) / Number(month_9_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_10_gross_profit_net.value != 0 && month_10_net_sales.value != 0)
		month_10_gpp_on_net_sales.value = (Math.round((Number(month_10_gross_profit_net.value.replace(/,/g, "")) / Number(month_10_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_11_gross_profit_net.value != 0 && month_11_net_sales.value != 0)
		month_11_gpp_on_net_sales.value = (Math.round((Number(month_11_gross_profit_net.value.replace(/,/g, "")) / Number(month_11_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	if(month_12_gross_profit_net.value != 0 && month_12_net_sales.value != 0)
		month_12_gpp_on_net_sales.value = (Math.round((Number(month_12_gross_profit_net.value.replace(/,/g, "")) / Number(month_12_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	var totals_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_totals');
	totals_gpp_on_net_sales.value = (Math.round(Number(document.getElementById('gross_profit_net_totals').value.replace(/,/g, "")) / Number(document.getElementById('net_sales_totals').value.replace(/,/g, "")) * 100)) + '%';

/* The Overall cost to field will be the sum of (Gross Sales... Misc 2)- Contributions from */
/*var contribution_1_val = Number(document.getElementById('contribution_1').value);
var contribution_2_val = Number(document.getElementById('contribution_2').value);
var contribution_3_val = Number(document.getElementById('contribution_3').value);
var contribution_4_val = Number(document.getElementById('contribution_4').value);
var contribution_5_val = Number(document.getElementById('contribution_5').value);
var contribution_total = contribution_1_val + contribution_2_val + contribution_3_val + contribution_4_val + contribution_5_val;

overall_cost_to_total.value = (Math.round(((Number(gross_sales_totals_val.value) + Number(vat_totals_val.value) + Number(net_sales_totals_val.value) + Number(cost_of_sales_totals_val.value) + Number(gross_profit_totals_val.value) + Number(gross_profit_net_totals_val.value) + Number(gpp_on_gross_sales_totals_val.value) + Number(gpp_on_net_sales_totals_val.value) + Number(free_issues_totals_val.value) + Number(labour_and_allocated_charges_totals_val.value) + Number(uniform_totals_val.value) + Number(chemicals_cleaning_totals_val.value) + Number(disposables_totals_val.value) + Number(delph_and_cutlery_totals_val.value) + Number(misc_1_totals_val.value) + Number(misc_2_totals_val.value)) - contribution_total) * 100) / 100).toFixed(2);*/
});

$('#vat_month_1, #vat_month_2, #vat_month_3, #vat_month_4, #vat_month_5, #vat_month_6, #vat_month_7, #vat_month_8, #vat_month_9, #vat_month_10, #vat_month_11, #vat_month_12').change(function() {

	month_1_vat.value = addCommas(month_1_vat.value);
	month_2_vat.value = addCommas(month_2_vat.value);
	month_3_vat.value = addCommas(month_3_vat.value);
	month_4_vat.value = addCommas(month_4_vat.value);
	month_5_vat.value = addCommas(month_5_vat.value);
	month_6_vat.value = addCommas(month_6_vat.value);
	month_7_vat.value = addCommas(month_7_vat.value);
	month_8_vat.value = addCommas(month_8_vat.value);
	month_9_vat.value = addCommas(month_9_vat.value);
	month_10_vat.value = addCommas(month_10_vat.value);
	month_11_vat.value = addCommas(month_11_vat.value);
	month_12_vat.value = addCommas(month_12_vat.value);
	var totals_vat = Number(month_1_vat.value.replace(/,/g, "")) + Number(month_2_vat.value.replace(/,/g, "")) + Number(month_3_vat.value.replace(/,/g, "")) + Number(month_4_vat.value.replace(/,/g, "")) + Number(month_5_vat.value.replace(/,/g, "")) + Number(month_6_vat.value.replace(/,/g, "")) + Number(month_7_vat.value.replace(/,/g, "")) + Number(month_8_vat.value.replace(/,/g, "")) + Number(month_9_vat.value.replace(/,/g, "")) + Number(month_10_vat.value.replace(/,/g, "")) + Number(month_11_vat.value.replace(/,/g, "")) + Number(month_12_vat.value.replace(/,/g, ""));
	vat_totals.value = addCommas(totals_vat);

	month_1_net_sales.value = addCommas(Number(month_1_gross_sales.value.replace(/,/g, "")) - Number(month_1_vat.value.replace(/,/g, "")));
	month_2_net_sales.value = addCommas(Number(month_2_gross_sales.value.replace(/,/g, "")) - Number(month_2_vat.value.replace(/,/g, "")));
	month_3_net_sales.value = addCommas(Number(month_3_gross_sales.value.replace(/,/g, "")) - Number(month_3_vat.value.replace(/,/g, "")));
	month_4_net_sales.value = addCommas(Number(month_4_gross_sales.value.replace(/,/g, "")) - Number(month_4_vat.value.replace(/,/g, "")));
	month_5_net_sales.value = addCommas(Number(month_5_gross_sales.value.replace(/,/g, "")) - Number(month_5_vat.value.replace(/,/g, "")));
	month_6_net_sales.value = addCommas(Number(month_6_gross_sales.value.replace(/,/g, "")) - Number(month_6_vat.value.replace(/,/g, "")));
	month_7_net_sales.value = addCommas(Number(month_7_gross_sales.value.replace(/,/g, "")) - Number(month_7_vat.value.replace(/,/g, "")));
	month_8_net_sales.value = addCommas(Number(month_8_gross_sales.value.replace(/,/g, "")) - Number(month_8_vat.value.replace(/,/g, "")));
	month_9_net_sales.value = addCommas(Number(month_9_gross_sales.value.replace(/,/g, "")) - Number(month_9_vat.value.replace(/,/g, "")));
	month_10_net_sales.value = addCommas(Number(month_10_gross_sales.value.replace(/,/g, "")) - Number(month_10_vat.value.replace(/,/g, "")));
	month_11_net_sales.value = addCommas(Number(month_11_gross_sales.value.replace(/,/g, "")) - Number(month_11_vat.value.replace(/,/g, "")));
	month_12_net_sales.value = addCommas(Number(month_12_gross_sales.value.replace(/,/g, "")) - Number(month_12_vat.value.replace(/,/g, "")));

	var totals_net_sales = Number(month_1_net_sales.value.replace(/,/g, "")) + Number(month_2_net_sales.value.replace(/,/g, "")) + Number(month_3_net_sales.value.replace(/,/g, "")) + Number(month_4_net_sales.value.replace(/,/g, "")) + Number(month_5_net_sales.value.replace(/,/g, "")) + Number(month_6_net_sales.value.replace(/,/g, "")) + Number(month_7_net_sales.value.replace(/,/g, "")) + Number(month_8_net_sales.value.replace(/,/g, "")) + Number(month_9_net_sales.value.replace(/,/g, "")) + Number(month_10_net_sales.value.replace(/,/g, "")) + Number(month_11_net_sales.value.replace(/,/g, "")) + Number(month_12_net_sales.value.replace(/,/g, ""));
	net_sales_totals.value = addCommas(totals_net_sales);

	month_1_gross_profit_net.value = addCommas(Number(month_1_net_sales.value.replace(/,/g, "")) - Number(month_1_cost_of_sales.value.replace(/,/g, "")));
	month_2_gross_profit_net.value = addCommas(Number(month_2_net_sales.value.replace(/,/g, "")) - Number(month_2_cost_of_sales.value.replace(/,/g, "")));
	month_3_gross_profit_net.value = addCommas(Number(month_3_net_sales.value.replace(/,/g, "")) - Number(month_3_cost_of_sales.value.replace(/,/g, "")));
	month_4_gross_profit_net.value = addCommas(Number(month_4_net_sales.value.replace(/,/g, "")) - Number(month_4_cost_of_sales.value.replace(/,/g, "")));
	month_5_gross_profit_net.value = addCommas(Number(month_5_net_sales.value.replace(/,/g, "")) - Number(month_5_cost_of_sales.value.replace(/,/g, "")));
	month_6_gross_profit_net.value = addCommas(Number(month_6_net_sales.value.replace(/,/g, "")) - Number(month_6_cost_of_sales.value.replace(/,/g, "")));
	month_7_gross_profit_net.value = addCommas(Number(month_7_net_sales.value.replace(/,/g, "")) - Number(month_7_cost_of_sales.value.replace(/,/g, "")));
	month_8_gross_profit_net.value = addCommas(Number(month_8_net_sales.value.replace(/,/g, "")) - Number(month_8_cost_of_sales.value.replace(/,/g, "")));
	month_9_gross_profit_net.value = addCommas(Number(month_9_net_sales.value.replace(/,/g, "")) - Number(month_9_cost_of_sales.value.replace(/,/g, "")));
	month_10_gross_profit_net.value = addCommas(Number(month_10_net_sales.value.replace(/,/g, "")) - Number(month_10_cost_of_sales.value.replace(/,/g, "")));
	month_11_gross_profit_net.value = addCommas(Number(month_11_net_sales.value.replace(/,/g, "")) - Number(month_11_cost_of_sales.value.replace(/,/g, "")));
	month_12_gross_profit_net.value = addCommas(Number(month_12_net_sales.value.replace(/,/g, "")) - Number(month_12_cost_of_sales.value.replace(/,/g, "")));

	var totals_gross_profit_net = document.getElementById('gross_profit_net_totals');
	totals_gross_profit_net.value = addCommas(Number(document.getElementById('net_sales_totals').value.replace(/,/g, "")) - Number(document.getElementById('cost_of_sales_totals').value.replace(/,/g, "")));

	month_1_gpp_on_gross_sales.value = (Math.round((Number(month_1_gross_profit.value.replace(/,/g, "")) / Number(month_1_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_2_gpp_on_gross_sales.value = (Math.round((Number(month_2_gross_profit.value.replace(/,/g, "")) / Number(month_2_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_3_gpp_on_gross_sales.value = (Math.round((Number(month_3_gross_profit.value.replace(/,/g, "")) / Number(month_3_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_4_gpp_on_gross_sales.value = (Math.round((Number(month_4_gross_profit.value.replace(/,/g, "")) / Number(month_4_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_5_gpp_on_gross_sales.value = (Math.round((Number(month_5_gross_profit.value.replace(/,/g, "")) / Number(month_5_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_6_gpp_on_gross_sales.value = (Math.round((Number(month_6_gross_profit.value.replace(/,/g, "")) / Number(month_6_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_7_gpp_on_gross_sales.value = (Math.round((Number(month_7_gross_profit.value.replace(/,/g, "")) / Number(month_7_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_8_gpp_on_gross_sales.value = (Math.round((Number(month_8_gross_profit.value.replace(/,/g, "")) / Number(month_8_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_9_gpp_on_gross_sales.value = (Math.round((Number(month_9_gross_profit.value.replace(/,/g, "")) / Number(month_9_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_10_gpp_on_gross_sales.value = (Math.round((Number(month_10_gross_profit.value.replace(/,/g, "")) / Number(month_10_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_11_gpp_on_gross_sales.value = (Math.round((Number(month_11_gross_profit.value.replace(/,/g, "")) / Number(month_11_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_12_gpp_on_gross_sales.value = (Math.round((Number(month_12_gross_profit.value.replace(/,/g, "")) / Number(month_12_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	var totals_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_totals');
	totals_gpp_on_gross_sales.value = (Math.round(Number(document.getElementById('gross_profit_totals').value.replace(/,/g, "")) / Number(document.getElementById('gross_sales_totals').value.replace(/,/g, "")) * 100)) + '%';

	month_1_gpp_on_net_sales.value = (Math.round((Number(month_1_gross_profit_net.value.replace(/,/g, "")) / Number(month_1_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_2_gpp_on_net_sales.value = (Math.round((Number(month_2_gross_profit_net.value.replace(/,/g, "")) / Number(month_2_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_3_gpp_on_net_sales.value = (Math.round((Number(month_3_gross_profit_net.value.replace(/,/g, "")) / Number(month_3_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_4_gpp_on_net_sales.value = (Math.round((Number(month_4_gross_profit_net.value.replace(/,/g, "")) / Number(month_4_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_5_gpp_on_net_sales.value = (Math.round((Number(month_5_gross_profit_net.value.replace(/,/g, "")) / Number(month_5_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_6_gpp_on_net_sales.value = (Math.round((Number(month_6_gross_profit_net.value.replace(/,/g, "")) / Number(month_6_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_7_gpp_on_net_sales.value = (Math.round((Number(month_7_gross_profit_net.value.replace(/,/g, "")) / Number(month_7_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_8_gpp_on_net_sales.value = (Math.round((Number(month_8_gross_profit_net.value.replace(/,/g, "")) / Number(month_8_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_9_gpp_on_net_sales.value = (Math.round((Number(month_9_gross_profit_net.value.replace(/,/g, "")) / Number(month_9_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_10_gpp_on_net_sales.value = (Math.round((Number(month_10_gross_profit_net.value.replace(/,/g, "")) / Number(month_10_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_11_gpp_on_net_sales.value = (Math.round((Number(month_11_gross_profit_net.value.replace(/,/g, "")) / Number(month_11_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_12_gpp_on_net_sales.value = (Math.round((Number(month_12_gross_profit_net.value.replace(/,/g, "")) / Number(month_12_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	var totals_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_totals');
	totals_gpp_on_net_sales.value = (Math.round(Number(document.getElementById('gross_profit_net_totals').value.replace(/,/g, "")) / Number(document.getElementById('net_sales_totals').value.replace(/,/g, "")) * 100)) + '%';

	/* The Overall cost to field will be the sum of (Gross Sales... Misc 2)- Contributions from */
/*var contribution_1_val = Number(document.getElementById('contribution_1').value);
var contribution_2_val = Number(document.getElementById('contribution_2').value);
var contribution_3_val = Number(document.getElementById('contribution_3').value);
var contribution_4_val = Number(document.getElementById('contribution_4').value);
var contribution_5_val = Number(document.getElementById('contribution_5').value);
var contribution_total = contribution_1_val + contribution_2_val + contribution_3_val + contribution_4_val + contribution_5_val;

	overall_cost_to_total.value = (Math.round(((Number(gross_sales_totals_val.value) + Number(vat_totals_val.value) + Number(net_sales_totals_val.value) + Number(cost_of_sales_totals_val.value) + Number(gross_profit_totals_val.value) + Number(gross_profit_net_totals_val.value) + Number(gpp_on_gross_sales_totals_val.value) + Number(gpp_on_net_sales_totals_val.value) + Number(free_issues_totals_val.value) + Number(labour_and_allocated_charges_totals_val.value) + Number(uniform_totals_val.value) + Number(chemicals_cleaning_totals_val.value) + Number(disposables_totals_val.value) + Number(delph_and_cutlery_totals_val.value) + Number(misc_1_totals_val.value) + Number(misc_2_totals_val.value)) - contribution_total) * 100) / 100).toFixed(2);*/
});

var month_1_net_sales = document.getElementById('net_sales_month_1');
var month_2_net_sales = document.getElementById('net_sales_month_2');
var month_3_net_sales = document.getElementById('net_sales_month_3');
var month_4_net_sales = document.getElementById('net_sales_month_4');
var month_5_net_sales = document.getElementById('net_sales_month_5');
var month_6_net_sales = document.getElementById('net_sales_month_6');
var month_7_net_sales = document.getElementById('net_sales_month_7');
var month_8_net_sales = document.getElementById('net_sales_month_8');
var month_9_net_sales = document.getElementById('net_sales_month_9');
var month_10_net_sales = document.getElementById('net_sales_month_10');
var month_11_net_sales = document.getElementById('net_sales_month_11');
var month_12_net_sales = document.getElementById('net_sales_month_12');

var month_1_gross_profit_net = document.getElementById('gross_profit_net_month_1');
var month_2_gross_profit_net = document.getElementById('gross_profit_net_month_2');
var month_3_gross_profit_net = document.getElementById('gross_profit_net_month_3');
var month_4_gross_profit_net = document.getElementById('gross_profit_net_month_4');
var month_5_gross_profit_net = document.getElementById('gross_profit_net_month_5');
var month_6_gross_profit_net = document.getElementById('gross_profit_net_month_6');
var month_7_gross_profit_net = document.getElementById('gross_profit_net_month_7');
var month_8_gross_profit_net = document.getElementById('gross_profit_net_month_8');
var month_9_gross_profit_net = document.getElementById('gross_profit_net_month_9');
var month_10_gross_profit_net = document.getElementById('gross_profit_net_month_10');
var month_11_gross_profit_net = document.getElementById('gross_profit_net_month_11');
var month_12_gross_profit_net = document.getElementById('gross_profit_net_month_12');

var month_1_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_1');
var month_2_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_2');
var month_3_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_3');
var month_4_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_4');
var month_5_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_5');
var month_6_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_6');
var month_7_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_7');
var month_8_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_8');
var month_9_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_9');
var month_10_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_10');
var month_11_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_11');
var month_12_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_month_12');

var month_1_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_1');
var month_2_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_2');
var month_3_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_3');
var month_4_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_4');
var month_5_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_5');
var month_6_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_6');
var month_7_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_7');
var month_8_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_8');
var month_9_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_9');
var month_10_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_10');
var month_11_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_11');
var month_12_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_month_12');

$('#net_sales_month_1, #net_sales_month_2, #net_sales_month_3, #net_sales_month_4, #net_sales_month_5, #net_sales_month_6, #net_sales_month_7, #net_sales_month_8, #net_sales_month_9, #net_sales_month_10, #net_sales_month_11, #net_sales_month_12').change(function() {

	month_1_net_sales.value = (Math.round(month_1_net_sales.value * 100) / 100).toFixed(2);
	month_2_net_sales.value = (Math.round(month_2_net_sales.value * 100) / 100).toFixed(2);
	month_3_net_sales.value = (Math.round(month_3_net_sales.value * 100) / 100).toFixed(2);
	month_4_net_sales.value = (Math.round(month_4_net_sales.value * 100) / 100).toFixed(2);
	month_5_net_sales.value = (Math.round(month_5_net_sales.value * 100) / 100).toFixed(2);
	month_6_net_sales.value = (Math.round(month_6_net_sales.value * 100) / 100).toFixed(2);
	month_7_net_sales.value = (Math.round(month_7_net_sales.value * 100) / 100).toFixed(2);
	month_8_net_sales.value = (Math.round(month_8_net_sales.value * 100) / 100).toFixed(2);
	month_9_net_sales.value = (Math.round(month_9_net_sales.value * 100) / 100).toFixed(2);
	month_10_net_sales.value = (Math.round(month_10_net_sales.value * 100) / 100).toFixed(2);
	month_11_net_sales.value = (Math.round(month_11_net_sales.value * 100) / 100).toFixed(2);
	month_12_net_sales.value = (Math.round(month_12_net_sales.value * 100) / 100).toFixed(2);
	var totals_net_sales = Number(month_1_net_sales.value) + Number(month_2_net_sales.value) + Number(month_3_net_sales.value) + Number(month_4_net_sales.value) + Number(month_5_net_sales.value) + Number(month_6_net_sales.value) + Number(month_7_net_sales.value) + Number(month_8_net_sales.value) + Number(month_9_net_sales.value) + Number(month_10_net_sales.value) + Number(month_11_net_sales.value) + Number(month_12_net_sales.value);
	net_sales_totals.value = (Math.round(totals_net_sales * 100) / 100).toFixed(2);
});
/* Gross Sales, VAT, Net Sales [ Ends ] */

/* Cost of Sales  [ Starts ] */
$('#cost_of_sales_month_1, #cost_of_sales_month_2, #cost_of_sales_month_3, #cost_of_sales_month_4, #cost_of_sales_month_5, #cost_of_sales_month_6, #cost_of_sales_month_7, #cost_of_sales_month_8, #cost_of_sales_month_9, #cost_of_sales_month_10, #cost_of_sales_month_11, #cost_of_sales_month_12').change(function() {

	month_1_cost_of_sales.value = addCommas(month_1_cost_of_sales.value);
	month_2_cost_of_sales.value = addCommas(month_2_cost_of_sales.value);
	month_3_cost_of_sales.value = addCommas(month_3_cost_of_sales.value);
	month_4_cost_of_sales.value = addCommas(month_4_cost_of_sales.value);
	month_5_cost_of_sales.value = addCommas(month_5_cost_of_sales.value);
	month_6_cost_of_sales.value = addCommas(month_6_cost_of_sales.value);
	month_7_cost_of_sales.value = addCommas(month_7_cost_of_sales.value);
	month_8_cost_of_sales.value = addCommas(month_8_cost_of_sales.value);
	month_9_cost_of_sales.value = addCommas(month_9_cost_of_sales.value);
	month_10_cost_of_sales.value = addCommas(month_10_cost_of_sales.value);
	month_11_cost_of_sales.value = addCommas(month_11_cost_of_sales.value);
	month_12_cost_of_sales.value = addCommas(month_12_cost_of_sales.value);
	var totals_cost_of_sales = Number(month_1_cost_of_sales.value.replace(/,/g, "")) + Number(month_2_cost_of_sales.value.replace(/,/g, "")) + Number(month_3_cost_of_sales.value.replace(/,/g, "")) + Number(month_4_cost_of_sales.value.replace(/,/g, "")) + Number(month_5_cost_of_sales.value.replace(/,/g, "")) + Number(month_6_cost_of_sales.value.replace(/,/g, "")) + Number(month_7_cost_of_sales.value.replace(/,/g, "")) + Number(month_8_cost_of_sales.value.replace(/,/g, "")) + Number(month_9_cost_of_sales.value.replace(/,/g, "")) + Number(month_10_cost_of_sales.value.replace(/,/g, "")) + Number(month_11_cost_of_sales.value.replace(/,/g, "")) + Number(month_12_cost_of_sales.value.replace(/,/g, ""));
	cost_of_sales_totals.value = addCommas(totals_cost_of_sales);

	month_1_gross_profit.value = addCommas(Number(month_1_gross_sales.value.replace(/,/g, "")) - Number(month_1_cost_of_sales.value.replace(/,/g, "")));
	month_2_gross_profit.value = addCommas(Number(month_2_gross_sales.value.replace(/,/g, "")) - Number(month_2_cost_of_sales.value.replace(/,/g, "")));
	month_3_gross_profit.value = addCommas(Number(month_3_gross_sales.value.replace(/,/g, "")) - Number(month_3_cost_of_sales.value.replace(/,/g, "")));
	month_4_gross_profit.value = addCommas(Number(month_4_gross_sales.value.replace(/,/g, "")) - Number(month_4_cost_of_sales.value.replace(/,/g, "")));
	month_5_gross_profit.value = addCommas(Number(month_5_gross_sales.value.replace(/,/g, "")) - Number(month_5_cost_of_sales.value.replace(/,/g, "")));
	month_6_gross_profit.value = addCommas(Number(month_6_gross_sales.value.replace(/,/g, "")) - Number(month_6_cost_of_sales.value.replace(/,/g, "")));
	month_7_gross_profit.value = addCommas(Number(month_7_gross_sales.value.replace(/,/g, "")) - Number(month_7_cost_of_sales.value.replace(/,/g, "")));
	month_8_gross_profit.value = addCommas(Number(month_8_gross_sales.value.replace(/,/g, "")) - Number(month_8_cost_of_sales.value.replace(/,/g, "")));
	month_9_gross_profit.value = addCommas(Number(month_9_gross_sales.value.replace(/,/g, "")) - Number(month_9_cost_of_sales.value.replace(/,/g, "")));
	month_10_gross_profit.value = addCommas(Number(month_10_gross_sales.value.replace(/,/g, "")) - Number(month_10_cost_of_sales.value.replace(/,/g, "")));
	month_11_gross_profit.value = addCommas(Number(month_11_gross_sales.value.replace(/,/g, "")) - Number(month_11_cost_of_sales.value.replace(/,/g, "")));
	month_12_gross_profit.value = addCommas(Number(month_12_gross_sales.value.replace(/,/g, "")) - Number(month_12_cost_of_sales.value.replace(/,/g, "")));

	var totals_gross_profit = document.getElementById('gross_profit_totals');
	totals_gross_profit.value = addCommas(Number(document.getElementById('gross_sales_totals').value.replace(/,/g, "")) - Number(document.getElementById('cost_of_sales_totals').value.replace(/,/g, "")));

	month_1_gross_profit_net.value = addCommas(Number(month_1_net_sales.value.replace(/,/g, "")) - Number(month_1_cost_of_sales.value.replace(/,/g, "")));
	month_2_gross_profit_net.value = addCommas(Number(month_2_net_sales.value.replace(/,/g, "")) - Number(month_2_cost_of_sales.value.replace(/,/g, "")));
	month_3_gross_profit_net.value = addCommas(Number(month_3_net_sales.value.replace(/,/g, "")) - Number(month_3_cost_of_sales.value.replace(/,/g, "")));
	month_4_gross_profit_net.value = addCommas(Number(month_4_net_sales.value.replace(/,/g, "")) - Number(month_4_cost_of_sales.value.replace(/,/g, "")));
	month_5_gross_profit_net.value = addCommas(Number(month_5_net_sales.value.replace(/,/g, "")) - Number(month_5_cost_of_sales.value.replace(/,/g, "")));
	month_6_gross_profit_net.value = addCommas(Number(month_6_net_sales.value.replace(/,/g, "")) - Number(month_6_cost_of_sales.value.replace(/,/g, "")));
	month_7_gross_profit_net.value = addCommas(Number(month_7_net_sales.value.replace(/,/g, "")) - Number(month_7_cost_of_sales.value.replace(/,/g, "")));
	month_8_gross_profit_net.value = addCommas(Number(month_8_net_sales.value.replace(/,/g, "")) - Number(month_8_cost_of_sales.value.replace(/,/g, "")));
	month_9_gross_profit_net.value = addCommas(Number(month_9_net_sales.value.replace(/,/g, "")) - Number(month_9_cost_of_sales.value.replace(/,/g, "")));
	month_10_gross_profit_net.value = addCommas(Number(month_10_net_sales.value.replace(/,/g, "")) - Number(month_10_cost_of_sales.value.replace(/,/g, "")));
	month_11_gross_profit_net.value = addCommas(Number(month_11_net_sales.value.replace(/,/g, "")) - Number(month_11_cost_of_sales.value.replace(/,/g, "")));
	month_12_gross_profit_net.value = addCommas(Number(month_12_net_sales.value.replace(/,/g, "")) - Number(month_12_cost_of_sales.value.replace(/,/g, "")));

	var totals_gross_profit_net = document.getElementById('gross_profit_net_totals');
	totals_gross_profit_net.value = addCommas(Number(document.getElementById('net_sales_totals').value.replace(/,/g, "")) - Number(document.getElementById('cost_of_sales_totals').value.replace(/,/g, "")));

	month_1_gpp_on_gross_sales.value = (Math.round((Number(month_1_gross_profit.value.replace(/,/g, "")) / Number(month_1_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_2_gpp_on_gross_sales.value = (Math.round((Number(month_2_gross_profit.value.replace(/,/g, "")) / Number(month_2_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_3_gpp_on_gross_sales.value = (Math.round((Number(month_3_gross_profit.value.replace(/,/g, "")) / Number(month_3_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_4_gpp_on_gross_sales.value = (Math.round((Number(month_4_gross_profit.value.replace(/,/g, "")) / Number(month_4_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_5_gpp_on_gross_sales.value = (Math.round((Number(month_5_gross_profit.value.replace(/,/g, "")) / Number(month_5_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_6_gpp_on_gross_sales.value = (Math.round((Number(month_6_gross_profit.value.replace(/,/g, "")) / Number(month_6_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_7_gpp_on_gross_sales.value = (Math.round((Number(month_7_gross_profit.value.replace(/,/g, "")) / Number(month_7_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_8_gpp_on_gross_sales.value = (Math.round((Number(month_8_gross_profit.value.replace(/,/g, "")) / Number(month_8_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_9_gpp_on_gross_sales.value = (Math.round((Number(month_9_gross_profit.value.replace(/,/g, "")) / Number(month_9_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_10_gpp_on_gross_sales.value = (Math.round((Number(month_10_gross_profit.value.replace(/,/g, "")) / Number(month_10_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_11_gpp_on_gross_sales.value = (Math.round((Number(month_11_gross_profit.value.replace(/,/g, "")) / Number(month_11_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_12_gpp_on_gross_sales.value = (Math.round((Number(month_12_gross_profit.value.replace(/,/g, "")) / Number(month_12_gross_sales.value.replace(/,/g, ""))) * 100)) + '%';

	var totals_gpp_on_gross_sales = document.getElementById('gpp_on_gross_sales_totals');
	totals_gpp_on_gross_sales.value = (Math.round(Number(document.getElementById('gross_profit_totals').value.replace(/,/g, "")) / Number(document.getElementById('gross_sales_totals').value.replace(/,/g, "")) * 100)) + '%';

	month_1_gpp_on_net_sales.value = (Math.round((Number(month_1_gross_profit_net.value.replace(/,/g, "")) / Number(month_1_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_2_gpp_on_net_sales.value = (Math.round((Number(month_2_gross_profit_net.value.replace(/,/g, "")) / Number(month_2_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_3_gpp_on_net_sales.value = (Math.round((Number(month_3_gross_profit_net.value.replace(/,/g, "")) / Number(month_3_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_4_gpp_on_net_sales.value = (Math.round((Number(month_4_gross_profit_net.value.replace(/,/g, "")) / Number(month_4_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_5_gpp_on_net_sales.value = (Math.round((Number(month_5_gross_profit_net.value.replace(/,/g, "")) / Number(month_5_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_6_gpp_on_net_sales.value = (Math.round((Number(month_6_gross_profit_net.value.replace(/,/g, "")) / Number(month_6_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_7_gpp_on_net_sales.value = (Math.round((Number(month_7_gross_profit_net.value.replace(/,/g, "")) / Number(month_7_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_8_gpp_on_net_sales.value = (Math.round((Number(month_8_gross_profit_net.value.replace(/,/g, "")) / Number(month_8_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_9_gpp_on_net_sales.value = (Math.round((Number(month_9_gross_profit_net.value.replace(/,/g, "")) / Number(month_9_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_10_gpp_on_net_sales.value = (Math.round((Number(month_10_gross_profit_net.value.replace(/,/g, "")) / Number(month_10_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_11_gpp_on_net_sales.value = (Math.round((Number(month_11_gross_profit_net.value.replace(/,/g, "")) / Number(month_11_net_sales.value.replace(/,/g, ""))) * 100)) + '%';
	month_12_gpp_on_net_sales.value = (Math.round((Number(month_12_gross_profit_net.value.replace(/,/g, "")) / Number(month_12_net_sales.value.replace(/,/g, ""))) * 100)) + '%';

	var totals_gpp_on_net_sales = document.getElementById('gpp_on_net_sales_totals');
	totals_gpp_on_net_sales.value = (Math.round(Number(document.getElementById('gross_profit_net_totals').value.replace(/,/g, "")) / Number(document.getElementById('net_sales_totals').value.replace(/,/g, "")) * 100)) + '%';

	/* The Overall cost to field will be the sum of (Gross Sales... Misc 2)- Contributions from */
/*var contribution_1_val = Number(document.getElementById('contribution_1').value);
var contribution_2_val = Number(document.getElementById('contribution_2').value);
var contribution_3_val = Number(document.getElementById('contribution_3').value);
var contribution_4_val = Number(document.getElementById('contribution_4').value);
var contribution_5_val = Number(document.getElementById('contribution_5').value);
var contribution_total = contribution_1_val + contribution_2_val + contribution_3_val + contribution_4_val + contribution_5_val;

	overall_cost_to_total.value = (Math.round(((Number(gross_sales_totals_val.value) + Number(vat_totals_val.value) + Number(net_sales_totals_val.value) + Number(cost_of_sales_totals_val.value) + Number(gross_profit_totals_val.value) + Number(gross_profit_net_totals_val.value) + Number(gpp_on_gross_sales_totals_val.value) + Number(gpp_on_net_sales_totals_val.value) + Number(free_issues_totals_val.value) + Number(labour_and_allocated_charges_totals_val.value) + Number(uniform_totals_val.value) + Number(chemicals_cleaning_totals_val.value) + Number(disposables_totals_val.value) + Number(delph_and_cutlery_totals_val.value) + Number(misc_1_totals_val.value) + Number(misc_2_totals_val.value)) - contribution_total) * 100) / 100).toFixed(2);*/
});
/* Cost of Sales [ Ends ] */

function display_months_header(obj) {
	var month = obj.value.substr(3,2);

	switch(month)
	{
		case '01':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  break;
		case '02':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  break;
		case '03':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  break;
		case '04':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  break;
		case '05':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  break;
		case '06':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  break;
		case '07':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  break;
		case '08':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  break;
		case '09':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  break;
		case '10':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  break;
		case '11':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="May.">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  break;
		case '12':
		  document.getElementById('month_one').innerHTML = '<input name="month_1_header" type="text" class="textbox_as_label" readonly="readonly" value="Dec.">';
		  document.getElementById('month_two').innerHTML = '<input name="month_2_header" type="text" class="textbox_as_label" readonly="readonly" value="Jan.">';
		  document.getElementById('month_three').innerHTML = '<input name="month_3_header" type="text" class="textbox_as_label" readonly="readonly" value="Feb.">';
		  document.getElementById('month_four').innerHTML = '<input name="month_4_header" type="text" class="textbox_as_label" readonly="readonly" value="Mar.">';
		  document.getElementById('month_five').innerHTML = '<input name="month_5_header" type="text" class="textbox_as_label" readonly="readonly" value="Apr.">';
		  document.getElementById('month_six').innerHTML = '<input name="month_6_header" type="text" class="textbox_as_label" readonly="readonly" value="May">';
		  document.getElementById('month_seven').innerHTML = '<input name="month_7_header" type="text" class="textbox_as_label" readonly="readonly" value="June">';
		  document.getElementById('month_eight').innerHTML = '<input name="month_8_header" type="text" class="textbox_as_label" readonly="readonly" value="July">';
		  document.getElementById('month_nine').innerHTML = '<input name="month_9_header" type="text" class="textbox_as_label" readonly="readonly" value="Aug.">';
		  document.getElementById('month_ten').innerHTML = '<input name="month_10_header" type="text" class="textbox_as_label" readonly="readonly" value="Sept.">';
		  document.getElementById('month_eleven').innerHTML = '<input name="month_11_header" type="text" class="textbox_as_label" readonly="readonly" value="Oct.">';
		  document.getElementById('month_twelve').innerHTML = '<input name="month_12_header" type="text" class="textbox_as_label" readonly="readonly" value="Nov.">';
		  break;
		default:
		  document.getElementById('month_one').innerHTML = 'Month 1';
		  document.getElementById('month_two').innerHTML = 'Month 2';
		  document.getElementById('month_three').innerHTML = 'Month 3';
		  document.getElementById('month_four').innerHTML = 'Month 4';
		  document.getElementById('month_five').innerHTML = 'Month 5';
		  document.getElementById('month_six').innerHTML = 'Month 6';
		  document.getElementById('month_seven').innerHTML = 'Month 7';
		  document.getElementById('month_eight').innerHTML = 'Month 8';
		  document.getElementById('month_nine').innerHTML = 'Month 9';
		  document.getElementById('month_ten').innerHTML = 'Month 10';
		  document.getElementById('month_eleven').innerHTML = 'Month 11';
		  document.getElementById('month_twelve').innerHTML = 'Month 12';
	}
}

//
function validation() {
  var validation_success = false;
  var d = new Date();
  d.setMonth(d.getMonth() - 12);
  var e = new Date();

  var unit_name_val = $("#unit_name").val();

  if(unit_name_val == '' || unit_name_val == '0' || unit_name_val == null) {
    $("#unit_name").focus();
    $("#unit_name_span.error_message").html("Please select a Unit.");
  }
  else
    validation_success = true;

  return validation_success;
}

function getRowTotal(row) {
	var total = 0;

	for (var month = 1; month <= 12; month++) {
		total += parseFloat($('#' + row + '_month_' + month).val().replace(/,/g, ""));
	}
	
	return addCommas(total);
}

function calculateBudgetTotal() {
	var colIndexes = ['totals', 'month_1', 'month_2', 'month_3', 'month_4', 'month_5', 'month_6', 'month_7', 'month_8', 'month_9', 'month_10', 'month_11', 'month_12'];
	
	$.each(colIndexes, function(arrIndex, colIndex) {
		var budgetTotal = 0;
		var netSub = parseFloat($('#gross_profit_net_' + colIndex).val().replace(/,/g, ""));

		if (isNaN(netSub)) {
			netSub = 0;
		}

		$.each(budgetSubRows, function(index, row) {
			if (!$('#' + row + '_row').hasClass('hidden')) {
				var rowAmount = parseFloat($('#' + row + '_' + colIndex).val().replace(/,/g, ""));

				if (isNaN(rowAmount)) {
					rowAmount = 0;
				}

				budgetTotal += rowAmount;
			}
		});

		$('#budget_total_' + colIndex).val(addCommas(budgetTotal));
		$('#net_sub_' + colIndex).val(addCommas(budgetTotal - netSub));
	});
}

$(document).ready(function() {
	calculateBudgetTotal();
	
    $("#trading_account").on("submit", function () {
        return validation();
    });

    $("#unit_name").on("change", function () {
	    if($("#unit_name").val() != '') {
	      	$("#unit_name_span.error_message").html("");
	    }
	});
	
	$.each(budgetSubRows, function(index, row) {
		for(var month = 1; month <= 12; month++) {
			$('#' + row + '_month_' + month).on('change', function() {
				var amount = parseFloat($(this).val().replace(/,/g, ""));
				$(this).val(addCommas(amount))
				
				var rowTotal = getRowTotal(row);
				
				$('#' + row + '_totals').val(rowTotal);
				
				calculateBudgetTotal();
			})
		}
	})
});

//
window.onload = function() {
  $('#hidden_unit_name').val($('#unit_name').find(':selected').text());
};