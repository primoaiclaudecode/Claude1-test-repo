@extends('layouts/dashboard_master')

@section('content')
    <section class="panel" id="bsi-report-report">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>BSI Confirm Results</strong></div>
            </div>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            {!! Form::open(['url' => 'accounts/sage-confirm', 'class' => 'form-horizontal form-bordered']) !!}
            <div class="margin-top-25">
                <table id="example" class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Unit Name</th>
                            <th>Supplier</th>
                            <th>A / C</th>
                            <th>Date</th>
                            <th>Inv #</th>
                            <th>Nom Code</th>
                            <th>Net Ext.</th>
                            <th>Details</th>
                            <th>Net</th>
                            <th>Tax Code</th>
                            <th>VAT</th>
                            <th>Gross</th>
                            <th>Transaction Type</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>Total</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <!-- Datatables renders here. -->
                    </tbody>
                </table>
            </div>
            <div class="btn-toolbar">
                <input type='submit' class="btn btn-primary btn-md" name='sage_confirm' value='Sage Confirm' />
                <input type='button' class="btn btn-primary btn-md" name='review_bsi_report' value='Review BSI Report' onclick="window.location='bsi-report'" />
            </div>
            {!!Form::close()!!}
        </section>
    </section>
@stop

@section('scripts')
    <script type="text/javascript" class="init">
        $(document).ready(function() {
            oTable = $('#example').DataTable({
                scrollX: "true",
                scrollCollapse: true,
                dom: 'frtBip',
                "order": [[ 0, "desc" ]],
                processing: true,
                serverSide: true,
                "ajax": ({
                    type: "GET",
                    url :"{{ url('/sage-confirm/json') }}", // json datasource
                    "deferRender": true,
                    error: function() {  // error handling
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display","none");

                    }
                }),
                "columnDefs": [
                    { "targets": [ 3,5,9,12 ], className: "text-align-center" },
                    //{ "targets": [2,3,6], width: "90px" },
                    { "targets": 8, render: $.fn.dataTable.render.number( '', '.', 2, '' ) },
                    { "targets": 10, render: $.fn.dataTable.render.number( '', '.', 2, '' ) },
                    { "targets": 11, render: $.fn.dataTable.render.number( '', '.', 2, '' ) }
                ],
                buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'Batch Suppliers Invoice Report',
                    filename: 'Excel_bsi_report_' + currentDate(),
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6,7,8,9,10,11,12]
                    }
                },
                {
                    extend: 'csvHtml5',
                    title: 'Batch Suppliers Invoice Report',
                    filename: 'CSV_bsi_report_' + currentDate(),
                    exportOptions: {
                        columns: [0,1,2,3,4,5,6,7,8,9,10,11,12]
                    }
                },
                {
                    extend: 'colvis',
                    collectionLayout: 'fixed three-column'
                }
                ],
                "language": {
                    "search": "Find:"
                },
                "pageLength": 150,
                stateSave: true,
                //Batch Suppliers Invoice Report Total in Footer
                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var colNumber = [8,10,11];

                    var intVal = function (i) {
                        return typeof i === 'string' ?
                                i.replace(/[, ₹]|(\.\d{2})/g, "") * 1 :
                                typeof i === 'number' ?
                                i : 0;
                    };
                    for (i = 0; i < colNumber.length; i++) {
                        var colNo = colNumber[i];
                        var total2 = api
                                .column(colNo,{ page: 'current'})
                                .data()
                                .reduce(function (a, b) {
                                    return intVal(a) + intVal(b);
                                }, 0);
                        $(api.column(colNo).footer()).html('€' + total2.toFixed(2));
                    }
                },
                "bSortClasses": false
            });
        });
    </script>
@stop