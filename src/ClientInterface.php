<?php

declare(strict_types=1);

namespace GeminiAPI;

use GeminiAPI\Enums\ModelName;
use GeminiAPI\Requests\CountTokensRequest;
use GeminiAPI\Requests\EmbedContentRequest;
use GeminiAPI\Requests\GenerateContentRequest;
use GeminiAPI\Responses\CountTokensResponse;
use GeminiAPI\Responses\EmbedContentResponse;
use GeminiAPI\Responses\GenerateContentResponse;
use GeminiAPI\Responses\ListModelsResponse;

/**
 * @since v1.1.0
 */
interface ClientInterface
{
    public const API_KEY_HEADER_NAME = 'x-goog-api-key';

    public function countTokens(CountTokensRequest $request): CountTokensResponse;
    public function generateContent(GenerateContentRequest $request): GenerateContentResponse;
    public function embedContent(EmbedContentRequest $request): EmbedContentResponse;
    public function generativeModel(ModelName $modelName): GenerativeModel;
    public function embeddingModel(ModelName $modelName): EmbeddingModel;
    public function listModels(): ListModelsResponse;
    public function withBaseUrl(string $baseUrl): self;
}
