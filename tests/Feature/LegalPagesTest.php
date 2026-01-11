<?php

use Inertia\Testing\AssertableInertia as Assert;

it('displays the terms and conditions page', function () {
    $this->get(route('terms'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Terms')
            ->has('title')
        );
});

it('displays the privacy policy page', function () {
    $this->get(route('privacy'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Privacy')
            ->has('title')
        );
});
