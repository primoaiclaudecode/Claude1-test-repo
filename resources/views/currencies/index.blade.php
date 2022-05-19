@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Currency Management</strong></div>
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
					<a name="create_new" alt="Create New Unit" title="Create New Currency" class="btn pull-right btn-primary"
					   href="{{ url('/currencies/create') }}">
						<i class="fa fa-plus"></i> Create New Currency
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
					<th class="text-align-center">
						<input type="checkbox" id="select_all"/>
					</th>
					<th>ID</th>
					<th>Currency Name</th>
					<th>Currency Code</th>
					<th>Currency Symbol</th>
					<th>Is Default</th>
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
                "order": [[1, "desc"]],
                processing: true,
                serverSide: true,
                "ajax": ({
                    url: "{{ url('/currencies_data/json') }}", // json datasource
                    "deferRender": true,
                    error: function () {  // error handling
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display", "none");
                    }
                }),
                "columnDefs": [
                    {
                        targets: [0, 4, 5, 6], bSortable: false 
                    },
                    {
                        targets: [0, 1, 3, 4, 5, 6], className: "text-align-center",
                    },
                ],
                buttons: [
                    {
                        text: 'Delete',
                        className: 'red',
                        action: function (e, dt, node, config) {
                            var itemsCount = $('.checkboxs:checked').length;
                            var itemToDel = itemsCount == 1 ? 'this currency' : 'these currencies';
                            var result = confirm("Do you want to delete " + itemToDel + "?");
                            
                            if (result && itemsCount > 0) {
                                var ids = [];
                                $('.checkboxs').each(function () {
                                    if ($(this).is(':checked')) {
                                        ids.push($(this).val());
                                    }
                                });

                                var ids_string = ids.toString();

                                $.ajax({
                                    type: "post",
                                    url: '{{ url('/currencies') }}' + '/' + ids_string,
                                    data: {_method: 'delete', _token: '{{csrf_token()}}'},
                                    success: function (data) {
                                        var itemIds = data.split(',');
                                
                                        for (var i = 0; i < itemsCount; i++) {
                                            oTable
                                                .row($('#tr_' + itemIds[i]))
                                                .remove()
                                                .draw(false);
                                        }
                                
                                        $("#select_all").prop('checked', false);
                                    }
                                });
                            }
                        }
                    },
                    {
                        text: 'Submit',
                        className: 'green',
                        action: function (e, dt, node, config) {
                            $("div.alert-success").css("display", "none");

                            $.ajax({
                                type: "post",
                                url: '{{ url('/currencies/default') }}',
                                data: {
                                    _method: 'post',
                                    _token: '{{csrf_token()}}',	                                
                                    currency_id: $('input[name="currency_id"]:checked').val(),
                                },
                                success: function () {
                                    $("div.alert-success").css("display", "block").html('Action has been completed successfully');

                                    dt.draw(false);
                                }
                            });
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
        $('#currencies tbody').on('click', 'button.delete', function () {
            var result = confirm("Do you want to delete this currency?");
            
            if (result) {
                var token = $('button.delete').attr('data-token');
                var rowID = $(this).closest('tr').attr('id');
                var id = rowID.split('_');
                $.ajax({
                    type: 'post',
                    url: '{{ url('/currencies') }}' + '/' + id[1],
                    data: {_method: 'delete', _token: token},
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