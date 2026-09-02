<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Laravel\Reverb\ReverbServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    ReverbServiceProvider::class,
];
