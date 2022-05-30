@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Unit Management</strong></div>
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
					<a name="create_new" alt="Create New Unit" title="Create New Unit" class="btn pull-right btn-primary" href="{{ url('/units/create') }}">
						<i class="fa fa-plus"></i> Create New Unit
					</a>
				</div>
			</div>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
		    <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
		@endif
			@if(Session::has('error_message'))
				<div class="alert alert-danger"><em> {!! session('error_message') !!}</em></div>
			@endif
			<table id="example" class="display margin-bottom-10" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th class="text-align-center"><input type="checkbox" id="select_all" /></th>
						<th>ID</th>
						<th>Unit Name</th>
						<th>Unit Status</th>
						<th>Operations Manager</th>
						<th>Details</th>
						<th>Address</th>
						<th>Town</th>
						<th>County</th>
						<th>Contact #</th>
						<th>Email</th>
						<th>Head Count</th>
						<th class="text-align-center">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach($units as $unit)
						<tr id="tr_{{ $unit->unit_id }}">
							<td>
								<input name="del_chks" type="checkbox" class="checkboxs" value="{{ $unit->unit_id }}">
							</td>
							<td>{{ $unit->unit_id }}</td>
							<td>{{ $unit->unit_name }}</td>
							<td>{{ $unit->unit_status }}</td>
							<td>{{ $unit->operation_manager }}</td>
							<td>{{ $unit->details }}</td>
							<td>{{ $unit->location }}</td>
							<td>{{ $unit->town }}</td>
							<td>{{ $unit->county }}</td>
							<td>{{ $unit->contact_number }}</td>
							<td>{{ $unit->email }}</td>
							<td>{{ $unit->head_count }}</td>
							<td>
								<a type="button" href="units/{{ $unit->unit_id }}/edit" class="btn btn-danger btn-xs">
									<i class="fa fa-edit"></i>
								</a>
								<button type="button" class="btn btn-danger btn-xs delete" data-unit_id="{{ $unit->unit_id }}" >
									<i class="fa fa-trash"></i>
								</button>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</section>
	</section>
@stop

@section('scripts')
	<script type="text/javascript" class="init">
	    $(document).ready(function() {
	        oTable = $('#example').DataTable({
				scrollX: true,
	            dom: '<f<t>lBip>',
                ordering: 'isSorted',
	            order: [],
	            columnDefs: [
		            {
		            	className: "text-align-center",
		            	bSortable: false,
		            	"targets": [ 0,10 ]
		            }
		        ],
	            buttons: [
	            {
	                text: 'Delete',
	                className: 'red',
	                action: function ( e, dt, node, config ) {
	                	var unitCount = $('.checkboxs:checked').length;
	                	var unitToDel = unitCount == 1 ? 'this unit' : 'these units';
	                    var result = confirm("Do you want to delete " + unitToDel + "?");
	                    if(result) {
		                    if( unitCount > 0 ) {  // at-least one checkbox checked
					            var ids = [];
					            $('.checkboxs').each(function(){
					                if($(this).is(':checked')) {
					                    ids.push($(this).val());
					                }
					            });
					            var ids_string = ids.toString();  // array to string conversion
					            $.ajax({
					                type: "post",
					                url: '{{ url('/units') }}' + '/' + ids_string,
					                data: {_method: 'delete', _token :'{{csrf_token()}}'},
					                success: function(data) {
					                	var unitIds = data.split(',');
					                    for(var i = 0; i < unitCount; i++) {
					                    	oTable
					                            .row($('#tr_'+unitIds[i]))
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
	                exportOptions: {
	                    columns: ':visible'
	                }
	            },
	            {
	                extend: 'csvHtml5',
	                exportOptions: {
	                    columns: ':visible'
	                }
	            },
	            {
		            extend: 'colvis',
		            columns: ':not(:first-child)'
		        }
	            ],
	            "language": {
	                "search": "Find:"
	            },
	            "pageLength": 10,
				"lengthMenu": [[10,25,50,-1],[10,25,50,"All"]],
	            stateSave: false
	        });
	    });

	    //select all checkboxes
		$("#select_all").change(function(){  //"select all" change
		    $(".checkboxs").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
		});

		//".checkbox" change
		$(document).on("change", "input[name='del_chks']", function () {
		    //uncheck "select all", if one of the listed checkbox item is unchecked
		    if(false == $(this).prop("checked")){ //if this item is unchecked
		        $("#select_all").prop('checked', false); //change "select all" checked status to false
		    }
		    //check "select all" if all checkbox items are checked
		    if ($('.checkboxs:checked').length == $('.checkboxs').length ){
		        $("#select_all").prop('checked', true);
		    }
		});

	    // This code removes row from datatable
	    $('#example tbody').on( 'click', 'button.delete', function () {
	        var result = confirm("Do you want to delete this unit?");
	        if(result) {
	            var id = $(this).data('unit_id');
	            
	            $.ajax({
	                type: 'post',
	                url: '{{ url('/units') }}' + '/' + id,
	                data: {
	                    _method: 'delete',
                        _token: '{{csrf_token()}}',
                    },
	                success: function(data) {
	                    if(data) {
	                        oTable
	                            .row($('#tr_'+data))
	                            .remove()
	                            .draw(false);
	                    }
	                }
	            });
	        }
	    } );
    </script>
@stop