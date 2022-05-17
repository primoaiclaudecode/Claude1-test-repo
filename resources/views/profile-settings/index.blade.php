@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Side Menu</strong></div>
            </div>
        </header>

        <section class="dataTables-padding">
            <form id="profile_settings" class="form-horizontal form-bordered">
                <div class="form-group">
                    <label class="col-lg-2 col-sm-2 col-md-2 col-xs-3 control-label custom-labels">Show sidebar:</label>
                    <div class="col-lg-5 col-sm-4 col-md-4 col-xs-9 ">
                        <label id="toggle_sidebar" class="switch">
                            <input type="checkbox" {{ $showSidebar ? "checked" : ""}}>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </form>
        </section>
    </section>
    
    <section class="panel">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Favourites Menu</strong></div>
            </div>
        </header>

        <section class="dataTables-padding">
            <form id="add_link_frm" class="form-horizontal form-bordered">
                <div class="form-group">
                    <label class="col-lg-2 col-sm-2 col-md-2 col-xs-3 control-label custom-labels">Menu item:</label>
                    <div class="col-lg-5 col-sm-4 col-md-4 col-xs-9 ">
                        <select id="link" class="form-control margin-bottom-15">
                            <option value="" selected>Select menu item...</option>
                            
                            <option value="/dashboard">{{ $menuLinkTitles['/dashboard'] }}</option>

                            @can('admin-user-group')
                                <optgroup label="Administration">
                                    @can('su-user-group')
                                        <option value="/events">{{ $menuLinkTitles['/events'] }}</option>
                                        <option value="/netexts">{{ $menuLinkTitles['/netexts'] }}</option>
                                    @endcan

                                    <option value="/regions">{{ $menuLinkTitles['/regions'] }}</option>
                                    <option value="/registers">{{ $menuLinkTitles['/registers'] }}</option>
                                    <option value="/suppliers">{{ $menuLinkTitles['/suppliers'] }}</option>

                                    @can('su-user-group')
                                        <option value="/taxcodes">{{ $menuLinkTitles['/taxcodes'] }}</option>
                                    @endcan

                                    <option value="/units">{{ $menuLinkTitles['/units'] }}</option>
                                    <option value="/users">{{ $menuLinkTitles['/users'] }}</option>
                                    <option value="/vendings">{{ $menuLinkTitles['/vendings'] }}</option>
                                </optgroup>
                            @endcan

                            @can('hq-user-group')
                                <optgroup label="Accounts">
                                    <option value="/accounts/bsi-report">{{ $menuLinkTitles['/accounts/bsi-report'] }}</option>
                                    <option value="/accounts/sage-confirm">{{ $menuLinkTitles['/accounts/sage-confirm'] }}</option>
                                    <option value="/accounts/statement-check">{{ $menuLinkTitles['/accounts/statement-check'] }}</option>
                                    <option value="/accounts/unit-month-end-closing">{{ $menuLinkTitles['/accounts/unit-month-end-closing'] }}</option>
                                </optgroup>
                            @endcan

                            <optgroup label="Sheets">
                                <option value="/sheets/purchases/cash">{{ $menuLinkTitles['/sheets/purchases/cash'] }}</option>
                                <option value="/sheets/cash-sales">{{ $menuLinkTitles['/sheets/cash-sales'] }}</option>

                                @can('operations-user-group')
                                    <option value="/sheets/customer-feedback">{{ $menuLinkTitles['/sheets/customer-feedback'] }}</option>
                                @endcan

                                <option value="/sheets/problem-report">{{ $menuLinkTitles['/sheets/problem-report'] }}</option>
                                <option value="/sheets/purchases/credit">{{ $menuLinkTitles['/sheets/purchases/credit'] }}</option>
                                <option value="/sheets/credit-sales">{{ $menuLinkTitles['/sheets/credit-sales'] }}</option>
                                <option value="/sheets/labour-hours">{{ $menuLinkTitles['/sheets/labour-hours'] }}</option>
                                <option value="/sheets/lodgements">{{ $menuLinkTitles['/sheets/lodgements'] }}</option>

                                @can('operations-user-group')
                                    <option value="/sheets/operations-scorecard">{{ $menuLinkTitles['/sheets/operations-scorecard'] }}</option>
                                @endcan

                                @can('hq-user-group')
                                    <option value="/sheets/phased-budget">{{ $menuLinkTitles['/sheets/phased-budget'] }}</option>
                                @endcan

                                <option value="/sheets/stock-control">{{ $menuLinkTitles['/sheets/stock-control'] }}</option>
                                <option value="/sheets/vending-sales">{{ $menuLinkTitles['/sheets/vending-sales'] }}</option>
                            </optgroup>

                            <optgroup label="Reports">
                                <option value="/reports/cash-sales">{{ $menuLinkTitles['/reports/cash-sales'] }}</option>
                                <option value="/reports/client-feedback">{{ $menuLinkTitles['/reports/client-feedback'] }}</option>
                                <option value="/reports/problem-report">{{ $menuLinkTitles['/reports/problem-report'] }}</option>
                                <option value="/reports/credit-sales">{{ $menuLinkTitles['/reports/credit-sales'] }}</option>
                                <option value="/reports/labour-hours">{{ $menuLinkTitles['/reports/labour-hours'] }}</option>
                                <option value="/reports/lodgements">{{ $menuLinkTitles['/reports/lodgements'] }}</option>

                                @if($allowsOperationsScorecard)
                                    <option value="/reports/operations-scorecard">{{ $menuLinkTitles['/reports/operations-scorecard'] }}</option>
                                @endif

                                <option value="/reports/purchases">{{ $menuLinkTitles['/reports/purchases'] }}</option>

                                @can('hq-user-group')
                                    <option value="/reports/purchases-summary">{{ $menuLinkTitles['/reports/purchases-summary'] }}</option>
                                @endcan

                                <option value="/reports/sales-summary">{{ $menuLinkTitles['/reports/sales-summary'] }}</option>
                                <option value="/reports/stock-control">{{ $menuLinkTitles['/reports/stock-control'] }}</option>
                                <option value="/reports/unit-trading-account">{{ $menuLinkTitles['/reports/unit-trading-account'] }}</option>
                                <option value="/reports/unit-trading-account-stock">{{ $menuLinkTitles['/reports/unit-trading-account-stock'] }}</option>
                                <option value="/reports/vending-sales">{{ $menuLinkTitles['/reports/vending-sales'] }}</option>
                            </optgroup>

                            <optgroup label="Files">
                                @can('su-user-group')
                                    <option value="/files">{{ $menuLinkTitles['/files'] }}</option>
                                @endcan

                                @foreach($rootDirNames as $dirName)
                                    <option value="/files/{{ $dirName->id }}">{{ $dirName->dir_file_name }}</option>
                                @endforeach
                            </optgroup>

                        </select>
                    </div>

                    <label class="col-lg-1 col-sm-2 col-md-2 col-xs-3 control-label custom-labels">Position:</label>
                    <div class="col-lg-2 col-sm-2 col-md-2 col-xs-9">
                        <select id="position" class="form-control margin-bottom-15"></select>
                    </div>

                    <div class="col-lg-2 col-sm-2 col-md-2 col-xs-12">
                        <button class="btn btn-primary btn-block button">ADD</button>
                    </div>
                </div>

                <div id="error_message" class="alert alert-danger hidden" role="alert"></div>
            </form>
        </section>

        <section class="dataTables-padding">
            <ul id="user_menu" class="list-group">
            </ul>
        </section>

        <div id="edit_position_modal" class="modal fade danger" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Edit position</h4>
                    </div>
                    <div id="unit_chart" class="modal-body">
                        <input id="link_id" type="hidden" value="0" />

                        <div class="form-group">
                            <select id="new_position" class="form-control margin-bottom-15"></select>
                        </div>

                        <div class="form-group">
                            <button id="save_position" class="btn btn-primary btn-block button">SAVE</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop

@section('scripts')
    <style>
        .list-group-item {
            display: flex;
            justify-content: space-between;
        }

        .link-position {
            text-align: left;
            flex-basis: 50px;
            margin-right: 15px;
            text-align: center;
            cursor: pointer;
        }

        .link-title {
            text-align: left;
            flex: 1;
        }

        .delete-link {
            text-align: left;
            flex: 0;
            font-size: 18px;
            color: #FF6C60;
            cursor: pointer;
        }
        
        .added {
            color: #5cb85c;
        }
        
        .list-group-item:hover {
            background-color: #f5f5f5;
        }
        
        /* Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 43px;
            height: 25px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
            margin-top: 0;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 17px;
            width: 17px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #A9D86E;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #A9D86E;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(17px);
            -ms-transform: translateX(17px);
            transform: translateX(17px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 100%;
        }        
    </style>
    
    <script src="{{asset('js/profile-settings.js')}}"></script>
@stop