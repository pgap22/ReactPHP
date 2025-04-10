<?php
require __DIR__ . '/vendor/autoload.php';
$config = include __DIR__ . '/config/database.php';

$loop = React\EventLoop\Loop::get();
$factory = new React\MySQL\Factory($loop);

$connection = $factory->createLazyConnection(
    "{$config['user']}:{$config['password']}@{$config['host']}/{$config['dbname']}"
);

// Prueba conexión básica
$connection->query('SELECT 1')
    ->then(function () {
        echo "✅ Conexión a MySQL exitosa\n";
    }, function ($error) {
        echo "❌ Error de conexión: " . $error->getMessage() . "\n";
    });

// Prueba consulta a la tabla
$connection->query('SELECT * FROM entries LIMIT 1')
    ->then(function ($result) {
        echo "✅ Consulta SELECT exitosa. Tabla existe.\n";
    }, function ($error) {
        echo "❌ Error en SELECT: " . $error->getMessage() . "\n";
    });

$loop->run();