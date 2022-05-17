@extends('layouts.dashboard_master')
@section('content')
<div class="panel panel-default col-lg-12">
    <div class="row">
        <div class="">
            <div class="panel-heading">{{ $loggedUserGroup }} Area</div>
            <div class="panel-body">
                <p><img src="img/CCSL-Logo-FINAL_.jpg"></p>
                <p>Welcome to CCSL Site Accounts Management System.</p>
                <p>You are currently logged in as {{ $loggedUserName }}.</p>
            </div>
        </div>
    </div>
</div>
@endsection
