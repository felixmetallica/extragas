<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controlador;
use App\Core\Sesion;
use App\Core\Vista;

class AuthController extends Controlador
{
    public function formulario(): string
    {
        if (Auth::usuario()) {
            redirigir('');
        }

        return Vista::render('auth/login', [], null);
    }

    public function ingresar(): never
    {
        $usuario = trim($_POST['username'] ?? '');
        if (! Auth::intentar($usuario, (string) ($_POST['password'] ?? ''))) {
            Sesion::guardarEntrada(['username' => $usuario]);
            Sesion::flash('errores', ['username' => 'Usuario o contraseña incorrectos.']);
            redirigir('login');
        }
        redirigir('');
    }

    public function salir(): never
    {
        Auth::salir();
        redirigir('login');
    }
}
