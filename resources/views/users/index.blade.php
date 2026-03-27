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

<button id="btnNewUser" type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#userModal">
    Nuevo usuario
</button>

    <div class="table-responsive">
        <table id="usersTable" class="table table-sm small table-materio">
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
                        <div class="text-center d-flex align-items-center justify-content-center">
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
                                class="btn btn-icon edit-user"
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
                            <button class="btn btn-icon" type="submit"
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
<script>
    $(function () {
        $("#usersTable").DataTable({
            "pageLength": 6,
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
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true, "lengthChange": true, "autoWidth": false,

        }).buttons().container().appendTo('#usersTable_wrapper .col-md-6:eq(0)');
    });
</script>

<!-- Modal: Crear / Editar Usuario -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="userForm" action="{{ route('users.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label for="userName" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="userName" name="name" required>
                        </div>
                        <div class="mb-3 col-md-8">
                            <label for="userEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="userEmail" name="email" required>
                        </div>
                    </div>
                    <div class="mb-3" id="passwordField">
                        <label for="userPassword" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="userPassword" name="password" required>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="userRoles" class="form-label">Rol</label>
                        <select class="form-select" id="userRoles" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-md-6 d-none" id="typeField">
                            <label for="scopeType" class="form-label">Tipo</label>
                            <select class="form-select" id="scopeType" name="tipo">
                                <option value="establecimiento">Establecimiento</option>
                                <option value="municipio">Municipio</option>
                                <option value="red">Red</option>
                                <option value="sedes">SEDES</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-none" id="establecimientoField">
                            <label for="scopeEst" class="form-label">Establecimiento</label>
                            <select class="form-select" id="scopeEst" name="establecimiento">
                                @foreach ($establecimientos as $establecimiento)
                                    <option value="{{ $establecimiento->id }}">{{ $establecimiento->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-none" id="municipioField">
                            <label for="scopeMun" class="form-label">Municipio</label>
                                <select class="form-select" id="scopeMun" name="municipio">
                                    @foreach ($municipios as $municipio)
                                    <option value="{{ $municipio->id }}">{{ $municipio->nombre }}</option>
                                    @endforeach
                                </select>
                        </div>
                        <div class="col-md-6 d-none" id="redField">
                            <label for="scopeRed" class="form-label">Red</label>
                            <select class="form-select" id="scopeRed" name="red">
                                @foreach($redes as $red)
                                    <option value="{{ $red->id }}">{{ $red->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="userSubmit">Guardar</button>
                </div>
            </form>
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
