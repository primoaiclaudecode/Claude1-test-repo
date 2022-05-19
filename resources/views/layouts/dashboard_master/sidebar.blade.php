<!--sidebar start-->
@php
    $isSuLevel = Gate::allows('su-user-group');
    $allowsOperationsScorecard = Gate::allows('admin-user-group') || Gate::allows('management-user-group');
    $rootDirNamesArr = Helpers::rootDirNames();
@endphp
<aside>
    <div id="sidebar" class="nav-collapse" style="display: none">
        <!-- sidebar menu start-->
        <ul class="sidebar-menu" id="nav-accordion">
            <i class="fa fa-close toggle-mobile-menu"></i>

            <li>
                <a class="{{ Helpers::getControllerName() == 'HomeController' ? 'active' : '' }}" href="{{ url('/home') }}">
                    <i class="fa fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            @can('admin-user-group')
                <li class="sub-menu">
                    <a href="javascript:;"
                       class="{{ Helpers::getControllerName() == 'UserController' || Helpers::getControllerName() == 'UnitController' || Helpers::getControllerName() == 'RegionController' || Helpers::getControllerName() == 'SupplierController' ? 'active' : '' || Helpers::getControllerName() == 'RegisterController' ? 'active' : '' || Helpers::getControllerName() == 'VendingController' ? 'active' : '' || Helpers::getControllerName() == 'NetExtController' ? 'active' : '' || Helpers::getControllerName() == 'TaxCodeController' ? 'active' : '' || Helpers::getControllerName() == 'EventController' ? 'active' : '' || Helpers::getControllerName() == 'CurrencyController' ? 'active' : '' || Helpers::getControllerName() == 'ExchangeRateController' ? 'active' : ''}}">
                        <i class="fa fa-user"></i>
                        <span>Administration</span>
                    </a>
                    <ul class="sub">
                        @can('su-user-group')
		                    <li class="{{ Helpers::getActionName() == 'currencys.index' || Helpers::getActionName() == 'currencys.create' || Helpers::getActionName() == 'currencys.edit' ? 'active' : '' }}">
			                    <a href="{{ url('/currencies') }}">Currency Management</a>
		                    </li>
                            <li class="{{ Helpers::getActionName() == 'events.index' ? 'active' : '' }}">
                                <a href="{{ url('/events') }}">Events</a>
                            </li>
		                    <li class="{{ Helpers::getActionName() == 'exchangerates.index' || Helpers::getActionName() == 'exchangerates.create' || Helpers::getActionName() == 'exchangerates.edit' ? 'active' : '' }}">
			                    <a href="{{ url('/exchange-rates') }}">Exchange Rate Management</a>
		                    </li>
                            <li class="{{ Helpers::getActionName() == 'netexts.index' || Helpers::getActionName() == 'netexts.create' || Helpers::getActionName() == 'netexts.edit' ? 'active' : '' }}">
                                <a href="{{ url('/netexts') }}">Net Ext Management</a>
                            </li>
                        @endcan

                        <li class="{{ Helpers::getActionName() == 'regions.index' || Helpers::getActionName() == 'regions.create' || Helpers::getActionName() == 'regions.edit' ? 'active' : '' }}">
                            <a href="{{ url('/regions') }}">Region Management</a>
                        </li>
                        <li class="{{ Helpers::getActionName() == 'registers.index' || Helpers::getActionName() == 'registers.create' || Helpers::getActionName() == 'registers.edit' ? 'active' : '' }}">
                            <a href="{{ url('/registers') }}">Register Management</a>
                        </li>
                        <li class="{{ Helpers::getActionName() == 'suppliers.index' || Helpers::getActionName() == 'suppliers.create' || Helpers::getActionName() == 'suppliers.edit' ? 'active' : '' }}">
                            <a href="{{ url('/suppliers') }}">Supplier Management</a>
                        </li>

                        @can('su-user-group')
                            <li class="{{ Helpers::getActionName() == 'taxcodes.index' || Helpers::getActionName() == 'taxcodes.create' || Helpers::getActionName() == 'taxcodes.edit' ? 'active' : '' }}">
                                <a href="{{ url('/taxcodes') }}">Tax Code Management</a>
                            </li>
                        @endcan

                        <li class="{{ Helpers::getActionName() == 'units.index' || Helpers::getActionName() == 'units.create' || Helpers::getActionName() == 'units.edit' ? 'active' : '' }}">
                            <a href="{{ url('/units') }}">Unit Management</a>
                        </li>
                        <li class="{{ Helpers::getActionName() == 'users.index' || Helpers::getActionName() == 'users.create' || Helpers::getActionName() == 'users.edit' ? 'active' : '' }}">
                            <a href="{{ url('/users') }}">User Management</a>
                        </li>
                        <li class="{{ Helpers::getActionName() == 'vendings.index' || Helpers::getActionName() == 'vendings.create' || Helpers::getActionName() == 'vendings.edit' ? 'active' : '' }}">
                            <a href="{{ url('/vendings') }}">Vending Management</a>
                        </li>
                    </ul>
                </li>
            @endcan

            @can('hq-user-group')
                <li class="sub-menu">
                    <a href="javascript:;" class="{{ Helpers::getControllerName() == 'AccountsController' ? 'active' : '' }}">
                        <i class="fa fa-adn"></i>
                        <span>Accounts</span>
                    </a>
                    <ul class="sub">
                        <li class="{{ Helpers::getActionName() == 'accountss.bsireport' || Helpers::getActionName() == 'accountss.bsireportgrid' ? 'active' : '' }}">
                            <a href="{{ url('/accounts/bsi-report') }}">BSI Report</a>
                        </li>
                        <li class="{{ Helpers::getActionName() == 'accountss.sageconfirm' || Helpers::getActionName() == 'accountss.sageconfirmgrid' ? 'active' : '' }}">
                            <a href="{{ url('/accounts/sage-confirm') }}">Sage Confirmation</a>
                        </li>
                        <li class="{{ Helpers::getActionName() == 'accountss.statementcheck' ? 'active' : '' }}">
                            <a href="{{ url('/accounts/statement-check') }}">Statement Check</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'accountss.unitmonthendclosing' || Helpers::getActionName() == 'accountss.unitmonthendclosingconfimation') ? 'active' : '' }}">
                            <a href="{{ url('/accounts/unit-month-end-closing') }}">Unit Month End Closing</a>
                        </li>
                    </ul>
                </li>
            @endcan

            <li class="sub-menu">
                <a href="javascript:;" class="{{ Helpers::getControllerName() == 'SheetController' ? 'active' : '' }}">
                    <i class="fa fa-table"></i>
                    <span>Sheets</span>
                </a>

                <ul class="sub">
                    <li class="{{ (Helpers::getActionName() == 'sheets.purchase' || Helpers::getActionName() == 'sheets.purchaseconfirmation') && $purchType == 'cash' ? 'active' : '' }}">
                        <a href="{{ url('/sheets/purchases/cash') }}">Cash Purchases</a>
                    </li>
                    <li class="{{ (Helpers::getActionName() == 'sheets.cashsales' || Helpers::getActionName() == 'sheets.cashsalesconfimation') ? 'active' : '' }}">
                        <a href="{{ url('/sheets/cash-sales') }}">Cash Sales</a>
                    </li>

                    @can('operations-user-group')
                        <li class="{{ (Helpers::getActionName() == 'sheets.customerfeedback' || Helpers::getActionName() == 'sheets.customerfeedbackconfirmation') ? 'active' : '' }}">
                            <a href="{{ url('/sheets/customer-feedback') }}">Client Feedback</a>
                        </li>
                    @endcan

                    <li class="{{ (Helpers::getActionName() == 'sheets.problemreport' || Helpers::getActionName() == 'sheets.problemreportconfimation') ? 'active' : '' }}">
                        <a href="{{ url('/sheets/problem-report') }}">Corrective Action Report</a>
                    </li>
                    <li class="{{ (Helpers::getActionName() == 'sheets.purchase' || Helpers::getActionName() == 'sheets.purchaseconfirmation') && $purchType == 'credit' ? 'active' : '' }}">
                        <a href="{{ url('/sheets/purchases/credit') }}">Credit Purchases</a>
                    </li>
                    <li class="{{ (Helpers::getActionName() == 'sheets.creditsales' || Helpers::getActionName() == 'sheets.creditsalesconfirmation') ? 'active' : '' }}">
                        <a href="{{ url('/sheets/credit-sales') }}">Credit Sales</a>
                    </li>
                    <li class="{{ (Helpers::getActionName() == 'sheets.labourhours' || Helpers::getActionName() == 'sheets.labourhoursconfimation') ? 'active' : '' }}">
                        <a href="{{ url('/sheets/labour-hours') }}">Labour Hours</a>
                    </li>
                    <li class="{{ (Helpers::getActionName() == 'sheets.lodgements' || Helpers::getActionName() == 'sheets.lodgementsconfirmation') ? 'active' : '' }}">
                        <a href="{{ url('/sheets/lodgements') }}">Lodgements</a>
                    </li>

                    @can('operations-user-group')
                        <li class="{{ (Helpers::getActionName() == 'sheets.operationsscorecard' || Helpers::getActionName() == 'sheets.operationsscorecardconfirmation' || Helpers::getActionName() == 'sheets.operationsscorecardedit') ? 'active' : '' }}">
                            <a href="{{ url('/sheets/operations-scorecard') }}">Operations Scorecard</a>
                        </li>
                    @endcan

                    @can('hq-user-group')
                        <li class="{{ (Helpers::getActionName() == 'sheets.phasedbudget' || Helpers::getActionName() == 'sheets.phasedbudgetconfimation') ? 'active' : '' }}">
                            <a href="{{ url('/sheets/phased-budget') }}">Phased Budget</a>
                        </li>
                    @endcan

                    <li class="{{ (Helpers::getActionName() == 'sheets.stockcontrol' || Helpers::getActionName() == 'sheets.stockcontrolconfimation') ? 'active' : '' }}">
                        <a href="{{ url('/sheets/stock-control') }}">Stock Control</a>
                    </li>
                    <li class="{{ (Helpers::getActionName() == 'sheets.vendingsales' || Helpers::getActionName() == 'sheets.vendingsalesconfirmation') ? 'active' : '' }}">
                        <a href="{{ url('/sheets/vending-sales') }}">Vending Sales</a>
                    </li>
                </ul>
            </li>

            @can('unit-user-group')
                <li class="sub-menu">
                    <a href="javascript:;" class="{{ Helpers::getControllerName() == 'ReportController' ? 'active' : '' }}">
                        <i class="fa fa-bar-chart-o"></i>
                        <span>Reports</span>
                    </a>

                    <ul class="sub">
                        <li class="{{ (Helpers::getActionName() == 'reports.cashsales' || Helpers::getActionName() == 'reports.cashsalesgrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/cash-sales') }}">Cash Sales Report</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'reports.clientfeedback' || Helpers::getActionName() == 'reports.clientfeedbackgrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/client-feedback') }}">Client Feedback Report</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'reports.problemreport' || Helpers::getActionName() == 'reports.problemreportgrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/problem-report') }}">Corrective Action Report</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'reports.creditsales' || Helpers::getActionName() == 'reports.creditsalesgrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/credit-sales') }}">Credit Sales Report</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'reports.labourhours' || Helpers::getActionName() == 'reports.labourhoursgrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/labour-hours') }}">Labour Hours Report</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'reports.lodgements' || Helpers::getActionName() == 'reports.lodgementsgrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/lodgements') }}">Lodgements Report</a>
                        </li>

                        @if($allowsOperationsScorecard)
                            <li class="{{ (Helpers::getActionName() == 'reports.operationsscorecard' || Helpers::getActionName() == 'reports.operationsscorecardgrid') ? 'active' : '' }}">
                                <a href="{{ url('/reports/operations-scorecard') }}">Operations Scorecard Report</a>
                            </li>
                        @endif

                        <li class="{{ (Helpers::getActionName() == 'reports.purchases' || Helpers::getActionName() == 'reports.purchasesgrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/purchases') }}">Purchases Report</a>
                        </li>

                        @can('hq-user-group')
                            <li class="{{ (Helpers::getActionName() == 'reports.purchasessummary' || Helpers::getActionName() == 'reports.purchasessummarygrid') ? 'active' : '' }}">
                                <a href="{{ url('/reports/purchases-summary') }}">Purchases Summary Report</a>
                            </li>
                        @endcan

                        <li class="{{ (Helpers::getActionName() == 'reports.salessummary' || Helpers::getActionName() == 'reports.salessummarygrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/sales-summary') }}">Sales Summary Report</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'reports.stockcontrol' || Helpers::getActionName() == 'reports.stockcontrolgrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/stock-control') }}">Stock Control Report</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'reports.unittradingaccount' || Helpers::getActionName() == 'reports.postunittradingaccount') ? 'active' : '' }}">
                            <a href="{{ url('/reports/unit-trading-account') }}">UTA Report</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'reports.unittradingaccountstock' || Helpers::getActionName() == 'reports.postunittradingaccountstock') ? 'active' : '' }}">
                            <a href="{{ url('/reports/unit-trading-account-stock') }}">UTA + Stock Report</a>
                        </li>
                        <li class="{{ (Helpers::getActionName() == 'reports.vendingsales' || Helpers::getActionName() == 'reports.vendingsalesgrid') ? 'active' : '' }}">
                            <a href="{{ url('/reports/vending-sales') }}">Vending Sales Report</a>
                        </li>
                    </ul>
                </li>
            @endcan

            <li class="sub-menu">
                <a href="javascript:;" class="{{ Helpers::getControllerName() == 'FileController' ? 'active' : '' }}">
                    <i class="fa fa-file"></i>
                    <span>Files</span>
                </a>

                <ul class="sub">
                    @can('su-user-group')
                        <li class="{{ (Helpers::getActionName() == 'files.index' && isset($dirId) && $dirId == 0) ? 'active' : '' }}">
                            <a href="{{ url('/files') }}">Files</a>
                        </li>
                    @endcan

                    @foreach($rootDirNamesArr as $rdn)
                        <li class="{{ $isSuLevel ? 'padding-left-50' : '' }} {{ Helpers::getActionName() == 'files.index' && isset($dirId) && $dirId == $rdn->id ? 'active' : '' }}">
                            <a href="{{ url('/files/'.$rdn->id) }}">{{ $rdn->dir_file_name }}</a>
                        </li>
                    @endforeach
                </ul>
            </li>

            <li class="sub-menu">
                <a href="javascript:;">
                    <i class="fa fa-external-link"></i>
                    <span>External Links</span>
                </a>

                <ul class="sub">
                    <li><a href="unit_trading_account_report.php" target="_blank">Corporate Catering</a></li>
                    <li><a href="https://www.glanbiacficustomerserv.ie/" target="_blank">Glanbia</a></li>
                    <li><a href="https://apps.oneposting.com/memberszone/login/loginmain.aspx" target="_blank">One Posting</a></li>
                </ul>
            </li>
        </ul>
        <!-- sidebar menu end-->
    </div>
</aside>
<!--sidebar end-->
