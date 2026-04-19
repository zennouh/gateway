<?php

namespace App\Test;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class UploadService
{
    public function __construct(private HttpClientInterface $httpClient) {}

    public function uploadImage(?UploadedFile $file): ?string
    {

        if (!$file) {
            return null;
        }

        // return "done";

        try {
            $dataPart = DataPart::fromPath(
                $file->getRealPath(),
                $file->getClientOriginalName(),
                $file->getClientMimeType()
            );

            $formFields = [
                'file' => $dataPart,
            ];
            $formData = new FormDataPart($formFields);

            // 3. Request
            $response = $this->httpClient->request(
                'POST',
                "http://media:8010/api/media/upload/avatar",
                [
                    'headers' => array_merge(
                        $formData->getPreparedHeaders()->toArray(),
                        ['Accept' => 'application/json']
                    ),
                    'body' => $formData->bodyToIterable(),
                ]
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                throw new Exception('Upload failed: ' . $response->getContent(false));
            }

            $data = $response->toArray();

            return $data['url'] ?? '';
        } catch (Exception $e) {
            return null;
        }
    }
}
