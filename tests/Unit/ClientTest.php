<?php

declare(strict_types=1);

namespace GeminiAPI\Tests\Unit;

use GeminiAPI\Client;
use GeminiAPI\ClientInterface as GeminiAPIClientInterface;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\GenerativeModel;
use GeminiAPI\Requests\CountTokensRequest;
use GeminiAPI\Requests\EmbedContentRequest;
use GeminiAPI\Requests\GenerateContentRequest;
use GeminiAPI\Resources\Content;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ClientTest extends TestCase
{
    public function testConstructor()
    {
        $client = new Client(
            'test-api-key',
            $this->createMock(HttpClientInterface::class),
        );
        self::assertInstanceOf(Client::class, $client);
    }

    public function testWithBaseUrl()
    {
        $client = new Client(
            'test-api-key',
            $this->createMock(HttpClientInterface::class),
        );
        $client = $client->withBaseUrl('test-base-url');
        self::assertInstanceOf(Client::class, $client);
    }

    public function testGeminiPro()
    {
        $client = new Client(
            'test-api-key',
            $this->createMock(HttpClientInterface::class),
        );
        $model = $client->geminiPro();
        self::assertInstanceOf(GenerativeModel::class, $model);
        self::assertEquals(ModelName::GeminiPro, $model->modelName);
    }

    public function testGeminiProVision()
    {
        $client = new Client(
            'test-api-key',
            $this->createMock(HttpClientInterface::class),
        );
        $model = $client->geminiProVision();
        self::assertInstanceOf(GenerativeModel::class, $model);
        self::assertEquals(ModelName::GeminiProVision, $model->modelName);
    }

    public function testGenerativeModel()
    {
        $client = new Client(
            'test-api-key',
            $this->createMock(HttpClientInterface::class),
        );
        $model = $client->generativeModel(ModelName::Embedding);
        self::assertInstanceOf(GenerativeModel::class, $model);
        self::assertEquals(ModelName::Embedding, $model->modelName);
    }

    public function testGenerateContent()
    {
        $httpRequest = new Request(
            'POST',
            'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent',
        );
        $httpResponse = new Response(
            body: <<<BODY
            {
              "candidates": [
                {
                  "content": {
                    "parts": [
                      {
                        "text": "This is the Gemini Pro response"
                      }
                    ],
                    "role": "model"
                  },
                  "finishReason": "STOP",
                  "index": 0,
                  "safetyRatings": [
                    {
                      "category": "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                      "probability": "NEGLIGIBLE"
                    }
                  ]
                }
              ],
              "promptFeedback": {
                "safetyRatings": [
                  {
                    "category": "HARM_CATEGORY_DANGEROUS_CONTENT",
                    "probability": "NEGLIGIBLE"
                  }
                ]
              }
            }
            BODY,
        );
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects(self::once())
            ->method('createRequest')
            ->with('POST', 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent')
            ->willReturn($httpRequest);

        $httpRequest = $httpRequest
            ->withAddedHeader(GeminiAPIClientInterface::API_KEY_HEADER_NAME, 'test-api-key')
            ->withAddedHeader('content-type', 'application/json');

        $stream = Utils::streamFor('{"model":"models\/gemini-pro","contents":[{"parts":[{"text":"this is a text"}],"role":"user"}]}');
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::once())
            ->method('createStream')
            ->with('{"model":"models\/gemini-pro","contents":[{"parts":[{"text":"this is a text"}],"role":"user"}]}')
            ->willReturn($stream);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('sendRequest')
            ->with($httpRequest->withBody($stream))
            ->willReturn($httpResponse);

        $client = new Client(
            'test-api-key',
            $httpClient,
            $requestFactory,
            $streamFactory,
        );
        $request = new GenerateContentRequest(
            ModelName::GeminiPro,
            [Content::text('this is a text')],
        );
        $response = $client->generateContent($request);
        self::assertEquals('This is the Gemini Pro response', $response->text());
    }

    public function testEmbedContent()
    {
        $httpRequest = new Request(
            'POST',
            'https://generativelanguage.googleapis.com/v1/models/embedding-001:embedContent',
        );
        $httpResponse = new Response(
            body: <<<BODY
            {
              "embedding": {
                "values": [
                  0.041395925,
                  -0.017692696
                ]
              }
            }
            BODY,
        );
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects(self::once())
            ->method('createRequest')
            ->with('POST', 'https://generativelanguage.googleapis.com/v1/models/embedding-001:embedContent')
            ->willReturn($httpRequest);

        $httpRequest = $httpRequest
            ->withAddedHeader(GeminiAPIClientInterface::API_KEY_HEADER_NAME, 'test-api-key')
            ->withAddedHeader('content-type', 'application/json');

        $stream = Utils::streamFor('{"content":{"parts":[{"text":"this is a text"}],"role":"user"}}');
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::once())
            ->method('createStream')
            ->with('{"content":{"parts":[{"text":"this is a text"}],"role":"user"}}')
            ->willReturn($stream);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('sendRequest')
            ->with($httpRequest->withBody($stream))
            ->willReturn($httpResponse);

        $client = new Client(
            'test-api-key',
            $httpClient,
            $requestFactory,
            $streamFactory,
        );
        $request = new EmbedContentRequest(
            ModelName::Embedding,
            Content::text('this is a text'),
        );
        $response = $client->embedContent($request);
        self::assertEquals([0.041395925, -0.017692696], $response->embedding->values);
    }

    public function testCountTokens()
    {
        $httpRequest = new Request(
            'POST',
            'https://generativelanguage.googleapis.com/v1/models/gemini-pro:countTokens',
        );
        $httpResponse = new Response(
            body: <<<BODY
            {
              "totalTokens": 10
            }
            BODY,
        );
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects(self::once())
            ->method('createRequest')
            ->with('POST', 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:countTokens')
            ->willReturn($httpRequest);

        $httpRequest = $httpRequest
            ->withAddedHeader(GeminiAPIClientInterface::API_KEY_HEADER_NAME, 'test-api-key')
            ->withAddedHeader('content-type', 'application/json');

        $stream = Utils::streamFor('{"model":"models\/gemini-pro","contents":[{"parts":[{"text":"this is a text"}],"role":"user"}]}');
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::once())
            ->method('createStream')
            ->with('{"model":"models\/gemini-pro","contents":[{"parts":[{"text":"this is a text"}],"role":"user"}]}')
            ->willReturn($stream);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('sendRequest')
            ->with($httpRequest->withBody($stream))
            ->willReturn($httpResponse);

        $client = new Client(
            'test-api-key',
            $httpClient,
            $requestFactory,
            $streamFactory,
        );
        $request = new CountTokensRequest(
            ModelName::GeminiPro,
            [Content::text('this is a text')],
        );
        $response = $client->countTokens($request);
        self::assertEquals(10, $response->totalTokens);
    }

    public function testListModels()
    {
        $httpRequest = new Request(
            'GET',
            'https://generativelanguage.googleapis.com/v1/models',
        );
        $httpResponse = new Response(
            body: <<<BODY
            {
              "models": [
                {
                  "name": "models/gemini-pro",
                  "version": "001",
                  "displayName": "Gemini Pro",
                  "description": "The best model for scaling across a wide range of tasks",
                  "inputTokenLimit": 30720,
                  "outputTokenLimit": 2048,
                  "supportedGenerationMethods": [
                    "generateContent",
                    "countTokens"
                  ],
                  "temperature": 0.9,
                  "topP": 1,
                  "topK": 1
                },
                {
                  "name": "models/gemini-pro-vision",
                  "version": "001",
                  "displayName": "Gemini Pro Vision",
                  "description": "The best image understanding model to handle a broad range of applications",
                  "inputTokenLimit": 12288,
                  "outputTokenLimit": 4096,
                  "supportedGenerationMethods": [
                    "generateContent",
                    "countTokens"
                  ],
                  "temperature": 0.4,
                  "topP": 1,
                  "topK": 32
                }
              ]
            }
            BODY,
        );
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects(self::once())
            ->method('createRequest')
            ->with('GET', 'https://generativelanguage.googleapis.com/v1/models')
            ->willReturn($httpRequest);

        $httpRequest = $httpRequest
            ->withAddedHeader(GeminiAPIClientInterface::API_KEY_HEADER_NAME, 'test-api-key')
            ->withAddedHeader('content-type', 'application/json');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('sendRequest')
            ->with($httpRequest)
            ->willReturn($httpResponse);

        $client = new Client(
            'test-api-key',
            $httpClient,
            $requestFactory,
        );
        $response = $client->listModels();
        self::assertCount(2, $response->models);
        self::assertEquals('models/gemini-pro', $response->models[0]->name);
        self::assertEquals('models/gemini-pro-vision', $response->models[1]->name);
    }
}
