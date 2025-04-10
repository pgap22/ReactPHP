<?php
namespace App\Services;

use React\Promise\PromiseInterface;
use App\Services\Connection;
use React\MySQL\Exception;
use React\MySQL\QueryResult;

class DatabaseService
{
    private $connection;

    public function __construct()
    {
        $this->connection = Connection::get();
    }

    /**
     * Obtiene todas las entradas de la base de datos
     */
    public function getAllEntries(): PromiseInterface
    {
        return $this->connection->query('SELECT id, title, description FROM entries ORDER BY created_at DESC')
            ->then(
                function (QueryResult $result) {
                    if (empty($result->resultRows)) {
                        error_log("No se encontraron entradas en la base de datos");
                        return [];
                    }
                    return $result->resultRows;
                },
                function (Exception $error) {
                    error_log("Error en SELECT: " . $error->getMessage());
                    throw new \RuntimeException('Error al obtener entradas: ' . $error->getMessage());
                }
            );
    }

    /**
     * Crea una nueva entrada en la base de datos
     */
    public function createEntry(array $data): PromiseInterface
    {
        // Validación de datos
        if (empty($data['title'])) {
            error_log("Intento de crear entrada sin título");
            throw new \InvalidArgumentException('El título es requerido');
        }

        return $this->connection->query(
            'INSERT INTO entries (title, description) VALUES (?, ?)',
            [$data['title'], $data['description'] ?? '']
        )->then(
            function (QueryResult $result) {
                if ($result->insertId === 0) {
                    error_log("No se pudo obtener el ID de la nueva entrada");
                    throw new \RuntimeException('No se pudo crear la entrada');
                }
                error_log("Entrada creada con ID: " . $result->insertId);
                return $result->insertId;
            },
            function (Exception $error) {
                error_log("Error en INSERT: " . $error->getMessage());
                throw new \RuntimeException('Error al crear entrada: ' . $error->getMessage());
            }
        );
    }

    /**
     * Actualiza una entrada existente
     */
    public function updateEntry(int $id, array $data): PromiseInterface
    {
        if (empty($data['title'])) {
            error_log("Intento de actualizar entrada sin título");
            throw new \InvalidArgumentException('El título es requerido');
        }

        return $this->connection->query(
            'UPDATE entries SET title = ?, description = ? WHERE id = ?',
            [$data['title'], $data['description'] ?? '', $id]
        )->then(
            function (QueryResult $result) {
                if ($result->affectedRows === 0) {
                    error_log("No se afectaron filas en la actualización");
                    throw new \RuntimeException('No se encontró la entrada a actualizar');
                }
                return true;
            },
            function (Exception $error) {
                error_log("Error en UPDATE: " . $error->getMessage());
                throw new \RuntimeException('Error al actualizar entrada: ' . $error->getMessage());
            }
        );
    }

    /**
     * Elimina una entrada de la base de datos
     */
    public function deleteEntry(int $id): PromiseInterface
    {
        return $this->connection->query(
            'DELETE FROM entries WHERE id = ?',
            [$id]
        )->then(
            function (QueryResult $result) {
                if ($result->affectedRows === 0) {
                    error_log("No se afectaron filas en el DELETE");
                    throw new \RuntimeException('No se encontró la entrada a eliminar');
                }
                return true;
            },
            function (Exception $error) {
                error_log("Error en DELETE: " . $error->getMessage());
                throw new \RuntimeException('Error al eliminar entrada: ' . $error->getMessage());
            }
        );
    }

    /**
     * Verifica la conexión a la base de datos
     */
    public function checkConnection(): PromiseInterface
    {
        return $this->connection->query('SELECT 1')
            ->then(
                function () {
                    return true;
                },
                function (Exception $error) {
                    throw new \RuntimeException('Error de conexión a MySQL: ' . $error->getMessage());
                }
            );
    }
}