<div class="side_bar">
    <div class="user_detail">
        <div>
            <h5 class="mg-b-2 tx-spacing--1 mt-3">{{ Auth()->user()->name }}</h5>
        </div>
    </div>
    <div class="social_links">

        <nav class="nav nav-classic tx-13">
            <ul class="p-0">
                @foreach($modules as $module)
                      @if(hasPermissions($module,Auth()->user()->role_id) =="yes" && Auth()->user()->role_id !== "1" && $module['is_active']== 1)
                           <li class="mt-2 mb-3 {{ !empty($module['children']) ? 'dropdown side_bar_dropdown' : '' }}
                            {{ checkInMultiDeminsionalArray($module['children'], 'route_name' , $current_route_name) ? 'active' : '' }}">
                                <a class="nav-link {{ $current_route_name == $module['route_name'] ? 'active' : '' }} {{ !empty($module['children']) ? 'set_dropdown_icon position-relative dropdown-toggle' : '' }}"
                                href="{{ $module['route_name'] == '#' ? '#' : route($module['route_name']) }}"
                                data-toggle="{{ !empty($module['children']) ? 'dropdown' : '' }}">
                                    <i class="{{ $module['icon'] }}"></i> &nbsp;
                                    {{ $module['title'] }}
                                </a>
                                @if(!empty($module['children']))
                                    <div class="dropdown-menu {{ checkInMultiDeminsionalArray($module['children'], 'route_name' , $current_route_name) ? 'show' : '' }}">
                                        @foreach($module['children'] as $children)
                                            @if(hasRole($children['slug'] , 'is_visible'))
                                                <a class="dropdown-item {{ $current_route_name == $children['route_name'] ? 'active' : '' }}"
                                                href="{{ route($children['route_name']) }}">{{ $children['title'] }}</a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @elseif(Auth()->user()->role_id  == "1" && $module['is_active']== 1)
                            <li class="mt-2 mb-3 {{ !empty($module['children']) ? 'dropdown side_bar_dropdown' : '' }}
                            {{ checkInMultiDeminsionalArray($module['children'], 'route_name' , $current_route_name) ? 'active' : '' }}">
                                <a class="nav-link {{ $current_route_name == $module['route_name'] ? 'active' : '' }} {{ !empty($module['children']) ? 'set_dropdown_icon position-relative dropdown-toggle' : '' }}"
                                href="{{ $module['route_name'] == '#' ? '#' : route($module['route_name']) }}"
                                data-toggle="{{ !empty($module['children']) ? 'dropdown' : '' }}">
                                    <i class="{{ $module['icon'] }}"></i> &nbsp;
                                    {{ $module['title'] }}
                                </a>
                                @if(!empty($module['children']))
                                    <div class="dropdown-menu {{ checkInMultiDeminsionalArray($module['children'], 'route_name' , $current_route_name) ? 'show' : '' }}">
                                        @foreach($module['children'] as $children)
                                            @if(hasRole($children['slug'] , 'is_visible'))
                                                <a class="dropdown-item {{ $current_route_name == $children['route_name'] ? 'active' : '' }}"
                                                href="{{ route($children['route_name']) }}">{{ $children['title'] }}</a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @endif
                @endforeach
                <li class="mt-2 mb-3">
                    <a class="nav-link" href="{{ route('change_password') }}" ><i class="fa fa-key"></i>&nbsp;Change Password</a>
                </li>
                <li class="mt-2 mb-3">
                    <a class="nav-link"
                       href="#" onclick="event.preventDefault();
                                   document.getElementById('logout-form').submit();">Sign Out</a>
                </li>
            </ul>
        </nav>
        <form id="logout-form" action="{{ route('logout') }}" method="POST"
              style="display: none;">{{ csrf_field() }}</form>
    </div>
</div>
