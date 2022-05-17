@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Corrective Action Report</strong></div>
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
						<th>CAR #</th>
						<th>CAR Status</th>
						<th>Date</th>
						<th>User</th>
						<th>Unit</th>
						<th>Problem Type</th>
						<th>Suppliers / Feedback</th>
						<th>Details</th>
						<th>RCA</th>
						<th>RCA Desc</th>
						<th>RCA Action</th>
						<th>Closing Comments</th>
						<th>Closed By</th>
						<th>Closed Date</th>
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
						<th>CAR #</th>
						<th>CAR Status</th>
						<th>Date</th>
						<th>User</th>
						<th>Unit</th>
						<th>Problem Type</th>
						<th>Suppliers / Feedback</th>
						<th>Details</th>
						<th>RCA</th>
						<th>RCA Desc</th>
						<th>RCA Action</th>
						<th>Closing Comments</th>
						<th>Closed By</th>
						<th>Closed Date</th>
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
                scrollX: "true",
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                "order": [[1, "desc"]],
                processing: true,
                serverSide: true,
                "ajax": ({
                    type: "GET",
                    url: "{{ url('/problem-report/json') }}", // json datasource
                    data: {
                        unit_id: '{{ $unitId ?: null }}',
                        from_date: '{{ $fromDate }}',
                        to_date: '{{ $toDate }}',
                        problem_type: '{{ $problemType }}',
                        all_records: {{ $allRecords }} },
                    "deferRender": true,
                    error: function () {  // error handling
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display", "none");

                    }
                }),
                "columnDefs": [
                    {
                        targets: [{{ $notVisiable }}], 
                        visible: false,
                    },
	                {
	                    targets: [0, 15], bSortable: false
                    },
                    {
                        targets: [0, 1, 3, -1, -2], 
                        className: "text-align-center",
                    }
                ],
                buttons: [
                    {
                        text: 'Delete',
                        className: 'red',
                        action: function (e, dt, node, config) {
                            var problemReportCount = $('.checkboxs:checked').length;
                            var problemReportToDel = problemReportCount == 1 ? 'this problem report record' : 'these problem report records';
                            var result = confirm("Do you want to delete " + problemReportToDel + "?");
                            if (result) {
                                if (problemReportCount > 0) {  // at-least one checkbox checked
                                    var ids = [];
                                    $('.checkboxs').each(function () {
                                        if ($(this).is(':checked')) {
                                            ids.push($(this).val());
                                        }
                                    });
                                    var ids_string = ids.toString();  // array to string conversion
                                    $.ajax({
                                        type: "get",
                                        url: '{{ url('/problem-report/delete') }}' + '/' + ids_string,
                                        data: {_token: '{{csrf_token()}}'},
                                        success: function (data) {
                                            var problemReportIds = data.split(',');
                                            for (var i = 0; i < problemReportCount; i++) {
                                                oTable
                                                    .row($('#tr_' + problemReportIds[i]))
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
                        title: 'Problem Report',
                        filename: 'Excel_corrective_action_report_' + currentDate(),
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Problem Report',
                        filename: 'CSV_corrective_action_report_' + currentDate(),
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                        }
                    },
                    {
                        extend: 'colvis',
                        collectionLayout: 'fixed three-column',
                        columns: ':not(:first-child)'
                    }
                ],
                "language": {
                    "search": "Find:"
                },
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false,
                "bSortClasses": false
            });
			@else
                oTable = $('#example').DataTable({
                scrollX: "true",
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                "order": [[0, "desc"]],
                processing: true,
                serverSide: true,
                "ajax": ({
                    type: "GET",
                    url: "{{ url('/problem-report/json') }}", // json datasource
                    data: {
                        unit_id: '{{ $unitId ?: null }}',
                        from_date: '{{ $fromDate }}',
                        to_date: '{{ $toDate }}',
                        problem_type: '{{ $problemType }}',
                        all_records: {{ $allRecords }} },
                    "deferRender": true,
                    error: function () {  // error handling
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display", "none");

                    }
                }),
                "columnDefs": [
                    {
                        targets: [{{ $notVisiable }}], 
                        visible: false,
                    },
	                {
	                    targets: [14], 
                        bSortable: false
                    },
                    {
                        targets: [0, 2, -1, -2], 
                        className: "text-align-center"
                    }
                ],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Problem Report',
                        filename: 'Excel_corrective_action_report_' + currentDate(),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Problem Report',
                        filename: 'CSV_corrective_action_report_' + currentDate(),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
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
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false,
                "bSortClasses": false
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
                        report_name: 'problem',
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
            var result = confirm("Do you want to delete this problem report record?");
            if (result) {
                var token = $('button.delete').attr('data-token');
                var rowID = $(this).closest('tr').attr('id');
                var id = rowID.split('_');
                $.ajax({
                    type: 'get',
                    url: '{{ url('/problem-report/delete') }}' + '/' + id[1],
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
