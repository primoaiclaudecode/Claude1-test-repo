@extends('layouts/dashboard_master')

@section('content')
    <div class="panel panel-default col-lg-12">
        <div class="row">
            <div class="">
                <div class="panel-heading">{{ $loggedUserGroup }} Area</div>
                <div class="panel-body">
                    <div id="error">
                        <a id="return_link" href="/">
                            <i class="fa fa-home"></i>
                        </a>
                        <span id="error_message">Too Many Request</span>
                    </div>

                    <p>You are currently logged in as {{ $loggedUserName }}.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <style>
        #error {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        #error_message {
            letter-spacing: .05em;
            text-transform: uppercase;
            font-size: 2rem;
            padding: 1rem;
            border-left: 1px solid #737373;
        }

        #return_link{
            color: #a9d96c;
            font-size: 4rem;
            padding: 1rem;
        }
    </style>
@stop