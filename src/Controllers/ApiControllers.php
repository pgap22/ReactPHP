<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use App\Services\DatabaseService;
use App\Services\EmailService;

class ApiControllers
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

            // Manejo de /api/entries
            if ($path === '/api/entries' && $method === 'GET') {
                return $this->handleGetEntries();
            }

            if ($path === '/api/entries' && $method === 'POST') {
                return $this->handlePostEntry($request);
            }

            // Manejo de /api/entries/{id}
            if (preg_match('#^/api/entries/(\d+)$#', $path, $matches)) {
                $id = (int)$matches[1];

                if ($method === 'PUT') {
                    return $this->handleUpdateEntry($request, $id);
                }

                if ($method === 'DELETE') {
                    return $this->handleDeleteEntry($id);
                }
            }

            // Manejo de /api/email
            if ($path === '/api/email' && $method === 'POST') {
                return $this->handleEmail($request);
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

    private function handleUpdateEntry(ServerRequestInterface $request, $id)
    {
        $data = $this->parseJsonBody($request);

        if (empty($data['title'])) {
            throw new \InvalidArgumentException('El título es requerido');
        }

        return $this->dbService->updateEntry($id, $data)
            ->then(function () {
                return $this->jsonResponse(200, ['message' => 'Entrada actualizada']);
            });
    }

    private function handleGetEntries()
    {
        return $this->dbService->getAllEntries()
            ->then(
                function ($entries) {
                    return $this->jsonResponse(200, $entries);
                },
                function ($error) {
                    throw new \RuntimeException('Error al obtener entradas: ' . $error->getMessage());
                }
            );
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

    private function handleDeleteEntry($id)
    {
        return $this->dbService->deleteEntry($id)
            ->then(
                function () {
                    return $this->jsonResponse(200, ['message' => 'Entrada eliminada']);
                },
                function ($error) {
                    throw new \RuntimeException('Error al eliminar la entrada: ' . $error->getMessage());
                }
            );
    }

    public function handleEmail(ServerRequestInterface $request)
    {
        try {
            $data = $this->parseJsonBody($request);

            $this->validateEmailData($data);

            // Usar el servicio de correo
            $emailService = new EmailService();
            $emailService->sendEmail($data['to'], $data['subject'], $data['message'])
                ->then(
                    function ($successMessage) {
                        echo $successMessage; // "Correo enviado exitosamente"
                    },
                    function ($error) {
                        echo $error->getMessage(); // "Error al enviar el correo: ..."
                    }
                );

            return $this->jsonResponse(200, ['message' => 'Correo enviado exitosamente']);
        } catch (\Throwable $e) {
            error_log("ERROR: " . $e->getMessage());
            return $this->jsonResponse(500, [
                'error' => 'Error al enviar el correo',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function validateEmailData(array $data)
    {
        if (empty($data['to']) || !filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El campo "to" debe ser un correo electrónico válido.');
        }

        if (empty($data['subject']) || strlen($data['subject']) > 255) {
            throw new \InvalidArgumentException('El campo "subject" es obligatorio y no debe exceder 255 caracteres.');
        }

        if (empty($data['message'])) {
            throw new \InvalidArgumentException('El campo "message" es obligatorio.');
        }
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
