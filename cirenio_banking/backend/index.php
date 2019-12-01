<?php

	require 'vendor/autoload.php';

	session_start();

	Flight::register('db', 'mysqli', array('localhost','homestead','secret','homestead'));

	$db = Flight::db();

	Flight::route('GET /', function(){
			checkLogin();
			$banks = getAllActiveBanks();
	    Flight::render('home', array('banks' => $banks));
	});

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
			],200);
		}
		return Flight::json(array('success' => false),404);
	});

	Flight::route('POST /job', function(){
		checkLogin();
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
			return Flight::json(array('success' => false),400);
		}

		return Flight::json(array('success' => true, 'hash' => $data['hash']),200);
	});

	Flight::route('POST /data/@hash', function($hash){
		// $data = Flight::request()->data->getData();
		// $json = json_encode($data);
		$json = '{"cajas":[{"id":"******9468","alias":"MAYO.TIERRA.AGOSTO","cbu":"15000459-00007361694682","currency":"ARS","balance":"0,26"},{"id":"******4174","alias":"CLAVEL.TRIBU.PASTA","cbu":"15006235-00062380941746","currency":"USD","balance":"0,00"}],"cuentas":[{"id":"******0054","currency":"ARS","alias":"PUMA.LICOR.GENIO","cbu":"15006235-00062332500546","balance":"0,00","limit":"15.000,00"}],"tarjetas":[{"id":"**** **** **** 6600","type":"Visa","limit":{"amount":"128000.00","currency":"ARS"},"available":{"amount":"18022.50","currency":"ARS"}},{"id":"**** **** **** 8876","type":"American Express","limit":{"amount":"64000.00","currency":"ARS"},"available":{"amount":"29200.00","currency":"ARS"}},{"id":"**** **** **** 6417","type":"Mastercard","limit":{"amount":"104000.00","currency":"ARS"},"available":{"amount":"101720.00","currency":"ARS"}},{"id":"**** **** **** 0571","type":"Visa","limit":null,"available":null},{"id":"**** **** **** 7881","type":"Mastercard","limit":null,"available":null}],"prestamos":[]}';

		$sql = "INSERT INTO `jobs_data` (`hash`, `data`) VALUES ('$hash', '$json')";

		try {
		    $sth = Flight::db()->prepare($sql);
		    $sth->execute();
		} catch (Exception $e) {
			return Flight::json(array('success' => false),400);
		}

		$sql = "DELETE FROM jobs WHERE hash = '$hash'";
		$sth = Flight::db()->prepare($sql);
		$sth->execute();
		return Flight::json(array('success' => true),200);
	});

	Flight::route('GET /data/@hash', function($hash){
		checkLogin();
		$data = Flight::db()->query("SELECT * FROM `jobs_data` WHERE hash = '$hash'")->fetch_assoc();

		if (!empty($data)){
			return Flight::render('result', array('data' => json_decode($data['data'])));
		}

		return Flight::json(array('success' => false),404);
	});

	Flight::route('GET /form/@alias', function($alias){
		checkLogin();
		Flight::render("forms/$alias");
	});

	Flight::route('POST /login', function(){
		$data = Flight::request()->data->getData();
		if ($data['user'] == 'admin' && $data['password'] == 'cireniodemo123'){
			$_SESSION['login_user'] = 'admin';
			return Flight::json(array('success' => true),200);
		}else{
			return Flight::json(array('success' => false),200);
		}
	});

	function getAllActiveBanks(){
		$result = Flight::db()->query("SELECT * FROM `banks` WHERE active = 1");

		 $banks = array();
		 while ($row = $result->fetch_assoc()) {
				 $banks[] = $row;
		 }
		 return $banks;
	}

	function checkLogin(){
		if (!isset($_SESSION['login_user'])){
			Flight::render("forms/login");
			die;
		}
	}

	Flight::start();
