@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Labour Hours Report</strong></div>
			</div>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif

			@if($isSuLevel)
				<table id="example" class="display margin-bottom-10" cellspacing="0" width="100%">
					<thead>
					<tr>
						<th class="text-align-center"><input type="checkbox" id="select_all"/></th>
						<th>ID</th>
						<th>Sheet ID</th>
						<th>Unit Name</th>
						<th>Supervisor</th>
						<th>Labour Hours</th>
						<th>Labour Date</th>
						<th>Labour Type</th>
						<th class="text-align-center">Action</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<th>Total</th>
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
				<table id="example" class="display margin-bottom-10" cellspacing="0" width="100%">
					<thead>
					<tr>
						<th>ID</th>
						<th>Sheet ID</th>
						<th>Unit Name</th>
						<th>Supervisor</th>
						<th>Labour Hours</th>
						<th>Labour Date</th>
						<th>Labour Type</th>
						<th class="text-align-center">Action</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<th>Total</th>
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
			@if($isSuLevel)
                oTable = $('#example').DataTable({
                scrollX: true,
                dom: '<f<t>lBip>',
                order: [[1, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    type: "GET",
                    url: "{{ url('/labour-hours/json') }}", // json datasource
                    data: {unit_id: '{{ $unitId ?: null }}', from_date: '{{ $fromDate }}', to_date: '{{ $toDate }}', all_records: {{ $allRecords }} },
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
                        targets: [0, 8], 
                        bSortable: false
                    },
                    {
                        targets: [0, 1, 2, 6, 8], 
                        className: "text-align-center"
                    },
                    {
                        targets: [5],
                        className: "text-align-right"
                    }
                ],
                buttons: [
                    {
                        text: 'Delete',
                        className: 'red',
                        action: function (e, dt, node, config) {
                            var labourHourCount = $('.checkboxs:checked').length;
                            var labourHourToDel = labourHourCount == 1 ? 'this labour hour' : 'these labour hours';
                            var result = confirm("Do you want to delete " + labourHourToDel + "?");
                            if (result) {
                                if (labourHourCount > 0) {  // at-least one checkbox checked
                                    var ids = [];
                                    $('.checkboxs').each(function () {
                                        if ($(this).is(':checked')) {
                                            ids.push($(this).val());
                                        }
                                    });
                                    var ids_string = ids.toString();  // array to string conversion
                                    $.ajax({
                                        type: "get",
                                        url: '{{ url('/labour-hours/delete') }}' + '/' + ids_string,
                                        data: {_token: '{{csrf_token()}}'},
                                        success: function (data) {
                                            var LabourHourIds = data.split(',');
                                            for (var i = 0; i < labourHourCount; i++) {
                                                oTable
                                                    .row($('#tr_' + LabourHourIds[i]))
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
                        title: 'Labour Hours Report',
                        filename: 'Excel_labour_hours_report_' + currentDate(),
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Labour Hours Report',
                        filename: 'CSV_labour_hours_report_' + currentDate(),
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7]
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
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false,
                //Labour Hours Total in Footer
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var colNumber = [5];

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
                        $(api.column(colNo).footer()).html(total2);
                    }
                }
                //Labour Hours Total in Footer
            });
			@else
                oTable = $('#example').DataTable({
                responsive: true,
                dom: '<f<t>lBip>',
                order: [[0, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    type: "GET",
                    url: "{{ url('/labour-hours/json') }}", // json datasource
                    data: {unit_id: '{{ $unitId ?: null }}', from_date: '{{ $fromDate }}', to_date: '{{ $toDate }}', all_records: {{ $allRecords }} },
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
                        targets: [7],
                        bSortable: false
                    },
                    {
                        targets: [0, 1, 5, 7], 
                        className: "text-align-center"
                    },
                    {
                        targets: [4],
                        className: "text-align-right"
                    }
                ],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Labour Hours Report',
                        filename: 'Excel_labour_hours_report_' + currentDate(),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Labour Hours Report',
                        filename: 'CSV_labour_hours_report_' + currentDate(),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
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
                //Labour Hours Total in Footer
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var colNumber = [4];

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
                        $(api.column(colNo).footer()).html(total2);
                    }
                }
                //Labour Hours Total in Footer
            });
			@endif

            // Column visibility
            $('#example').on('column-visibility.dt', function (e, settings, column, state) {
                $.ajax({
                    url: "{{ url('/report/column_visibility/toggle') }}",
                    type: "post",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        report_name: 'labour-hours',
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
            var result = confirm("Do you want to delete this labour hour?");
            if (result) {
                var token = $('button.delete').attr('data-token');
                var rowID = $(this).closest('tr').attr('id');
                var id = rowID.split('_');
                $.ajax({
                    type: 'get',
                    url: '{{ url('/labour-hours/delete') }}' + '/' + id[1],
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