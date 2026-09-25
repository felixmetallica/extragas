<?php
/* Campo de formulario. Variables: name, label, [type, value, col, req, attrs, placeholder, opciones, vacio, rows] */
$type ??= 'text';
$valor = viejo($name, $value ?? '');
$error = error_de($name);
$clase = $error ? ' is-invalid' : '';
?>
<div class="<?= $col ?? 'col-md-6' ?>">
    <label class="form-label <?= ! empty($req) ? 'req' : '' ?>" for="f_<?= e($name) ?>"><?= e($label) ?></label>
    <?php if ($type === 'textarea'): ?>
        <textarea class="form-control<?= $clase ?>" name="<?= e($name) ?>" id="f_<?= e($name) ?>" rows="<?= $rows ?? 2 ?>" placeholder="<?= e($placeholder ?? '') ?>"><?= e($valor) ?></textarea>
    <?php elseif ($type === 'select'): ?>
        <select class="form-select<?= $clase ?>" name="<?= e($name) ?>" id="f_<?= e($name) ?>" <?= ! empty($req) ? 'required' : '' ?>>
            <?php if (isset($vacio)): ?><option value=""><?= e($vacio) ?></option><?php endif ?>
            <?php foreach ($opciones as $v => $t): ?><option value="<?= e($v) ?>" <?= sel($valor, $v) ?>><?= e($t) ?></option><?php endforeach ?>
        </select>
    <?php else: ?>
        <input type="<?= e($type) ?>" class="form-control<?= $clase ?>" name="<?= e($name) ?>" id="f_<?= e($name) ?>" value="<?= e($valor) ?>" placeholder="<?= e($placeholder ?? '') ?>" <?= ! empty($req) ? 'required' : '' ?> <?= $attrs ?? '' ?>>
    <?php endif ?>
    <?php if ($error): ?><div class="invalid-feedback"><?= e($error) ?></div><?php endif ?>
</div>
