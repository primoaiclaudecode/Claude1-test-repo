@extends('layouts/dashboard_master')

@section('content')
	<section class="panel">
		<header class="panel-heading">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Net Extensions Management</strong></div>
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
					<a name="create_new" alt="Create New Net Extension" title="Create New Net Extension" class="btn pull-right btn-primary"
					   href="{{ url('/netexts/create') }}">
						<i class="fa fa-plus"></i> Create New Net Extension
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
					<th>Net Extension</th>
					<th>Nominal Code</th>
					<th class="text-align-center">Cash Purchase</th>
					<th class="text-align-center">Credit Purchase</th>
					<th class="text-align-center">Vending Sales</th>
					<th class="text-align-center">COS</th>
					<th class="text-align-center">Action</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th class="text-align-center"><input type="checkbox" id="select_all_cash_purch"></th>
					<th class="text-align-center"><input type="checkbox" id="select_all_credit_purch"></th>
                    <th class="text-align-center"><input type="checkbox" id="select_all_vending_sales"></th>
					<th class="text-align-center"><input type="checkbox" id="select_all_cost_of_sales"></th>
					<th class="text-align-center"></th>
				</tr>
				</tfoot>
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
            oTable = $('#example').DataTable({
                scrollX: true,
                // responsive: true,
                dom: 'frtiBp',
                "order": [[1, "desc"]],
                processing: true,
                serverSide: true,
                "ajax": ({
                    url: "{{ url('/netexts_data/json') }}", // json datasource
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
                        targets: [0, 4, 5, 6, 7, 8]
                    }
                ],
                buttons: [
                    {
                        text: 'Delete',
                        className: 'red',
                        action: function (e, dt, node, config) {
                            var netExtCount = $('.checkboxs:checked').length;
                            var netExtToDel = netExtCount == 1 ? 'this net extension' : 'these net extensions';
                            var result = confirm("Do you want to delete " + netExtToDel + "?");
                            if (result) {
                                if (netExtCount > 0) {  // at-least one checkbox checked
                                    var ids = [];
                                    $('.checkboxs').each(function () {
                                        if ($(this).is(':checked')) {
                                            ids.push($(this).val());
                                        }
                                    });
                                    var ids_string = ids.toString();  // array to string conversion
                                    $.ajax({
                                        type: "post",
                                        url: '{{ url('/netexts') }}' + '/' + ids_string,
                                        data: {_method: 'delete', _token: '{{csrf_token()}}'},
                                        success: function (data) {
                                            var netExtIds = data.split(',');
                                            for (var i = 0; i < netExtCount; i++) {
                                                oTable
                                                    .row($('#tr_' + netExtIds[i]))
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
                        extend: 'colvis',
                        columns: ':not(:first-child)'
                    },
                    {
                        text: 'Submit',
                        className: 'green',
                        action: function (e, dt, node, config) {
                            var cashPurchCount = $('.cash_purch_chk:checked').length;
                            if (cashPurchCount > 0) {  // at-least one checkbox checked
                                $("div.cash_credit_purch_msg").css("display", "none");
                                $("div.alert-success").css("display", "none");
                                var cashPurchIds = [];
                                $('.cash_purch_chk').each(function () {
                                    if ($(this).is(':checked')) {
                                        cashPurchIds.push($(this).val());
                                    }
                                });

                                $.ajax({
                                    type: "post",
                                    url: '{{ url('/netexts_data/cash_credit_purch') }}',
                                    data: {_method: 'get', cashPurch: cashPurchIds},
                                    dataType: "json",
                                    success: function (data) {
                                        if (data) {
                                            $("div.cash_credit_purch_msg").css("display", "block");
                                            $("div.cash_credit_purch_msg").html(data);

                                            if ($('.cash_purch_chk:checked').length == $('.cash_purch_chk').length)
                                                $("#select_all_cash_purch").prop('checked', true);
                                            else
                                                $("#select_all_cash_purch").prop('checked', false);
                                        }
                                    }
                                });
                            }

                            var creditPurchCount = $('.credit_purch_chk:checked').length;
                            if (creditPurchCount > 0) {  // at-least one checkbox checked
                                $("div.cash_credit_purch_msg").css("display", "none");
                                var creditPurchIds = [];
                                $('.credit_purch_chk').each(function () {
                                    if ($(this).is(':checked')) {
                                        creditPurchIds.push($(this).val());
                                    }
                                });

                                $.ajax({
                                    type: "post",
                                    url: '{{ url('/netexts_data/cash_credit_purch') }}',
                                    data: {_method: 'get', creditPurch: creditPurchIds},
                                    dataType: "json",
                                    success: function (data) {
                                        if (data) {
                                            if ($('.credit_purch_chk:checked').length == $('.credit_purch_chk').length)
                                                $("#select_all_credit_purch").prop('checked', true);
                                            else
                                                $("#select_all_credit_purch").prop('checked', false);
                                        }
                                    }
                                });
                            }

                            var vendingSalesCount = $('.vending_sales_chk:checked').length;
                            if (vendingSalesCount > 0) {  // at-least one checkbox checked
                                $("div.cash_credit_purch_msg").css("display", "none");
                                var vendingSalesIds = [];
                                $('.vending_sales_chk').each(function () {
                                    if ($(this).is(':checked')) {
                                        vendingSalesIds.push($(this).val());
                                    }
                                });

                                $.ajax({
                                    type: "post",
                                    url: '{{ url('/netexts_data/cash_credit_purch') }}',
                                    data: {_method: 'get', vendingSales: vendingSalesIds},
                                    dataType: "json",
                                    success: function (data) {
                                        if (data) {
                                            if ($('.vending_sales_chk:checked').length == $('.vending_sales_chk').length)
                                                $("#select_all_vending_sales").prop('checked', true);
                                            else
                                                $("#select_all_vending_sales").prop('checked', false);
                                        }
                                    }
                                });
                            }
                            
                            var costOfSaleCount = $('.cost_of_sales_chk:checked').length;
                            if (costOfSaleCount > 0) {  // at-least one checkbox checked
                                $("div.cash_credit_purch_msg").css("display", "none");
                                var costOfSaleIds = [];
                                $('.cost_of_sales_chk').each(function () {
                                    if ($(this).is(':checked')) {
                                        costOfSaleIds.push($(this).val());
                                    }
                                });

                                $.ajax({
                                    type: "post",
                                    url: '{{ url('/netexts_data/cash_credit_purch') }}',
                                    data: {_method: 'get', costOfSale: costOfSaleIds},
                                    dataType: "json",
                                    success: function (data) {
                                        if (data) {
                                            if ($('.cost_of_sales_chk:checked').length == $('.cost_of_sales_chk').length)
                                                $("#select_all_cost_of_sales").prop('checked', true);
                                            else
                                                $("#select_all_cost_of_sales").prop('checked', false);
                                        }
                                    }
                                });
                            }


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

            setTimeout(function () {
                if ($('.cash_purch_chk:checked').length == $('.cash_purch_chk').length) {
                    $("#select_all_cash_purch").prop('checked', true);
                }
                
                if ($('.credit_purch_chk:checked').length == $('.credit_purch_chk').length) {
                    $("#select_all_credit_purch").prop('checked', true);
                }

                if ($('.vending_sales_chk:checked').length == $('.vending_sales_chk').length) {
                    $("#select_all_vending_sales").prop('checked', true);
                }
                
                if ($('.cost_of_sales_chk:checked').length == $('.cost_of_sales_chk').length) {
                    $("#select_all_cost_of_sales").prop('checked', true);
                }
            }, 3000);
        });

        //select all checkboxes to delete
        $("#select_all").change(function () {  //"select all" change 
            $(".checkboxs").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
        });

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

        //select all checkboxes for cash purchases
        $("#select_all_cash_purch").change(function () {  //"select all" change 
            $(".cash_purch_chk").prop('checked', $(this).prop("checked")); //change all ".cash_purch_chk" checked status
        });

        //".checkbox" change for cash purchases
        $(document).on("change", "input[name='cash_purch[]']", function () {
            //uncheck "select all", if one of the listed checkbox item is unchecked
            if (false == $(this).prop("checked")) { //if this item is unchecked
                $("#select_all_cash_purch").prop('checked', false); //change "select all" checked status to false
            }
            //check "select all" if all checkbox items are checked
            if ($('.cash_purch_chk:checked').length == $('.cash_purch_chk').length) {
                $("#select_all_cash_purch").prop('checked', true);
            }
        });

        //select all checkboxes for credit purchases
        $("#select_all_credit_purch").change(function () {  //"select all" change 
            $(".credit_purch_chk").prop('checked', $(this).prop("checked")); //change all ".credit_purch_chk" checked status
        });

        //"credit_purch" change for credit purchases
        $(document).on("change", "input[name='credit_purch[]']", function () {
            //uncheck "select all", if one of the listed checkbox item is unchecked
            if (false == $(this).prop("checked")) { //if this item is unchecked
                $("#select_all_credit_purch").prop('checked', false); //change "select all" checked status to false
            }
            //check "select all" if all checkbox items are checked
            if ($('.credit_purch_chk:checked').length == $('.credit_purch_chk').length) {
                $("#select_all_credit_purch").prop('checked', true);
            }
        });


        //select all checkboxes for vending sales
        $("#select_all_vending_sales").change(function () {  //"select all" change 
            $(".vending_sales_chk").prop('checked', $(this).prop("checked")); //change all ".credit_purch_chk" checked status
        });

        //"credit_purch" change for credit purchases
        $(document).on("change", "input[name='vending_sales[]']", function () {
            //uncheck "select all", if one of the listed checkbox item is unchecked
            if (false == $(this).prop("checked")) { //if this item is unchecked
                $("#select_all_vending_sales").prop('checked', false); //change "select all" checked status to false
            }
            //check "select all" if all checkbox items are checked
            if ($('.vending_sales_chk:checked').length == $('.vending_sales_chk').length) {
                $("#select_all_vending_sales").prop('checked', true);
            }
        });

        //select all checkboxes for cost of sale
        $("#select_all_cost_of_sales").change(function () {  //"select all" change 
            $(".cost_of_sales_chk").prop('checked', $(this).prop("checked")); //change all ".cost_of_sales_chk" checked status
        });
        
        // This code removes row from datatable
        $('#example tbody').on('click', 'button.delete', function () {
            var result = confirm("Do you want to delete this net extension?");
            if (result) {
                var token = $('button.delete').attr('data-token');
                var rowID = $(this).closest('tr').attr('id');
                var id = rowID.split('_');
                $.ajax({
                    type: 'post',
                    url: '{{ url('/netexts') }}' + '/' + id[1],
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