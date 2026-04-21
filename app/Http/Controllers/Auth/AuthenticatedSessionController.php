<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $name = $request->string('name');
        $password = $request->string('password');
        $sistemaId = $request->input('sistema_id');

        $user = User::with('sistemas')->where('name', $name)->first();

        if (! $user) {
            // Si no existe usuario, dejamos que authenticate genere el error
            $request->authenticate();
        }

        if (! $sistemaId && $user->sistemas->count() > 1) {
            $systems = $user->sistemas->map(function ($system) {
                return [
                    'id' => $system->id,
                    'nombre' => $system->nombre,
                ];
            })->all();

            return back()
                ->withInput($request->only('name', 'remember'))
                ->with('password', $password)
                ->with('login_systems', $systems)
                ->with('login_message', 'Selecciona el sistema con el que deseas ingresar.');
        }

        $request->authenticate();

        if ($sistemaId && ! $user->sistemas->contains('id', $sistemaId)) {
            return back()
                ->withInput($request->only('name', 'remember'))
                ->with('login_message', 'El usuario no pertenece a ese sistema.');
        }

        if ($sistemaId && $user->sistema_id !== (int) $sistemaId) {
            $user->sistema_id = (int) $sistemaId;
            $user->save();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
