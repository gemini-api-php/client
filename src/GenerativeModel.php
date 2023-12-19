<?php

declare(strict_types=1);

namespace GenerativeAI;

use GenerativeAI\Enums\Model;
use GenerativeAI\Enums\Role;
use GenerativeAI\Requests\CountTokensRequest;
use GenerativeAI\Requests\GenerateContentRequest;
use GenerativeAI\Responses\CountTokensResponse;
use GenerativeAI\Responses\GenerateContentResponse;
use GenerativeAI\Resources\Content;
use GenerativeAI\Resources\Parts\PartInterface;
use Psr\Http\Client\ClientExceptionInterface;

class GenerativeModel
{
    /** @var SafetySetting[] */
    private array $safetySettings = [];

    private ?GenerationConfig $generationConfig = null;

    public function __construct(
        private readonly Client $client,
        private readonly Model $model,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function generateContent(PartInterface ...$parts): GenerateContentResponse
    {
        $content = new Content($parts, Role::User);
        $request = new GenerateContentRequest(
            $this->model,
            [$content],
            $this->safetySettings,
            $this->generationConfig,
        );

        return $this->client->generateContent($request);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function countTokens(PartInterface ...$parts): CountTokensResponse
    {
        $content = new Content($parts, Role::User);
        $request = new CountTokensRequest(
            $this->model,
            [$content],
        );

        return $this->client->countTokens($request);
    }

    public function withAddedSafetySetting(SafetySetting $safetySetting): self
    {
        $clone = clone $this;
        $clone->safetySettings[] = $safetySetting;

        return $clone;
    }

    public function withGenerationConfig(GenerationConfig $generationConfig): self
    {
        $clone = clone $this;
        $clone->generationConfig = $generationConfig;

        return $clone;
    }
}
