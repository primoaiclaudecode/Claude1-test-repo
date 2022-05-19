@extends('layouts/dashboard_master')

@section('content')
	<section class="panel" id="purchases-report">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Client Feedback Report</strong></div>
			</div>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif

			{!! Form::open(['url' => 'reports/client-feedback/grid', 'class' => 'form-horizontal form-bordered', 'id' => 'cliect_feedbact_report_form']) !!}

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
						<tr>
							<td>Region</td>
							<td>
								<input type="text" name="region_name" id="region_name" value="{{ $regionName }}" class="form-control text-right" readonly
									   tabindex="4"/>
							</td>
							<td>Contract Status</td>
							<td>
								<input type="text" name="contract_status" id="contract_status" value="{{ $contractStatus }}" class="form-control text-right"
									   readonly tabindex="5"/>
							</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Operations Manager</td>
							<td>
								<input type="text" name="operations_manager_name" id="operations_manager_name" value="{{ $operationsManagerName }}"
									   class="form-control text-right" readonly tabindex="6"/>
							</td>
							<td>Contract Type</td>
							<td>
								<input type="text" name="contract_type" id="contract_type" value="{{ $contractType }}" class="form-control text-right"
									   readonly tabindex="7"/>
							</td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Communication per month</td>
							<td>
								<input type="text" name="onsite_visits" id="onsite_visits" value="{{ $onsiteVisits }}" class="form-control text-right"
									   readonly tabindex="8"/>
							</td>
							<td>Client Contact</td>
							<td>
								<input type="text" name="client_contact" id="client_contact" value="{{ $clientContact }}" class="form-control text-right"
									   readonly tabindex="9"/>
							</td>
							<td></td>
							<td></td>
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
			<table id="report_data" class="display nowrap full-width" cellspacing="0">
				<thead>
				<tr>
					<th class="text-align-center">Contact Date</th>
					<th>Contact Type</th>
					<th>Notes</th>
					<th class="text-align-center">Customer Feedback</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th></th>
					<th>
						<input type="text" class="full-width" placeholder="Search" />
					</th>
					<th></th>
					<th>
						<input type="text" class="full-width" placeholder="Search" />
					</th>
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
            }).on('changeDate', function (e) {
                $('#to_date').focus();
            });

            $('#from_date_icon').click(function () {
                $("#from_date").datepicker().focus();
            });

            $('#to_date_icon').click(function () {
                $("#to_date").datepicker().focus();
            });

            // Datatable
            var oTable = $('#report_data').DataTable({
                scrollX: "true",
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                order: [[0, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    type: "GET",
                    url: "{{ url('/reports/client-feedback/json') }}",
                    data: {
                        unit_id: '{{ $selectedUnit ?: null }}',
                        from_date: '{{ $fromDate }}',
                        to_date: '{{ $toDate }}',
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
                    	targets: [0], className: "text-align-center"
					},
                    {
                    	width: "150px", targets: [0, 1, 3]
					},
                ],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Client Feedback Report',
                        filename: 'Excel_client_feedback_report_' + currentDate(),
                        exportOptions: {
                            columns: [0, 1, 2, 3]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Client Feedback Report',
                        filename: 'CSV_client_feedback_' + currentDate(),
                        exportOptions: {
                            columns: [0, 1, 2, 3]
                        }
                    },
                ],
                language: {
                    search: "Find:"
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false,
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
            
        });
	</script>
@stop
