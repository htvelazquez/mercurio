<?php namespace App\Services;

use \Illuminate\Support\Facades\DB;

interface IService {
	public static function getInstance();
}

abstract class Service implements IService
{
    protected static $_Objects = [];

    protected function __construct() { }

    protected static function build($class) {
        if(!isset(self::$_Objects[$class]))
            self::$_Objects[$class] = new $class;

        return self::$_Objects[$class];
    }
}
