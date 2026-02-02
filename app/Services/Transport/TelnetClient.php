<?php

declare(strict_types=1);

namespace App\Services\Transport;

class TelnetClient
{
    private string $host;
    private int $port;
    private int $timeout;
    private $resource;

    public function __construct(string $host, int $port = 23, int $timeout = 10)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function connect(): bool
    {
        $this->resource = @stream_socket_client(sprintf('tcp://%s:%d', $this->host, $this->port), $errno, $errstr, $this->timeout);

        if (! $this->resource) {
            return false;
        }

        stream_set_timeout($this->resource, $this->timeout);

        // Read initial banner if any
        $this->read(1024);

        return true;
    }

    public function disconnect(): void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }

        $this->resource = null;
    }

    public function read(int $len = 4096): string
    {
        if (! is_resource($this->resource)) {
            return '';
        }

        return stream_get_contents($this->resource, $len) ?: '';
    }

    public function write(string $data): bool
    {
        if (! is_resource($this->resource)) {
            return false;
        }

        fwrite($this->resource, $data);

        return true;
    }

    public function exec(string $cmd, float $wait = 0.2): string
    {
        $this->write($cmd . "\r\n");
        usleep((int) ($wait * 1_000_000));
        return $this->read();
    }
}