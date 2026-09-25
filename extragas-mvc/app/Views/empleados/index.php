<div class="toolbar"><div class="text-body-secondary small">Personal que atiende pedidos, reparte y recibe mercadería.</div>
    <div class="ms-auto"><a class="btn btn-sm btn-primary" href="<?= url('empleados/nuevo') ?>"><i class="bi bi-plus-lg"></i> Nuevo empleado</a></div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle">
    <thead><tr><th>Empleado</th><th>DNI / CUIL</th><th>Teléfono</th><th>Ingreso</th><th>Usuario</th><th class="num">Pedidos</th><th>Estado</th><th></th></tr></thead>
    <tbody><?php foreach ($empleados as $e): ?>
        <tr><td class="fw-semibold"><?= e(nombre($e)) ?></td><td><?= e(implode(' · ', array_filter([$e['dni'], $e['cuil']])) ?: '—') ?></td><td><?= e($e['telefono'] ?: '—') ?></td>
            <td><?= fecha($e['fecha_ingreso']) ?></td><td><?= e($e['username'] ?? '—') ?></td><td class="num"><?= $e['pedidos'] ?></td>
            <td><?= $e['activo'] ? badge('Activo', 'b-pagado') : badge('Inactivo') ?></td>
            <td class="actions"><a class="btn btn-sm btn-light" href="<?= url('empleados/'.$e['id'].'/editar') ?>"><i class="bi bi-pencil"></i></a></td></tr>
    <?php endforeach ?></tbody>
</table></div></div>
