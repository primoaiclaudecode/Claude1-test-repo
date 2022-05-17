@extends('layouts/dashboard_master')

@section('content')
    <section class="panel" id="purchases-report">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Sales Summary Report</strong></div>
            </div>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            <table id="example" class="display nowrap" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Sale Type</th>
                    <th>Date of Entry</th>
                    <th>Unit Name</th>
                    <th>Supervisor</th>
                    <th>Reg Number</th>
                    <th>Machine Name</th>
                    <th>Sale Date</th>
                    <th>Z Number</th>
                    <th>Z Food</th>
                    <th>Z Conf. Food</th>
                    <th>Z Fruit Juice</th>
                    <th>Z Minerals</th>
                    <th>Z Confectionary</th>
                    <th>Cash Count</th>
                    <th>Credit Card</th>
                    <th>Staff Card</th>
                    <th>Total Receipts</th>
                    <th>Z Read</th>
                    <th>Cash Purchase</th>
                    <th>Credit Sale</th>
                    @foreach($goods as $good)
                        <th>{{ $good }}</th>
                    @endforeach
                    <th>Vend Total</th>
                    <th>Total Sales</th>
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
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    @foreach($goods as $good)
                        <th></th>
                    @endforeach
                    <th></th>
                    <th></th>
                </tr>
                </tfoot>
                <tbody>
                </tbody>
            </table>
        </section>
    </section>
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

        .dataTables_scrollFoot {
            position: absolute;
            top: 38px
        }

        #select_all {
            margin-top: 0;
        }
        
        .full-width {
            min-width: 100px;
            width: 100%;
        }
        
        .amount-cell {
            padding-left: 10px !important;
	        padding-right: 10px !important;
            text-align: right;
        }

        table.dataTable thead th {
            text-align: center;
            padding-right: 18px !important;
        }

        table.dataTable tfoot th {
            padding: 8px 10px;
        }        
    </style>
    <script type="text/javascript" class="init">
        $(document).ready(function () {
            $('#example tfoot th').each(function () {
                var title = $(this).text();
                var className = 'full-width';
                $(this).html('<input type="text" placeholder="Search ' + title + '" class="' + className + '" />');
            });

            var goodColumns = {{ $goodColumns }};
            
            oTable = $('#example').DataTable({
                scrollX: "true",
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                order: [[1, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    type: "GET",
                    url: "{{ url('/sales-summary/json') }}",
                    data: {
                        unit_id: '{{ $unitId ?: null }}',
                        from_date: '{{ $fromDate }}',
                        to_date: '{{ $toDate }}',
                        all_records: {{ $allRecords }},
                    },
                    deferRender: true,
                    error: function () {
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display", "none");
                    }
                }),
                columnDefs: [
                    {
                        targets: [{{ $notVisiable }}], 
                        visible: false,
                    },
                    {
                        targets: [0, 1, 6], 
                        className: "text-align-center"
                    },
                    {
                        targets: [7], className: "text-align-right"
                    },
                    {
                        targets: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19],
                        className: "amount-cell",                        
                        render: $.fn.dataTable.render.number('', '.', 2, ''),
                        searchable: false
                    },
                    {
                        targets: goodColumns,
                        className: "amount-cell",
                        render: $.fn.dataTable.render.number('', '.', 2, ''),
                        searchable: false
                    }
                ],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Cash Sales Report',
                        filename: 'Excel_sales_summary_report_' + currentDate(),
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Cash Sales Report',
                        filename: 'CSV_sales_summary_report_' + currentDate(),
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'colvis',
                        collectionLayout: 'fixed three-column',
                    }
                ],
                language: {
                    search: "Find:"
                },
                pageLength: 10,
                stateSave: false,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var colNumber = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19].concat(goodColumns);

                    for (i = 0; i < colNumber.length; i++) {
                        var colNo = colNumber[i];
                        var total2 = api
                            .column(colNo, {page: 'current'})
                            .data()
                            .reduce(function (a, b) {
                                return parseFloat(a) + parseFloat(b);
                            }, 0);
                        $(api.column(colNo).footer()).html('€' + total2.toFixed(2));
                    }
                },
                bSortClasses: false
            });
            
            // Apply the search
            oTable.columns().every(function () {
                var that = this;

                $('input', this.footer()).on('keyup change', function () {
                    if (that.search() !== this.value) {
                        that
                            .search(this.value)
                            .draw();
                    }
                });
            });

            // Column visibility
            $('#example').on('column-visibility.dt', function (e, settings, column, state) {
                $.ajax({
                    url: "{{ url('/report/column_visibility/toggle') }}",
                    type: "post",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        report_name: 'sales_summary',
                        column_index: column,
                    }
                });
            });
        });
    </script>
@stop
