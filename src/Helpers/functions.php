<?php

use HermesTecnologia\Analyser\Analyser;

if (!function_exists('analyse')) {
    function analyse($value) {
        return Analyser::make($value);
    }
}