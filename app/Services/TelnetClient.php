<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class TelnetClient
{
    private $socket;
    private string $host;
    private int $port;
    private float $timeout;

    public function __construct(string $host, int $port = 23, float $timeout = 10.0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function login(string $username, string $password): bool
    {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, (float) $this->timeout);

        if (! $this->socket) {
            throw new RuntimeException("Telnet connect failed: {$errno} {$errstr}");
        }

        stream_set_blocking($this->socket, false);

        // Simple login attempt: write username and password, some OLTs need interactive prompts
        // This is a best-effort approach; vendor-specific handling should be added in OltService when needed.
        $this->write($username . "\n");
        usleep(200000);
        $this->write($password . "\n");
        usleep(200000);

        return true;
    }

    public function exec(string $command)
    {
        if (! $this->socket) {
            throw new RuntimeException('Telnet connection not established');
        }

        $this->write($command . "\n");
        usleep(250000);

        // Read available data for a short window
        $output = '';
        $start = microtime(true);
        while ((microtime(true) - $start) < 1.0) {
            $data = stream_get_contents($this->socket);
            if ($data === false) {
                break;
            }
            $output .= $data;
            usleep(100000);
        }

        return $output;
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    private function write(string $data): void
    {
        if (! $this->socket) {
            throw new RuntimeException('Telnet socket not open');
        }

        fwrite($this->socket, $data);
    }
}
