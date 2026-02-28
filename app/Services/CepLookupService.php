<?php

declare(strict_types=1);

namespace App\Services;

final class CepLookupService
{
    private string $correiosBaseUrl;
    private string $correiosBearerToken;
    private bool $enableViaCepFallback;
    private int $timeoutSeconds;

    public function __construct(array $config = [])
    {
        $this->correiosBaseUrl = rtrim(
            (string) ($config['correios_base_url'] ?? 'https://api.correios.com.br/cep/v2'),
            '/'
        );
        $this->correiosBearerToken = trim((string) ($config['correios_bearer_token'] ?? ''));
        $this->enableViaCepFallback = (bool) ($config['enable_viacep_fallback'] ?? true);
        $this->timeoutSeconds = max(2, (int) ($config['timeout_seconds'] ?? 6));
    }

    public function lookup(string $cep): ?array
    {
        $digits = preg_replace('/\D+/', '', $cep);
        if (!is_string($digits) || strlen($digits) !== 8) {
            return null;
        }

        if ($this->correiosBearerToken !== '') {
            $address = $this->lookupFromCorreios($digits);
            if ($address !== null) {
                return $address;
            }
        }

        if ($this->enableViaCepFallback) {
            return $this->lookupFromViaCep($digits);
        }

        return null;
    }

    private function lookupFromCorreios(string $cep): ?array
    {
        $endpoint = $this->correiosBaseUrl . '/enderecos/' . $cep;
        $response = $this->requestJson($endpoint, [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->correiosBearerToken,
        ]);

        if ($response['status'] === 404 || $response['status'] === 422) {
            return null;
        }

        if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($response['data'])) {
            return null;
        }

        return $this->mapAddress($response['data'], 'correios');
    }

    private function lookupFromViaCep(string $cep): ?array
    {
        $endpoint = 'https://viacep.com.br/ws/' . $cep . '/json/';
        $response = $this->requestJson($endpoint, ['Accept: application/json']);

        if ($response['status'] < 200 || $response['status'] >= 300 || !is_array($response['data'])) {
            return null;
        }

        if (($response['data']['erro'] ?? false) === true) {
            return null;
        }

        return $this->mapAddress($response['data'], 'viacep');
    }

    private function mapAddress(array $data, string $source): ?array
    {
        $cep = $this->formatCep((string) ($data['cep'] ?? ''));
        $address = trim((string) ($data['logradouro'] ?? $data['street'] ?? ''));
        $neighborhood = trim((string) ($data['bairro'] ?? $data['district'] ?? ''));
        $city = trim((string) ($data['localidade'] ?? $data['cidade'] ?? $data['city'] ?? ''));
        $state = strtoupper(substr(trim((string) ($data['uf'] ?? $data['state'] ?? '')), 0, 2));
        $complement = trim((string) ($data['complemento'] ?? $data['complement'] ?? ''));

        if ($address === '' && $neighborhood === '' && $city === '' && $state === '') {
            return null;
        }

        return [
            'cep' => $cep,
            'address' => $address,
            'neighborhood' => $neighborhood,
            'city' => $city,
            'state' => $state,
            'complement' => $complement,
            'source' => $source,
        ];
    }

    private function requestJson(string $url, array $headers): array
    {
        $headerString = implode("\r\n", array_merge([
            'User-Agent: dashboard-php-pbt/1.0',
        ], $headers));

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
                'timeout' => $this->timeoutSeconds,
                'header' => $headerString,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $responseHeaders = isset($http_response_header) && is_array($http_response_header)
            ? $http_response_header
            : [];
        $status = $this->extractStatusCode($responseHeaders);

        if ($body === false || $body === '') {
            return ['status' => $status, 'data' => null];
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return ['status' => $status, 'data' => null];
        }

        return ['status' => $status, 'data' => $data];
    }

    private function extractStatusCode(array $headers): int
    {
        if ($headers === []) {
            return 0;
        }

        $statusLine = (string) $headers[0];
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $statusLine, $matches) !== 1) {
            return 0;
        }

        return (int) ($matches[1] ?? 0);
    }

    private function formatCep(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if (!is_string($digits) || strlen($digits) !== 8) {
            return $value;
        }

        return substr($digits, 0, 5) . '-' . substr($digits, 5, 3);
    }
}
