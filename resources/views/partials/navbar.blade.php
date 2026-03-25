@php
    use App\Models\Red;
    use App\Models\Municipio;
    use App\Models\Establecimiento;
@endphp
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-menu-theme">

    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ri ri-menu-2-line"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search fs-4 lh-0"></i>
                <input type="text" class="form-control border-0 shadow-none" placeholder="Buscar...">
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            {{Auth::user()->name}}
            @if (Auth::user()->alcances()->count() > 0)
            -
                @switch(Auth::user()->alcances()->first()->tipo)
                    @case('red')
                        {{ Red::find(Auth::user()->alcances()->first()->alcance_id)->nombre }}
                        @break
                    @case('municipio')
                        {{ Municipio::find(Auth::user()->alcances()->first()->alcance_id)->nombre }}
                        @break
                    @case('establecimiento')
                        {{ Establecimiento::find(Auth::user()->alcances()->first()->alcance_id)->descripcion }}
                        @break
                @endswitch
            @endif
            <!-- Usuario -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="#" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('assets/img/avatars/1.png') }}" class="w-px-40 h-auto rounded-circle">
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="ri ri-dashboard-fill me-2"></i>
                            <span>Inicio</span>
                        </a>
                    </li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item">
                            <i class="ri ri-logout-box-fill me-2"></i>
                            Cerrar sesión
                        </button>
                    </form>
                </ul>
            </li>

        </ul>
    </div>
</nav>
