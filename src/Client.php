<?php

declare(strict_types=1);

namespace GeminiAPI;

use GeminiAPI\ClientInterface as GeminiClientInterface;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Requests\CountTokensRequest;
use GeminiAPI\Requests\GenerateContentRequest;
use GeminiAPI\Requests\ListModelsRequest;
use GeminiAPI\Requests\RequestInterface;
use GeminiAPI\Responses\CountTokensResponse;
use GeminiAPI\Responses\GenerateContentResponse;
use GeminiAPI\Responses\ListModelsResponse;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

use function json_decode;

class Client implements GeminiClientInterface
{
    private string $baseUrl = 'https://generativelanguage.googleapis.com';
    public function __construct(
        private readonly string  $apiKey,
        private ?HttpClientInterface $client = null,
        private ?RequestFactoryInterface $requestFactory = null,
        private ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->client ??= Psr18ClientDiscovery::find();
        $this->requestFactory ??= Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory ??= Psr17FactoryDiscovery::findStreamFactory();
    }

    public function geminiPro(): GenerativeModel
    {
        return $this->generativeModel(ModelName::GeminiPro);
    }

    public function geminiProVision(): GenerativeModel
    {
        return $this->generativeModel(ModelName::GeminiProVision);
    }

    public function generativeModel(ModelName $modelName): GenerativeModel
    {
        return new GenerativeModel(
            $this,
            $modelName,
        );
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function generateContent(GenerateContentRequest $request): GenerateContentResponse
    {
        $response = $this->doRequest($request);
        $json = json_decode($response, associative: true);

        return GenerateContentResponse::fromArray($json);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function countTokens(CountTokensRequest $request): CountTokensResponse
    {
        $response = $this->doRequest($request);
        $json = json_decode($response, associative: true);

        return CountTokensResponse::fromArray($json);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function listModels(): ListModelsResponse
    {
        $request = new ListModelsRequest();
        $response = $this->doRequest($request);
        $json = json_decode($response, associative: true);

        return ListModelsResponse::fromArray($json);
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
    private function doRequest(RequestInterface $request): string
    {
        if (!isset($this->client, $this->requestFactory, $this->streamFactory)) {
            throw new RuntimeException('Missing client or factory for Gemini API operation');
        }

        $uri = "{$this->baseUrl}/v1/{$request->getOperation()}";
        $httpRequest = $this->requestFactory
            ->createRequest($request->getHttpMethod(), $uri)
            ->withAddedHeader(self::API_KEY_HEADER_NAME, $this->apiKey);

        $payload = $request->getHttpPayload();
        if (!empty($payload)) {
            $stream = $this->streamFactory->createStream($payload);
            $httpRequest = $httpRequest->withBody($stream);
        }

        $response = $this->client->sendRequest($httpRequest);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException(
                sprintf(
                    'Gemini API operation failed: operation=%s, status_code=%d,  response=%s',
                    $request->getOperation(),
                    $response->getStatusCode(),
                    $response->getBody(),
                ),
            );
        }

        return (string) $response->getBody();
    }
}
