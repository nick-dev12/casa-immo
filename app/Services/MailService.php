<?php

declare(strict_types=1);

namespace App\Services;

final class MailService
{
    /**
     * @return array{success: bool, message: string, mailed: bool}
     */
    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        bool $transactional = false
    ): array {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Adresse destinataire invalide.', 'mailed' => false];
        }

        $mailer = strtolower((string) config('mail', 'mailer', 'smtp'));
        $host = (string) config('mail', 'host', '');
        $port = (int) config('mail', 'port', 465);
        $encryption = strtolower((string) config('mail', 'encryption', 'ssl'));
        $username = (string) config('mail', 'username', '');
        $password = (string) config('mail', 'password', '');
        $fromAddress = (string) config('mail', 'from_address', $username);
        $fromName = (string) config('mail', 'from_name', 'Casa-blog Immo');
        $verifyPeer = (bool) config('mail', 'verify_peer', false);

        $textBody ??= trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));

        if ($mailer === 'log') {
            $this->sendViaLog($to, $subject, $htmlBody, $textBody);

            return ['success' => true, 'message' => 'Logged', 'mailed' => false];
        }

        if ($host === '' || $username === '' || $password === '') {
            return ['success' => false, 'message' => 'Configuration mail incomplète.', 'mailed' => false];
        }

        try {
            $this->sendViaSmtp(
                $host,
                $port,
                $encryption,
                $username,
                $password,
                $fromAddress,
                $fromName,
                $to,
                $subject,
                $htmlBody,
                $textBody,
                $verifyPeer,
                $transactional
            );

            $this->logSuccess($to, $subject, (string) $host);

            return ['success' => true, 'message' => 'OK', 'mailed' => true];
        } catch (\Throwable $exception) {
            $this->logError($exception->getMessage());

            return ['success' => false, 'message' => $exception->getMessage(), 'mailed' => false];
        }
    }

    private function sendViaLog(
        string $to,
        string $subject,
        string $htmlBody,
        string $textBody,
        ?string $smtpError = null
    ): void {
        $dir = base_path('storage/logs');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $entry = [
            'time' => date('c'),
            'to' => $to,
            'subject' => $subject,
            'smtp_error' => $smtpError,
            'text' => $textBody,
            'html' => $htmlBody,
        ];

        @file_put_contents(
            $dir . '/mail-outbox-' . date('Y-m-d') . '.log',
            json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n",
            FILE_APPEND
        );
    }

    private function sendViaSmtp(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password,
        string $fromAddress,
        string $fromName,
        string $to,
        string $subject,
        string $htmlBody,
        string $textBody,
        bool $verifyPeer = true,
        bool $transactional = false
    ): void {
        $remote = $encryption === 'ssl'
            ? 'ssl://' . $host . ':' . $port
            : 'tcp://' . $host . ':' . $port;

        $socket = @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            20,
            STREAM_CLIENT_CONNECT,
            stream_context_create([
                'ssl' => [
                    'verify_peer' => $verifyPeer,
                    'verify_peer_name' => $verifyPeer,
                    'allow_self_signed' => !$verifyPeer,
                ],
            ])
        );

        if ($socket === false) {
            throw new \RuntimeException('Connexion SMTP impossible : ' . ($errstr ?: 'erreur réseau'));
        }

        stream_set_timeout($socket, 20);

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'EHLO ' . gethostname(), [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('Échec STARTTLS.');
                }
                $this->command($socket, 'EHLO ' . gethostname(), [250]);
            }

            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($username), [334]);
            $this->command($socket, base64_encode($password), [235]);
            $this->command($socket, 'MAIL FROM:<' . $fromAddress . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $encodedSubject = $this->encodeHeader($subject);
            $encodedFromName = $this->encodeHeader($fromName);
            $outerBoundary = 'zig_related_' . bin2hex(random_bytes(8));
            $alternativeBoundary = 'zig_alternative_' . bin2hex(random_bytes(8));
            $messageId = '<' . bin2hex(random_bytes(16)) . '@' . $host . '>';
            $inlineBrand = (string) config('mail', 'inline_brand_image', 'assets/images/brand/icon-casa-immo.png');
            $logoPath = base_path($inlineBrand);
            $hasInlineLogo = str_contains($htmlBody, 'cid:casa-blog-logo') && is_file($logoPath);

            $headers = [
                'Date: ' . date(DATE_RFC2822),
                'Message-ID: ' . $messageId,
                'From: ' . $encodedFromName . ' <' . $fromAddress . '>',
                'Reply-To: <' . $fromAddress . '>',
                'To: <' . $to . '>',
                'Subject: ' . $encodedSubject,
                'MIME-Version: 1.0',
                'Content-Type: multipart/related; boundary="' . $outerBoundary . '"',
            ];

            if (!$transactional) {
                $headers[] = 'Auto-Submitted: auto-generated';
                $headers[] = 'X-Auto-Response-Suppress: All';
            }

            $body = implode("\r\n", $headers) . "\r\n\r\n";
            $body .= '--' . $outerBoundary . "\r\n";
            $body .= 'Content-Type: multipart/alternative; boundary="' . $alternativeBoundary . '"' . "\r\n\r\n";
            $body .= '--' . $alternativeBoundary . "\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($textBody), 76, "\r\n") . "\r\n";
            $body .= '--' . $alternativeBoundary . "\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($htmlBody), 76, "\r\n") . "\r\n";
            $body .= '--' . $alternativeBoundary . "--\r\n";

            if ($hasInlineLogo) {
                $logo = file_get_contents($logoPath);
                if ($logo !== false) {
                    $body .= '--' . $outerBoundary . "\r\n";
                    $inlineFilename = basename($logoPath);
                    $body .= 'Content-Type: image/png; name="' . $inlineFilename . "\"\r\n";
                    $body .= "Content-Transfer-Encoding: base64\r\n";
                    $body .= "Content-ID: <casa-blog-logo>\r\n";
                    $body .= 'Content-Disposition: inline; filename="' . $inlineFilename . "\"\r\n\r\n";
                    $body .= chunk_split(base64_encode($logo), 76, "\r\n");
                }
            }

            $body .= '--' . $outerBoundary . "--\r\n";
            $body = $this->dotStuff($body);

            fwrite($socket, $body . "\r\n.\r\n");
            $acceptedResponse = $this->expect($socket, [250]);
            $this->logSmtpAccepted($to, $messageId, $acceptedResponse);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    /**
     * @param resource $socket
     * @param list<int> $codes
     */
    private function command($socket, string $command, array $codes): string
    {
        fwrite($socket, $command . "\r\n");

        return $this->expect($socket, $codes);
    }

    /**
     * @param resource $socket
     * @param list<int> $codes
     */
    private function expect($socket, array $codes): string
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        if ($response === '') {
            throw new \RuntimeException('Réponse SMTP vide.');
        }

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new \RuntimeException(trim($response));
        }

        return trim($response);
    }

    private function encodeHeader(string $value): string
    {
        if (preg_match('/[^\x20-\x7E]/', $value)) {
            return '=?UTF-8?B?' . base64_encode($value) . '?=';
        }

        return $value;
    }

    private function dotStuff(string $message): string
    {
        return preg_replace('/^\./m', '..', $message) ?? $message;
    }

    private function logError(string $message): void
    {
        $this->appendMailLog('mail-' . date('Y-m-d') . '.log', $message);
    }

    private function logSuccess(string $to, string $subject, string $host): void
    {
        $line = sprintf('sent to=%s host=%s subject=%s', $to, $host, $subject);
        $this->appendMailLog('mail-sent-' . date('Y-m-d') . '.log', $line);
    }

    private function logSmtpAccepted(string $to, string $messageId, string $response): void
    {
        $line = sprintf(
            'accepted to=%s message_id=%s response=%s',
            $to,
            $messageId,
            preg_replace('/\s+/', ' ', $response) ?? $response
        );
        $this->appendMailLog('mail-smtp-' . date('Y-m-d') . '.log', $line);
    }

    private function appendMailLog(string $filename, string $message): void
    {
        $dir = base_path('storage/logs');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $line = sprintf("[%s] MailService: %s\n", date('Y-m-d H:i:s'), $message);
        @file_put_contents($dir . '/' . $filename, $line, FILE_APPEND);
    }
}
