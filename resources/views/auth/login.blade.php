<x-guest-layout>

    <h4 class="mb-2"></h4>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre de usuario</label>
            <input type="text" name="name" class="form-control" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('name')" class="text-danger mt-1" />
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
            <x-input-error :messages="$errors->get('password')" class="text-danger mt-1" />
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" class="form-check-input">
            <label class="form-check-label">Recordarme</label>
        </div>

        <button class="btn btn-primary d-grid w-100">
            Ingresar
        </button>
    </form>

</x-guest-layout>
