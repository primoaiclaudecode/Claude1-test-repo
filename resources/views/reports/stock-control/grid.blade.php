@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Stock Control Report</strong></div>
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
						<th>Unit Name</th>
						<th>User</th>
						<th>Stock Date</th>
						<th>Foods</th>
						<th>Min./Alc.</th>
						<th>Snacks</th>
						<th>Vending</th>
						<th>Prev. Food/Min. Total</th>
						<th>Food/Min. Total</th>
						<th>Food/Min. +/-</th>
						<th>Chemicals</th>
						<th>Disposables</th>
						<th>Free Issues</th>
						<th>Prev. Chem/Disp./F.I.</th>
						<th>Chem/Disp./F.I.</th>
						<th>Chem/Disp./F.I. +/-</th>
						<th>Prev. Overall Total</th>
						<th>Overall Total</th>
						<th>Overall Total +/-</th>
						<th>Comments</th>
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
						<th>Unit Name</th>
						<th>User</th>
						<th>Stock Date</th>
						<th>Foods</th>
						<th>Minerals</th>
						<th>Snacks</th>
						<th>Vending</th>
						<th>Prev. Food/Min. Total</th>
						<th>Food/Min. Total</th>
						<th>Food/Min. +/-</th>
						<th>Chemicals</th>
						<th>Disposables</th>
						<th>Free Issues</th>
						<th>Prev. Chem/Disp./F.I.</th>
						<th>Chem/Disp./F.I.</th>
						<th>Chem/Disp./F.I. +/-</th>
						<th>Prev. Overall Total</th>
						<th>Overall Total</th>
						<th>Overall Total +/-</th>
						<th>Comments</th>
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
		#select_all {
			margin-top: 0;
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
    </style>
	<script type="text/javascript" class="init">
        $(document).ready(function () {
			@if($isSuLevel)
                oTable = $('#example').DataTable({
                scrollX: "true",
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                order: [[1, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    type: "GET",
                    url: "{{ url('/stock-control/json') }}", // json datasource
                    data: {unit_id: '{{ $unitId ?: null }}', from_date: '{{ $fromDate }}', to_date: '{{ $toDate }}', all_records: {{ $allRecords }} },
                    deferRender: true,
                    error: function () {  // error handling
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display", "none");

                    }
                }),
                columns: [
                    {
                        searchable: false
                    },
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    null,
                    null,
                    null,
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    }
                ],
                columnDefs: [
                    {
                        targets: [{{ $notVisiable }}],
                        visible: false,
                    },
                    {
                        targets: [0, 9, 10, 11, 15, 16, 17, 18, 19, 20, 21, 22],
                        bSortable: false
                    },
                    {
                        targets: [0, 1, 4, 22],
                        className: "text-align-center"
                    },
                    {
                        targets: [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
                        className: "text-align-right"
                    },
                    {
                        targets: [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
                        render: $.fn.dataTable.render.number('', '.', 2, ''),
                        className: "amount-cell",
                    },
                ],
                buttons: [
                    {
                        text: 'Delete',
                        className: 'red',
                        action: function (e, dt, node, config) {
                            var stockControlCount = $('.checkboxs:checked').length;
                            var stockControlToDel = stockControlCount == 1 ? 'this stock control' : 'these stock control';
                            var result = confirm("Do you want to delete " + stockControlToDel + "?");
                            if (result) {
                                if (stockControlCount > 0) {  // at-least one checkbox checked
                                    var ids = [];
                                    $('.checkboxs').each(function () {
                                        if ($(this).is(':checked')) {
                                            ids.push($(this).val());
                                        }
                                    });
                                    var ids_string = ids.toString();  // array to string conversion
                                    $.ajax({
                                        type: "get",
                                        url: '{{ url('/stock-control/delete') }}' + '/' + ids_string,
                                        data: {_token: '{{csrf_token()}}'},
                                        success: function (data) {
                                            var stockControlIds = data.split(',');
                                            for (var i = 0; i < stockControlCount; i++) {
                                                oTable
                                                    .row($('#tr_' + stockControlIds[i]))
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
                        title: 'Stock Control Report',
                        filename: 'Excel_stock_control_report_' + currentDate(),
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Stock Control Report',
                        filename: 'CSV_stock_control_report_' + currentDate(),
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21]
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
                //Labour Hours Total in Footer
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var colNumber = [11, 17, 20];

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
                        $(api.column(colNo).footer()).html(total2.toFixed(2));
                    }
                }
                //Labour Hours Total in Footer
            });
			@else
                oTable = $('#example').DataTable({
                scrollX: "true",
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                order: [[0, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    type: "GET",
                    url: "{{ url('/stock-control/json') }}", // json datasource
                    data: {unit_id: '{{ $unitId ?: null }}', from_date: '{{ $fromDate }}', to_date: '{{ $toDate }}', all_records: {{ $allRecords }} },
                    deferRender: true,
                    error: function () {  // error handling
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display", "none");

                    }
                }),
                columns: [
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    null,
                    null,
                    null,
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    },
                    {
                        searchable: false
                    }
                ],
                columnDefs: [
                    {
                        targets: [{{ $notVisiable }}],
                        visible: false,
                    },
                    {
                        targets: [8, 9, 10, 14, 15, 16, 17, 18, 19, 20, 21],
                        bSortable: false
                    },
                    {
                        targets: [0, 3, 21],
                        className: "text-align-center"
                    },
                    {
                        targets: [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], 
                        className: "text-align-right"
                    },
                    {
                        targets: [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19],
                        render: $.fn.dataTable.render.number('', '.', 2, ''),
                        className: "amount-cell",
                    }
                ],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Labour Hours Report',
                        filename: 'Excel_stock_control_report_' + currentDate(),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 20]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Labour Hours Report',
                        filename: 'CSV_stock_control_report_' + currentDate(),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 20]
                        }
                    },
                    {
                        extend: 'colvis',
                        collectionLayout: 'fixed two-column',
                    }
                ],
                language: {
                    search: "Find:"
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false,
                //Stock Control Total in Footer
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var colNumber = [10, 16, 19];

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
                        $(api.column(colNo).footer()).html(total2.toFixed(2));
                    }
                }
                //Stock Control Total in Footer
            });
			@endif

            // Column visibility
            $('#example').on('column-visibility.dt', function (e, settings, column, state) {
                $.ajax({
                    url: "{{ url('/report/column_visibility/toggle') }}",
                    type: "post",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        report_name: 'stock-control',
                        column_index: column,
                    }
                });
            });
        });

        //select all checkboxes
        $("#select_all").change(function () {  //"select all" change
            $(".checkboxs").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
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
            var result = confirm("Do you want to delete this stock control?");
            if (result) {
                var token = $('button.delete').attr('data-token');
                var rowID = $(this).closest('tr').attr('id');
                var id = rowID.split('_');
                $.ajax({
                    type: 'get',
                    url: '{{ url('/stock-control/delete') }}' + '/' + id[1],
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