@extends('layouts.dashboard_master')

@section('styles')
    <link rel="stylesheet" href="{{ elixir('css/dashboard.css') }}">
@stop

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Dashboard</strong>
        </header>

        <div class="panel-body">
            <div class="row">
                <div class="form-group col-lg-6 col-xs-12">
                    <div id="budget_reminder_stats" class="card danger">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fa fa-calendar-plus-o"></i>
                            </div>
                            <p class="card-category">Phased Budget Reminder</p>
                            <h3 class="card-title">0</h3>
                        </div>
                        <div class="card-footer">
                            <a href="" data-toggle="modal" data-target="#budgets_modal">Show expired budgets</a>
                        </div>
                    </div>
                </div>

                <div class="form-group col-lg-6 col-xs-12">
                    <div id="problem_reminder_stats" class="card warning">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fa fa-exclamation-triangle"></i>
                            </div>
                            <p class="card-category">Corrective Action Reminder</p>
                            <h3 class="card-title">0</h3>
                        </div>
                        <div class="card-footer">
                            <a href="" data-toggle="modal" data-target="#problems_modal">Show report details</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-xs-12">
                    <div class="chart-card">
                        <div class="row">
                            <div class="form-group col-lg-6 col-xs-12">
                                <label class="col-xs-3 control-label custom-labels">From Date:</label>
                                <div class="col-xs-9">
                                    <div class="input-group">
                                        {{ Form::text('from_date', $fromDate, array('id' => 'from_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 1, 'readonly' => '')) }}
                                        <span class="input-group-addon cursor-pointer" id="from_date_icon">
                                            <i class="fa fa-calendar"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-lg-6 col-xs-12">
                                <label class="col-xs-3 control-label custom-labels">To Date:</label>
                                <div class="col-xs-9">
                                    <div class="input-group">
                                        {{ Form::text('from_date', $toDate, array('id' => 'to_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 2, 'readonly' => '')) }}
                                        <span class="input-group-addon cursor-pointer" id="to_date_icon">
                            				<i class="fa fa-calendar"></i>
                        				</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div id="chart" class="col-xs-12"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-xs-12">
                    <div class="responsive-content">
                        <table id="dashboard_data_table" class="table table-hover table-bordered table-striped">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="text-center">Gross Sales</th>
                            <th class="text-center">Net Sales</th>
                            <th class="text-center">Cost of Sales</th>
                            <th class="text-center">Cleaning & Disp.</th>
                            <th class="text-center">GP % (Gross)</th>
                            <th class="text-center">GP % (Net)</th>
                            <th class="text-center">Latest Entry (Purch)</th>
                            <th class="text-center">Latest Entry (Sales)</th>
                            <th class="text-center">Cash Lodgements</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="budgets_modal" class="modal fade danger" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Phased Budget</h4>
                    </div>
                    <div id="budgets_reminder" class="modal-body"></div>
                </div>
            </div>
        </div>

        <div id="problems_modal" class="modal fade danger" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Corrective Action</h4>
                    </div>
                    <div id="problems_reminder" class="modal-body"></div>
                </div>
            </div>
        </div>

        <div id="chart_modal" class="modal fade danger" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title"></h4>
                    </div>
                    <div id="unit_chart" class="modal-body"></div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/apexcharts.js') }}"></script>
    <script src="{{ elixir('js/dashboard.js') }}"></script>
@stop
