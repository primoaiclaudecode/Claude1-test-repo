@extends('layouts/dashboard_master')

@section('content')
  	<section class="panel">
        <header class="panel-heading">
            <strong>Sage Confirm</strong>
        </header>

		<section class="dataTables-padding">
  			@if(Session::has('flash_message'))
                <div class="alert alert-danger"><em> {!! session('flash_message') !!}</em></div>
  			@endif

            @if(count($errors) > 0)
                <div class="alert alert-danger">
                    <em>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </em>
                </div>
            @endif

            {!! Form::open(['url' => 'accounts/sage-confirm', 'class' => 'bsi_report_form form-horizontal form-bordered', 'id' => 'bsi_report_form', 'files' => true]) !!}
			<div class="form-group">
                <label class="col-xs-5 col-sm-3 control-label custom-labels upload-csv">Upload CSV File:</label>
                <div class="col-xs-7 col-sm-4">
                    {!! Form::file('csv_file', array('class' => 'form-control')) !!}
                    <span id="unit_name_span" class="error_message"></span>
                </div>
   			</div>

            <div class="btn-toolbar">
                <input type='submit' id="submit_btn" class="btn btn-primary btn-md" name='submit' value='Submit' />
                <input type='button' id="cancel_btn" class="btn btn-primary btn-md" name='cancel' value='Cancel' onclick="window.location='{{ $backUrl }}'" />
            </div>
   			{!!Form::close()!!}
       	</section>
  	</section>
@stop