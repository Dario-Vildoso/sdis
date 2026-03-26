<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-dark">

    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bold ms-2">
                Materio
            </span>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner">

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
        <li class="menu-item {{ request()->routeIs(['formularios.*', 'establecimientos.*']) ? 'open' : '' }}">
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
                <li class="menu-item {{ request()->routeIs('formularios.*') ? 'active' : '' }}">
                    <a href="{{ route('formularios.menu') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('formularios.*') ? 'ri-download-fill' : 'ri-download-line' }} bg-warning"></i>
                        <div>Exportar</div>
                    </a>
                </li>
            </ul>
        </li>
------------------------------------------
        <li class="menu-item {{ request()->routeIs('') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri {{ request()->routeIs('formularios.*') ? 'ri-survey-fill' : 'ri-survey-line' }} bg-warning"></i>
                <div>Formularios Activos</div>  
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('') ? 'active' : '' }}">
                    <a href="{{ route('formularios.menu') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('users.*') ? 'ri-file-edit-fill' : 'ri-file-edit-line' }} bg-warning"></i>
                        <div>Formularios Actuales</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('roles.*') ? 'ri-alert-fill' : 'ri-alert-line' }} bg-warning"></i>
                        <div>Pendientes</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="menu-link">
                        <i class="menu-icon ri {{ request()->routeIs('roles.*') ? 'ri-history-fill' : 'ri-history-line' }} bg-warning"></i>
                        <div>Historial</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ request()->routeIs(['variables.*', 'subvariables.*', 'cabeceras.*', 'periodos.*', 'gestiones.*']) ? 'open' : '' }}">
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
        </li>
    </ul>
</aside>
