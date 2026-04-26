<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// ---------------------------------------------------------------------------
// 1. `auth` middleware — protected routes
// ---------------------------------------------------------------------------

describe('auth middleware', function () {
    test('guest is redirected to login when accessing dashboard', function () {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    });

    test('authenticated user can access the dashboard', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    });

    test('guest is redirected to login when posting to logout', function () {
        $this->post(route('logout'))
            ->assertRedirect(route('login'));
    });

    test('authenticated user can post to logout', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));
    });
});

// ---------------------------------------------------------------------------
// 2. `guest` middleware — public-only routes
// ---------------------------------------------------------------------------

describe('guest middleware', function () {
    test('guest can access the login page', function () {
        $this->get(route('login'))
            ->assertOk();
    });

    test('authenticated user is redirected away from the login page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    });

    test('authenticated user is redirected away when posting to login', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('dashboard'));
    });
});

// ---------------------------------------------------------------------------
// 3. Login flow — AuthenticatedSessionController@store
// ---------------------------------------------------------------------------

describe('login flow', function () {
    test('user is authenticated and redirected to dashboard with valid credentials', function () {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    });

    test('login with wrong password returns a validation error and does not authenticate', function () {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    test('login with non-existent email returns a validation error', function () {
        $this->post(route('login'), [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });
});

// ---------------------------------------------------------------------------
// 4. Logout flow — AuthenticatedSessionController@destroy
// ---------------------------------------------------------------------------

describe('logout flow', function () {
    test('authenticated user is logged out and redirected to login', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    });

    test('user session is invalidated after logout', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        expect(Auth::check())->toBeTrue();

        $this->post(route('logout'));

        expect(Auth::check())->toBeFalse();
    });
});
