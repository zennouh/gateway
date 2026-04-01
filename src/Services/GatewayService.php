<?php

namespace App\Services;

use App\Enum\ServicesEnum;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

class GatewayService
{

    private array $services = [
        ServicesEnum::FORUMS->value => 'http://forums:8000/api/forums/threads',
        ServicesEnum::CHILDCARE->value => 'http://childcare:8008/api/childcare',
        ServicesEnum::JOBS->value => 'http://jobs:8009/api/jobs',
    ];

    public function __construct(private HttpClientInterface $httpClient) {}

    public function forwardRequest(
        string $method,
        string $service,
        ?string $path,
        array $headers = [],
        ?string $body = null,
        array $query = []
    ): array {


        if (!isset($this->services[$service])) {
            throw new \Exception("Service '$service' not found", 404);
        }


        $path = !$path  ? "" : '/' . $path;
        $url = rtrim($this->services[$service], '/') .  $path;
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        // dd($method, $url, $headers, $body);
        
        $response = $this->httpClient->request($method, $url, [
            'headers' => $headers,
            'body'    => $body,
        ]);

        return [
            'status_code' => $response->getStatusCode(),
            'body'        => $response->getContent(false),
            'headers'     => $response->getHeaders(false),
        ];
    }
}
