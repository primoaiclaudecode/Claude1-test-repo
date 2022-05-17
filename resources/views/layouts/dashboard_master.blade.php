<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">      
    <link rel="shortcut icon" href="{{asset('img/favicon.png')}}">

    <title>S.A.M</title>

    <!-- Bootstrap core CSS -->
    <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('css/bootstrap-reset.css')}}" rel="stylesheet">
    <link href="{{asset('assets/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css')}}" rel="stylesheet">
    <link href="{{asset('fontawesome/css/font-awesome.css')}}" rel="stylesheet" />
    <link href="{{asset('css/table-responsive.css')}}" rel="stylesheet" />
    @yield('styles')
    <link href="{{asset('css/slidebars.css')}}" rel="stylesheet">
    <link href="{{asset('css/plus.css')}}" rel="stylesheet">
    <link href="{{ elixir('css/style.css') }}" rel="stylesheet">
    <link href="{{ elixir('css/style-responsive.css') }}" rel="stylesheet" />
    <link href="{{ elixir('css/custom.css') }}" rel="stylesheet" />
    <link href="{{asset('css/theme-custom.css')}}" rel="stylesheet" />
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet" />
    <link href="{{asset('css/select2-bootstrap.min.css')}}" rel="stylesheet" />
    <!-- Data Tables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.1.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/3.3.2/css/fixedColumns.dataTables.min.css">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
      <script src="{{asset('js/html5shiv.js')}}"></script>
      <script src="{{asset('js/respond.min.js')}}"></script>
    <![endif]-->
  </head>
 <body class="sticky-header sidebar-collapsed">
 <section id="container" class="{{ $showSidebar ? "" : "sidebar-closed"}}">
    @include("layouts/dashboard_master/header")
    @include("layouts/dashboard_master/sidebar")
    <section id="main-content">
        <section class="wrapper">
            @yield('small_widgets')
            @yield('content')
        </section>
    </section>
 </section>
  <!-- js placed at the end of the document so the pages load faster -->
    <script src="{{asset('js/jquery.js')}}"></script>
    <script src="{{asset('js/bootstrap.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js')}}"></script>
    <script class="include" type="text/javascript" src="{{asset('js/jquery.dcjqaccordion.2.7.js')}}"></script>
    <script src="{{asset('js/jquery.scrollTo.min.js')}}"></script>
    <script src="{{asset('js/slidebars.min.js')}}"></script>
    <script src="{{asset('js/jquery.nicescroll.js')}}" type="text/javascript"></script>
    <script src="{{asset('js/respond.min.js')}}" ></script>

    <!--common script for all pages-->
    <script src="{{ elixir('js/common-scripts.js') }}"></script>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script type="text/javascript">
        $(function() {
            $( "#start_date" ).datepicker({
                format: 'mm-dd-yyyy',
            }).on('changeDate', function(e){    // autoclose: true is not working so using this code
                $(this).datepicker('hide');
            });
            $( "#end_date" ).datepicker({
                format: 'mm-dd-yyyy',
            }).on('changeDate', function(e){
                $(this).datepicker('hide');
            });
            $('#color').colorpicker();
        });
    </script>

    <!-- Data Tables JS -->
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="javascript" src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>

    <script type="text/javascript" language="javascript" src="//cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.datatables.net/buttons/1.2.2/js/buttons.colVis.min.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.datatables.net/responsive/2.1.0/js/dataTables.responsive.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/fixedcolumns/3.3.2/js/dataTables.fixedColumns.min.js"></script>
 
    <script type="text/javascript" language="javascript" src="{{asset('js/jquery.validate.js')}}"></script>
    @yield('scripts')
 </body>
 </html>
