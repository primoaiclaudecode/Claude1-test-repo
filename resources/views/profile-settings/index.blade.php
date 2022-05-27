@extends('layouts/dashboard_master')

@section('content')
   <section class="panel">
      <header class="panel-heading">
         <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Side Menu</strong></div>
         </div>
      </header>
      <section class="dataTables-padding">
         <form id="profile_settings" class="form-horizontal form-bordered">
            <div class="form-group">
               <label class="col-lg-2 col-sm-2 col-md-2 col-xs-3 control-label custom-labels">Show sidebar:</label>
               <div class="col-lg-5 col-sm-4 col-md-4 col-xs-9 ">
                  <label id="toggle_sidebar" class="switch">
                     <input type="checkbox" {{ $showSidebar ? "checked" : ""}}>
                     <span class="slider round"></span>
                  </label>
               </div>
            </div>
         </form>
      </section>
   </section>
   
   <section class="panel">
      <header class="panel-heading">
         <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><strong>Favourites Menu</strong></div>
         </div>
      </header>
      <section class="dataTables-padding">
         <form id="add_link_frm" class="form-horizontal form-bordered">
            <div class="form-group">
               <label class="col-lg-2 col-sm-2 col-md-2 col-xs-3 control-label custom-labels">Menu item:</label>
               <div class="col-lg-5 col-sm-4 col-md-4 col-xs-9 ">
                  <select id="link" class="form-control margin-bottom-15">
                     <option value="" selected>Select menu item...</option>
                     <option value="/dashboard">Dashboard</option>
                     @can('admin-user-group')
                        <optgroup label="Administration">
                           @foreach($menu->get('administration') as $item)
                              @if(empty($item->gate) || Gate::allows($item->gate))
                                 <option value="{{$item->link}}">{{ $item->name }}</option>
                              @endif
                           @endforeach
                        </optgroup>
                     @endcan
                     
                     @can('hq-user-group')
                        <optgroup label="Accounts">
                           @foreach($menu->get('accounts') as $item)
                              @if(empty($item->gate) || Gate::allows($item->gate))
                                 <option value="{{$item->link}}">{{ $item->name }}</option>
                              @endif
                           @endforeach
                        </optgroup>
                     @endcan
                     <optgroup label="Sheets">
                        @foreach($menu->get('sheets') as $item)
                           @if(empty($item->gate) || Gate::allows($item->gate))
                              <option value="{{$item->link}}">{{ $item->name }}</option>
                           @endif
                        @endforeach
                     </optgroup>
                     @can('unit-user-group')
                        <optgroup label="Reports">
                           @foreach($menu->get('reports') as $item)
                              @if(empty($item->gate) || Gate::allows($item->gate))
                                 <option value="{{$item->link}}">{{ $item->name }}</option>
                              @endif
                           @endforeach
                        </optgroup>
                     @endcan
                  </select>
               </div>
               <label class="col-lg-1 col-sm-2 col-md-2 col-xs-3 control-label custom-labels">Position:</label>
               <div class="col-lg-2 col-sm-2 col-md-2 col-xs-9">
                  <select id="position" class="form-control margin-bottom-15"></select>
               </div>
               <div class="col-lg-2 col-sm-2 col-md-2 col-xs-12">
                  <button class="btn btn-primary btn-block button">ADD</button>
               </div>
            </div>
            <div id="error_message" class="alert alert-danger hidden" role="alert"></div>
         </form>
      </section>
      <section class="dataTables-padding">
         <ul id="user_menu" class="list-group"></ul>
      </section>
      <div id="edit_position_modal" class="modal fade danger" tabindex="-1" role="dialog">
         <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                  </button>
                  <h4 class="modal-title">Edit position</h4>
               </div>
               <div id="unit_chart" class="modal-body">
                  <input id="link_id" type="hidden" value="0"/>
                  <div class="form-group">
                     <select id="new_position" class="form-control margin-bottom-15"></select>
                  </div>
                  <div class="form-group">
                     <button id="save_position" class="btn btn-primary btn-block button">SAVE</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
@stop

@section('scripts')
   <style>
       .list-group-item {
           display: flex;
           justify-content: space-between;
       }

       .link-position {
           text-align: left;
           flex-basis: 50px;
           margin-right: 15px;
           text-align: center;
           cursor: pointer;
       }

       .link-title {
           text-align: left;
           flex: 1;
       }

       .delete-link {
           text-align: left;
           flex: 0;
           font-size: 18px;
           color: #FF6C60;
           cursor: pointer;
       }

       .added {
           color: #5cb85c;
       }

       .list-group-item:hover {
           background-color: #f5f5f5;
       }

       /* Switch */
       .switch {
           position: relative;
           display: inline-block;
           width: 43px;
           height: 25px;
       }

       /* Hide default HTML checkbox */
       .switch input {
           opacity: 0;
           width: 0;
           height: 0;
       }

       /* The slider */
       .slider {
           position: absolute;
           cursor: pointer;
           top: 0;
           left: 0;
           right: 0;
           bottom: 0;
           background-color: #ccc;
           -webkit-transition: .4s;
           transition: .4s;
           margin-top: 0;
       }

       .slider:before {
           position: absolute;
           content: "";
           height: 17px;
           width: 17px;
           left: 4px;
           bottom: 4px;
           background-color: white;
           -webkit-transition: .4s;
           transition: .4s;
       }

       input:checked + .slider {
           background-color: #A9D86E;
       }

       input:focus + .slider {
           box-shadow: 0 0 1px #A9D86E;
       }

       input:checked + .slider:before {
           -webkit-transform: translateX(17px);
           -ms-transform: translateX(17px);
           transform: translateX(17px);
       }

       .slider.round {
           border-radius: 34px;
       }

       .slider.round:before {
           border-radius: 100%;
       }
   </style>
   
   <script src="{{asset('js/profile-settings.js')}}"></script>
@stop