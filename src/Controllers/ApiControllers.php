<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\DatabaseService;

class ApiController
{
    private $dbService;

    public function __construct()
    {
        $this->dbService = new DatabaseService();
    }

    public function handle(ServerRequestInterface $request)
    {
        try {
            $path = $request->getUri()->getPath();
            $method = $request->getMethod();

            error_log("Solicitud recibida: $method $path"); // Log para depuración

            if ($path === '/api/entries' && $method === 'GET') {
                return $this->handleGetEntries();
            }
            
            if ($path === '/api/entries' && $method === 'POST') {
                return $this->handlePostEntry($request);
            }
            
            return $this->jsonResponse(404, ['error' => 'Endpoint no encontrado']);
            
        } catch (\Throwable $e) {
            error_log("ERROR: " . $e->getMessage()); // Log detallado
            return $this->jsonResponse(500, [
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function handleGetEntries()
    {
        return $this->dbService->getAllEntries()
            ->then(function ($entries) {
                return $this->jsonResponse(200, $entries);
            },
            function ($error) {
                throw new \RuntimeException('Error al obtener entradas: ' . $error->getMessage());
            });
    }

    private function handlePostEntry(ServerRequestInterface $request)
    {
        $data = $this->parseJsonBody($request);
        
        if (empty($data['title'])) {
            throw new \InvalidArgumentException('El título es requerido');
        }

        return $this->dbService->createEntry($data)
            ->then(function ($id) {
                return $this->jsonResponse(201, ['id' => $id]);
            });
    }

    private function parseJsonBody(ServerRequestInterface $request)
    {
        $data = json_decode((string)$request->getBody(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Datos JSON inválidos');
        }
        
        return $data;
    }

    private function jsonResponse($status, $data)
    {
        return new Response(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
    }
}