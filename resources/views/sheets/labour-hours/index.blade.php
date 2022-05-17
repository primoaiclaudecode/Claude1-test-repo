@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Labour Hours</strong>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            {!! Form::open(['url' => 'sheets/labour-hours/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'labour_hours_form']) !!}
            <div class="form-group">
                <label class="col-xs-12 col-sm-4 col-md-3 control-label custom-labels">Unit Name:</label>
                <div class="col-xs-12 col-sm-8 col-md-4">
                    {!! Form::select('unit_name', $userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus', 'onchange' => 'labourHoursRemaining(this.value)']) !!}
                    <span id="unit_name_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-4 col-md-3 control-label custom-labels">Labour Hours Remaining:</label>
                <div class="col-xs-12 col-sm-8 col-md-4">
                    {{ Form::text('labour_hours_remaining', 0, array('class' => 'form-control text-tablet-right text-phone-right', 'id' => 'labour_hours_remaining', 'readonly' => 'readonly')) }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-4 col-md-3 control-label custom-labels">Supervisor:</label>
                <div class="col-xs-12 col-sm-8 col-md-4">
                    {{ Form::text('supervisor', $supervisor, array('id' => 'supervisor', 'class' => 'form-control', 'tabindex' => 2)) }}
                </div>
            </div>

            <div class="form-group">
                <div class="col-xs-12">
                    <div class="responsive-content">
                        <table class="table table-bordered table-striped table-small">
                            <thead>
                            <tr>
                                <th width="5%">&nbsp;</th>
                                <th width="20%"><strong>Hrs</strong></th>
                                <th width="20%"><strong>&nbsp;Date</strong></th>
                                <th width="55%"><strong>Labour Type</strong></th>
                            </tr>
                            </thead>
                        </table>

                        @if(isset($reportDataTableStr) && $reportDataTableStr != '')
                            {!! $reportDataTableStr !!}
                        @elseif(isset($dataTableStrShow) && $dataTableStrShow != '')
                            {!! $dataTableStrShow !!}
                        @else
                            <table id="dataTable" class="table table-bordered table-striped table-small">
                                <tr>
                                    <td width="5%">
                                        <input type="checkbox" class="margin-top-10" name="chkbox_0" id="chkbox_0"/>
                                    </td>
                                    <td width="20%">{{ Form::text('hours_0', null, array('id' => 'hours_0', 'class' => 'form-control', 'tabindex' => 4)) }}
                                    </td>
                                    <td width="20%">{{ Form::text('date_0', $todayDate, array('id' => 'date_0', 'class' => 'form-control datepick cursor-pointer', 'tabindex' => 5, 'readonly' => 'readonly')) }}</td>
                                    <td width="55%">
                                        {!! Form::select('labour_type_0', $labourTypes, 'Choose Labour Type', ['class'=>'form-control','id' => 'labour_type_0','placeholder' => 'Choose Labour Type', 'tabindex' => 6]) !!}
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </div>
                </div>

                <div class="col-xs-12">
                    <table>
                        <tr>
                            <td>
                                <div class="hidden_element">
                                    {!! Form::select('labour_type_hidden', $labourTypes, 'Choose Labour Type', ['class'=>'form-control','id' => 'labour_type_hidden','placeholder' => 'Choose Labour Type']) !!}
                                </div>
                                <input id="add_line" class="mb-xs mt-xs mr-xs btn btn-primary" type="button" value="Add Line" onclick="addRow()"
                                       tabindex='70'/>
                                <input id="del_line" class="mb-xs mt-xs mr-xs btn btn-primary" type="button" value="Delete Line" onclick="deleteRow()"
                                       tabindex='71'/>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="form-group set-margin-left-0 set-margin-right-0">
                {{ Form::hidden('hidden_unit_name', $unitName, array('id' => 'hidden_unit_name')) }}
                {{ Form::hidden('rows_counter', $rowsCounter, array('id' => 'rows_counter')) }}
                {{ Form::hidden('sheet_id', $sheetId) }}
                <input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-25" name='submit' value='Add Labour Hours'
                       tabindex='72'/>
            </div>
            {!!Form::close()!!}
        </section>
    </section>
@stop

@section('scripts')
    <script src="{{ elixir('js/labour_hours_js.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#unit_name").change(function () {
                $('#hidden_unit_name').val($(this).find(':selected').text());
            });

            labourHoursRemaining($('#unit_name').val());
        });

        function labourHoursRemaining(selectedValue) {
            if (selectedValue) {
                $.ajax({
                    type: 'GET',
                    url: "{{ url('/labour_hours_remaining/json') }}",
                    data: {unit_name: selectedValue}
                }).done(function (data) {
                    var obj = jQuery.parseJSON(data);
                    $('#labour_hours_remaining').val(obj.labour_hours_remaining);
                });
            }
        }

        $(document).on('focusin', '.datepick', function () {
            $(this).datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            })
        });
    </script>
@stop