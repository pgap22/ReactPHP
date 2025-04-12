<?php
namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\Factory;
use React\EventLoop\Loop;

class Connection
{
    private static $instance;
    private $connection;

    private function __construct()
    {
        $config = include  getcwd(). '/config/database.php';
        $factory = new Factory(Loop::get());
        
        $dsn = "{$config['user']}:{$config['password']}@{$config['host']}/{$config['dbname']}?charset={$config['charset']}";
        $this->connection = $factory->createLazyConnection($dsn);
    }

    public static function get(): ConnectionInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
}