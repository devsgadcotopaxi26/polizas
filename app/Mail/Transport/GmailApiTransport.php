<?php

namespace App\Mail\Transport;

use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\Message as GmailMessage;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Envía correo mediante la Gmail API usando una cuenta de servicio con
 * Domain-Wide Delegation, impersonando una cuenta real del Workspace.
 * Se usa para notificaciones generales del sistema (mailer 'sistema'),
 * separado del SMTP que usa el gestor para enviar oficios.
 */
class GmailApiTransport extends AbstractTransport
{
    public function __construct(
        private readonly string $serviceAccountPath,
        private readonly string $impersonate,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if ($this->impersonate === '') {
            throw new RuntimeException('GMAIL_IMPERSONATE_EMAIL no está configurado.');
        }

        $path = $this->resolvedServiceAccountPath();

        if (!is_file($path)) {
            throw new RuntimeException("No se encontró el archivo de credenciales de Gmail API en [{$path}].");
        }

        $client = new GoogleClient();
        $client->setAuthConfig($path);
        $client->setSubject($this->impersonate);
        $client->addScope(Gmail::GMAIL_SEND);

        $service = new Gmail($client);

        $raw = strtr(base64_encode($message->toString()), '+/', '-_');

        $gmailMessage = new GmailMessage();
        $gmailMessage->setRaw($raw);

        $service->users_messages->send('me', $gmailMessage);
    }

    private function resolvedServiceAccountPath(): string
    {
        $path = $this->serviceAccountPath;

        $isAbsolute = str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path) === 1;

        return $isAbsolute ? $path : base_path($path);
    }

    public function __toString(): string
    {
        return 'gmailapi';
    }
}
