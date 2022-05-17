@extends('layouts/dashboard_master')

@section('content')
    <section class="panel">
        <header class="panel-heading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Purchases Summary Report</strong></div>
            </div>
        </header>

        <section class="dataTables-padding">
        @if(Session::has('flash_message'))
            <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
        @endif
        <div class="fixed-width-table-with-x-scroll purchases-summary">
            {!! $table !!}
        </div>
        <div class="btn-toolbar margin-top-25">
            {!! Form::open(['url' => 'reports/purchases-summary/export-to-csv']) !!}
                {{ Form::hidden('unit_id', $unitId) }}
                {{ Form::hidden('from_date', $fromDate) }}
                {{ Form::hidden('to_date', $toDate) }}
                {{ Form::hidden('purch_type', $purchaseType) }}
                <input type='submit' id="submit_btn" class="btn btn-primary btn-md" name='submit' value='Export to CSV' />
            {!!Form::close()!!}
        </div>
        </section>
    </section>
@stop

@section('scripts')

@stop