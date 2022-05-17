@extends('layouts/dashboard_master')

@section('content')
  	<section class="panel">
        <header class="panel-heading">
            <strong>Unit Month End Closing</strong>
        </header>

		<section class="dataTables-padding">
  			@if(Session::has('flash_message'))
                <div class="alert alert-success"><em> {!! session('flash_message') !!}</em></div>
  			@endif

            {!! Form::open(['url' => 'accounts/unit-month-end-closing/confirmation', 'class' => 'form-horizontal form-bordered', 'id' => 'unit_month_end_closing_form']) !!}

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Month:</label>
                <div class="col-xs-12 col-sm-4">
                    {{ Form::selectMonth('month', $month, ['id' => 'month', 'class' => 'form-control', 'placeholder' => 'Select Month', 'tabindex' => 1, 'autofocus']) }}
                    <span id="month_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Year:</label>
                <div class="col-xs-12 col-sm-4">
                    {{ Form::selectYear('year', date("Y")-1, date("Y"), $year, ['id' => 'year', 'class' => 'form-control', 'placeholder' => 'Select Year', 'tabindex' => 2]) }}
                    <span id="year_span" class="error_message"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Unit Name:</label>
                <div class="col-xs-12 col-sm-4">
                    {!! Form::select('unit_name', $userUnits, $selectedUnit, ['id' => 'unit_name', 'class'=>'form-control', 'placeholder' => 'Select Unit', 'tabindex' => 3, 'onchange' => 'purchasesSalesUnitCloseJson()']) !!}
                    <span id="unit_name_span" class="error_message"></span>
                </div>
   			</div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Supervisor:</label>
                <div class="col-xs-12 col-sm-4">
                    {{ Form::text('supervisor', $supervisor, array('id' => 'supervisor', 'class' => 'form-control', 'tabindex' => 4)) }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Purchases:</label>
                <div class="col-xs-12 col-sm-4">
                    <table class="table table-hover table-bordered table-striped margin-bottom-5">
                        <thead>
                            <tr>
                                <th class="text-center">Total Net</th>
                                <th class="text-center">Total VAT</th>
                                <th class="text-center">Total Gross</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="row" id="total_net">0.00</td>
                                <td id="total_vat">0.00</td>
                                <td id="total_gross">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 control-label custom-labels">Sales:</label>
                <div class="col-xs-12 col-sm-4">
                    <table class="table table-hover table-bordered table-striped margin-bottom-5">
                        <thead>
                            <tr>
                                <th class="text-center">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="row" id="total_sales">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="btn-toolbar">
                {{ Form::hidden('hidden_unit_name', $unitName, array('id' => 'hidden_unit_name')) }}
                <div id="closed_unit" class="error_message margin-bottom-10"></div>
                <input type='submit' id="submit_btn" class="btn btn-primary btn-md button" name='submit' value='Submit' tabindex="5" />
                
                @if($isSuLevel)
                    {{ Form::hidden('post_btn', 'Unfreeze Unit / Month') }}
                    <input type='submit' class="btn btn-primary btn-md" name='submit' value='Un-close' tabindex="6" />
                @endif
            </div>

   			{!!Form::close()!!}
       	</section>
  	</section>
@stop

@section('scripts')
    <!-- <script src="{{asset('js/unit_month_end_closing_js.js')}}"></script> -->

    <script type="text/javascript">
        $(document).ready(function() {
            $("#unit_name").change(function() {
                $('#hidden_unit_name').val($(this).find(':selected').text());
            });
            $("#month").change(function() {
                purchasesSalesUnitCloseJson();
            });
            $("#year").change(function() {
                purchasesSalesUnitCloseJson();
            });            
            purchasesSalesUnitCloseJson();
            
            $("#unit_month_end_closing_form").validate({
               errorElement: 'small',
               rules: {
                  month: {
                     required: true
                  },
                  year: {
                     required: true
                  },
                  unit_name: {
                     required: true
                  }
               },
               messages: {
                  month: {
                     required:"Please select month."
                  },
                  year: {
                     required:"Please select year."
                  },
                  unit_name: {
                     required:"Please select unit name."
                  }
               },
               submitHandler: function (form) {
                  form.submit();
               }
            });            
        });

        function purchasesSalesUnitCloseJson(selectedValue) {
            var selectedMonth   = $("#month option:selected").val();
            var selectedYear    = $("#year option:selected").val();
            var selectedUnit    = $("#unit_name option:selected").val();
            
            if(selectedMonth >0 && selectedYear >0 && selectedUnit >=0) {
                var selectedMonth   = $("#month").val();
                var selectedYear    = $("#year").val();

                $.ajax({
                    type: 'GET',
                    url: "{{ url('/purchases-sales-unit-close/json') }}",
                    data: { unit_name: selectedUnit, month: selectedMonth, year: selectedYear}
                }).done(function( data ) {
                    var obj = jQuery.parseJSON(data);

                    if(obj.total_net > 0)
                        $('#total_net').html(obj.total_net);
                    else
                        $('#total_net').html('0.00');

                    if(obj.total_vat > 0)
                        $('#total_vat').html(obj.total_vat);
                    else
                        $('#total_vat').html('0.00');

                    if(obj.total_gross > 0)
                        $('#total_gross').html(obj.total_gross);
                    else
                        $('#total_gross').html('0.00');

                    if(obj.total_sales > 0)
                        $('#total_sales').html(obj.total_sales);
                    else
                        $('#total_sales').html('0.00');

                    if(obj.closed_unit_error_msg) {
                        $('.button').removeAttr("id");
                        $('#closed_unit').slideDown("slow");
                        $('#closed_unit').html(obj.closed_unit_error_msg);
                        $('.button').slideUp();
                        $('.unfreeze_button').slideDown("slow");
                        // disable enter key if error
                        $('#unit_month_end_closing_form').bind("keyup keypress", function(e) {
                          var code = e.keyCode || e.which;
                          if (code  == 13) {
                                e.preventDefault();
                                return false;
                          }
                        });
                    } else {
                        // enable enter key if error
                        $('.button').attr("id", "submit_btn");
                        $('#unit_month_end_closing_form').bind("keyup keypress", function(e) {
                            var code = e.keyCode || e.which;
                            if (code  == 13) {
                                e.preventDefault();
                                $('#submit_btn').click();
                            }
                        });
                        $('#closed_unit').slideUp("slow");
                        $('.unfreeze_button').slideUp("slow");
                        $('.button').slideDown("slow");
                    }
                });
            }else{
               $('#total_net').html('0.00');
               $('#total_vat').html('0.00');
               $('#total_gross').html('0.00');
               $('#total_sales').html('0.00');
            }
        }
    </script>
@stop