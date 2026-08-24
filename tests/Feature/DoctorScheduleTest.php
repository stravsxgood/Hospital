<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest can access doctor schedule page and receives schedule dataset', function () {
    $response = $this->get(route('schedule-guest'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('doctor/Schedule')
        ->has('schedules')
    );
});

test('authenticated patient can access doctor schedules page', function () {
    $user = User::factory()->create();
    $user->load('teams');

    $response = $this->actingAs($user)->get(route('doctor.schedules'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('doctor/Schedule')
        ->has('schedules')
    );
});
