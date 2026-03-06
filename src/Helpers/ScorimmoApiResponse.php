<?php

namespace CLDT\Scorimmo\Helpers;

class ScorimmoApiResponse
{
    protected int $statusCode = 200;
    protected bool $hasError = false;
    protected string $message;
    protected string $verbose;

    protected ScorimmoApiPagination $meta;

    protected array $data;

    public function __construct(int $statusCode, array $response = [], string $dataKey = null)
    {
        if ($statusCode < 200 || $statusCode >= 300) {
            $this->hasError = true;
            $this->message = $response['message'] ?? $response['error'] ?? 'Unknown error';
            $this->verbose = $response['verbose'] ?? $response['message'] ?? 'Unknown error';
            $this->statusCode = $statusCode;
            $this->data = [];

            return;
        }

        if (isset($response['informations'])) {
            $informations = $response['informations'];

            // Scorimmo wraps pagination in an array with a nested 'informations' key
            if (is_array($informations) && isset($informations[0]['informations'])) {
                $this->meta = new ScorimmoApiPagination($informations[0]['informations']);
            } elseif (is_array($informations) && isset($informations['limit'])) {
                $this->meta = new ScorimmoApiPagination($informations);
            }
        }

        if (! isset($dataKey)) {
            $this->data = $response;

            return;
        }

        if (! isset($response[$dataKey])) {
            $this->data = [];

            return;
        }

        $this->data = $response[$dataKey];
    }

    public function hasError(): bool
    {
        return $this->hasError;
    }

    public function getVerbose(): string
    {
        return $this->verbose;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMeta(): ScorimmoApiPagination
    {
        return $this->meta;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getFirst(): ?array
    {
        return $this->data[0] ?? null;
    }

    public function getLast(): ?array
    {
        return $this->data[count($this->data) - 1] ?? null;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function isPaginated(): bool
    {
        return isset($this->meta);
    }

    public function getPagination(): ScorimmoApiPagination
    {
        return $this->meta;
    }

    public function toArray(): array
    {
        $array = [
            'statusCode' => $this->statusCode,
            'hasError' => $this->hasError,
            'message' => $this->message ?? '',
            'verbose' => $this->verbose ?? '',
            'data' => $this->data,
        ];

        if (isset($this->meta)) {
            $array['meta'] = $this->meta->toArray();
        }

        return $array;
    }

    public function toString(): string
    {
        return json_encode($this->data);
    }
}
