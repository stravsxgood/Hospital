<?php

use Inertia\Testing\AssertableInertia as Assert;

test('specializations page can be rendered successfully with default specialization', function () {
    $response = $this->get(route('specializations.index'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Specializations/Index')
        ->has('specializations')
        ->has('currentSpecialization')
        ->where('currentSpecialization.slug', 'pulmonologi')
        ->has('schedules')
        ->has('doctors')
    );
});

test('specializations page can switch specialization by slug query param', function () {
    $response = $this->get(route('specializations.index', ['slug' => 'kardiologi']));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Specializations/Index')
        ->has('currentSpecialization')
        ->where('currentSpecialization.slug', 'kardiologi')
    );
});
