<?php

/* **
CREATE TABLE `contact_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `subject` varchar(128) DEFAULT NULL,
  `message` text,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
** */

require 'vendor/autoload.php';

Flight::register('db', 'PDO', array("mysql:host=localhost;dbname=homestead", 'homestead', 'secret'));

Flight::route('GET /', function(){
    Flight::render('home');
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
