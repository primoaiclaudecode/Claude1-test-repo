@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <strong>Operations Scorecard Edit</strong>
        </header>

        @if(!$operationsScorecard)
            <div class="alert alert-danger margin-top-25"><em>Operation Scorecard not found</em></div>
        @else
            <section class="dataTables-padding">
                @if(Session::has('flash_message'))
                    <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
                @endif

                @if(Session::has('error_message'))
                    <div class="alert alert-danger"><em> {!! session('error_message') !!}</em></div>
                @endif

                {!! Form::open(['url' => 'sheets/operations-scorecard/save', 'class' => 'form-horizontal form-bordered', 'id' => 'operations_scorecard_frm']) !!}
                {{ Form::hidden('ops_scorecard_id', $operationsScorecard->ops_scorecard_id) }}

                <div class="form-group">
                    <label class="col-xs-12 col-sm-4 col-md-3 control-label custom-labels">Unit Name:</label>
                    <div class="col-xs-12 col-sm-8 col-md-3">
                        {!! Form::select('unit_id', $userUnits, $operationsScorecard->unit_id, ['id' => 'unit_id', 'class'=>'form-control margin-bottom-15', 'placeholder' => 'Select Unit', 'tabindex' => 1, 'autofocus']) !!}
                    </div>

                    <label class="col-xs-12 col-sm-4 col-md-3 control-label custom-labels">Date:</label>
                    <div class="col-xs-12 col-sm-8 col-md-3">
                        <div class="input-group">
                            {{ Form::text('scorecard_date', $scorecardDate, array('id' => 'scorecard_date', 'class' => 'form-control text-right cursor-pointer', 'tabindex' => 2, 'readonly' => '')) }}
                            <span class="input-group-addon cursor-pointer" id="scorecard_date_icon">
								<i class="fa fa-calendar"></i>
							</span>
                        </div>
                    </div>
                </div>

                <div class="form-group margin-top-25">
                    <div class="col-xs-12">
                        <span class="legend label label-danger margin-right-20 margin-bottom-5 pull-left">1-3 Poor – Action Required</span>
                        <span class="legend label label-warning margin-right-20 margin-bottom-5 pull-left">4-6 Below average - Above average</span>
                        <span class="legend label label-success margin-right-20 margin-bottom-5 pull-left">7-9 Meeting expectation - Exceeding expectation</span>
                        <span class="legend label label-primary margin-right-20 margin-bottom-5 pull-left">10 Excellent</span>
                    </div>
                </div>

                <div class="form-group margin-left-0 margin-right-0">
                    <div class="col-md-12 margin-top-25">
                        <div class="responsive-content">
                            <table id="operations_scorecard_tbl" class="table table-bordered table-striped table-small">
                                <thead>
                                <tr>
                                    <th width="50px"></th>
                                    <th width="250px">Performance metrics</th>
                                    <th width="150px" class="text-center">Score</th>
                                    <th class="text-center">Notes</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="presentation_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->presentation_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->presentation_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Food offer/Presentation</td>
                                    <td>
                                        {!! Form::select('presentation', $score, $operationsScorecard->presentation, ['id' => 'presentation', 'class'=>'form-control', 'tabindex' => 3]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('presentation_notes', $operationsScorecard->presentation_notes, array('id' => 'presentation_notes', 'class' => 'form-control', 'tabindex' => 4)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="foodcost_awareness_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->foodcost_awareness_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->foodcost_awareness_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Food costings / account awareness</td>
                                    <td>
                                        {!! Form::select('foodcost_awareness', $score, $operationsScorecard->foodcost_awareness, ['id' => 'foodcost_awareness', 'class'=>'form-control', 'tabindex' => 5]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('foodcost_awareness_notes', $operationsScorecard->foodcost_awareness_notes, array('id' => 'foodcost_awareness_notes', 'class' => 'form-control', 'tabindex' => 5)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="hr_issues_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->hr_issues_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->hr_issues_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">HR Issues</td>
                                    <td>
                                        {!! Form::select('hr_issues', $score, $operationsScorecard->hr_issues, ['id' => 'hr_issues', 'class'=>'form-control', 'tabindex' => 7]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('hr_issues_notes', $operationsScorecard->hr_issues_notes, array('id' => 'hr_issues_notes', 'class' => 'form-control', 'tabindex' => 8)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="morale_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->morale_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->morale_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Staff Morale</td>
                                    <td>
                                        {!! Form::select('morale', $score, $operationsScorecard->morale, ['id' => 'morale', 'class'=>'form-control', 'tabindex' => 9]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('morale_notes', $operationsScorecard->morale_notes, array('id' => 'morale_notes', 'class' => 'form-control', 'tabindex' => 10)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="purch_compliance_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->purch_compliance_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->purch_compliance_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Purchasing compliance</td>
                                    <td>
                                        {!! Form::select('purch_compliance', $score, $operationsScorecard->purch_compliance, ['id' => 'purch_compliance', 'class'=>'form-control', 'tabindex' => 11]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('purch_compliance_notes', $operationsScorecard->purch_compliance_notes, array('id' => 'purch_compliance_notes', 'class' => 'form-control', 'tabindex' => 12)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="haccp_compliance_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->haccp_compliance_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->haccp_compliance_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">HACCP compliance</td>
                                    <td>
                                        {!! Form::select('haccp_compliance', $score, $operationsScorecard->haccp_compliance, ['id' => 'haccp_compliance', 'class'=>'form-control', 'tabindex' => 13]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('haccp_compliance_notes', $operationsScorecard->haccp_compliance_notes, array('id' => 'haccp_compliance_notes', 'class' => 'form-control', 'tabindex' => 14)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="health_safety_iso_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->health_safety_iso_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->health_safety_iso_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Health and Safety compliance</td>
                                    <td>
                                        {!! Form::select('health_safety_iso', $score, $operationsScorecard->health_safety_iso, ['id' => 'health_safety_iso', 'class'=>'form-control', 'tabindex' => 15]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('health_safety_iso_notes', $operationsScorecard->health_safety_iso_notes, array('id' => 'health_safety_iso_notes', 'class' => 'form-control', 'tabindex' => 16)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="accidents_incidents_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->accidents_incidents_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->accidents_incidents_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Accidents / Incidents</td>
                                    <td>
                                        {!! Form::select('accidents_incidents', $score, $operationsScorecard->accidents_incidents, ['id' => 'accidents_incidents', 'class'=>'form-control', 'tabindex' => 17]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('accidents_incidents_notes', $operationsScorecard->accidents_incidents_notes, array('id' => 'accidents_incidents_notes', 'class' => 'form-control', 'tabindex' => 18)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="security_cash_ctl_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->security_cash_ctl_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->security_cash_ctl_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Site security and cash control</td>
                                    <td>
                                        {!! Form::select('security_cash_ctl', $score, $operationsScorecard->security_cash_ctl, ['id' => 'security_cash_ctl', 'class'=>'form-control', 'tabindex' => 19]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('security_cash_ctl_notes', $operationsScorecard->security_cash_ctl_notes, array('id' => 'security_cash_ctl_notes', 'class' => 'form-control', 'tabindex' => 20)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="marketing_upselling_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->marketing_upselling_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->marketing_upselling_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Marketing / Upselling</td>
                                    <td>
                                        {!! Form::select('marketing_upselling', $score, $operationsScorecard->marketing_upselling, ['id' => 'marketing_upselling', 'class'=>'form-control', 'tabindex' => 21]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('marketing_upselling_notes', $operationsScorecard->marketing_upselling_notes, array('id' => 'marketing_upselling_notes', 'class' => 'form-control', 'tabindex' => 22)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="training_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->training_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->training_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Training</td>
                                    <td>
                                        {!! Form::select('training', $score, $operationsScorecard->training, ['id' => 'training', 'class'=>'form-control', 'tabindex' => 23]) !!}
                                    </td>
                                    <td>
                                        {{ Form::text('training_notes', $operationsScorecard->training_notes, array('id' => 'training_notes', 'class' => 'form-control', 'tabindex' => 24)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="objectives_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->objectives_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->objectives_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Objectives (Month)</td>
                                    <td>
                                    </td>
                                    <td>
                                        {{ Form::text('objectives', $operationsScorecard->objectives, array('id' => 'objectives', 'class' => 'form-control', 'tabindex' => 25)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="outstanding_issues_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->outstanding_issues_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->outstanding_issues_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Issues outstanding</td>
                                    <td></td>
                                    <td>
                                        {{ Form::text('outstanding_issues', $operationsScorecard->outstanding_issues, array('id' => 'outstanding_issues', 'class' => 'form-control', 'tabindex' => 26)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="sp_projects_functions_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->sp_projects_functions_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->sp_projects_functions_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Special projects/functions</td>
                                    <td></td>
                                    <td>
                                        {{ Form::text('sp_projects_functions', $operationsScorecard->sp_projects_functions, array('id' => 'sp_projects_functions', 'class' => 'form-control', 'tabindex' => 27)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="innovation_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->innovation_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->innovation_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Innovation/Chef's WhatsApp Group</td>
                                    <td></td>
                                    <td>
                                        {{ Form::text('innovation', $operationsScorecard->innovation, array('id' => 'innovation', 'class' => 'form-control', 'tabindex' => 28)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <label class="private-field-label">
                                            <input type="checkbox" name="add_support_required_private" value="1"
                                                   class="hidden private-field" {{ $operationsScorecard->add_support_req_private ? 'checked' : ''}}/>
                                            <i class="fa {{ $operationsScorecard->add_support_req_private ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        </label>
                                    </td>
                                    <td class="vertical-align-middle">Additional Support required</td>
                                    <td></td>
                                    <td>
                                        {{ Form::text('add_support_required', $operationsScorecard->add_support_req, array('id' => 'add_support_required', 'class' => 'form-control', 'tabindex' => 29)) }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-group margin-left-0 margin-right-0">
                    <div class="col-xs-10 col-md-3">
                        <input type='button' id="browse_btn" class="btn btn-primary btn-block button margin-bottom-15" name='browse_btn'
                               value='Attach Files' tabindex='6' style="margin-top: 1px !important;"/>
                    </div>

                    <div class="col-xs-12 col-md-9">
                        {{ Form::hidden('file_id', implode(',', $attachedFiles), array('id' => 'file_id')) }}
                        <div id="attached_file_name">
                            @foreach($attachedFiles as $fileName => $fileUrl)
                                <span>
                                    <a href="{{ $fileUrl}}" target="_blank">{{ $fileName }}</a>
                                    <a href="javascript:void(0);" class="del-file" style="float:right;" data-fid="{{ $fileUrl }}">
                                        <i class="fa fa-trash fa-fw"></i>
                                    </a>
                                    <br>
    							</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-group margin-left-0 margin-right-0">
                    <div class="col-xs-12">
                        <label>
                            <input type="checkbox" name="send_email" value="1" {{ $operationsScorecard->send_email ? 'checked' : ''}} />
                            Send this sheet via email
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <input type='submit' id="submit_btn" class="btn btn-primary btn-block button margin-top-35"
                               name='submit' value='Save' tabindex='30'/>
                    </div>
                </div>
                {!!Form::close()!!}
            </section>

            <!-- Modals -->
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
                                    <div class="visible-xs" id="current_dir"
                                         style="padding: 5px 15px;background-color: #f8f8f8;color: #5e5e5e;"></div>

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
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                    aia-hidden="true">&times;</span>
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
        @endif
    </section>
@stop

@section('scripts')
    <style>
        .highlighted {
            color: #F00 !important;
        }

        .legend {
            text-align: left;
            font-size: 14px;
            white-space: pre-wrap;
        }

        .private-field-label {
            font-size: 24px;
        }

        #browse_btn {
            color: #fff;
            background-color: #337ab7;
            border-color: #2e6da4;
        }
    </style>
    <script src="{{ elixir('js/operations-scorecard.js') }}"></script>

    <!-- File Manager -->
    <link rel="shortcut icon" type="image/png" href="{{ asset('vendor/laravel-filemanager/img/folder.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/cropper.min.css') }}">
    <style>{!! \File::get(base_path('vendor/sam/laravel-filemanager/public/css/lfm.css')) !!}</style>
    <link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/mfb.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/laravel-filemanager/css/dropzone.min.css') }}">

    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>

    <script src="{{ asset('vendor/laravel-filemanager/js/cropper.min.js') }}"></script>
    <script src="{{ asset('vendor/laravel-filemanager/js/jquery.form.min.js') }}"></script>
    <script src="{{ asset('vendor/laravel-filemanager/js/dropzone.min.js') }}"></script>
    <script>{!! \File::get(base_path('vendor/sam/laravel-filemanager/public/js/script.js')) !!}</script>

    <script>
        var route_prefix = "{{ url('/') }}";
        var lfm_route = "{{ url(config('lfm.url_prefix', config('lfm.prefix'))) }}";
        var lang = {!! json_encode(trans('laravel-filemanager::lfm')) !!};

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
