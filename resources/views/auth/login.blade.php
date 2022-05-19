@extends('layouts.app')

@section('content')
<div id="sheet">
        <section class="body-sign">
            <div class="center-sign login_white_bg">
                <a href="/" class="logo pull-left">
                    {!! Html::image('/img/CCSL-logo-mainABM.jpg', 'S.A.M', array('height' => 65)) !!}
                </a>

                <div class="panel panel-sign">
                    <div class="panel-title-sign mt-xl text-right">
                        <h2 class="title text-uppercase text-bold m-none"><i class="fa fa-user mr-xs"></i> Sign In</h2>
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
                            {{ csrf_field() }}
                            <div class="mb-lg{{ $errors->has('username') ? ' has-error' : '' }}">
                                <label>Username</label>
                                <div class="input-group input-group-icon">
                                    <input name="username" type="text" class="form-control input-lg" tabindex="1" />

                                    @if ($errors->has('username'))
                                        <span class="help-block margin-bottom-0">
                                            <strong>{{ $errors->first('username') }}</strong>
                                        </span>
                                    @endif

                                    <span class="input-group-addon">
                                        <span class="icon icon-lg">
                                            <i class="fa fa-user"></i>
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-lg{{ $errors->has('password') ? ' has-error' : '' }}">
                                <div class="clearfix">
                                    <label class="pull-left">Password</label>
                                </div>
                                <div class="input-group input-group-icon">
                                    <input name="password" type="password" class="form-control input-lg" tabindex="2" />

                                    @if ($errors->has('password'))
                                        <span class="help-block margin-bottom-0">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif

                                    <span class="input-group-addon">
                                        <span class="icon icon-lg">
                                            <i class="fa fa-lock"></i>
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-8">
                                    <div class="checkbox-custom checkbox-default">
                                        <input id="remember" name="remember" type="checkbox" tabindex="3"/>
                                        <label for="remember">Remember Me</label>
                                    </div>
                                </div>
                                <div class="col-sm-4 text-right">
                                    <button type="submit" name="submit" value="Login" class="btn btn-primary hidden-xs" tabindex="4">Sign In</button>
                                    <button type="submit" name="submit" value="Login" class="btn btn-primary btn-block btn-lg visible-xs mt-lg">Sign In</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                <p class="text-center text-muted mt-md mb-md">&copy; Copyright 2015 Primo Solutions. All Rights Reserved.</p>
            </div>
        </section>
    </div>
@endsection
