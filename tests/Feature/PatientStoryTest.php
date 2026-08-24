<?php

use Inertia\Testing\AssertableInertia as Assert;

test('patient story page can be rendered successfully', function () {
    $response = $this->get(route('patient.story'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('PatientStory')
        ->has('featuredStory')
        ->has('stories')
        ->has('categories')
    );
});
