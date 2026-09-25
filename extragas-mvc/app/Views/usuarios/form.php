<?php use App\Core\Vista; $u = $usuario; $campo = fn (array $d) => Vista::parcial('campo', $d); ?>
<form method="POST" action="<?= $u['id'] ? url('usuarios/'.$u['id'].'/editar') : url('usuarios') ?>" class="card" style="max-width:720px">
    <?= csrf_campo() ?>
    <div class="card-body row g-3">
        <?php $campo(['name' => 'username', 'label' => 'Usuario', 'value' => $u['username'] ?? '', 'req' => true, 'attrs' => 'autocomplete="off"']) ?>
        <?php $campo(['name' => 'rol_id', 'label' => 'Rol', 'type' => 'select', 'opciones' => $roles, 'value' => $u['rol_id']]) ?>
        <?php $campo(['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $u['email'] ?? '']) ?>
        <?php $campo(['name' => 'empleado_id', 'label' => 'Empleado vinculado', 'type' => 'select', 'opciones' => $empleados, 'value' => $u['empleado_id'], 'vacio' => '— Sin vincular —']) ?>
        <?php $campo(['name' => 'password', 'label' => 'Contraseña', 'type' => 'password', 'req' => ! $u['id'], 'attrs' => 'autocomplete="new-password"', 'placeholder' => $u['id'] ? 'Dejar vacío para no cambiarla' : 'Mínimo 6 caracteres']) ?>
        <?php $campo(['name' => 'password_confirmacion', 'label' => 'Repetir contraseña', 'type' => 'password', 'attrs' => 'autocomplete="new-password"']) ?>
        <?php if ($u['id']) Vista::parcial('activo', ['valor' => $u['activo'], 'texto' => 'Usuario activo']) ?>
        <div class="col-12"><div class="alert alert-light border small mb-0"><b>Administrador:</b> acceso total, precios, usuarios y configuración. <b>Empleado:</b> pedidos, clientes, cobros, garrafas, proveedores e informes.</div></div>
    </div>
    <div class="card-body border-top d-flex gap-2 justify-content-end"><a class="btn btn-light" href="<?= url('usuarios') ?>">Cancelar</a><button class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button></div>
</form>
