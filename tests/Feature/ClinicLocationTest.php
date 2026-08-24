<?php

use Inertia\Testing\AssertableInertia as Assert;

test('clinic location page can be rendered for guest visitors', function () {
    $response = $this->get(route('clinic.location'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Clinic/Location')
        ->has('clinics', 10)
        ->has('cities')
        ->has('facilityTypes')
        ->where('clinics.0.emergency_24h', true)
        ->where('clinics.0.city', 'Tangerang')
    );
});
