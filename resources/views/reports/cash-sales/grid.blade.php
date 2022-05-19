@extends('layouts/dashboard_master')

@section('content')
    <section class="panel" id="purchases-report">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Cash Sales Report</strong></div>
            </div>
        </header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif

			@if($isSuLevel)
				<table id="example" class="display nowrap" cellspacing="0" width="100%">
					<thead>
					<tr>
						<th class="text-align-center"><input type="checkbox" id="select_all"/></th>
						<th>ID</th>
						<th>Date of Entry</th>
						<th>Unit Name</th>
						<th>Supervisor</th>
						<th>Reg Number</th>
						<th>Sale Date</th>
						<th>Z Number</th>
						<th>Currency</th>
						<th>Exchange Rate</th>
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
						<th>Variance</th>
						<th>Cash Purchase</th>
						<th>Credit Sale</th>
						<th>Over Ring</th>
						<th>Cash +/-</th>
						<th>Lodgement Cash</th>
						<th>Lodgement Coin</th>
						<th>Lodgement Total</th>
						<th>Lodgement Date</th>
						<th>Lodgement Number</th>
						<th>G4S Bag #</th>
						<th>Remarks</th>
						<th>Updated By</th>
						<th>Updated</th>
						<th class="text-align-center">Action</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<th class="no-search"></th>
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
						<th class="no-search"></th>
					</tr>
					</tfoot>
					<tbody>
					<!-- Datatables renders here. -->
					</tbody>
				</table>
			@else
				<table id="example" class="display nowrap" cellspacing="0" width="100%">
					<thead>
					<tr>
						<th>ID</th>
						<th>Date of Entry</th>
						<th>Unit Name</th>
						<th>Supervisor</th>
						<th>Reg Number</th>
						<th>Sale Date</th>
						<th>Z Number</th>
						<th>Currency</th>
						<th>Exchange Rate</th>
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
						<th>Variance</th>
						<th>Cash Purchase</th>
						<th>Credit Sale</th>
						<th>Over Ring</th>
						<th>Cash +/-</th>
						<th>Lodgement Cash</th>
						<th>Lodgement Coin</th>
						<th>Lodgement Total</th>
						<th>Lodgement Date</th>
						<th>Lodgement Number</th>
						<th>G4S Bag #</th>
						<th>Remarks</th>
						<th>Updated By</th>
						<th>Updated</th>
						<th class="text-align-center">Action</th>
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
						<th class="no-search"></th>
					</tr>
					</tfoot>
					<tbody>
					<!-- Datatables renders here. -->
					</tbody>
				</table>
			@endif
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
                    url: "{{ url('/cash-sales/json') }}",
                    data: {
                        unit_id: '{{ $unitId ?: null }}',
                        from_date: '{{ $fromDate }}',
                        to_date: '{{ $toDate }}',
                        all_records: {{ $allRecords }},
                        sheet_id: '{{ $sheetId }}'
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
                        targets: [0, 1, 2, 6, 28, -1], 
                        className: "text-align-center"
                    },
                    {
                        targets: [7, 9], className: "text-align-right"
                    },
                    {
                        targets: [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27],
                        render: $.fn.dataTable.render.number('', '.', 2, ''),
                        className: "amount-cell",
                        searchable: false
                    }
                ],
                buttons: [
                    {
                        text: 'Delete',
                        className: 'red',
                        action: function (e, dt, node, config) {
                            var cashSalesCount = $('.checkboxs:checked').length;
                            var cashSalesToDel = cashSalesCount == 1 ? 'this cash sales record' : 'these cash sales records';
                            var result = confirm("Do you want to delete " + cashSalesToDel + "?");
                            if (result) {
                                if (cashSalesCount > 0) {  // at-least one checkbox checked
                                    var ids = [];
                                    $('.checkboxs').each(function () {
                                        if ($(this).is(':checked')) {
                                            ids.push($(this).val());
                                        }
                                    });
                                    var ids_string = ids.toString();  // array to string conversion
                                    $.ajax({
                                        type: "get",
                                        url: '{{ url('/cash-sales/delete') }}' + '/' + ids_string,
                                        data: {_token: '{{csrf_token()}}'},
                                        success: function (data) {
                                            var cashSalesIds = data.split(',');
                                            for (var i = 0; i < cashSalesCount; i++) {
                                                oTable
                                                    .row($('#tr_' + cashSalesIds[i]))
                                                    .remove()
                                                    .draw(false);
                                            }
                                            $("#select_all").prop('checked', false); //change "select all" checked status to false
                                        }
                                    });
                                }
                            }
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'Cash Sales Report',
                        filename: 'Excel_cash_sales_report_' + currentDate(),
                        exportOptions: {
                            columns: ':not(:first-child)'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Cash Sales Report',
                        filename: 'CSV_cash_sales_report_' + currentDate(),
                        exportOptions: {
                            columns: ':not(:first-child)'
                        }
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
                stateSave: false,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var colNumber = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27];

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
                        $(api.column(colNo).footer()).html('{{ $currencySymbol }}' + total2.toFixed(2));
                    }
                },
                bSortClasses: false
            });
            @else
                oTable = $('#example').DataTable({
                scrollX: true,
                scrollCollapse: true,
                dom: '<f<t>Blip>',
                order: [[0, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    type: "GET",
                    url: "{{ url('/cash-sales/json') }}",
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
                        targets: [-1], 
                        bSortable: false
                    },
                    {
                        targets: [0, 1, 5, 27, -1], 
                        className: "text-align-center"
                    },
                    {
                        targets: [6, 8], 
                        className: "text-align-right"
                    },
                    {
                        targets: [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26],
                        render: $.fn.dataTable.render.number('', '.', 2, ''),
                        className: "amount-cell",
                        searchable: false
                    }
                ],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Cash Sales Report',
                        filename: 'Excel_cash_sales_report_' + currentDate(),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Cash Sales Report',
                        filename: 'CSV_cash_sales_report_' + currentDate(),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'colvis',
                        collectionLayout: 'fixed three-column'
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
                    var colNumber = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26];

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
                        $(api.column(colNo).footer()).html('{{ $currencySymbol }}' + total2.toFixed(2));
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
                        report_name: 'cash-sales',
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

        function confirm_before_unfreeze() {
            return confirm("You are about to un-freeze a record which has already been statement checked. Do you wish to proceed?");
        }

        // This code removes row from datatable
        $('#example tbody').on('click', 'button.delete', function () {
            var result = confirm("Do you want to delete this cash sales record?");
            if (result) {
                var token = $('button.delete').attr('data-token');
                var rowID = $(this).closest('tr').attr('id');
                var id = rowID.split('_');
                $.ajax({
                    type: 'get',
                    url: '{{ url('/cash-sales/delete') }}' + '/' + id[1],
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
