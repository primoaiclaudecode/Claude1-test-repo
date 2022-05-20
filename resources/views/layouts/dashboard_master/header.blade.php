
 <!--header start-->
      <header class="header white-bg">
          <div class="sidebar-toggle-box">
              <div data-original-title="Toggle Navigation" data-placement="right" class="fa fa-bars toggle-sidebar tooltips"></div>
              <div class="fa fa-bars toggle-sidebar-mobile"></div>

              <ul class="nav top-menu favourites">
                  <li class="dropdown">
                      <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                          <i class="fa fa-heart"></i>
                          <span class="username">Favourites</span>
                          <b class="caret"></b>
                      </a>
                      <ul class="dropdown-menu favourite-menu">
                          @foreach($favouritesMenu as $menuItem)
                              <li>
                                  <a href="{{ url($menuItem['link']) }}">{{ $menuItem['title'] }}</a>
                              </li>
                          @endforeach
                      </ul>
                      <div style="display: none" favourites="{{ json_encode($favouritesMenu) }} " id="favourites-menu-json"></div>
                  </li>
              </ul>
          </div>

          <a class="logo">
              {!! Html::image('/img/CCSL-logo-mainABM-small.jpg', 'S.A.M', array('class' => 'ccsl-logo')) !!}
          </a>

          <div class="top-nav">
              <!--search & user info start-->
              <ul class="nav pull-right top-menu">
                 
                  <!-- user login dropdown start-->
                  <li class="dropdown">
                      <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                          {!! Html::image('/img/unisex.png') !!}
                          <span class="username">@if(Session::has('userName')) {{ Session::get('userName') }} @endif</span>
                          <b class="caret"></b>
                      </a>
                      <ul class="dropdown-menu extended logout">
                          <div class="log-arrow-up"></div>
                          <li><a href="#"><i class=" fa fa-suitcase"></i>Profile</a></li>
                          <li><a href="/profile-settings"><i class="fa fa-cog"></i> Settings</a></li>
                          <li><a href="#"><i class="fa fa-bell-o"></i> Notification</a></li>
                          <li><a href="{{ url('/logout') }}"><i class="fa fa-key"></i> Log Out</a></li>
                      </ul>
                  </li>                    
                  <!-- user login dropdown end -->
              </ul>
              <!--search & user info end-->
          </div>
      </header>
      <!--header end-->