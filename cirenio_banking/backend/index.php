<?php

	require 'vendor/autoload.php';

	Flight::register('db', 'mysqli', array('localhost','homestead','secret','homestead'));

	$db = Flight::db();

	Flight::route('GET /', function(){
			$banks = getAllActiveBanks();
	    Flight::render('home', array('banks' => $banks));
	});

	function getAllActiveBanks(){
		$result = Flight::db()->query("SELECT * FROM `banks` WHERE active = 1");

		 $banks = array();
		 while ($row = $result->fetch_assoc()) {
				 $banks[] = $row;
		 }
		 return $banks;
	}

	Flight::route('GET /job', function(){
		$job = Flight::db()->query("SELECT j.id, j.password, j.user, j.document_num, j.document_type, j.hash, b.alias, b.link
			FROM `jobs` j
			INNER JOIN banks b ON (b.`id` = j.`bank_id`)
			WHERE status = 0 AND retries = 0 AND b.active = 1
			ORDER BY j.`id` ASC LIMIT 1")->fetch_assoc();

		if (!empty($job)){

			$update = "UPDATE `jobs` SET retries = 1 WHERE id = {$job['id']}";
		    $sth = Flight::db()->prepare($update);
		    $sth->execute();

			return Flight::json([
				'password'=> $job['password'],
				'user'		=> $job['user'],
				'doc_num' => $job['document_num'],
				'doc_type'=> $job['document_type'],
				'alias'		=> $job['alias'],
				'url'			=> $job['link'],
				'hash'		=> $job['hash'],
				'success'	=> true
			]);
		}
		return Flight::json(array('success' => false));
	});

	Flight::route('POST /job', function(){
		$data = Flight::request()->data->getData();
		$data['timestamp'] = 'NOW()';

		$values = '';
		foreach ($data as $key => $value) {
			if (empty($value) || $value == ''){
				$values.= 'NULL,';
			}elseif(in_array($key,['bank_id','timestamp'])){
				$values.= "$value,";
			}else{
				$values.="'$value',";
			}
		}

		$data['hash'] = hash ('sha256', "$values".time());
		$values.= "'{$data['hash']}'";

		$fields = implode(array_keys($data),",");

		$sql = "INSERT INTO `jobs` ($fields) VALUES ($values)";

		try {
			$sth = Flight::db()->prepare($sql);
			$sth->execute();
		} catch (Exception $e) {
			return Flight::json(array('success' => false));
		}

		return Flight::json(array('success' => true, 'hash' => $data['hash']));
	});

	Flight::route('POST /data/@hash', function($hash){
		$data = Flight::request()->data->getData();
		$json = json_encode($data);
		$sql = "INSERT INTO `jobs_data` (`hash`, `data`) VALUES ('$hash', '$json')";

		try {
		    $sth = Flight::db()->prepare($sql);
		    $sth->execute();
		} catch (Exception $e) {
			return Flight::json(array('success' => false));
		}

		$sql = "DELETE FROM jobs WHERE hash = '$hash'";
		$sth = Flight::db()->prepare($sql);
		$sth->execute();
		return Flight::json(array('success' => true));
	});

	Flight::route('GET /data/@hash', function($hash){
		$data = Flight::db()->query("SELECT * FROM `jobs_data` WHERE hash = '$hash'")->fetch_assoc();

		if (!empty($data)){
			return Flight::json(array('success' => true, 'data' => json_decode($data['data'])));
		}

		return Flight::json(array('success' => false));
	});

	Flight::route('GET /form/@alias/@id[\d+]', function($alias,$id){
		Flight::render("forms/$alias");
	});

	Flight::start();
