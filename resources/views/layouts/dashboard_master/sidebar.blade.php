<!--sidebar start-->
@php
    use App\Libraries\MenuHelper;use App\Menu;
    $isSuLevel = Gate::allows('su-user-group');
    $allowsOperationsScorecard = Gate::allows('admin-user-group') || Gate::allows('management-user-group');
    $rootDirNamesArr = MenuHelper::getRootDirNames();
    $menu = MenuHelper::getMenuList();
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
                        @foreach($menu->get('administration') as $item)
                            @include('layouts.dashboard_master.menu-item', ['item' => $item])
                        @endforeach
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
                        @foreach($menu->get('accounts') as $item)
                            @include('layouts.dashboard_master.menu-item', ['item' => $item])
                        @endforeach
                    </ul>
                </li>
            @endcan
            <li class="sub-menu">
                <a href="javascript:;" class="{{ Helpers::getControllerName() == 'SheetController' ? 'active' : '' }}">
                    <i class="fa fa-table"></i>
                    <span>Sheets</span>
                </a>
                <ul class="sub">
                    @foreach($menu->get('sheets') as $item)
                        @include('layouts.dashboard_master.menu-item', ['item' => $item])
                    @endforeach
                </ul>
            </li>
            @can('unit-user-group')
                <li class="sub-menu">
                    <a href="javascript:;" class="{{ Helpers::getControllerName() == 'ReportController' ? 'active' : '' }}">
                        <i class="fa fa-bar-chart-o"></i>
                        <span>Reports</span>
                    </a>
                    <ul class="sub">
                        @foreach($menu->get('reports') as $item)
                            @include('layouts.dashboard_master.menu-item', ['item' => $item])
                        @endforeach
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
                    <li>
                        <a href="unit_trading_account_report.php" target="_blank">Corporate Catering</a>
                    </li>
                    <li>
                        <a href="https://www.glanbiacficustomerserv.ie/" target="_blank">Glanbia</a>
                    </li>
                    <li>
                        <a href="https://apps.oneposting.com/memberszone/login/loginmain.aspx" target="_blank">One Posting</a>
                    </li>
                </ul>
            </li>
        </ul>
        <!-- sidebar menu end-->
    </div>
</aside><!--sidebar end-->
