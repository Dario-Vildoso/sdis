@extends('layouts.materio')

@section('title', 'Usuarios')

@section('content')

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Exito',
            text: "{{ session('success') }}",
            timer: 1000,
            showConfirmButton: false
        });
    </script>
@endif
<div class="row">
    <div class="col-md-8 mb-4">
    <div class="card shadow-sm card-info">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white">
                <i class="ri ri-user-fill bg-white"></i> Tipos de Usuarios
            </h5>
            <span class="badge bg-light text-dark">{{ $users->count() }} total</span>
        </div>
        
        <div class="card-body">
            <div class="row text-center d-flex">

                <!-- Red -->
                <div class="col-md-4 col-4">
                    <div class="d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background: linear-gradient(90deg, #037ed6, #0066ae);">
                            <i class="ri-git-branch-line ri fs-1"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 text-info">{{ $conteos['red'] }}</h3>
                            <p class="text-muted mb-0 small">Red</p>
                        </div>
                    </div>
                </div>

                <!-- Municipio -->
                <div class="col-md-4 col-4">
                    <div class="d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background: linear-gradient(90deg, #ffc107, #ff9800);">
                            <i class="ri ri-community-line fs-1 "></i>
                        </div>
                        <div>
                            <h3 class="mb-0 text-warning">{{ $conteos['municipio'] }}</h3>
                            <p class="text-muted mb-0 small">Municipio</p>
                        </div>
                    </div>
                </div>

                <!-- Establecimiento -->
                <div class="col-md-4 col-4">
                    <div class="d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background: linear-gradient(90deg, #1ab817, #019617);">
                            <i class="ri ri-building-4-line fs-1"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 text-success">{{ $conteos['establecimiento'] }}</h3>
                            <p class="text-muted mb-0 small">Establecimiento</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
    <div class="col-md-4 mb-4">
        <div class="card card-action">
            <div class="card-body">
                <h5 class="card-title">Crear un nuevo usuario</h5>
            </div>
            <div class="card-footer">
                <button id="btnNewUser" type="button" class="btn btn-card-action" data-bs-toggle="modal" data-bs-target="#userModal">
                    <i class="ri ri-add-line fs-5"></i>&nbsp;&nbsp;&nbsp;Nuevo Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card" style="padding: 20px">
    <div class="table-responsive">
        <table id="usersTable" class="table table-sm table-bordered table-stripped small table-materio">
            <thead>
            <tr>
                <th class="id-header"></th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Roles</th>
                <th class="actions">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td class="id-cell">
                        <div 
                        @switch($user->alcances->tipo ?? '')
                            @case('red')
                                style="background: linear-gradient(90deg, #037ed6, #0066ae); color: white;"
                                @break
                            @case('municipio')
                                style="background: linear-gradient(90deg, #ffc107, #ff9800); color: white;"
                                @break
                            @case('establecimiento')
                                style="background: linear-gradient(90deg, #1ab817, #019617); color: white;"
                                @break
                            @default
                                style="background: linear-gradient(90deg, #6c757d, #495057); color: white;"
                        @endswitch
                        >
                            <strong>{{ $user->id }}</strong>
                        </div>
                    </td>
                    <td class="data">{{ $user->name }}</td>
                    <td class="data">{{ $user->email }}</td>
                    <td class="data">
                        @if($user->roles && $user->roles->count() > 0)
                            @foreach($user->roles as $role)
                                <span class="badge bg-info me-1 small">{{ $role->name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">Sin roles</span>
                        @endif
                    </td>
                    <td>
                        <button type="button"
                                class=" btn-icon edit-user"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-rol="{{ $user->roles()->first()?->slug }}"
                                data-rol-id="{{ $user->roles()->first()?->id }}"
                                data-tipo="{{ $user->alcances()->first()?->tipo }}"
                                data-alcance="{{ $user->alcances()->first()?->alcance_id }}"
                                data-bs-toggle="modal" data-bs-target="#userModal"
                                >
                            <i class="ri ri-pencil-line fs-4 bg-success"></i>
                        </button>

                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn-icon" type="submit"
                                onclick="return confirm('¿Eliminar usuario?')">
                                <i class="ri ri-delete-bin-line fs-4 bg-danger"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
$(function () {
    var table = $("#usersTable").DataTable({
        "pageLength": 10,                    // Cambié de 6 a 10 (más estándar)
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        "language": {
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(Filtrado de _MAX_ total registros)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": '<i class="ri ri-search-line"></i>',
            "searchPlaceholder": "Buscar...",
            "zeroRecords": "Sin resultados encontrados",
            
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
            
        },
        
        "responsive": true, "lengthChange": true, "autoWidth": false,

        
        // === BOTONES (Exportar + Acciones) ===
        "dom": 'Bfrtip',
        
        "buttons": [
            {
                extend: 'collection',
                text: '<i class="ri ri-file-excel-line"></i> Exportar',
                className: 'btn-success btn-sm',
                buttons: [
                    { extend: 'excelHtml5', text: 'Excel (.xlsx)', exportOptions: { columns: ':visible' } },
                    { extend: 'csvHtml5',   text: 'CSV', exportOptions: { columns: ':visible' } },
                    { extend: 'pdfHtml5',   text: 'PDF', exportOptions: { columns: ':visible' } },
                    { extend: 'copyHtml5',  text: 'Copiar al portapapeles' }
                ]
            }
        ],
        
        // Opciones recomendadas para mejor experiencia
        "pagingType": "full_numbers",
        "scrollX": true,                    // Útil si tienes muchas columnas
        "stateSave": true,                  // Recuerda la página, filtros y orden al recargar
    });

    // Mover los botones a la izquierda (opcional)
    table.buttons().container().appendTo('#usersTable_wrapper .col-md-6:eq(0)');
});
</script>

<!-- Modal: Crear / Editar Usuario -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-center w-100" id="userModalLabel">
                    <span id="modalTitle">Nuevo Usuario</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
<hr>
            <div class="modal-body pt-2">
                <form id="userForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nombre de Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success"><i class="ri ri-user-line"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="userName" 
                                       name="name" 
                                       placeholder="Ej: PIEDRA GRANDE"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6" id="passwordField">
                            <label class="form-label fw-medium">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success"><i class="ri ri-lock-line"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="userPassword" 
                                       name="password" 
                                       placeholder="••••••••••"
                                       required>
                            </div>
                        </div>

                        <hr class="my-2">

                        <!-- Rol -->
                        <div class="col-12">
                            <label class="form-label fw-medium">Rol del usuario</label>
                            <div class="input-group">
                                <span class="input-group-text bg-warning"><i class="ri ri-shield-user-line"></i></span>
                                <select class="form-select" id="userRoles" name="role" required>
                                    <option value="">Seleccione un rol...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Tipo de Alcance -->
                        <div class="col-md-6" id="typeField">
                            <label class="form-label fw-medium">Tipo de Alcance</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info"><i class="ri ri-map-pin-line"></i></span>
                                <select class="form-select" id="scopeType" name="tipo">
                                    <option value="">Seleccione tipo...</option>
                                    <option value="red">Red</option>
                                    <option value="municipio">Municipio</option>
                                    <option value="establecimiento">Establecimiento</option>
                                    <option value="sedes">SEDES</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 d-none" id="redField">
                            <label class="form-label fw-medium">Red</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info"><i class="ri ri-node-tree"></i></span>
                                <select class="form-select" id="scopeRed" name="red">
                                    <option value="">Seleccione una red...</option>
                                    @foreach($redes as $red)
                                        <option value="{{ $red->id }}">{{ $red->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 d-none" id="municipioField">
                            <label class="form-label fw-medium">Municipio</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info"><i class="ri ri-building-2-line"></i></span>
                                <select class="form-select" id="scopeMun" name="municipio">
                                    <option value="">Seleccione un municipio...</option>
                                    @foreach($municipios as $municipio)
                                        <option value="{{ $municipio->id }}">{{ $municipio->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 d-none" id="establecimientoField">
                            <label class="form-label fw-medium">Establecimiento</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info"><i class="ri ri-hospital-line"></i></span>
                                <select class="form-select" id="scopeEst" name="establecimiento">
                                    <option value="">Seleccione un establecimiento...</option>
                                    @foreach($establecimientos as $establecimiento)
                                        <option value="{{ $establecimiento->id }}">{{ $establecimiento->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary px-4" id="userSubmit" style="background-color: #139b92;">
                    <i class="ri-save-line me-2"></i>Guardar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<script>
        $(function () {
                // Base URL for update action
                var baseUrl = "{{ url('users') }}";
                $('#userRoles').on('change', function () {
                    var selectedRole = this.options[this.selectedIndex].text.toString().toLowerCase();
                    if (selectedRole === 'reportes') {
                            $('#typeField').removeClass('d-none').prop('required', true);
                    } else {
                            $('#typeField').addClass('d-none').prop('required', false);
                            $('#establecimientoField').addClass('d-none').prop('required', false);
                            $('#municipioField').addClass('d-none').prop('required', false);
                            $('#redField').addClass('d-none').prop('required', false);
                    }
                })
                $('#scopeType').on('change', function () {
                    var selectedType = $(this).val();
                    if (selectedType === 'establecimiento') {
                            $('#establecimientoField').removeClass('d-none').prop('required', true);
                            $('#municipioField').addClass('d-none').prop('required', false);
                            $('#redField').addClass('d-none').prop('required', false);
                    } else if (selectedType === 'municipio') {
                            $('#establecimientoField').addClass('d-none').prop('required', false);
                            $('#municipioField').removeClass('d-none').prop('required', true);
                            $('#redField').addClass('d-none').prop('required', false);
                    } else if (selectedType === 'red') {
                            $('#establecimientoField').addClass('d-none').prop('required', false);
                            $('#municipioField').addClass('d-none').prop('required', false);
                            $('#redField').removeClass('d-none').prop('required', true);
                    }
                })

                // New user: reset form
                $('#btnNewUser').on('click', function () {
                        $('#userModalLabel').text('Nuevo usuario');
                        $('#userForm').attr('action', "{{ route('users.store') }}");
                        $('#formMethod').val(''); // Limpia el método para creación
                        $('#userName').val('');
                        $('#userEmail').val('');
                        $('#passwordField').removeClass('d-none'); // Muestra el campo de contraseña al crear
                        $('#userPassword').prop('required', true).val('');
                        $('#userSubmit').text('Crear');
                });

                // Edit user: populate and set action
                $(document).on('click', '.edit-user', function () {
                    // alert('id')
                        var id = $(this).data('id');
                        // alert(id)
                        var name = $(this).data('name');
                        // alert(name)
                        var email = $(this).data('email');
                        // alert(email)
                        var rol = $(this).data('rol');
                        var rolId = $(this).data('rol-id');
                        var tipo = $(this).data('tipo');
                        // alert(tipo)
                        var alcance = $(this).data('alcance');
                        // alert(alcance)
                        $('#userModalLabel').text('Editar usuario');
                        $('#userForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#userName').val(name);
                        $('#userEmail').val(email);
                        $('#passwordField').addClass('d-none'); // Oculta el campo de contraseña al editar
                        $('#userPassword').prop('required', false).val('');
                        $('#userRoles').val(rolId);
                        if (rol == 'reportes'){
                            $('#typeField').removeClass('d-none').prop('required', true);
                            $('#scopeType').val(tipo)
                            switch (tipo) {
                                case 'establecimiento':
                                    $('#establecimientoField').removeClass('d-none').prop('required', true);
                                    $('#municipioField').addClass('d-none').prop('required', false);
                                    $('#redField').addClass('d-none').prop('required', false);
                                    $('#scopeEst').val(alcance);
                                    break;
                                case 'municipio':
                                    $('#municipioField').removeClass('d-none').prop('required', true);
                                    $('#establecimientoField').addClass('d-none').prop('required', false);
                                    $('#redField').addClass('d-none').prop('required', false);
                                    $('#scopeMun').val(alcance);
                                    break;
                                case 'red':
                                    $('#redField').removeClass('d-none').prop('required', true);
                                    $('#establecimientoField').addClass('d-none').prop('required', false);
                                    $('#municipioField').addClass('d-none').prop('required', false);
                                    $('#scopeRed').val(alcance);
                                    break;
                            }
                        }
                        $('#userSubmit').text('Actualizar');

                });
        });
</script>
@endsection
