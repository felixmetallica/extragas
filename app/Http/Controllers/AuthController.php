<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function formulario()
    {
        return view('auth.login');
    }

    public function ingresar(Request $request)
    {
        $datos = $request->validate(['username' => 'required|string', 'password' => 'required|string']);

        if (! Auth::attempt(['username' => $datos['username'], 'password' => $datos['password'], 'activo' => true])) {
            return back()->withInput($request->only('username'))->withErrors(['username' => 'Usuario o contraseña incorrectos.']);
        }

        $request->session()->regenerate();
        Auth::user()->forceFill(['ultimo_login' => now()])->save();

        return redirect()->intended(route('inicio'));
    }

    public function salir(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
