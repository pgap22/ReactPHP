<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use React\Promise\Promise;

class EmailService
{
    public function sendEmail($to, $subject, $message)
    {
        return new Promise(function ($resolve, $reject) use ($to, $subject, $message) {
            $mail = new PHPMailer(true);

            try {
                // Configuración del servidor SMTP de Mailtrap
                $mail->isSMTP();
                $mail->Host = 'sandbox.smtp.mailtrap.io'; // Servidor SMTP de Mailtrap
                $mail->SMTPAuth = true;
                $mail->Username = 'b7b9c0531c99e7'; // Reemplaza con tu usuario de Mailtrap
                $mail->Password = '62d2861b2bbeda'; // Reemplaza con tu contraseña de Mailtrap
                $mail->SMTPSecure = 'tls';
                $mail->Port = 2525;

                // Configuración del correo
                $mail->setFrom('noreply@example.com', 'Tu Aplicación');
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->Body = $message;

                // Enviar correo
                $mail->send();

                // Resolver la promesa si el correo se envía correctamente
                $resolve('Correo enviado exitosamente');
            } catch (Exception $e) {
                // Rechazar la promesa si ocurre un error
                $reject(new \RuntimeException('Error al enviar el correo: ' . $mail->ErrorInfo));
            }
        });
    }
}