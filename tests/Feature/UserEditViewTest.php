<?php

use App\Models\Role;
use App\Models\User;
use App\Models\UserStatus;

it('renders the user edit view in Spanish with design system copy', function () {
    withoutVite();

    $user = new User([
        'id' => 8,
        'name' => 'Laura Mejia',
        'email' => 'laura@wirechat.co',
    ]);
    $role = new Role(['name' => 'Admin']);
    $status = new UserStatus(['name' => 'Activo']);

    $user->setRelation('role', $role);
    $user->setRelation('status', $status);

    $view = view('users.edit', [
        'user' => $user,
        'roles' => collect([$role]),
        'user_statuses' => collect([$status]),
    ]);

    $view->assertSee('Editar perfil de usuario');
    $view->assertSee('Correo electrónico');
    $view->assertSee('Guardar cambios');
});
