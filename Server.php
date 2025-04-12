<?php

require __DIR__ . '/vendor/autoload.php';

use App\Router;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Socket\SocketServer;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

$loop = Loop::get();

// Agrega este middleware para mostrar errores detallados
$server = new HttpServer(
    $loop,
    function (ServerRequestInterface $request, callable $next) {
        try {
            return $next($request);
        } catch (\Throwable $e) {
            return new Response(
                500,
                ['Content-Type' => 'text/plain'],
                "ERROR DETALLADO:\n" .
                    $e->getMessage() . "\n\n" .
                    "ARCHIVO: " . $e->getFile() . " LINEA: " . $e->getLine() . "\n\n" .
                    "TRACE:\n" . $e->getTraceAsString()
            );
        }
    },
    new Router()
);

$socket = new SocketServer('0.0.0.0:3001', [], $loop);
$server->listen($socket);

echo "Servidor ReactPHP corriendo en http://localhost:3001\n";
$loop->run();
