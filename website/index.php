<?php

require 'vendor/autoload.php';

Flight::route('GET /', function(){
    Flight::render('home_new');
});

Flight::route('GET /en', function(){
    Flight::render('home_new');
});

Flight::route('GET /es', function(){
    Flight::render('home_new');
});

Flight::start();
