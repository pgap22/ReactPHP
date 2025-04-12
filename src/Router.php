<?php

namespace App;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Controllers\ApiControllers;

class Router
{
    public function __invoke(ServerRequestInterface $request)
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        if ($path === '/') {
            return $this->serveFile('public/index.html', 'text/html');
        }

        if ($path === '/contact') {
            return $this->serveFile('public/contact.html', 'text/html');
        }

        if ($path === '/data') {
            return $this->serveFile('public/data.html', 'text/html');
        }

        if ($path === '/style.css') {
            return $this->serveFile('public/style.css', 'text/css');
        }

        // API CRUD
        if (strpos($path, '/api/') === 0) {
            return (new ApiControllers())->handle($request);
        }

        if ($path === '/api/email' && $method === 'POST') {
            return (new ApiControllers())->handleEmail($request);
        }

        return new Response(404, ['Content-Type' => 'text/plain'], '404 No encontrado');
    }

    private function serveFile(string $path, string $contentType)
    {
        if (!file_exists($path)) {
            return new Response(404, [], 'Archivo no encontrado');
        }

        return new Response(200, ['Content-Type' => $contentType], file_get_contents($path));
    }
}
