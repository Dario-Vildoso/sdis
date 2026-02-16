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

        <li class="menu-item {{ request()->routeIs(['users.*', 'establecimientos.*', 'formularios.*', 'variables.*', 'subvariables.*', 'cabeceras.*', 'periodos.*', 'gestiones.*']) ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri ri-settings-3-fill bg-warning"></i>
                <div>Gestión</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-user-line bg-warning"></i>
                        <div>Usuarios</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('establecimientos.*') ? 'active' : '' }}">
                    <a href="{{ route('establecimientos.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-building-line bg-warning"></i>
                        <div>Establecimientos</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('gestiones.*') ? 'active' : '' }}">
                    <a href="{{ route('gestiones.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-calendar-2-line bg-warning"></i>
                        <div>Gestiones</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('formularios.*') ? 'active' : '' }}">
                    <a href="{{ route('formularios.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-survey-line bg-warning"></i>
                        <div>Formularios</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('variables.*') ? 'active' : '' }}">
                    <a href="{{ route('variables.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-code-line bg-warning"></i>
                        <div>Variables</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('subvariables.*') ? 'active' : '' }}">
                    <a href="{{ route('subvariables.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-indent-increase bg-warning"></i>
                        <div>Subvariables</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('cabeceras.*') ? 'active' : '' }}">
                    <a href="{{ route('cabeceras.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-layout-top-line bg-warning"></i>
                        <div>Cabeceras</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('periodos.*') ? 'active' : '' }}">
                    <a href="{{ route('periodos.index') }}" class="menu-link">
                        <i class="menu-icon ri ri-time-line bg-warning"></i>
                        <div>Periodos</div>
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</aside>
