<x-guest-layout>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <div class="stepper mb-4">
        <div class="step active">
            <span class="step-number">1</span>
            <span class="step-label">Credenciales</span>
        </div>
        <div class="step {{ session('login_systems') ? 'active' : '' }}">
            <span class="step-number">2</span>
            <span class="step-label">Sistema</span>
        </div>
    </div>

    <form method="POST" action="{{ route('login') }}" class="form-login">
        @csrf

        <div class="mb-3">
            <label class="form-label">Nombre de usuario</label>
            <div class="input-group">
                <span class="input-group-text bg-success"><i class="ri ri-user-line"></i></span>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus autocomplete="username">
            </div>
            <x-input-error :messages="$errors->get('name')" class="text-danger mt-1" />
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-danger"><i class="ri ri-lock-line"></i></span>
                <input type="password" name="password" class="form-control" required>
            </div>
            <x-input-error :messages="$errors->get('password')" class="text-danger mt-1" />
        </div>

        @if(session('login_message'))
            <div class="alert alert-info">{{ session('login_message') }}</div>
        @endif

        @if(session('login_systems'))
            <div class="mb-3">
                <label class="form-label">Sistema</label>
                <div class="input-group">
                    <span class="input-group-text bg-primary"><i class="ri ri-organization-chart"></i></span>
                    <select name="sistema_id" class="form-control" required autofocus>
                        <option value="">Selecciona un sistema</option>
                        @foreach(session('login_systems') as $system)
                            <option value="{{ $system['id'] }}" {{ old('sistema_id') == $system['id'] ? 'selected' : '' }}>{{ $system['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input-error :messages="$errors->get('sistema_id')" class="text-danger mt-1" />
            </div>
        @endif

        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label">Recordarme</label>
        </div>

        <button class="btn d-grid w-100" style="background-color: #139b92 !important; color: white;">
            {{ session('login_systems') ? 'Ingresar al sistema' : 'Continuar' }}
        </button>
    </form>

</x-guest-layout>

<style>
    .stepper {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        opacity: 0.5;
    }

    .step.active {
        opacity: 1;
    }

    .step-number {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #139b92;
        color: #fff;
        font-weight: 700;
    }

    .step-label {
        color: #333;
        font-size: 0.9rem;
    }

    .form-login .input-group-text {
            border: none;
            color: white;
            border-radius: 5rem;
        }

    .form-login select,
    .form-login input{
            border: none;
            border-radius: 50rem;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.15);
        }

    .form-login select:disabled,
    .form-login input:disabled {
            background-color: #f0f0f0;
            color: #999;
            cursor: not-allowed;
        }

    .form-login select:focus,
    .form-login input:focus {
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.25);
            outline: none;
        }

    .form-login .btn{
            border: none;
            border-radius: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
</style>