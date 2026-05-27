<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-dark">

    <div class="text-center py-4" style="background: linear-gradient(90deg, #086375, #1dd3b0) !important;">
        <img src="{{ asset('assets/img/sedes.png') }}" alt="Logo" style="max-width: 100px; width: 100%;">
        
            <span class="fw-bold" style="color: white; font-size: 26px;">{{ Auth::user()->sistema->nombre }}</span>
    </div>
        <div class="menu-header">
            <span class="menu-header-text" style="font-size: 18px; color: #ffc107;">Menu Principal</span>
        </div>
    
    <ul class="menu-inner">
        @if (Auth::user()->hasAnyPermission(['users.view', 'users.management', 'roles.view', 'roles.management']))
        <li class="menu-item {{ request()->routeIs(['users.*', 'roles.*']) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs(['users.*', 'roles.*']) ? 'ri-group-fill' : 'ri-group-line' }} bg-warning"></i>
                <div>Gestion Usuarios</div>
            </a>
            
            <ul class="menu-sub">
                @if (Auth::user()->hasAnyPermission(['users.view', 'users.management']))
                
                <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('users.*') ? 'ri-user-fill' : 'ri-user-line' }} bg-warning"></i>
                        <div>Usuarios</div>
                    </a>
                </li>
                @endif

                @if (Auth::user()->hasAnyPermission(['roles.view', 'roles.management']))
                <li class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('roles.*') ? 'ri-key-fill' : 'ri-key-line' }} bg-warning"></i>
                        <div>Roles</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if (Auth::user()->hasAnyPermission(['gestiones.view', 'gestiones.management', 'establecimientos.view', 'establecimientos.management', 'municipios.view', 'municipios.management', 'reds.view', 'reds.management']))
        <li class="menu-item {{ request()->routeIs(['gestiones.*', 'establecimientos.*', 'municipios.*', 'redes.*']) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs(['gestiones.*', 'establecimientos.*', 'municipios.*', 'redes.*']) ? 'ri-settings-3-fill' : 'ri-settings-3-line' }} bg-warning"></i>
                <div>Configuracion</div>
            </a>
            <ul class="menu-sub">
                @if (Auth::user()->hasAnyPermission(['gestiones.view', 'gestiones.management']))
                <li class="menu-item {{ request()->routeIs('gestiones.*') ? 'active' : '' }}">
                    <a href="{{ route('gestiones.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('gestiones.*') ? 'ri-calendar-2-fill' : 'ri-calendar-2-line' }} bg-warning"></i>
                        <div>Gestiones</div>
                    </a>
                </li>
                @endif
                @if (Auth::user()->hasAnyPermission(['establecimientos.view', 'establecimientos.management']))
                <li class="menu-item {{ request()->routeIs('establecimientos.*') ? 'active' : '' }}">
                    <a href="{{ route('establecimientos.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('establecimientos.*') ? 'ri-building-fill' : 'ri-building-line' }} bg-warning"></i>
                        <div>Establecimientos</div>
                    </a>
                </li>
                @endif
                @if (Auth::user()->hasAnyPermission(['municipios.view', 'municipios.management']))
                <li class="menu-item {{ request()->routeIs('municipios.*') ? 'active' : '' }}">
                    <a href="{{ route('municipios.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-community-{{ request()->routeIs('municipios.*') ? 'fill' : 'line' }} bg-warning"></i>
                        <div>Municipios</div>
                    </a>
                </li>
                @endif
                @if (Auth::user()->hasAnyPermission(['reds.view', 'reds.management']))
                <li class="menu-item {{ request()->routeIs('redes.*') ? 'active' : '' }}">
                    <a href="{{ route('redes.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-git-branch-{{ request()->routeIs('redes.*') ? 'fill' : 'line' }} bg-warning"></i>
                        <div>Redes</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if (Auth::user()->hasAnyPermission(['formularios.view', 'formularios.management', 'variables.view', 'variables.management', 'periodos.view', 'periodos.management']))
        <li class="menu-item {{ request()->routeIs(['formularios.*', 'variables.*', 'subvariables.*', 'cabeceras.*', 'periodos.*']) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs(['formularios.*', 'variables.*', 'subvariables.*', 'cabeceras.*', 'periodos.*']) ? 'ri-database-2-fill' : 'ri-database-2-line' }} bg-warning"></i>
                <div>Gestion Formularios</div>
            </a>
            <ul class="menu-sub">
                @if (Auth::user()->hasAnyPermission(['formularios.view', 'formularios.management']))
                <li class="menu-item {{ request()->routeIs('formularios.*') ? 'active' : '' }}">
                    <a href="{{ route('formularios.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('formularios.*') ? 'ri-survey-fill' : 'ri-survey-line' }} bg-warning"></i>
                        <div>Formularios</div>
                    </a>
                </li>
                @endif
                @if (Auth::user()->hasAnyPermission(['variables.view', 'variables.management']))
                <li class="menu-item {{ request()->routeIs('variables.*') ? 'active' : '' }}">
                    <a href="{{ route('variables.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('variables.*') ? 'ri-database-fill' : 'ri-database-line' }} bg-warning"></i>
                        <div>Variables</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('subvariables.*') ? 'active' : '' }}">
                    <a href="{{ route('subvariables.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('subvariables.*') ? 'ri-function-fill' : 'ri-function-line' }} bg-warning"></i>
                        <div>Subvariables</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('cabeceras.*') ? 'active' : '' }}">
                    <a href="{{ route('cabeceras.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('cabeceras.*') ? 'ri-layout-top-fill' : 'ri-layout-top-line' }} bg-warning"></i>
                        <div>Cabeceras</div>
                    </a>
                </li>
                @endif
                @if (Auth::user()->hasAnyPermission(['periodos.view', 'periodos.management']))
                <li class="menu-item {{ request()->routeIs('periodos.*') ? 'active' : '' }}">
                    <a href="{{ route('periodos.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('periodos.*') ? 'ri-time-fill' : 'ri-time-line' }} bg-warning"></i>
                        <div>Periodos</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @if (Auth::user()->hasAnyPermission(['reportes', 'exportaciones', 'oportunidad']))
        <li class="menu-item {{ request()->routeIs(['reportes.*', 'exportaciones.*']) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs(['reportes.*', 'exportaciones.*']) ? 'ri-booklet-fill' : 'ri-booklet-line' }} bg-warning"></i>
                <div>Reportes</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('reportes.index') ? 'active' : '' }}">
                    <a href="{{ route('reportes.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('reportes.index') ? 'ri-bar-chart-fill' : 'ri-bar-chart-line' }} bg-warning"></i>
                        <div>Por Variable</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('exportaciones.*') ? 'active' : '' }}">
                    <a href="{{ route('exportaciones.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('exportaciones.*') ? 'ri-download-fill' : 'ri-download-line' }} bg-warning"></i>
                        <div>Por Formulario</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('reportes.tabulator') ? 'active' : '' }}">
                    <a href="{{ route('reportes.tabulator') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('reportes.tabulator') ? 'ri-table-fill' : 'ri-table-line' }} bg-warning"></i>
                        <div>Por Variable (Tabulator)</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('reportes.pivote') ? 'active' : '' }}">
                    <a href="{{ route('reportes.pivote') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('reportes.pivote') ? 'ri-table-fill' : 'ri-table-line' }} bg-warning"></i>
                        <div>Dinámico</div>
                    </a>
                </li>
                @if (Auth::user()->hasAnyPermission(['oportunidad']) && Auth::user()->alcances?->tipo != 'establecimiento')
                <li class="menu-item {{ request()->routeIs('reportes.oportunidad') ? 'active' : '' }}">
                    <a href="{{ route('reportes.oportunidad') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('reportes.oportunidad') ? 'ri-file-chart-fill' : 'ri-file-chart-line' }} bg-warning"></i>
                        <div>Oportunidad</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        <!-- ------------------------------------- -->
        @if (Auth::user()->hasPermissionTo('formularios.submit') && (Auth::user()->alcances?->tipo == 'establecimiento'))
        <li class="menu-item {{ request()->routeIs('formularios.menu') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs('formularios.menu') ? 'ri-survey-fill' : 'ri-survey-line' }} bg-warning"></i>
                <div>Formularios</div>  
            </a>
            
            <ul class="menu-sub">

                @if (Auth::user()->alcances?->tipo == 'establecimiento' && Auth::user()->hasAnyPermission(['formularios.submit']))
                <li class="menu-item {{ request()->routeIs('formularios.menu') ? 'active' : '' }}">
                    <a href="{{ route('formularios.menu') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('formularios.menu') ? 'ri-file-edit-fill' : 'ri-file-edit-line' }} bg-warning"></i>
                        <div>Formularios Actuales</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        <!-- <li class="menu-item {{ request()->routeIs(['variables.*', 'subvariables.*', 'cabeceras.*', 'periodos.*', 'gestiones.*']) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs(['variables.*', 'subvariables.*', 'cabeceras.*', 'periodos.*', 'gestiones.*']) ? 'ri-settings-3-fill' : 'ri-settings-3-line' }} bg-warning"></i>
                <div>Reportes Personales</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('gestiones.*') ? 'active' : '' }}">
                    <a href="{{ route('gestiones.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('gestiones.*') ? 'ri-file-list-fill' : 'ri-file-list-line' }} bg-warning"></i>
                        <div>Datos Ingresados</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('variables.*') ? 'active' : '' }}">
                    <a href="{{ route('variables.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('variables.*') ? 'ri-file-chart-fill' : 'ri-file-chart-line' }} bg-warning"></i>
                        <div>Resumen Mensual</div>
                    </a>
                </li>
            </ul>
        </li> -->
    </ul>
</aside>
