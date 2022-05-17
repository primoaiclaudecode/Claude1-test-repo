function getReminderData() {
    $('#problems_reminder').empty();
    $('#budgets_reminder').empty();

    $.ajax({
        type: "get",
        url: '/dashboard/reminder',
        data: {
            unitId: $('#unit_id').val(),
        },
        success: function (data) {
            $('#budget_reminder_stats .card-title').text(data.budgets.length);
            $('#problem_reminder_stats .card-title').text(data.problems.length);

            $.each(data.budgets, function (index, problem) {
                insertBudgetRow(problem);
            });

            $.each(data.problems, function (index, problem) {
                insertProblemRow(problem);
            });
        }
    });
};

function insertProblemRow(problem) {
    $('#problems_reminder')
        .append(
            $('<div />').addClass('problem-card')
                .append(
                    $('<p />')
                        .append(
                            $('<strong />')
                                .append(
                                    $('<span />').text('Unit name:')
                                )
                        )
                        .append(
                            $('<span />').addClass('margin-left-10').text(problem.unitName)
                        )
                        .append(
                            $('<strong />')
                                .append(
                                    $('<a />').attr({
                                        href: '/sheets/problem-report/' + problem.id,
                                        target: '_blank'
                                    }).addClass('pull-right').text('CAR#:' + problem.id)
                                )
                        )
                )
                .append(
                    $('<p />')
                        .append(
                            $('<strong />')
                                .append(
                                    $('<span />').text('Problem type: ')
                                )
                        )
                        .append(
                            $('<span />').addClass('margin-left-10').text(problem.problemName)
                        )
                )
                .append(
                    $('<p />')
                        .append(
                            $('<strong />')
                                .append(
                                    $('<span />').text('How long has report been opened: ')
                                )
                        )
                        .append(
                            $('<span />').addClass('margin-left-10').text(problem.problemDuration)
                        )
                )
        )
}

function insertBudgetRow(budget) {
    $('#budgets_reminder')
        .append(
            $('<div />').addClass('budget-card')
                .append(
                    $('<p />')
                        .append(
                            $('<strong />')
                                .append(
                                    $('<span />').text('Unit name:')
                                )
                        )
                        .append(
                            $('<span />').addClass('margin-left-10').text(budget.unitName)
                        )
                        .append(
                            $('<strong />')
                                .append(
                                    $('<a />').attr({
                                        href: '/sheets/phased-budget?unit_id=' + budget.unitId,
                                        target: '_blank'
                                    }).addClass('pull-right').text('BUDGET#:' + budget.unitId)
                                )
                        )
                )
                .append(
                    $('<p />')
                        .append(
                            $('<strong />')
                                .append(
                                    $('<span />').text('Budget is due to expire on: ')
                                )
                        )
                        .append(
                            $('<span />').addClass('margin-left-10').text(budget.budgetDate)
                        )
                )
        )
}

function getDashboardData() {
    $.ajax({
        type: "get",
        url: '/dashboard',
        data: {
            startDate: $('#from_date').val(),
            endDate: $('#to_date').val(),
        },
        success: function (data) {
            // Clear data
            $('#dashboard_data_table tbody').empty();

            // Insert totals
            $('#dashboard_data_table tbody')
                .append(
                    $('<tr />')
                        .css('font-weight', 'bold')
                        .append(
                            $('<td />').text('Totals')
                        )
                        .append(
                            $('<td />').addClass('text-center').text(numberFormat(data.totals.grossSalesActual))
                        )
                        .append(
                            $('<td />').addClass('text-center').text(numberFormat(data.totals.netSalesActual))
                        )
                        .append(
                            $('<td />').addClass('text-center').text(numberFormat(data.totals.costOfSalesActual))
                        )
                        .append(
                            $('<td />').addClass('text-center').text(numberFormat(data.totals.cleaningDisp))
                        )
                        .append(
                            $('<td />').addClass('text-center').text(percentFormat(data.totals.gpGrossPercent))
                        )
                        .append(
                            $('<td />').addClass('text-center').text(percentFormat(data.totals.gpNetPercent))
                        )
                        .append(
                            $('<td />').addClass('text-center').text(data.totals.lePurchase)
                        )
                        .append(
                            $('<td />').addClass('text-center').text(data.totals.leSales)
                        )
                        .append(
                            $('<td />').addClass('text-center').text(numberFormat(data.totals.cashLodge))
                        )
                );

            // Insert units
            $.each(data.units, function (index, unit) {
                $('#dashboard_data_table tbody')
                    .append(
                        $('<tr />').attr('id', unit.id)
                            .css('font-weight', 'normal')
                            .append(
                                $('<td />')
                                    .append(
                                        $('<a />').attr('href', '').addClass('show-chart').text(unit.name)
                                    )
                                    .on('click', function (e) {
                                        e.preventDefault();

                                        showUnitChart(unit)
                                    })
                            )
                            .append(
                                $('<td />').addClass('text-center').text(numberFormat(unit.grossSalesActual))
                            )
                            .append(
                                $('<td />').addClass('text-center').text(numberFormat(unit.netSalesActual))
                            )
                            .append(
                                $('<td />').addClass('text-center').text(numberFormat(unit.costOfSalesActual))
                            )
                            .append(
                                $('<td />').addClass('text-center').text(numberFormat(unit.cleaningDisp))
                            )
                            .append(
                                $('<td />').addClass('text-center').text(unit.hideGrossCell ? '' : percentFormat(unit.gpGrossPercent))
                            )
                            .append(
                                $('<td />').addClass('text-center').text(unit.hideNetCell ? '' : percentFormat(unit.gpNetPercent))
                            )
                            .append(
                                $('<td />').addClass('text-center').text(unit.lePurchase)
                            )
                            .append(
                                $('<td />').addClass('text-center').text(unit.leSales)
                            )
                            .append(
                                $('<td />').addClass('text-center').text(numberFormat(unit.cashLodge))
                            )
                    );
            })

            // Draw Totals chart
            showTotalsChart(data.totals)
        }
    });
};

function showTotalsChart(totals) {
    $('#chart').empty();

    $('#chart')
        .append(
            $('<div />').addClass('row')
                .append(
                    $('<div />').addClass('col-md-4 col-lg-4 col-xlg-2')
                        .append(
                            $('<div />').attr('id', 'totals_gross_percents_chart')
                        )
                        .append(
                            $('<div />').addClass('text-center')
                                .append(
                                    $('<div />').addClass('percent-chart-totals')
                                        .append(
                                            $('<span />').text('Actual: ' + numberFormat(totals.grossSalesActual))
                                        )
                                        .append(
                                            $('<hr />').addClass('purple')
                                        )
                                        .append(
                                            $('<span />').text('Budget: ' + numberFormat(totals.grossSalesBudget))
                                        )
                                )
                        )
                )
                .append(
                    $('<div />').addClass('col-md-4 col-lg-4 col-xlg-2')
                        .append(
                            $('<div />').attr('id', 'totals_net_percents_chart')
                        )
                        .append(
                            $('<div />').addClass('text-center')
                                .append(
                                    $('<div />').addClass('percent-chart-totals')
                                        .append(
                                            $('<span />').text('Actual: ' + numberFormat(totals.netSalesActual))
                                        )
                                        .append(
                                            $('<hr />').addClass('yellow')
                                        )
                                        .append(
                                            $('<span />').text('Budget: ' + numberFormat(totals.netSalesBudget))
                                        )
                                )
                        )
                )
                .append(
                    $('<div />').addClass('col-md-4 col-lg-4 col-xlg-2')
                        .append(
                            $('<div />').attr('id', 'totals_cost_percents_chart')
                        )
                        .append(
                            $('<div />').addClass('text-center')
                                .append(
                                    $('<div />').addClass('percent-chart-totals')
                                        .append(
                                            $('<span />').text('Actual: ' + numberFormat(totals.costOfSalesActual))
                                        )
                                        .append(
                                            $('<hr />').addClass('red')
                                        )
                                        .append(
                                            $('<span />').text('Budget: ' + numberFormat(totals.costOfSalesBudget))
                                        )
                                )
                        )
                )
                .append(
                    $('<div />').addClass('col-sm-12 col-md-12 col-lg-12 col-xlg-6')
                        .append(
                            $('<div />').attr('id', 'totals_chart')
                        )
                )
        );

    // Gross percents chart
    drawPercentsChart($('#totals_gross_percents_chart').get(0), totals.grossSalesPercent, 'Gross Sales', '#775dd0');

    // Net percents chart
    drawPercentsChart($('#totals_net_percents_chart').get(0), totals.netSalesPercent, 'Net Sales', '#feb019');

    // Cost of Sales percents chart
    drawPercentsChart($('#totals_cost_percents_chart').get(0), totals.costOfSalesPercent, 'Cost of Sales', '#ff4560');

    // Sales chart
    var chartData = [
        totals.grossSalesActual,
        totals.grossSalesBudget,
        totals.netSalesActual,
        totals.netSalesBudget,
        totals.costOfSalesActual,
        totals.costOfSalesBudget,
        totals.cashLodge
    ];

    var chartCategories = [
        'Gross Sales Actual',
        'Gross Sales Budget',
        'Net Sales Actual',
        'Net Sales Budget',
        'Cost of Sales Actual',
        'Cost of Sales Budget',
        'Cash Lodgements'
    ];
    
    var chartColors = ['#775dd0', '#546E7A', '#feb019', '#546E7A', '#ff4560', '#546E7A', '#008ffb'];
    
    drawSalesChart($('#totals_chart').get(0), chartCategories, chartData, chartColors);
}

function showUnitChart(unit) {
    $('.modal-title').text(unit.name);

    $('#unit_chart').empty();

    var row = $('<div />').addClass('row');

    var chartData = [];

    var chartCategories = [];

    var chartColors = [];
    
    // Gross Budget
    if (unit.showGrossChart) {
        chartData.push(unit.grossSalesActual);
        chartData.push(unit.grossSalesBudget);

        chartCategories.push('Gross Sales Actual');
        chartCategories.push('Gross Sales Budget');

        chartColors.push('#775dd0');
        chartColors.push('#546E7A');
        
        row.append(
            $('<div />').addClass(unit.showNetChart ? 'col-xs-4' : 'col-xs-6')
                .append(
                    $('<div />').attr('id', 'unit_gross_percents_chart')
                )
                .append(
                    $('<div />').addClass('text-center')
                        .append(
                            $('<div />').addClass('percent-chart-totals')
                                .append(
                                    $('<span />').text('Actual: ' + numberFormat(unit.grossSalesActual))
                                )
                                .append(
                                    $('<hr />').addClass('purple')
                                )
                                .append(
                                    $('<span />').text('Budget: ' + numberFormat(unit.grossSalesBudget))
                                )
                        )
                )
        );
    }

    // Net Budget
    if (unit.showNetChart) {
        chartData.push(unit.netSalesActual);
        chartData.push(unit.netSalesBudget);

        chartCategories.push('Net Sales Actual');
        chartCategories.push('Net Sales Budget');

        chartColors.push('#feb019');
        chartColors.push('#546E7A');

        row.append(
            $('<div />').addClass(unit.showGrossChart ? 'col-xs-4' : 'col-xs-6')
                .append(
                    $('<div />').attr('id', 'unit_net_percents_chart')
                )
                .append(
                    $('<div />').addClass('text-center')
                        .append(
                            $('<div />').addClass('percent-chart-totals')
                                .append(
                                    $('<span />').text('Actual: ' + numberFormat(unit.netSalesActual))
                                )
                                .append(
                                    $('<hr />').addClass('yellow')
                                )
                                .append(
                                    $('<span />').text('Budget: ' + numberFormat(unit.netSalesBudget))
                                )
                        )
                )
        );
    }

    // Cost of sales & Cash lodge
    chartData.push(unit.costOfSalesActual);
    chartData.push(unit.costOfSalesBudget);
    chartData.push(unit.cashLodge);

    chartCategories.push('Cost of Sales Actual');
    chartCategories.push('Cost of Sales Budget');
    chartCategories.push('Cash Lodgements');

    chartColors.push('#ff4560');
    chartColors.push('#546E7A');
    chartColors.push('#008ffb');
    
    row.append(
        $('<div />').addClass(unit.showGrossChart && unit.showNetChart ? 'col-xs-4' : 'col-xs-6')
            .append(
                $('<div />').attr('id', 'unit_cost_percents_chart')
            )
            .append(
                $('<div />').addClass('text-center')
                    .append(
                        $('<div />').addClass('percent-chart-totals')
                            .append(
                                $('<span />').text('Actual: ' + numberFormat(unit.costOfSalesActual))
                            )
                            .append(
                                $('<hr />').addClass('red')
                            )
                            .append(
                                $('<span />').text('Budget: ' + numberFormat(unit.costOfSalesBudget))
                            )
                    )
            )
    );

    $('#unit_chart')
        .append(row)
        .append(
            $('<div />').addClass('row')
                .append(
                    $('<div />').addClass('col-xs-12')
                        .append(
                            $('<div />').attr('id', 'sales_chart')
                        )
                )
        );

    $('#chart_modal').modal('show')

    // Gross percents chart
    if (unit.showGrossChart) {
        drawPercentsChart($('#unit_gross_percents_chart').get(0), unit.grossSalesPercent, 'Gross Sales', '#775dd0');
    }

    // Net percents chart
    if (unit.showNetChart) {
        drawPercentsChart($('#unit_net_percents_chart').get(0), unit.netSalesPercent, 'Net Sales', '#feb019');
    }

    // Cost of Sales percents chart
    drawPercentsChart($('#unit_cost_percents_chart').get(0), unit.costOfSalesPercent, 'Cost of Sales', '#ff4560');
    
    // Sales chart
    drawSalesChart($('#sales_chart').get(0), chartCategories, chartData, chartColors);
}

function percentFormat(number) {
    return number + '%';
}

function numberFormat(number) {
    return new Intl.NumberFormat('en-EN').format(number)
}

function drawSalesChart(element, chartCategories, chartData, chartColors) {
    var chartOptions = {
        chart: {
            height: 330,
            type: 'bar',
            toolbar: {
                show: false
            }
        },
        series: [{
            data: chartData
        }],
        colors: chartColors,
        plotOptions: {
            bar: {
                horizontal: true,
                columnWidth: '80%',
                distributed: true
            }
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            show: false
        },
        xaxis: {
            labels: {
                formatter: function (value) {
                    if (value > 1000) {
                        return Math.round(value / 1000) + 'K';
                    }

                    return value;
                }
            },
            categories: chartCategories,
        },
        tooltip: {
            y: {
                formatter: function (value, {series, seriesIndex, dataPointIndex, w}) {
                    return numberFormat(value);
                },
                title: {
                    formatter: function (seriesName) {
                        return '';
                    },
                }
            }
        }
    };

    var chart = new ApexCharts(element, chartOptions);

    chart.render();
}

function drawPercentsChart(element, value, title, color) {
    var chartOptions = {
        chart: {
            height: 300,
            type: 'radialBar',
        },
        series: [value],
        labels: [title],
        colors: [color],
    };

    var chart = new ApexCharts(element, chartOptions);

    chart.render();
}

$(document).ready(function () {
    getReminderData();

    getDashboardData();

    $('#from_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    })

    $('#to_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    })

    $('#from_date_icon').click(function () {
        $("#from_date").datepicker().focus();
    });

    $('#to_date_icon').click(function () {
        $("#to_date").datepicker().focus();
    });

    $("#from_date, #to_date").change(function () {
        getDashboardData();
    });
});
