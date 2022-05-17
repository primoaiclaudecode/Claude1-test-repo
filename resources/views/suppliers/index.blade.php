@extends('layouts/dashboard_master')

@section('content')
<section class="panel">
	<header class="panel-heading">
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Supplier Management</strong></div> 
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
				<a name="create_new" alt="Create New Supplier" title="Create New Supplier" class="btn pull-right btn-primary" href="{{ url('/suppliers/create') }}">
					<i class="fa fa-plus"></i> Create New Supplier
				</a>
			</div>
		</div>
	</header>		

	<section class="dataTables-padding">
		@if(Session::has('flash_message'))
		<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
		@endif
		<table id="example" class="display margin-bottom-10" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-align-center"><input type="checkbox" id="select_all"></th>
					<th>ID</th>
					<th>Name</th>
					<th>Address</th>
					<th>Phone</th>
					<th>Fax</th>
					<th>Details</th>
					<th>Sage A/C #</th>
					<th>A/C #</th>
					<th>A/C Contact</th>
					<th>A/C Email</th>
					<th>A/C Query Email</th>
					<th>Action</th>
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
	$(document).ready(function(){
      	oTable = $('#example').DataTable({
			scrollX: true,
            dom: '<f<t>lBip>',
            "order": [[ 1, "desc" ]],
	        processing: true,
            serverSide: true,
            "ajax": ({
				url :"{{ url('/suppliers_data/json') }}", // json datasource
				"deferRender": true,
				error: function() {  // error handling
					$(".employee-grid-error").html("");
					$("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
					$("#employee-grid_processing").css("display","none");
					
				}
			}),
            "columnDefs": [		            
	        	{
	                "targets": [ 6,7,8,9,10,11 ],
	                "visible": false
	            },
	            {
	            	className: "text-align-center",
	            	bSortable: false,
	            	"targets": [ 0,12 ]
	            }
	        ],            
            buttons: [
	            {
	                text: 'Delete',
	                className: 'red',
	                action: function ( e, dt, node, config ) {
	                	var supplierCount = $('.checkboxs:checked').length;
	                	var supplierToDel = supplierCount == 1 ? 'this supplier' : 'these suppliers';
	                    var result = confirm("Do you want to delete " + supplierToDel + "?");
	                    if(result) {
		                    if( supplierCount > 0 ) {  // at-least one checkbox checked
					            var ids = [];
					            $('.checkboxs').each(function(){
					                if($(this).is(':checked')) { 
					                    ids.push($(this).val());
					                }
					            });
					            var ids_string = ids.toString();  // array to string conversion
					            $.ajax({
					                type: "post",
					                url: '{{ url('/suppliers') }}' + '/' + ids_string,
					                data: {_method: 'delete', _token :'{{csrf_token()}}'},
					                success: function(data) {
					                	var supplierIds = data.split(',');
					                    for(var i = 0; i < supplierCount; i++) {
					                    	oTable
					                            .row($('#tr_'+supplierIds[i]))
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
				/*
	            {
	                extend: 'copyHtml5',
	                exportOptions: {
	                    columns: [ 0, ':visible' ]
	                }
	            },
				*/
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
				/*
	            {
	                extend: 'pdfHtml5',
	                exportOptions: {
	                    columns: ':visible'
	                }
	            },
	            {
	                extend: 'print',
	                exportOptions: {
	                    columns: ':visible'
	                }
	            },
				*/
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
            stateSave: true
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
        var result = confirm("Do you want to delete this supplier?");
        if(result) {
            var token = $('button.delete').attr('data-token');
            var rowID = $(this).closest('tr').attr('id');
            var id = rowID.split('_');
            $.ajax({
                type: 'post',
                url: '{{ url('/suppliers') }}' + '/' + id[1],
                data: {_method: 'delete', _token :token},
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