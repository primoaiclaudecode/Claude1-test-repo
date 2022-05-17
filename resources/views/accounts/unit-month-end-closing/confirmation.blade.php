@extends('layouts/dashboard_master')

@section('content')
  	<section class="panel">
        <header class="panel-heading">
            <strong>Unit Month End Closing Confirmation</strong>
        </header>

    		<section class="dataTables-padding">
            {!! Form::open(['url' => 'accounts/unit-month-end-closing/post', 'class' => 'form-horizontal form-bordered']) !!}
                <div class="form-group">
                    <div class="col-md-12">
                        {!! $confStr !!}
                    </div>
                </div>
            {!!Form::close()!!}

            {!! Form::open(['url' => 'accounts/unit-month-end-closing', 'name' => 're_enter_frm', 'id' => 're_enter_frm']) !!}
                {{ Form::hidden('return_from', 'confirm') }}
                {{ Form::hidden('unit_id', $unitId) }}
                {{ Form::hidden('unit_name', $unitName) }}
                {{ Form::hidden('month', $month) }}
                {{ Form::hidden('year', $year) }}
                {{ Form::hidden('supervisor', $supervisor) }}
            </form>

            <p><a href='javascript: void(0)' onclick="document.forms['re_enter_frm'].submit();">Go back and re-enter unit month end closing</a><br /></p>

       	</section>
  	</section>
@stop