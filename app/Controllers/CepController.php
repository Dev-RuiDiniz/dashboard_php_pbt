<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Services\CepLookupService;
use Throwable;

final class CepController
{
    public function __construct(private readonly Container $container)
    {
    }

    public function lookup(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $rawCep = trim((string) ($_GET['cep'] ?? ''));
        $cepDigits = preg_replace('/\D+/', '', $rawCep);
        if (!is_string($cepDigits) || strlen($cepDigits) !== 8) {
            http_response_code(422);
            echo json_encode([
                'ok' => false,
                'message' => 'CEP invalido. Informe 8 digitos.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $appConfig = $this->container->get('config')['app'] ?? [];
        $cepConfig = is_array($appConfig['cep_lookup'] ?? null)
            ? $appConfig['cep_lookup']
            : [];

        try {
            $service = new CepLookupService($cepConfig);
            $address = $service->lookup($cepDigits);
        } catch (Throwable $exception) {
            http_response_code(502);
            echo json_encode([
                'ok' => false,
                'message' => 'Falha ao consultar o servico de CEP.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        if ($address === null) {
            http_response_code(404);
            echo json_encode([
                'ok' => false,
                'message' => 'CEP nao encontrado.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        echo json_encode([
            'ok' => true,
            'data' => $address,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
