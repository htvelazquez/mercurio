<?php

require 'vendor/autoload.php';

Flight::register('db', 'PDO', array("mysql:host=localhost;dbname=homestead", 'homestead', 'secret'));

Flight::route('GET /', function(){
    Flight::render('home_new');
});

Flight::route('GET /en', function(){
    Flight::render('home_new');
});

Flight::route('GET /es', function(){
    Flight::render('home_new');
});

Flight::route('POST /contact', function(){
    $data = Flight::request()->data->getData();

    $sql = "INSERT INTO `contact_info` (`name`, `email`, `subject`, `message`, `timestamp`) VALUES (?,?,?,?, NOW())";
    $sth = Flight::db()->prepare($sql);
    $sth->bindParam(1, $data['name']);
    $sth->bindParam(2, $data['email']);
    $sth->bindParam(3, $data['subject']);
    $sth->bindParam(4, $data['message']);
    $sth->execute();

    Flight::json(array(
        'status' => 200
    ), 200);
});

Flight::start();
