@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>User Management</strong></div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                    <a name="create_new" alt="Create New User" title="Create New User" class="btn pull-right btn-primary"
                       href="{{ url('/users/create') }}">
                        <i class="fa fa-plus"></i> Create New User
                    </a>
                </div>
            </div>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif
            <table id="example" class="display nowrap" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th class="text-align-center"><input type="checkbox" id="select_all"/></th>
                    <th>ID</th>
                    <th>Username</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Group Member</th>
                    <th>Unit Member</th>
                    <th class="text-align-center">Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- Datatables renders here. -->
                </tbody>
                <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <input type="text" placeholder="Search"/>
                    </td>
                    <td></td>
                </tr>
                </tfoot>

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
            top: 41px
        }
    </style>
    <script type="text/javascript" class="init">
        $(document).ready(function () {
            // Data table
            oTable = $('#example').DataTable({
                scrollX: true,
                dom: '<f<t>lBip>',
                order: [[1, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    url: "{{ url('/users_data/json') }}", // json datasource
                    deferRender: true,
                    error: function () {  // error handling
                        $(".employee-grid-error").html("");
                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                        $("#employee-grid_processing").css("display", "none");

                    }
                }),
                columnDefs: [
                    {
                        className: "text-align-center",
                        bSortable: false,
                        targets: [0, 9]
                    }
                ],
                buttons: [
                    {
                        text: 'Delete',
                        className: 'red',
                        action: function (e, dt, node, config) {
                            var userCount = $('.checkboxs:checked').length;
                            var userToDel = userCount == 1 ? 'this user' : 'these users';
                            var result = confirm("Do you want to delete " + userToDel + "?");
                            if (result) {
                                if (userCount > 0) {  // at-least one checkbox checked
                                    var ids = [];
                                    $('.checkboxs').each(function () {
                                        if ($(this).is(':checked')) {
                                            ids.push($(this).val());
                                        }
                                    });
                                    var ids_string = ids.toString();  // array to string conversion
                                    $.ajax({
                                        type: "post",
                                        url: '{{ url('/users') }}' + '/' + ids_string,
                                        data: {_method: 'delete', _token: '{{csrf_token()}}'},
                                        success: function (data) {
                                            var userIds = data.split(',');
                                            for (var i = 0; i < userCount; i++) {
                                                oTable
                                                    .row($('#tr_' + userIds[i]))
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
                language: {
                    search: "Find:"
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false
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
                var result = confirm("Do you want to delete this user?");
                if (result) {
                    var token = $('button.delete').attr('data-token');
                    var rowID = $(this).closest('tr').attr('id');
                    var id = rowID.split('_');
                    $.ajax({
                        type: 'post',
                        url: '{{ url('/users') }}' + '/' + id[1],
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
        });
    </script>
@stop