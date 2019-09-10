<?php

require 'vendor/autoload.php';

Flight::route('GET /', function(){
    Flight::render('home_en');
});

Flight::route('GET /en', function(){
    Flight::render('home_en');
});

Flight::route('GET /es', function(){
    Flight::render('home_es');
});

Flight::start();
