<?php
$title = 'Usuários';
$headerAction = '<a href="' . url('/usuarios/novo') . '" class="btn btn-primary">Novo usuário</a>';
?>
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th><th>Usuário</th><th>Perfil</th><th>Status</th><th>Criado</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><span class="badge badge-info"><?= e(role_label($u['role'])) ?></span></td>
                    <td><?= $u['active'] ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-neutral">Inativo</span>' ?></td>
                    <td><?= format_date($u['created_at']) ?></td>
                    <td><a href="<?= url("/usuarios/{$u['id']}/editar") ?>" class="btn btn-ghost btn-sm">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
