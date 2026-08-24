<?php

use Inertia\Testing\AssertableInertia as Assert;

test('poli team page can be rendered successfully with default poli', function () {
    $response = $this->get(route('teams.poli.index'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('teams/Index')
        ->has('polis')
        ->has('currentPoli')
        ->where('currentPoli.code', 'POL-UM')
        ->has('schedules')
    );
});

test('poli team page can switch polyclinic by query param', function () {
    $response = $this->get(route('teams.poli.index', ['poli' => 'Poli Penyakit Dalam']));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('teams/Index')
        ->has('currentPoli')
        ->where('currentPoli.code', 'POL-PD')
    );
});
