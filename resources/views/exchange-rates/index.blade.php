@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Currency Management</strong></div>
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
					<a name="create_new" alt="Create New Unit" title="Create New Exchange Rate" class="btn pull-right btn-primary"
					   href="{{ url('/exchange-rates/create') }}">
						<i class="fa fa-plus"></i> Create New Exchange Rate
					</a>
				</div>
			</div>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif

			<table id="currencies" class="display margin-bottom-10" cellspacing="0" width="100%">
				<thead>
				<tr>
					<th>Date</th>
					@foreach($currencies as $currencyId => $currencyName)
						<th>{{ $currencyName }}</th>
					@endforeach
					<th class="text-align-center">Action</th>
				</tr>
				</thead>
				<tbody>
				<!-- Datatables renders here. -->
				</tbody>
			</table>
		</section>
	</section>
@stop

@section('scripts')
	<script type="text/javascript" class="init">
        $(document).ready(function () {
            oTable = $('#currencies').DataTable({
                scrollX: true,
                dom: '<f<t>lBip>',
                ordering: 'isSorted',
                order: [],	            
                processing: true,
                serverSide: true,
                "ajax": ({
                    url: "{{ url('/exchange_rate_data/json') }}", // json datasource
                    "deferRender": true,
                    error: function () {  // error handling
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display", "none");
                    }
                }),
                "columnDefs": [
                    {
                        targets: [-1], bSortable: false
                    },
                    {
                        targets: [-1], className: "text-align-center",
                    },
                ],
                buttons: [
                    {
                        extend: 'excelHtml5',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                "language": {
                    "search": "Find:"
                },
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: true
            });
        });
	</script>
@stop