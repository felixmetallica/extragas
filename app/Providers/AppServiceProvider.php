<?php

namespace App\Providers;

use App\Models\ConfiguracionEmpresa;
use App\Models\Pedido;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('es');
        Paginator::useBootstrapFive();

        // Columnas comunes de las tablas del sistema (ver extragas.sql)
        Blueprint::macro('fechasRegistro', function () {
            /** @var Blueprint $this */
            $this->dateTime('created_at')->useCurrent();
            $this->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Blueprint::macro('auditoria', function () {
            /** @var Blueprint $this */
            $this->fechasRegistro();
            $this->foreignId('created_by')->nullable()->constrained('usuarios');
            $this->foreignId('updated_by')->nullable()->constrained('usuarios');
            $this->dateTime('deleted_at')->nullable()->index();
        });

        View::composer(['layouts.*', 'auth.*', 'pdf.*'], fn ($view) => $view->with('empresa', ConfiguracionEmpresa::actual()));
        View::composer('layouts.app', fn ($view) => $view->with('pedidosEnCurso', Pedido::enCurso()->count()));

        Gate::define('administrar', fn ($usuario) => $usuario->esAdministrador());
    }
}
