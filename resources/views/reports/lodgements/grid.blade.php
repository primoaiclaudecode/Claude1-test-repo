@extends('layouts/dashboard_master')

@section('content')
    <section class="panel" id="purchases-report">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Lodgements Report</strong></div>
            </div>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            <table id="example" class="display nowrap" cellspacing="0" width="100%">
                <thead>
                <tr>
                    @if($isSuLevel)
                        <th class="text-align-center">
                            <input type="checkbox" id="select_all"/>
                        </th>
                    @endif
                    <th>ID</th>
                    <th>Date</th>
                    <th>Unit Name</th>
                    <th>Supervisor</th>
                    <th>Slip Number</th>
                    <th>G4S Bag Number</th>
                    @foreach($currencies as $currency)
                        <th data-symbol="{{ $currency->currency_symbol }}">Cash {{ $currency->currency_code }}</th>
                        <th data-symbol="{{ $currency->currency_symbol }}">Coin {{ $currency->currency_code }}</th>
                    @endforeach
                    <th data-symbol="{{ $currencySymbol }}">Cash Total</th>
                    <th data-symbol="{{ $currencySymbol }}">Coin Total</th>
                    <th>Remarks</th>
                    <th class="text-align-center">Action</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    @if($isSuLevel)
                        <th class="no-search"></th>
                    @endif
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    @foreach($currencies as $currency)
                        <th></th>
                        <th></th>
                    @endforeach
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="no-search"></th>
                </tr>
                </tfoot>
                <tbody>
                <!-- Datatables renders here. -->
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
            $('#example tfoot th:not(.no-search)').each(function () {
                var title = $(this).text();
                var className = 'full-width';
                $(this).html('<input type="text" placeholder="Search ' + title + '" class="' + className + '" />');
            });

            var costColumns = {{ $costColumns }};
            
            @if($isSuLevel)
                oTable = $('#example').DataTable({
                scrollX: true,
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                order: [[1, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    type: "GET",
                    url: "{{ url('/lodgements/json') }}", // json datasource
                    data: {
                        unit_id: '{{ $unitId ?: null }}',
                        from_date: '{{ $fromDate }}',
                        to_date: '{{ $toDate }}',
                        all_records: {{ $allRecords }}
                    },
                    deferRender: true,
                    error: function () {  // error handling
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
                        targets: [0, -1], 
                        bSortable: false
                    },
                    {
                        targets: [1],
                        width: '50px'
                    },
                    {
                        targets: [0, 1, 2, -1], 
                        className: "text-align-center"
                    },
                    {
                        targets: costColumns, 
                        render: $.fn.dataTable.render.number('', '.', 2, ''),
                        className: "amount-cell",
                        searchable: false
                    },
                ],
                buttons: [
                    {
                        text: 'Delete',
                        className: 'red',
                        action: function (e, dt, node, config) {
                            var itemsCount = $('.checkboxs:checked').length;
                            var itemsToDel = itemsCount == 1 ? 'this lodgements record' : 'these lodgements records';
                            var result = confirm("Do you want to delete " + itemsToDel + "?");
                            if (result) {
                                if (itemsCount > 0) {
                                    var ids = [];
                                    $('.checkboxs').each(function () {
                                        if ($(this).is(':checked')) {
                                            ids.push($(this).val());
                                        }
                                    });
                                    var ids_string = ids.toString();

                                    $.ajax({
                                        type: "get",
                                        url: '{{ url('/lodgements/delete') }}' + '/' + ids_string,
                                        data: {_token: '{{csrf_token()}}'},
                                        success: function (data) {
                                            var itemsIds = data.split(',');
                                            for (var i = 0; i < itemsCount; i++) {
                                                oTable
                                                    .row($('#tr_' + itemsIds[i]))
                                                    .remove()
                                                    .draw(false);
                                            }
                                            $("#select_all").prop('checked', false);
                                        }
                                    });
                                }
                            }
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'Lodgements Report',
                        filename: 'Excel_lodgements_report_' + currentDate(),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Lodgements Report',
                        filename: 'CSV_lodgements_report_' + currentDate(),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'colvis',
                        collectionLayout: 'fixed two-column',
                        columns: ':not(:first-child)'
                    }
                ],
                language: {
                    search: "Find:"
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false,
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var colNumber = costColumns;

                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[, ₹]|(\.\d{2})/g, "") * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    for (i = 0; i < colNumber.length; i++) {
                        var colNo = colNumber[i];
                        var total2 = api
                            .column(colNo, {page: 'current'})
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        
                        var currSymbol = $(api.column(colNo).header()).data('symbol');
                        
                        $(api.column(colNo).footer()).html(currSymbol + total2.toFixed(2));
                    }
                },
                bSortClasses: false
            });
            @else
                oTable = $('#example').DataTable({
                scrollX: true,
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                order: [[0, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    type: "GET",
                    url: "{{ url('/lodgements/json') }}",
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
                        targets: [-1], 
                        bSortable: false
                    },
                    {
                        targets: [0],
                        width: '50px'
                    },
                    {
                        targets: [0, 1, -1], 
                        className: "text-align-center"
                    },
                    {
                        targets: costColumns, 
                        render: $.fn.dataTable.render.number('', '.', 2, ''),
                        className: "amount-cell",
                        searchable: false
                    },
                ],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Lodgements Report',
                        filename: 'Excel_lodgements_report_' + currentDate(),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Lodgements Report',
                        filename: 'CSV_lodgements_report_' + currentDate(),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'colvis',
                        collectionLayout: 'fixed two-column'
                    }
                ],
                language: {
                    search: "Find:"
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false,
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var colNumber = costColumns;

                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[, ₹]|(\.\d{2})/g, "") * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    for (i = 0; i < colNumber.length; i++) {
                        var colNo = colNumber[i];
                        var total2 = api
                            .column(colNo, {page: 'current'})
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        var currSymbol = $(api.column(colNo).header()).data('symbol');
                        
                        $(api.column(colNo).footer()).html(currSymbol + total2.toFixed(2));
                    }
                },
                bSortClasses: false
            });
            @endif

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
                        report_name: 'lodgements',
                        column_index: column,
                    }
                });
            });
        });

        //select all checkboxes
        $("#select_all").change(function () {  //"select all" change
            $(".checkboxs").not(":disabled").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
        });

        //".checkbox" change
        $(document).on("change", "input[name='del_chks']", function () {
            //uncheck "select all", if one of the listed checkbox item is unchecked
            if (false == $(this).prop("checked")) { //if this item is unchecked
                $("#select_all").prop('checked', false); //change "select all" checked status to false
            }
            //check "select all" if all checkbox items are checked
            if ($('.checkboxs:checked').length == $('.checkboxs').length) {
                $("#select_all").prop('checked', true);
            }
        });

        // This code removes row from datatable
        $('#example tbody').on('click', 'button.delete', function () {
            var result = confirm("Do you want to delete this lodgements record?");
            if (result) {
                var token = $('button.delete').attr('data-token');
                var rowID = $(this).closest('tr').attr('id');
                var id = rowID.split('_');
                $.ajax({
                    type: 'get',
                    url: '{{ url('/lodgements/delete') }}' + '/' + id[1],
                    data: {_token: token},
                    success: function (data) {
                        if (data) {
                            oTable
                                .row($('#tr_' + data))
                                .remove()
                                .draw(false);
                        }
                    }
                });
            }
        });
    </script>
@stop
