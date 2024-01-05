<?php

declare(strict_types=1);

namespace GeminiAPI;

use BadMethodCallException;
use CurlHandle;
use GeminiAPI\ClientInterface as GeminiClientInterface;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Json\ObjectListParser;
use GeminiAPI\Requests\CountTokensRequest;
use GeminiAPI\Requests\EmbedContentRequest;
use GeminiAPI\Requests\GenerateContentRequest;
use GeminiAPI\Requests\GenerateContentStreamRequest;
use GeminiAPI\Requests\ListModelsRequest;
use GeminiAPI\Requests\RequestInterface;
use GeminiAPI\Responses\CountTokensResponse;
use GeminiAPI\Responses\EmbedContentResponse;
use GeminiAPI\Responses\GenerateContentResponse;
use GeminiAPI\Responses\ListModelsResponse;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

use function curl_close;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function extension_loaded;
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

    public function embeddingModel(ModelName $modelName): EmbeddingModel
    {
        return new EmbeddingModel(
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
     * @param callable(GenerateContentResponse): void $callback
     * @throws BadMethodCallException
     * @throws RuntimeException
     */
    public function generateContentStream(
        GenerateContentStreamRequest $request,
        callable $callback,
    ): void {
        if (!extension_loaded('curl')) {
            throw new BadMethodCallException('Gemini API requires `curl` extension for streaming responses');
        }

        $parser = new ObjectListParser(
            /* @phpstan-ignore-next-line */
            static fn (array $arr) => $callback(GenerateContentResponse::fromArray($arr)),
        );

        $ch = curl_init("{$this->baseUrl}/v1/{$request->getOperation()}");

        if ($ch === false) {
            throw new RuntimeException('Gemini API cannot initialize streaming content request');
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-type: application/json',
            self::API_KEY_HEADER_NAME . ": {$this->apiKey}",
        ]);
        curl_setopt(
            $ch,
            CURLOPT_WRITEFUNCTION,
            static fn (CurlHandle $ch, string $str): int => $parser->consume($str),
        );
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function embedContent(EmbedContentRequest $request): EmbedContentResponse
    {
        $response = $this->doRequest($request);
        $json = json_decode($response, associative: true);

        return EmbedContentResponse::fromArray($json);
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
