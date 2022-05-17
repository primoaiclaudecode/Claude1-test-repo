@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Tax Codes Management</strong></div>
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
					<a name="create_new" alt="Create New Tax Code" title="Create New Tax Code" class="btn pull-right btn-primary"
					   href="{{ url('/taxcodes/create') }}">
						<i class="fa fa-plus"></i> Create New Tax Code
					</a>
				</div>
			</div>
		</header>

		<section class="dataTables-padding">
			@if(Session::has('flash_message'))
				<div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
			@endif
			<div class="alert alert-success cash_credit_purch_msg"></div>
			<table id="example" class="display margin-bottom-10" cellspacing="0" width="100%">
				<thead>
				<tr>
					<th class="text-align-center"><input type="checkbox" id="select_all"/></th>
					<th>ID</th>
					<th>Tax Code Title</th>
					<th>Tax Rate</th>
					<th>Tax Code Display Rate</th>
					<th class="text-align-center">Cash Purchase</th>
					<th class="text-align-center">Credit Purchase</th>
					<th class="text-align-center">Credit Sales</th>
					<th class="text-align-center">Vending Sales</th>
					<th></th>
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
					<th class="text-align-center"><input type="checkbox" id="select_all_cash_purch"></th>
					<th class="text-align-center"><input type="checkbox" id="select_all_credit_purch"></th>
					<th class="text-align-center"><input type="checkbox" id="select_all_credit_sales"></th>
					<th class="text-align-center"><input type="checkbox" id="select_all_vending_sales"></th>
					<th></th>
					<th></th>
				</tr>
				</tfoot>
				<tbody>
				<!-- Datatables renders here. -->
				</tbody>
			</table>
		</section>
	</section>

	<div class="modal fade" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button id="save_changes" type="button" class="btn btn-primary">Save changes</button>
				</div>
			</div>
		</div>
	</div>
@stop

@section('scripts')
	<script type="text/javascript" class="init">
        function showSettings(event, taxCodeId) {
            event.preventDefault();

            $.ajax({
                type: 'GET',
                url: "/taxcodes_data/net-ext-settings",
                data: {
                    tax_code_id: taxCodeId
                },
                dataType: 'json'
            }).done(function (data) {
                $('#settingsModal .modal-title').text('Vending Sales net ext for ' + data.taxCode);

                $('#settingsModal .modal-body').empty();

                $('#settingsModal .modal-body')
                    .append(
                        $('<input />').attr({id: 'tax_code_id', type: 'hidden'}).val(taxCodeId)
                    )

                $.each(data.goods, function (index, item) {
                    $('#settingsModal .modal-body')
                        .append(
                            $('<div />').addClass('form-group')
                                .append(
                                    $('<label />').addClass('normal-font-weight')
                                        .append(
                                            $('<input />').attr({
                                                name: 'net_ext',
                                                type: 'checkbox'
                                            }).addClass('margin-right-8').prop('checked', item.selected).val(item.id)
                                        )
                                        .append(
                                            item.name
                                        )
                                )
                        )
                });

                $('#settingsModal').modal('show');
            });
        }

        $(document).ready(function () {
            oTable = $('#example').DataTable({
                scrollX: true,
                dom: 'frtiBp',
                "order": [[1, "desc"]],
                processing: true,
                serverSide: true,
                "ajax": ({
                    url: "{{ url('/taxcodes_data/json') }}", // json datasource
                    "deferRender": true,
                    error: function () {  // error handling
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display", "none");
                    }
                }),
                "columnDefs": [
                    {
                        className: "text-align-center",
                        bSortable: false,
                        "targets": [0, 5, 6, 7, 8, 9, 10]
                    }
                ],
                buttons: [
                    {
                        text: 'Delete',
                        className: 'red',
                        action: function (e, dt, node, config) {
                            var taxCodeCount = $('.checkboxs:checked').length;
                            var taxCodeToDel = taxCodeCount == 1 ? 'this tax code' : 'these tax codes';
                            var result = confirm("Do you want to delete " + taxCodeToDel + "?");
                            if (result) {
                                if (taxCodeCount > 0) {  // at-least one checkbox checked
                                    var ids = [];
                                    $('.checkboxs').each(function () {
                                        if ($(this).is(':checked')) {
                                            ids.push($(this).val());
                                        }
                                    });
                                    var ids_string = ids.toString();  // array to string conversion
                                    $.ajax({
                                        type: "post",
                                        url: '{{ url('/taxcodes') }}' + '/' + ids_string,
                                        data: {_method: 'delete', _token: '{{csrf_token()}}'},
                                        success: function (data) {
                                            var taxCodeIds = data.split(',');
                                            for (var i = 0; i < taxCodeCount; i++) {
                                                oTable
                                                    .row($('#tr_' + taxCodeIds[i]))
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
                        text: 'Submit',
                        className: 'green',
                        action: function (e, dt, node, config) {
                            $("div.cash_credit_purch_msg").css("display", "none");
                            $("div.alert-success").css("display", "none");

                            var taxCodes = {};

                            $.each(['cash_purch', 'credit_purch', 'credit_sales', 'vending_sales'], function (index, el) {
                                taxCodes[el] = [];

                                $('.' + el + '_chk:checked').each(function () {
                                    taxCodes[el].push($(this).val());
                                });
                            })

                            $.ajax({
                                type: "post",
                                url: '{{ url('/taxcodes_data/apply') }}',
                                data: {
                                    _method: 'get',
                                    taxCodes: taxCodes,
                                },
                                success: function () {
                                    $("div.cash_credit_purch_msg").css("display", "block").html('Action has been completed successfully');

                                    $.each(['cash_purch', 'credit_purch', 'credit_sales', 'vending_sales'], function (index, el) {
                                        if ($('.' + el + '_chk:checked').length == $('.' + el + '_chk').length) {
                                            $("#select_all_" + el).prop('checked', true);
                                        } else {
                                            $("#select_all_cash_" + el).prop('checked', false);
                                        }
                                    })
	                                
	                                dt.draw(false);
                                }
                            });
                        }
                    }
                ],
                "language": {
                    "search": "Find:"
                },
                "paging": false,
                "info": false,
                stateSave: true
            });

            $.each(['cash_purch', 'credit_purch', 'credit_sales', 'vending_sales'], function (index, el) {
                setTimeout(function() {
                    $("#select_all_" + el).prop('checked', $('.' + el + '_chk:checked').length == $('.' + el + '_chk').length);
                }, 3000)

                $('#select_all_' + el).change(function () {
                    $('.' + el + 'credit_purch_chk').prop('checked', $(this).prop("checked"));
                });

                $(document).on('change', '.' + el + '_chk', function () {
                    $("#select_all_" + el).prop('checked', $('.' + el + '_chk:checked').length == $('.' + el + '_chk').length);
                });
            })

            //"del_chks" change for delete
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

            //select all checkboxes to delete
            $("#select_all").change(function () {  //"select all" change 
                $(".checkboxs").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
            });

            // This code removes row from datatable
            $('#example tbody').on('click', 'button.delete', function () {
                var result = confirm("Do you want to delete this tax code?");
                if (result) {
                    var token = $('button.delete').attr('data-token');
                    var rowID = $(this).closest('tr').attr('id');
                    var id = rowID.split('_');
                    $.ajax({
                        type: 'post',
                        url: '{{ url('/taxcodes') }}' + '/' + id[1],
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

            $('#save_changes').on('click', function (event) {
                event.preventDefault();

                var netExt = [];

                $('#settingsModal input[name="net_ext"]:checked').each(function (index, el) {
                    netExt.push($(el).val())
                })

                $.ajax({
                    type: 'POST',
                    url: "/taxcodes_data/net-ext-settings/save",
                    data: {
                        _token: '{{csrf_token()}}',
                        tax_code_id: $('#tax_code_id').val(),
                        net_ext: netExt
                    },
                }).done(function () {
                    $('#settingsModal').modal('hide');
                }).fail(function () {
                    $('#settingsModal .modal-body')
                        .prepend(
                            $('<div />').addClass('alert alert-danger').text('Error saving settings.')
                        )
                });

            });
        });
	</script>
@stop