<?php
require_once __DIR__ . '/config.php';

class SimpleSMTPMailer {
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;

    public function __construct(string $host, int $port, string $username, string $password, string $encryption = 'tls') {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->encryption = strtolower($encryption);
    }

    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, string $html): bool {
        $remote = $this->encryption === 'ssl'
            ? 'ssl://' . $this->host . ':' . $this->port
            : $this->host . ':' . $this->port;

        $stream = @stream_socket_client($remote, $errno, $errstr, 30,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);

        if(!$stream) {
            throw new Exception("SMTP connection failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($stream, 30);

        $this->expect($stream, [220]);
        $this->ehlo($stream);

        if($this->encryption === 'tls') {
            $this->command($stream, 'STARTTLS', [220]);
            if(!stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('Unable to establish TLS encryption.');
            }
            $this->ehlo($stream);
        }

        if($this->username !== '') {
            $this->command($stream, 'AUTH LOGIN', [334]);
            $this->command($stream, base64_encode($this->username), [334]);
            $this->command($stream, base64_encode($this->password), [235]);
        }

        $this->command($stream, 'MAIL FROM: <' . $fromEmail . '>', [250]);
        $this->command($stream, 'RCPT TO: <' . $toEmail . '>', [250, 251]);
        $this->command($stream, 'DATA', [354]);

        $headers = [
            'From: ' . $this->formatName($fromName) . " <{$fromEmail}>",
            'To: <' . $toEmail . '>',
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];

        $body = implode("\r\n", $headers) . "\r\n\r\n" . $this->sanitizeBody($html) . "\r\n.";
        $this->command($stream, $body, [250]);
        $this->command($stream, 'QUIT', [221]);
        fclose($stream);

        return true;
    }

    private function ehlo($stream): void {
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->command($stream, 'EHLO ' . $serverName, [250]);
    }

    private function command($stream, string $command, array $expectedCodes): void {
        fwrite($stream, $command . "\r\n");
        $this->expect($stream, $expectedCodes);
    }

    private function expect($stream, array $expectedCodes): void {
        $response = '';
        while($line = fgets($stream, 515)) {
            $response .= $line;
            if(isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        if(!$response) {
            throw new Exception('Empty response from SMTP server.');
        }

        $code = (int) substr($response, 0, 3);
        if(!in_array($code, $expectedCodes)) {
            throw new Exception("Unexpected SMTP response ({$code}): {$response}");
        }
    }

    private function sanitizeBody(string $html): string {
        $body = preg_replace("/(\r\n|\r|\n)/", "\r\n", $html);
        $body = preg_replace('/^\./m', '..', $body);
        return $body;
    }

    private function formatName(string $name): string {
        $clean = trim($name);
        return $clean === '' ? SITE_NAME : str_replace(['"', '\n', '\r'], '', $clean);
    }
}

if (!function_exists('sendAppMail')) {
    function sendAppMail(string $to, string $subject, string $message): bool {
        $logDir = __DIR__ . '/../storage/logs';
        if(!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $fromEmail = FROM_EMAIL;
        $fromName = SITE_NAME;

        if(SMTP_HOST && SMTP_USERNAME && SMTP_PASSWORD) {
            try {
                $mailer = new SimpleSMTPMailer(SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD, SMTP_SECURE);
                $mailer->send($fromEmail, $fromName, $to, $subject, $message);
                return true;
            } catch (Throwable $e) {
                file_put_contents($logDir . '/mail.log', '[' . date('Y-m-d H:i:s') . "] SMTP error: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        $headers  = 'From: ' . $fromName . ' <' . $fromEmail . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $sent = @mail($to, $subject, $message, $headers);
        if(!$sent) {
            $error = error_get_last();
            file_put_contents($logDir . '/mail.log', '[' . date('Y-m-d H:i:s') . "] mail() error: " . ($error['message'] ?? 'Unknown error') . "\n", FILE_APPEND);
        }

        return $sent;
    }
}
