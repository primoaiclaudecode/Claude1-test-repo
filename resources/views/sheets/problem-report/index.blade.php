@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Corrective Action Report</strong>
        </header>

        <section class="dataTables-padding">
            @if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
            @endif

            {!! Form::open(['url' => 'sheets/problem-report/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'problem_report']) !!}

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-md-offset-6 control-label custom-labels">CAR #:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('id', $carNum, array('class' => 'form-control', 'readonly' => 'readonly')) }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">User:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('user_name', $userName, array('class' => 'form-control margin-bottom-15', 'readonly' => 'readonly')) }}
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Date:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        {{ Form::text('problem_date', $problemDate ? $problemDate : $todayDate, array('id' => 'problem_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 1, 'readonly' => 'readonly')) }}
                        <span class="input-group-addon cursor-pointer" id="problem_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Category:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {!! Form::select('problem_type', $problemTypes, $selectedProblemType ?: 'Select Problem Type', ['class'=>'form-control margin-bottom-15','id' => 'problem_type','placeholder' => 'Select Problem Type', 'tabindex' => 2]) !!}
                    <span id="problem_type_span" class="error_message"></span>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Unit:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    @if($selectAll == 1)
                        {!! Form::select('unit_name', [''=>'Select Unit']+$userUnits->toArray(), $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'Select Unit', 'tabindex' => 3, 'onchange' => 'supplierBox(this.value)']) !!}
                    @else
                        {!! Form::select('unit_name', $userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'Select Unit', 'tabindex' => 3, 'onchange' => 'supplierBox(this.value)']) !!}
                    @endif
                    <span id="unit_name_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group supplier-div">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Supplier:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <span id="supplier_span">
                        {{ Form::text('supplier', '', array('id' => 'supplier', 'class' => 'form-control', 'tabindex' => 4)) }}
                    </span>
                    <span id="suppliers_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group feedback-div">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Feedback:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    <div class="radio">
                        <label class="radio-inline left-padding-0 margin-right-20">
                            <input type="radio" name="feedback" class="feedback" tabindex="4" value="1" {{ $feedbackPositive }}> Positive
                        </label>

                        <label class="radio-inline">
                            <input type="radio" name="feedback" class="feedback" tabindex="4" value="2" {{ $feedbackNegative }}> Negative
                        </label>

                        <label class="radio-inline">
                            <input type="radio" name="feedback" class="feedback" tabindex="4" value="3" {{ $feedbackComment }}> Comment
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group comment-div">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Comments:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    {{ Form::textarea('comments', $comments, array('class' => 'form-control', 'rows' => 2, 'tabindex' => 5, 'id' => 'comments')) }}
                    <span id="details_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Details:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    {{ Form::textarea('details', $details, array('class' => 'form-control', 'rows' => 2, 'tabindex' => 5, 'id' => 'details')) }}
                    <span id="details_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">File:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <input type='button' id="browse_btn" class="btn btn-primary btn-block button margin-bottom-15" name='browse_btn'
                           value='Attach Files'
                           tabindex='6' style="margin-top: 1px !important;"/>
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Attached Files:</label>
                @if(count($dir_file_arr1) > 0 && !empty($dir_file_arr1))
                    <div class="col-xs-12 col-sm-9 col-md-4" id="attached_file_name" style="text-align: left;padding-top: 5px;">
                        @foreach($dir_file_arr1 as $key => $value)
                            <span>
								<a href="{{ url('/laravel-filemanager/') }}/{{ $value}}" target="_blank">{{ $key }}</a>
								<a href="javascript:void(0);" class="del-file" style="float:right;" data-fid="{{ $value }}">
									<i class="fa fa-trash fa-fw"></i>
								</a>
								<br>
							</span>
                        @endforeach
                    </div>
                @else
                    <div class="col-xs-12 col-md-4" id="attached_file_name" style="text-align: left;padding-top: 5px;"></div>
                @endif
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Root Cause Analysis:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    <div class="radio">
                        <label class="radio-inline left-padding-0 margin-right-47">
                            <input type="radio" name="root_cause_analysis" tabindex="7" value="1" {{ $rootCauseAnalysisYes }}> Yes
                        </label>

                        <label class="radio-inline">
                            <input type="radio" name="root_cause_analysis" tabindex="7" value="2" {{ $rootCauseAnalysisNo }}> No
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group hidden_element root_cause_analysis-div">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Root Cause Analysis Description:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    {{ Form::textarea('root_cause_analysis_desc', $rootCauseAnalysisDesc, array('class' => 'form-control', 'rows' => 2, 'tabindex' => 8, 'id' => 'root_cause_analysis_desc')) }}
                    <span id="root_cause_analysis_desc_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group hidden_element root_cause_analysis-div">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Action:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    {{ Form::textarea('action', $rootCauseAnalysisAction, array('class' => 'form-control', 'rows' => 2, 'tabindex' => 9, 'id' => 'action')) }}
                    <span id="action_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">CAR Status:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    <div class="radio">
                        <label class="radio-inline left-padding-0 margin-right-34">
                            <input type="radio" name="problem_status" class="problem_status" tabindex="10" value="1" {{ $problemStatusOpen }}> Open
                        </label>

                        <label class="radio-inline">
                            <input type="radio" name="problem_status" class="problem_status" tabindex="10" value="0" {{ $problemStatusClosed }}>
                            Closed
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group hidden_element problem_status-div">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Closing Comments:</label>
                <div class="col-xs-12 col-sm-9 col-md-10">
                    {{ Form::textarea('closing_comments', $closingComments, array('class' => 'form-control', 'rows' => 2, 'tabindex' => 11, 'id' => 'closing_comments')) }}
                    <span id="closing_comments_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group hidden_element problem_status-div">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">User:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    {{ Form::text('closed_by', $userName, array('class' => 'form-control margin-bottom-15', 'readonly' => 'readonly')) }}
                </div>

                <label class="col-xs-12 col-sm-3 col-md-2 control-label custom-labels">Date:</label>
                <div class="col-xs-12 col-sm-9 col-md-4">
                    <div class="input-group">
                        {{ Form::text('closed_date', $closedDate ?: $todayDate, array('id' => 'closed_date', 'class' => 'form-control cursor-pointer', 'tabindex' => 12, 'readonly' => 'readonly')) }}
                        <span class="input-group-addon cursor-pointer" id="closed_date_icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group set-margin-left-0 set-margin-right-0">
                {{ Form::hidden('sheet_id', $sheetId) }}
                {{ Form::hidden('file_id', implode(',', $dir_file_arr1), array('id' => 'file_id')) }}
                {{ Form::hidden('hidden_unit_name', $unitName, array('id' => 'hidden_unit_name')) }}
                {{ Form::hidden('hidden_supplier', $supplierName, array('id' => 'hidden_supplier')) }}
                {{ Form::hidden('hidden_problem_type', $problemType, array('id' => 'hidden_problem_type')) }}
                <input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-25" name='submit' value='Submit' tabindex='13'/>
            </div>
            {!!Form::close()!!}
        </section>
    </section>
    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg" style="width:80%;">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modal_title"><strong>{{ trans('laravel-filemanager::lfm.title-panel') }}</strong></h4>
                </div>
                <div class="container-fluid" id="wrapper">
                    <div class="row">
                        <div class="col-sm-2 hidden-xs">
                            <div id="tree"></div>
                        </div>

                        <div class="col-sm-10 col-xs-12" id="main">
                            <nav class="navbar navbar-default" id="nav">
                                <div class="navbar-header">
                                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#nav-buttons">
                                        <span class="sr-only">Toggle navigation</span>
                                        <span class="icon-bar"></span>
                                        <span class="icon-bar"></span>
                                        <span class="icon-bar"></span>
                                    </button>
                                    <a class="navbar-brand clickable hide" id="to-previous">
                                        <i class="fa fa-arrow-left"></i>
                                        <span class="hidden-xs">{{ trans('laravel-filemanager::lfm.nav-back') }}</span>
                                    </a>
                                    <a class="navbar-brand visible-xs" href="#">{{ trans('laravel-filemanager::lfm.title-panel') }}</a>
                                </div>
                                <div class="collapse navbar-collapse" id="nav-buttons">
                                    <ul class="nav navbar-nav navbar-right">
                                        <li>
                                            <a class="clickable" id="thumbnail-display">
                                                <i class="fa fa-th-large"></i>
                                                <span>{{ trans('laravel-filemanager::lfm.nav-thumbnails') }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="clickable" id="list-display">
                                                <i class="fa fa-list"></i>
                                                <span>{{ trans('laravel-filemanager::lfm.nav-list') }}</span>
                                            </a>
                                        </li>
                                        <li class="dropdown">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                                               aria-expanded="false">
                                                {{ trans('laravel-filemanager::lfm.nav-sort') }} <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href="#" id="list-sort-alphabetic">
                                                        <i class="fa fa-sort-alpha-asc"></i> {{ trans('laravel-filemanager::lfm.nav-sort-alphabetic') }}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" id="list-sort-time">
                                                        <i class="fa fa-sort-amount-asc"></i> {{ trans('laravel-filemanager::lfm.nav-sort-time') }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <div class="visible-xs" id="current_dir" style="padding: 5px 15px;background-color: #f8f8f8;color: #5e5e5e;"></div>

                            <div id="alerts"></div>

                            <div id="content"></div>
                        </div>

                        <ul id="fab">
                            <li>
                                <a href="#"></a>
                                <ul class="hide">
                                    <li>
                                        <a href="#" id="add-folder" data-mfb-label="{{ trans('laravel-filemanager::lfm.nav-new') }}">
                                            <i class="fa fa-folder"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="upload" data-mfb-label="{{ trans('laravel-filemanager::lfm.nav-upload') }}">
                                            <i class="fa fa-upload"></i>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aia-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="myModalLabel">{{ trans('laravel-filemanager::lfm.title-upload') }}</h4>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('unisharp.lfm.upload') }}" role='form' id='uploadForm' name='uploadForm' method='post'
                                      enctype='multipart/form-data' class="dropzone">
                                    <div class="form-group" id="attachment">
                                        <div class="controls text-center">
                                            <div class="input-group" style="width: 100%">
                                                <a class="btn btn-primary"
                                                   id="upload-button">{{ trans('laravel-filemanager::lfm.message-choose') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <input type='hidden' name='working_dir' id='working_dir'>
                                    <input type='hidden' name='type' id='type' value='{{ request("type") }}'>
                                    <input type='hidden' name='_token' value='{{csrf_token()}}'>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">{{ trans('laravel-filemanager::lfm.btn-close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="lfm-loader">
                    <img src="{{asset('vendor/laravel-filemanager/img/loader.svg')}}">
                </div>
            </div>

        </div>
    </div>

    <div id="upload_file" class="modal fade">
        {!! Form::open(['url' => '', 'class' => 'form-horizontal form-bordered', 'files' => true]) !!}
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="panel-title">File Upload</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group margin-bottom-0">
                        <label class="col-md-2 control-label modal-label" for="name">Browse: </label>
                        <div class="col-md-10">
                            <input type='file' name="file_name" id="name" tabindex="2" class="form-control"/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" name="submit" value="Upload File" class="btn btn-primary">Upload File</button>
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    </div>
@stop

@section('scripts')
    <style>
        .checkbox label, .radio label {
            min-height: 20px;
            padding-left: 20px !important;
            margin-bottom: 0;
            font-weight: 400;
            cursor: pointer;
        }

        .form-horizontal .control-label {
            text-align: left !important;
        }
    </style>

    <script src="{{ elixir('js/problem_report.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $(document).on("click", "#btn_upload_file", function (e) {
                $("#myModal").modal('hide');
                $('#upload_file').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });
            });

            $(document).on("click", ".fileClass", function (e) {
                //attached_file_name
                var file_val = $(this).val();
                var fileSplit = file_val.split("~");
                $('#attached_file_name').html(fileSplit[1]);
                $('#file_id').val(fileSplit[0]);

            });
            $(document).on("click", "#browse_btn", function (e) {
                $('#myModal').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });
            });

            var unitId = $("#unit_name").val();

            var selectedSupplier = {{ $selectedSupplier }}

            $("#unit_name").change(function () {
                $('#hidden_unit_name').val($(this).find(':selected').text());
            });

            $('#hidden_problem_type').val($("#problem_type option:selected").text());

            setTimeout(function () {
                var problem_type1 = $('#problem_type').val();
                var radioValue = $("input[name='feedback']:checked").val();
                if (radioValue == 3 && problem_type1 == 6) {
                    $(".comment-div").show(1000);
                } else {
                    $(".comment-div").hide(1000);
                }
            }, 1000);

            if ($.isNumeric(selectedSupplier)) {
                supplierBox(unitId, selectedSupplier);
            }

            $('#problem_date_icon').click(function () {
                $(document).ready(function () {
                    $("#problem_date").datepicker().focus();
                });
            });

            $('#closed_date_icon').click(function () {
                $(document).ready(function () {
                    $("#closed_date").datepicker().focus();
                });
            });
        });

        function supplierBox(unitId, selectedSupplier) {
            if (unitId) {
                $.ajax({
                    type: 'GET',
                    url: '/unit_suppliers/json',
                    data: {
                        unit_id: unitId
                    }
                }).done(function (data) {
                    if (data.length > 0) {
                        $('#supplier_span').empty();

                        var suppliersList = $('<select />')
                            .attr({
                                id: 'supplier_id',
                                name: 'supplier',
                                tabindex: 4
                            })
                            .addClass('form-control margin-bottom-15');

                        $.each(data, function (index, supplier) {
                            suppliersList.append(
                                $('<option />').val(supplier.suppliers_id).text(supplier.supplier_name)
                            )
                        });

                        suppliersList.removeClass('hidden').val(selectedSupplier || 0);

                        $('#supplier_span').append(suppliersList);
                    } else {
                        $('#supplier_span').html("<input type='text' class='form-control' name='supplier' value='' id='supplier' tabindex='4' />");
                    }
                });
            }
        }
    </script>

    <link rel="shortcut icon" type="image/png" href="{{ asset('vendor/laravel-filemanager/img/folder.png') }}">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
{{--    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">--}}
    <link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/cropper.min.css') }}">
    <style>{!! \File::get(base_path('vendor/sam/laravel-filemanager/public/css/lfm.css')) !!}</style>
    {{-- Use the line below instead of the above if you need to cache the css. --}}
    {{-- <link rel="stylesheet" href="{{ asset('/vendor/laravel-filemanager/css/lfm.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/mfb.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/dropzone.min.css') }}">
    <!-- <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.css"> -->

{{--    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>--}}
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <!-- <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script> -->
    <script src="{{ asset('vendor/laravel-filemanager/js/cropper.min.js') }}"></script>
    <script src="{{ asset('vendor/laravel-filemanager/js/jquery.form.min.js') }}"></script>
    <script src="{{ asset('vendor/laravel-filemanager/js/dropzone.min.js') }}"></script>
    <script>
        var route_prefix = "{{ url('/') }}";
        var lfm_route = "{{ url(config('lfm.url_prefix', config('lfm.prefix'))) }}";
        var lang = {!! json_encode(trans('laravel-filemanager::lfm')) !!};
    </script>
    <script>{!! \File::get(base_path('vendor/sam/laravel-filemanager/public/js/script.js')) !!}</script>
    {{-- Use the line below instead of the above if you need to cache the script. --}}
    {{-- <script src="{{ asset('vendor/laravel-filemanager/js/script.js') }}"></script> --}}
    <script>
        $.fn.fab = function () {
            var menu = this;
            menu.addClass('mfb-component--br mfb-zoomin').attr('data-mfb-toggle', 'hover');
            var wrapper = menu.children('li');
            wrapper.addClass('mfb-component__wrap');
            var parent_button = wrapper.children('a');
            parent_button.addClass('mfb-component__button--main')
                .append($('<i>').addClass('mfb-component__main-icon--resting fa fa-plus'))
                .append($('<i>').addClass('mfb-component__main-icon--active fa fa-times'));
            var children_list = wrapper.children('ul');
            children_list.find('a').addClass('mfb-component__button--child');
            children_list.find('i').addClass('mfb-component__child-icon');
            children_list.addClass('mfb-component__list').removeClass('hide');
        };
        $('#fab').fab({
            buttons: [
                {
                    icon: 'fa fa-folder',
                    label: "{{ trans('laravel-filemanager::lfm.nav-new') }}",
                    attrs: {id: 'add-folder'}
                },
                {
                    icon: 'fa fa-upload',
                    label: "{{ trans('laravel-filemanager::lfm.nav-upload') }}",
                    attrs: {id: 'upload'}
                }
            ]
        });

        Dropzone.options.uploadForm = {
            paramName: "upload[]", // The name that will be used to transfer the file
            uploadMultiple: false,
            parallelUploads: 5,
            clickable: '#upload-button',
            dictDefaultMessage: 'Or drop files here to upload',
            init: function () {
                var _this = this; // For the closure
                this.on('success', function (file, response) {
                    if (response == 'OK') {
                        refreshFoldersAndItems('OK');
                    } else {
                        this.defaultOptions.error(file, response.join('\n'));
                    }
                });
            },
            acceptedFiles: "{{ lcfirst(str_singular(request('type') ?: '')) == 'image' ? implode(',', config('lfm.valid_image_mimetypes')) : implode(',', config('lfm.valid_file_mimetypes')) }}",
            maxFilesize: ({{ lcfirst(str_singular(request('type') ?: '')) == 'image' ? config('lfm.max_image_size') : config('lfm.max_file_size') }} / 1000)
        }
    </script>
@stop
