<?php

namespace App\Controller;

use App\Enum\ServicesEnum;
use App\Services\GatewayService;
use App\Exception\ServiceRouteException;
use App\Exception\ServiceTimeoutException;
use App\Exception\ServiceUnavailableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

class GatewayController extends AbstractController
{
    public function __construct(
        private GatewayService $gatewayService,
        private LoggerInterface $logger
    ) {}

    /**
     * Main gateway route - forwards all API requests to appropriate microservice
     */
    #[Route(
        '/api/{service}/{path?}',
        name: 'gateway_proxy',
        requirements: [
            'path' => '.+',
            'service' => ServicesEnum::ALL,
        ],
        methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']
    )]
    public function proxy(Request $request, string $service, ?string $path): Response
    {
        try {
            $method = $request->getMethod();
            $body = $request->getContent() ?: null;
            $headers = $this->extractHeaders($request);
            $queryParams = $request->query->all();

            // Forward request to microservice
            $response = $this->gatewayService->forwardRequest(
                $method,
                $service,
                $path,
                $headers,
                $body,
                $queryParams
            );

            // Return response with appropriate status code
            return new Response(
                $response['body'],
                $response['status_code'],
                $this->flattenHeaders($response['headers'])
            );
        }
        // catch (ServiceRouteException $e) {
        //     return $this->jsonError(
        //         'Route not found',
        //         Response::HTTP_NOT_FOUND,
        //         $e->getMessage()
        //     );
        // } catch (ServiceTimeoutException $e) {
        //     return $this->jsonError(
        //         'Service timeout',
        //         Response::HTTP_GATEWAY_TIMEOUT,
        //         $e->getMessage()
        //     );
        // } catch (ServiceUnavailableException $e) {
        //     return $this->jsonError(
        //         'Service unavailable',
        //         Response::HTTP_SERVICE_UNAVAILABLE,
        //         $e->getMessage()
        //     );
        // } 
        catch (\Exception $e) {
            $this->logger->error('Gateway error', [
                'error' => $e->getMessage(),
                'path' => $path ?? 'unknown'
            ]);

            return $this->jsonError(
                'Gateway error',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'An unexpected error occurred'
            );
        }
    }

    /**
     * Gateway health check endpoint
     */
    // #[Route('/api/gateway/health', name: 'gateway_health', methods: ['GET'])]
    // public function health(): JsonResponse
    // {
    //     return $this->json([
    //         'status' => 'healthy',
    //         'timestamp' => date('c'),
    //         'services' => count($this->gatewayService->getServices()),
    //         'routes' => count($this->gatewayService->getRoutes()),
    //     ]);
    // }

    /**
     * Get gateway information and available services
     */
    // #[Route('/api/gateway/info', name: 'gateway_info', methods: ['GET'])]
    // public function info(): JsonResponse
    // {
    //     $services = [];
    //     foreach ($this->gatewayService->getServices() as $name => $config) {
    //         $services[$name] = [
    //             'url' => $config['url'],
    //             'prefix' => $config['prefix'] ?? null,
    //             'timeout' => $config['timeout'] ?? 30,
    //         ];
    //     }

    //     return $this->json([
    //         'gateway' => 'API Gateway',
    //         'version' => '1.0.0',
    //         'services' => $services,
    //         'routes' => $this->gatewayService->getRoutes(),
    //     ]);
    // }

    /**
     * Get status of all microservices
     */
    // #[Route('/api/gateway/services/status', name: 'gateway_services_status', methods: ['GET'])]
    // public function servicesStatus(): JsonResponse
    // {
    //     $status = [];

    //     foreach ($this->gatewayService->getServices() as $serviceName => $config) {
    //         $status[$serviceName] = $this->gatewayService->getServiceStatus($serviceName);
    //     }

    //     return $this->json([
    //         'timestamp' => date('c'),
    //         'services' => $status,
    //     ]);
    // }

    /**
     * Get status of a specific microservice
     */
    // #[Route('/api/gateway/services/{serviceName}/status', name: 'gateway_service_status', methods: ['GET'])]
    // public function serviceStatus(string $serviceName): JsonResponse
    // {
    //     try {
    //         $status = $this->gatewayService->getServiceStatus($serviceName);

    //         return $this->json($status);
    //     } catch (\Exception $e) {
    //         return $this->jsonError(
    //             'Service not found',
    //             Response::HTTP_NOT_FOUND,
    //             "Service '{$serviceName}' not found"
    //         );
    //     }
    // }

    /**
     * Extract and return request headers
     */
    private function extractHeaders(Request $request): array
    {
        $headers = [];

        foreach ($request->headers->all() as $key => $value) {
            // Skip some headers that shouldn't be forwarded
            $skipHeaders = ['host', 'connection', 'content-length'];

            if (!in_array(strtolower($key), $skipHeaders)) {
                $headers[$key] = is_array($value) ? $value[0] : $value;
            }
        }

        return $headers;
    }

    /**
     * Flatten header array for response
     */
    private function flattenHeaders(array $headers): array
    {
        $flattened = [];

        foreach ($headers as $key => $value) {
            if (is_array($value)) {
                $flattened[$key] = $value[0] ?? null;
            } else {
                $flattened[$key] = $value;
            }
        }

        return $flattened;
    }

    /**
     * Return JSON error response
     */
    private function jsonError(string $message, int $statusCode, string $detail = ''): JsonResponse
    {
        return $this->json([
            'error' => $message,
            'detail' => $detail,
            'timestamp' => date('c'),
        ], $statusCode);
    }
}
