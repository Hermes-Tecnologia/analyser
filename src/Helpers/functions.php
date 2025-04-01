<?php

use HermesTecnologia\Analyser\Facades\Analyser;

if (!function_exists('analyse')) {
    function analyse($value) {
        return Analyser::analyse($value);
    }
}