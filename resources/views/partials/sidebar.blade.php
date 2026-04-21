<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-dark">

    <div class="text-center py-4" style="background: linear-gradient(90deg, #086375, #1dd3b0) !important;">
        <img src="{{ asset('assets/img/sedes.png') }}" alt="Logo" style="max-width: 100px; width: 100%;">
        
            <span class="fw-bold" style="color: white; font-size: 26px;">{{ Auth::user()->sistema->nombre }}</span>
    </div>
        <div class="menu-header">
            <span class="menu-header-text" style="font-size: 18px; color: #ffc107;">Menú Principal</span>
        </div>
    
    <ul class="menu-inner">
        @if ((Auth::user()->roles->first()->slug == 'admin') || (Auth::user()->roles->first()->slug == 'super-admin'))
        
        <li class="menu-item {{ request()->routeIs(['users.*', 'roles.*']) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs(['users.*', 'roles.*']) ? 'ri-group-fill' : 'ri-group-line' }} bg-warning"></i>
                <div>Gestión de usuarios</div>
            </a>
            
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('users.*') ? 'ri-user-fill' : 'ri-user-line' }} bg-warning"></i>
                        <div>Usuarios</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('roles.*') ? 'ri-key-fill' : 'ri-key-line' }} bg-warning"></i>
                        <div>Roles</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ request()->routeIs(['variables.*', 'subvariables.*', 'cabeceras.*', 'periodos.*', 'gestiones.*']) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs(['variables.*', 'subvariables.*', 'cabeceras.*', 'periodos.*', 'gestiones.*']) ? 'ri-settings-3-fill' : 'ri-settings-3-line' }} bg-warning"></i>
                <div>Configuración del Sistema</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('gestiones.*') ? 'active' : '' }}">
                    <a href="{{ route('gestiones.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('gestiones.*') ? 'ri-calendar-2-fill' : 'ri-calendar-2-line' }} bg-warning"></i>
                        <div>Gestiones</div>
                    </a>
                </li>
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
                <li class="menu-item {{ request()->routeIs('periodos.*') ? 'active' : '' }}">
                    <a href="{{ route('periodos.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('periodos.*') ? 'ri-time-fill' : 'ri-time-line' }} bg-warning"></i>
                        <div>Periodos</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ request()->routeIs(['formularios.*', 'establecimientos.*', 'reportes.*', 'exportaciones.*']) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs(['formularios.*', 'establecimientos.*']) ? 'ri-database-2-fill' : 'ri-database-2-line' }} bg-warning"></i>
                <div>Formularios y Datos</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('establecimientos.*') ? 'active' : '' }}">
                    <a href="{{ route('establecimientos.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('establecimientos.*') ? 'ri-building-fill' : 'ri-building-line' }} bg-warning"></i>
                        <div>Establecimientos</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('formularios.*') ? 'active' : '' }}">
                    <a href="{{ route('formularios.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('formularios.*') ? 'ri-survey-fill' : 'ri-survey-line' }} bg-warning"></i>
                        <div>Formularios</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                    <a href="{{ route('reportes.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('reportes.*') ? 'ri-bar-chart-fill' : 'ri-bar-chart-line' }} bg-warning"></i>
                        <div>Reportes</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('exportaciones.*') ? 'active' : '' }}">
                    <a href="{{ route('exportaciones.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('exportaciones.*') ? 'ri-download-fill' : 'ri-download-line' }} bg-warning"></i>
                        <div>Exportar</div>
                    </a>
                </li>
            </ul>
        </li>
        @else
        
        <li class="menu-item {{ request()->routeIs('formularios.*', 'reportes.*') ? 'open' : '' }}">
            @if (Auth::user()->alcances->tipo == 'establecimiento')
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs('formularios.*', 'reportes.*') ? 'ri-survey-fill' : 'ri-survey-line' }} bg-warning"></i>
                <div>Formularios Activos</div>  
            </a>
            @endif
            
            <ul class="menu-sub">
                @if (Auth::user()->alcances->tipo == 'establecimiento')
                
                <li class="menu-item {{ request()->routeIs('formularios.menu') ? 'active' : '' }}">
                    <a href="{{ route('formularios.menu') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('formularios.menu') ? 'ri-file-edit-fill' : 'ri-file-edit-line' }} bg-warning"></i>
                        <div>Formularios Actuales</div>
                    </a>
                </li>
                @endif
                <li class="menu-item {{ request()->routeIs('') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="menu-link disabled">
                        <i class="menu-icon ri {{ request()->routeIs('roles.*') ? 'ri-alert-fill' : 'ri-alert-line' }} bg-warning"></i>
                        <div>Pendientes</div>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                    <a href="{{ route('reportes.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('reportes.*') ? 'ri-bar-chart-fill' : 'ri-bar-chart-line' }} bg-warning"></i>
                        <div>Reportes</div>
                    </a>
                </li>
            </ul>
        </li>
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
        @endif
    </ul>
</aside>
