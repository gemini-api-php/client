<?php

declare(strict_types=1);

namespace GenerativeAI;

use GenerativeAI\Enums\Model;
use GenerativeAI\Requests\CountTokensRequest;
use GenerativeAI\Requests\GenerateContentRequest;
use GenerativeAI\Responses\CountTokensResponse;
use GenerativeAI\Responses\GenerateContentResponse;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

use function json_decode;

class Client
{
    private string $baseUrl = 'https://generativelanguage.googleapis.com';

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    public function __construct(
        private readonly string  $apiKey,
        private ?ClientInterface $client = null,
    ) {
        $this->client = Psr18ClientDiscovery::find();
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    }

    public function GeminiPro(): GenerativeModel
    {
        return $this->generativeModel(Model::GeminiPro);
    }

    public function GeminiProVision(): GenerativeModel
    {
        return $this->generativeModel(Model::GeminiProVision);
    }

    public function generativeModel(Model $model): GenerativeModel
    {
        return new GenerativeModel(
            $this,
            $model,
        );
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function generateContent(GenerateContentRequest $request): GenerateContentResponse
    {
        $response = $this->doRequest(
            $request->model,
            'generateContent',
            (string) $request,
        );

        $json = json_decode($response, associative: true);

        return GenerateContentResponse::fromArray($json);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function countTokens(CountTokensRequest $request): CountTokensResponse
    {
        $response = $this->doRequest(
            $request->model,
            'countTokens',
            (string) $request,
        );

        /** @var array{totalTokens: int} $json */
        $json = json_decode($response, associative: true);

        return CountTokensResponse::fromArray($json);
    }

    public function withBaseUrl(string $baseUrl): self
    {
        $clone = clone $this;
        $clone->baseUrl = $baseUrl;

        return $clone;
    }

    /**
     * @throws ClientExceptionInterface
     */
    private function doRequest(
        Model $model,
        string $operation,
        string $payload,
    ): string {
        if (!$this->client) {
            throw new RuntimeException('Missing client for Generative AI operation');
        }

        $uri = sprintf(
            '%s/v1/%s:%s?key=%s',
            $this->baseUrl,
            $model->value,
            $operation,
            $this->apiKey,
        );
        $httpRequest = $this->requestFactory->createRequest('POST', $uri);

        if (!empty($payload)) {
            $stream = $this->streamFactory->createStream($payload);
            $httpRequest = $httpRequest->withBody($stream);
        }

        $response = $this->client->sendRequest($httpRequest);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException(
                sprintf(
                    'Generative AI operation failed: operation=%s, status_code=%d,  response=%s',
                    $operation,
                    $response->getStatusCode(),
                    $response->getBody(),
                ),
            );
        }

        return (string) $response->getBody();
    }
}
