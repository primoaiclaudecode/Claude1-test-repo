@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Active users</strong></div>
            </div>
        </header>

        <section class="dataTables-padding">
            <div class="responsive-content">
                <table id="active_users_table" class="table table-no-wrap table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>User Name</th>
                        <th>IP Address</th>
                        <th>Logged At</th>
                        <th>Last Action At</th>
                        <th>Expired At</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($activeUsers as $activeUser)
                        <tr>
                            <td>{{ $activeUser['userName'] }}</td>
                            <td>{{ $activeUser['ipAddress'] }}</td>
                            <td>{{ $activeUser['createdAt'] }}</td>
                            <td>{{ $activeUser['updatedAt'] }}</td>
                            <td>{{ $activeUser['expiredAt'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <p class="text-align-center">No active users</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>            
        </section>

        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Events</strong></div>
            </div>
        </header>

        <section class="dataTables-padding">
            <div class="responsive-content">
                <table class="table simpleTable table-hover table-bordered table-striped margin-bottom-0">
                    <tbody>
                    <tr>
                        <td>User</td>
                        <td>
                            {!! Form::select('user_id', $users, null, ['id' => 'user_id', 'class'=>'form-control', 'placeholder' => 'Select User Name', 'tabindex' => 1, 'autofocus']) !!}
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
                    </tbody>
                </table>
            </div>            
        </section>

        <section class="dataTables-padding">
            <table id="example" class="display margin-bottom-10" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>User Name</th>
                    <th>IP Address</th>
                    <th>Event</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <!-- Datatables renders here. -->
                </tbody>
                <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
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
			top: 38px
		}
        
        #user_id, #from_date, #to_date {
            min-width: 150px;
        }
    </style>
    <script type="text/javascript" class="init">
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
            }).on('changeDate', function () {
                $('#to_date').focus();
            });

            $('#from_date_icon').click(function () {
                $("#from_date").datepicker().focus();
            });

            $('#to_date_icon').click(function () {
                $("#to_date").datepicker().focus();
            });

            // Apply the search
            $('#example tfoot th').each(function () {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Search ' + title + '" />');
            });

            var oTable = $('#example').DataTable({
                scrollX: true,
                scrollCollapse: true,
                dom: '<f<t>lBip>',
                order: [[3, "desc"]],
                processing: true,
                serverSide: true,
                ajax: ({
                    url: "{{ url('/events/json') }}",
                    data: {
                        user_id: function () {
                            return $('#user_id').val();
                        },
                        from_date: function () {
                            return $('#from_date').val();
                        },
                        to_date: function () {
                            return $('#to_date').val();
                        },
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
                        targets: [2], className: "text-align-center"
                    }
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
                language: {
                    search: "Find:"
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                stateSave: false
            });

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

            // Update table when change user or dates
            $('#user_id, #from_date, #to_date').on('change', function () {
                oTable.draw(false);
            });
        });
    </script>
@stop