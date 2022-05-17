@extends('layouts/dashboard_master')

@section('content')
    <section class="panel" id="purchases-report">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Operations Scorecard Report</strong></div>
            </div>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            {!! Form::open(['url' => 'reports/operations-scorecard/grid', 'class' => 'form-horizontal form-bordered', 'id' => 'cliect_feedbact_report_form']) !!}

            <div class="responsive-content">
                <table class="table simpleTable table-hover table-bordered table-striped margin-bottom-0 table-small">
                    <tbody>
                    <tr>
                        <td>Unit</td>
                        <td>
                            {!! Form::select('unit_id', $userUnits, $selectedUnit, ['id' => 'unit_id', 'class'=>'form-control', 'placeholder' => 'Select Unit Name', 'tabindex' => 1, 'autofocus']) !!}
                        </td>
                        <td>From</td>
                        <td>
                            <div class="input-group">
                                {{ Form::text('from_date', $fromDate, array('id' => 'from_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 2, 'readonly' => '')) }}
                                <span class="input-group-addon cursor-pointer" id="from_date_icon">
								<i class="fa fa-calendar"></i>
                            </span>
                            </div>
                        </td>
                        <td>To</td>
                        <td>
                            <div class="input-group">
                                {{ Form::text('to_date', $toDate, array('id' => 'to_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 3, 'readonly' => '')) }}
                                <span class="input-group-addon cursor-pointer" id="to_date_icon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="btn-toolbar margin-top-25">
                <input type='submit' id="submit_btn" class="btn btn-primary btn-md" name='submit' value='Get Report' tabindex='10'/>
            </div>
            {!!Form::close()!!}
        </section>

        <section class="dataTables-padding">
            <div class="row">
                <div class="col-xs-12">
                    <span class="legend label label-danger margin-right-20 margin-bottom-5 pull-left">1-3 Poor – Action Required</span>
                    <span class="legend label label-warning margin-right-20 margin-bottom-5 pull-left">4-6 Below average - Above average</span>
                    <span class="legend label label-success margin-right-20 margin-bottom-5 pull-left">7-9 Meeting expectation - Exceeding expectation</span>
                    <span class="legend label label-primary margin-right-20 margin-bottom-5 pull-left">10 Excellent</span>
                </div>
            </div>
        </section>

        <section class="dataTables-padding">
            <table id="report_data" class="display nowrap" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th></th>
                    <th class="text-align-center">Unit Name</th>
                    <th class="text-align-center">Status</th>
                    <th class="text-align-center">Contract<br/> type</th>
                    <th class="text-align-center">Operations<br/> Manager</th>
                    <th class="text-align-center">Region</th>
                    <th class="text-align-center">Aggregate<br/> score</th>
                    <th class="text-align-center">Ops scorecards<br/> reports</th>
                    <th class="text-align-center">Client<br/> contacts</th>
                    <th class="text-align-center">Customer<br/> Feedback</th>
                    <th class="text-align-center">Budget<br/> Performance</th>
                    <th class="text-align-center">Food Offer /<br/> Presentation</th>
                    <th class="text-align-center">Food Costings /<br/> account<br/> awareness / GP</th>
                    <th class="text-align-center">HR Issues</th>
                    <th class="text-align-center">Staff Morale</th>
                    <th class="text-align-center">Purchasing Compliance,<br/> Check CARS on SAM<br/> against deliveries</th>
                    <th class="text-align-center">Haccp - Check records<br/> for compliance</th>
                    <th class="text-align-center">H&S Compliance /<br/> ISO Audit<br/> completed</th>
                    <th class="text-align-center">Accidents /<br/> Incidents on<br/> site</th>
                    <th class="text-align-center">Site security<br/> and cash control</th>
                    <th class="text-align-center">Marketing / Evidence of<br/> upselling / promotions</th>
                    <th class="text-align-center">Training</th>
                    <th class="text-align-center">GDPR on site</th>
                    <th class="text-align-center">Objectives for<br/> month</th>
                    <th class="text-align-center">Issues<br/> Outstanding</th>
                    <th class="text-align-center">Special Projects /<br/> Functions</th>
                    <th class="text-align-center">Innovation /<br/> chefs What's<br/> App Group</th>
                    <th class="text-align-center">Additional<br/> support<br/> required</th>
                </tr>
                </thead>
                <tbody>
                @foreach($reportData as $reportRow)
                    <tr class="{{ $reportRow['highlightClass'] }}">
                        <td>{{ $reportRow['unitId'] }}</td>
                        <td>{{ $reportRow['unitName'] }}</td>
                        <td>{{ $reportRow['unitStatus'] }}</td>
                        <td>{{ $reportRow['contractType'] }}</td>
                        <td>{{ $reportRow['operationsManager'] }}</td>
                        <td>{{ $reportRow['region'] }}</td>
                        <td>{{ $reportRow['aggregateScore'] }}</td>
                        <td>
                            @if($reportRow['scorecardsReportsTotal'] > 0)
                                <a href="" class="show-reports" data-scorecards="{{ json_encode($reportRow['scorecardsReports']) }}">
                                    {{ $reportRow['scorecardsReportsTotal'] }}
                                </a>
                            @else
                                <span>{{ $reportRow['scorecardsReportsTotal'] }}</span>
                            @endif
                        </td>
                        <td class="vertical-align-middle">
                            @if($reportRow['clientContactsTotal'] > 0)
                                <a href="" class="show-contacts" data-contacts="{{ json_encode($reportRow['clientContacts']) }}">
                                    {{ $reportRow['clientContactsTotal'] }}
                                </a>
                            @else
                                <span>{{ $reportRow['clientContactsTotal'] }}</span>
                            @endif
                        </td>
                        <td>{{ $reportRow['customerFeedback'] }}</td>
                        <td>{{ $reportRow['budgetPerformance'] }}</td>
                        <td>{{ $reportRow['presentation'] }}</td>
                        <td>{{ $reportRow['foodcostAwareness'] }}</td>
                        <td>{{ $reportRow['hrIssues'] }}</td>
                        <td>{{ $reportRow['morale'] }}</td>
                        <td>{{ $reportRow['purchCompliance'] }}</td>
                        <td>{{ $reportRow['haccpCompliance'] }}</td>
                        <td>{{ $reportRow['healthSafetyIso'] }}</td>
                        <td>{{ $reportRow['accidentsIncidents'] }}</td>
                        <td>{{ $reportRow['securityCashControl'] }}</td>
                        <td>{{ $reportRow['marketingUpselling'] }}</td>
                        <td>{{ $reportRow['training'] }}</td>
                        <td>{{ $reportRow['gdpr'] }}</td>
                        <td>
                            @if(count($reportRow['objectives']) > 0)
                                <span>{{ $reportRow['objectives'][0]['value'] }}</span>
                                <a href="" class="show-record" data-title="Objectives for month"
                                   data-record="{{ json_encode($reportRow['objectives']) }}">Show all</a>
                            @endif
                        </td>
                        <td>
                            @if(count($reportRow['outstandingIssues']) > 0)
                                <span>{{ $reportRow['outstandingIssues'][0]['value'] }}</span>
                                <a href="" class="show-record" data-title="Issues Outstanding"
                                   data-record="{{ json_encode($reportRow['objectives']) }}">Show all</a>
                            @endif
                        </td>
                        <td>
                            @if(count($reportRow['spProjectsFunctions']) > 0)
                                <span>{{ $reportRow['spProjectsFunctions'][0]['value'] }}</span>
                                <a href="" class="show-record" data-title="Special Projects / Functions"
                                   data-record="{{ json_encode($reportRow['objectives']) }}">Show all</a>
                            @endif
                        </td>
                        <td>
                            @if(count($reportRow['innovation']) > 0)
                                <span>{{ $reportRow['innovation'][0]['value'] }}</span>
                                <a href="" class="show-record" data-title="Innovation / chefs What's App Group"
                                   data-record="{{ json_encode($reportRow['objectives']) }}">Show all</a>
                            @endif
                        </td>
                        <td>
                            @if(count($reportRow['addSupportReq']) > 0)
                                <span>{{ $reportRow['addSupportReq'][0]['value'] }}</span>
                                <a href="" class="show-record" data-title="Additional support required"
                                   data-record="{{ json_encode($reportRow['objectives']) }}">Show all</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th>
                        <div class="fixed-footer-column"></div>
                    </th>
                    <th>
                        <div class="fixed-footer-column">
                            <input class="column-search unit_search full-width" type="text" placeholder="Search"/>
                        </div>
                    </th>
                    <th></th>
                    <th>
                        <input class="column-search contract_type_search full-width" type="text" placeholder="Search"/>
                    </th>
                    <th>
                        <input class="column-search operation_manager_search full-width" type="text" placeholder="Search"/>
                    </th>
                    <th>
                        <input class="column-search region_search" type="text full-width" placeholder="Search"/>
                    </th>
                    <th colspan="20"></th>
                </tr>
                </tfoot>
            </table>
        </section>
    </section>

    <div id="show_record_modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <style>
        body {
            border: 0
        }

        .dataTables_scroll {
            position: relative
        }

        .dataTables_scrollHead {
            margin-bottom: 40px;
        }

        .DTFC_LeftBodyWrapper {
            margin-top: 40px !important;
        }

        .dataTables_scrollFoot, .DTFC_LeftFootWrapper {
            position: absolute !important;
            top: 74px !important;
        }

        .no-wrap {
            white-space: nowrap;
        }

        .highlight-red, .highlight-yellow, .highlight-green, .highlight-blue {
            color: #000;
        }

        table.dataTable .highlight a, table.dataTable .highlight a:hover {
            color: #000 !important;
        }

        .highlight-red {
            background-color: #FF6C60 !important;
        }

        .highlight-yellow {
            background-color: #FCB322 !important;
        }

        .highlight-green {
            background-color: #A9D86E !important;
        }

        .highlight-blue {
            background-color: #59ace2 !important;
        }

        table.dataTable.display tbody tr.odd > .sorting_1, table.dataTable.order-column.stripe tbody tr.odd > .sorting_1 {
            background: none !important;
        }

        table.dataTable.display tbody tr.even > .sorting_1, table.dataTable.order-column.stripe tbody tr.even > .sorting_1 {
            background: none !important;
        }

        .dt-button-collection.three-column {
            width: 850px !important;;
            margin-left: 0 !important;
            transform: translateX(-50%);
        }

        .legend {
            text-align: left;
            font-size: 14px;
            white-space: pre-wrap;
        }

        .full-width {
            min-width: 100px;
            width: 100%;
        }
        
        table.dataTable thead th {
            text-align: center;
            padding-right: 18px !important;
        }

        table.dataTable tfoot th {
            padding: 8px 10px;
        }

        .DTFC_LeftBodyLiner {
            overflow: hidden;    
        }
        
        table.DTFC_Cloned tfoot th {
            padding: 10px 18px 6px 18px;
            position: relative;
        }

        table.DTFC_Cloned tfoot th .fixed-footer-column {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 8px 10px;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            // Init values
            $('#from_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            }).on('changeDate', function (e) {
                $('#to_date').focus();
            });

            $('#to_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            }).on('changeDate', function () {
                $('#to_date').focus();
            });

            $('#from_date_icon').click(function () {
                $("#from_date").datepicker().focus();
            });

            $('#to_date_icon').click(function () {
                $("#to_date").datepicker().focus();
            });

            $('.show-record').click(function (e) {
                e.preventDefault();

                var title = $(this).data('title');
                var record = $(this).data('record');

                $('#show_record_modal .modal-title').text(title);

                $('#show_record_modal .modal-body').empty();

                $.each(record, function (i, data) {
                    $('#show_record_modal .modal-body')
                        .append(
                            $('<p />')
                                .append(
                                    $('<b />').text(data.date)
                                )
                                .append(
                                    $('<span />')
                                        .addClass('margin-left-20')
                                        .text(data.value)
                                )
                        )
                });

                $('#show_record_modal').modal('show');
            });

            $('.show-contacts').click(function (e) {
                e.preventDefault();

                var contacts = $(this).data('contacts');

                $('#show_record_modal .modal-title').text('Client Contacts');

                $('#show_record_modal .modal-body').empty();

                $.each(contacts, function (i, contact) {
                    $('#show_record_modal .modal-body')
                        .append(
                            $('<p />').text(contact)
                        )
                });

                $('#show_record_modal').modal('show');
            });

            $('.show-reports').click(function (e) {
                e.preventDefault();

                var scorecardIds = $(this).data('scorecards');

                $('#show_record_modal .modal-title').text('Operations Scorecards');

                $('#show_record_modal .modal-body').empty();

                $.each(scorecardIds, function (i, scorecardId) {
                    $('#show_record_modal .modal-body')
                        .append(
                            $('<p />')
                                .append(
                                    $('<a />').attr({
                                        href: '/sheets/operations-scorecard/edit/' + scorecardId,
                                        target: '_blank'
                                    }).text('Operation Scorecard #' + scorecardId)
                                )
                        )
                });

                $('#show_record_modal').modal('show');
            });

            // Apply DataTable plugin
            var oTable = $('#report_data').DataTable({
                scrollX: "true",
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                ordering: 'isSorted',
                order: [],
                columnDefs: [
                    {
                        targets: [{{ $notVisible }}],
                        visible: false,
                    },
                    {
                        targets: [0],
                        orderable: false
                    },
                    {
                        targets: [3],
                        width: "150px"
                    },
                    {
                        targets: [0],
                        className: "text-align-center"
                    },
                    {
                        targets: [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22],
                        className: "text-align-right"
                    },
                ],
                fixedColumns: {
                    leftColumns: 2
                },
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Operations Scorecard Report',
                        filename: 'Excel_operations_scorecard_report_' + currentDate(),
                        customize: function (xlsx) {
                            var new_style = '<?xml version = "1.0" encoding = "UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac" mc:Ignorable="x14ac"><fonts count="5" x14ac:knownFonts="1"><font><sz val="11"/><name val="Calibri"/></font><font><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><sz val="11"/><name val="Calibri"/><b/></font><font><sz val="11"/><name val="Calibri"/><i/></font><font><sz val="11"/><name val="Calibri"/><u/></font></fonts><fills count="6"><fill><patternFill patternType="none"/></fill><fill/><fill><patternFill patternType="solid"><fgColor rgb="FCB322"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF6C60"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="A9D86E"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="59ace2"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border diagonalUp="false" diagonalDown="false"><left style="thin"><color auto="1"/></left><right style="thin"><color auto="1"/></right><top style="thin"><color auto="1"/></top><bottom style="thin"><color auto="1"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="56"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="4" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="5" borderId="0" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="2" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="3" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="4" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment horizontal="left"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment horizontal="center"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment horizontal="right"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment horizontal="fill"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment textRotation="90"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1"><alignment wrapText="1"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles><dxfs count="0"/><tableStyles count="0" defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotStyleMedium4"/></styleSheet>';
                            xlsx.xl['styles.xml'] = $.parseXML(new_style);

                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            var row = 0;

                            $('row', sheet).each(function (x) {
                                if (x > 0) {
                                    if ($(oTable.row(':eq(' + row + ')').node()).hasClass('highlight-red')) {
                                        $('row:nth-child(' + (x + 1) + ') c', sheet).attr('s', '35');
                                    }

                                    if ($(oTable.row(':eq(' + row + ')').node()).hasClass('highlight-yellow')) {
                                        $('row:nth-child(' + (x + 1) + ') c', sheet).attr('s', '30');
                                    }

                                    if ($(oTable.row(':eq(' + row + ')').node()).hasClass('highlight-green')) {
                                        $('row:nth-child(' + (x + 1) + ') c', sheet).attr('s', '40');
                                    }

                                    if ($(oTable.row(':eq(' + row + ')').node()).hasClass('highlight-blue')) {
                                        $('row:nth-child(' + (x + 1) + ') c', sheet).attr('s', '45');
                                    }

                                    row++;
                                }
                            });
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Operations Scorecard Report',
                        filename: 'CSV_operations_scorecard_' + currentDate(),
                    },
                    {
                        extend: 'colvis',
                        collectionLayout: 'fixed three-column',
                        columns: ':not(:first-child)'
                    }
                ],
                language: {
                    search: "Find:"
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false,
            });

            // Apply the search
            $('.unit_search').on('keyup', function () {
                oTable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });
            $('.contract_type_search').on('keyup', function () {
                oTable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });
            $('.operation_manager_search').on('keyup', function () {
                oTable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });
            $('.region_search').on('keyup', function () {
                oTable
                    .columns(5)
                    .search(this.value)
                    .draw();
            });

            // Column visibility
            $('#report_data').on('column-visibility.dt', function (e, settings, column, state) {
                $.ajax({
                    url: "{{ url('/report/column_visibility/toggle') }}",
                    type: "post",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        report_name: 'operations-scorecard',
                        column_index: column,
                    }
                });
            });
        });
    </script>
@stop
