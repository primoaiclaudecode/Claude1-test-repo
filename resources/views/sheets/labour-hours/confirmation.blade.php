@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Labour Hours Confirmation</strong>
        </header>

        <section class="dataTables-padding">
            {!! Form::open(['url' => 'sheets/labour-hours/post', 'class' => 'form-horizontal form-bordered']) !!}
            <div class="form-group margin-bottom-0 margin-left-0 margin-right-0">
                <div class="clearfix"></div>
                <div class="table-responsive col-md-12 padding-left-0 padding-right-0 border-top-0">
                    <table id="cash_purchases_tbl" class="table table-bordered table-striped">
                        <tr>
                            <td>
                                <label>Unit Name:</label>
                                {{ Form::text('unit_name_1', $unitName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                                {{ Form::hidden('unit_id', $unitId) }}
                                {{ Form::hidden('supervisor_id', $userId) }}
                                {{ Form::hidden('supervisor_name', $userName) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Supervisor:</label>
                                {{ Form::text('supervisor_name', $userName, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Labour Hours Used:</label>
                                {{ Form::text('labour_hours_used', $labourHoursUsed, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>Updated Labour Hours Remaining:</label>
                                {{ Form::text('labour_hours_remaining', $labourHoursRemaining, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <h5>Are you sure you want to submit these labour hours?</h5>
                    {{ Form::hidden('sheet_id', $sheetId) }}
                    <input type='submit' class='btn btn-primary btn-block' name='submit' value='Confirm Labour Hours'/>
                </div>
            </div>
            {!!Form::close()!!}

            {!! Form::open(['url' => 'sheets/labour-hours', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
                {{ Form::hidden('return_from', 'confirm') }}
                {{ Form::hidden('unit_id', $unitId) }}
                {{ Form::hidden('unit_name', $unitName) }}
                {{ Form::hidden('rows_counter', $rowsCounter) }}
                {{ Form::hidden('data_table_hidden', $dataTableStr) }}
                {{ Form::hidden('sheet_id', $sheetId) }}
            {!!Form::close()!!}

            <p>
                <a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter labour hours</a>
                <br/>
            </p>
        </section>
    </section>
@stop
@section('scripts')
    <script src="{{asset('js/jquery.backDetect.js')}}"></script>
    <script type="text/javascript">
        $(window).load(function () {
            $('body').backDetect(function () {
                alert('Confirm form resubmission');
                $('#re_enter_frm').submit()
            });
        });

        // Prevent double submit
        $('form').on('submit', function () {
            if ($(this).hasClass('processing')) {
                return false;
            }

            $(this).addClass('processing');
        });
    </script>
@stop