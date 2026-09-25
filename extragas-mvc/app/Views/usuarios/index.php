<div class="toolbar"><div class="text-body-secondary small">Personas que ingresan al sistema. Cada usuario se vincula con un empleado para registrar quién tomó cada pedido.</div>
    <div class="ms-auto"><a class="btn btn-sm btn-primary" href="<?= url('usuarios/nuevo') ?>"><i class="bi bi-person-plus"></i> Nuevo usuario</a></div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle">
    <thead><tr><th>Usuario</th><th>Empleado</th><th>Rol</th><th>Email</th><th>Último ingreso</th><th>Estado</th><th></th></tr></thead>
    <tbody><?php foreach ($usuarios as $u): ?>
        <tr><td><div class="d-flex align-items-center gap-2"><span class="avatar"><?= e(mb_strtoupper($u['empleado_id'] ? mb_substr($u['empleado_nombre'], 0, 1).mb_substr($u['empleado_apellido'], 0, 1) : mb_substr($u['username'], 0, 2))) ?></span><span class="fw-semibold"><?= e($u['username']) ?></span></div></td>
            <td><?= e(nombre($u, 'empleado_') ?: '—') ?></td><td><?= badge($u['rol_nombre'], $u['rol_codigo'] === 'ADMIN' ? 'b-parcial' : 'b-info') ?></td>
            <td><?= e($u['email'] ?: '—') ?></td><td><?= fecha($u['ultimo_login'], true) ?></td><td><?= $u['activo'] ? badge('Activo', 'b-pagado') : badge('Inactivo') ?></td>
            <td class="actions"><a class="btn btn-sm btn-light" href="<?= url('usuarios/'.$u['id'].'/editar') ?>"><i class="bi bi-pencil"></i></a></td></tr>
    <?php endforeach ?></tbody>
</table></div></div>
