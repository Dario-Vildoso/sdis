@extends('layouts.materio')

@section('title', 'Usuarios')

@section('content')

@if(session('success'))
    <div id="successAlert" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
    </div>

    <script>
        setTimeout(function() {
            var alert = new bootstrap.Alert(document.getElementById('successAlert'));
            alert.close();
        }, 3000);
    </script>
@endif

<button id="btnNewUser" type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#userModal">
    Nuevo usuario
</button>

    <div class="table-responsive">
        <table id="usersTable" class="table table-striped table-materio">
            <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <button type="button"
                                class="btn btn-icon edit-user"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}">
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
                    <div class="mb-3">
                        <label for="userName" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="userName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail" name="email" required>
                    </div>
                    <div class="mb-3" id="passwordField">
                        <label for="userPassword" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="userPassword" name="password" required>
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
                $('.edit-user').on('click', function () {
                        var id = $(this).data('id');
                        var name = $(this).data('name');
                        var email = $(this).data('email');

                        $('#userModalLabel').text('Editar usuario');
                        $('#userForm').attr('action', baseUrl + '/' + id);
                        $('#formMethod').val('PUT'); // Cambia el método a PUT para actualización
                        $('#userName').val(name);
                        $('#userEmail').val(email);
                        $('#passwordField').addClass('d-none'); // Oculta el campo de contraseña al editar
                        $('#userPassword').prop('required', false).val('');
                        $('#userSubmit').text('Actualizar');

                        var userModal = new bootstrap.Modal(document.getElementById('userModal'));
                        userModal.show();
                });
        });
</script>
@endsection
