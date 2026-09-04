<?php

use Spatie\Permission\Models\Role;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    // Standard registration assigns the `user` role (see CreateNewUser),
    // so the role must exist for the guard used by the app.
    Role::findOrCreate('user', 'web');

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('user.dashboard', absolute: false));

    $this->assertAuthenticated();
});