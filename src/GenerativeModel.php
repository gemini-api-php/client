<?php

declare(strict_types=1);

namespace GenerativeAI;

use GenerativeAI\Enums\ModelName;
use GenerativeAI\Enums\Role;
use GenerativeAI\Requests\CountTokensRequest;
use GenerativeAI\Requests\GenerateContentRequest;
use GenerativeAI\Responses\CountTokensResponse;
use GenerativeAI\Responses\GenerateContentResponse;
use GenerativeAI\Resources\Content;
use GenerativeAI\Resources\Parts\PartInterface;
use GenerativeAI\Traits\ArrayTypeValidator;
use Psr\Http\Client\ClientExceptionInterface;

class GenerativeModel
{
    use ArrayTypeValidator;

    /** @var SafetySetting[] */
    private array $safetySettings = [];

    private ?GenerationConfig $generationConfig = null;

    public function __construct(
        private readonly Client $client,
        private readonly ModelName $modelName,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function generateContent(PartInterface ...$parts): GenerateContentResponse
    {
        $content = new Content($parts, Role::User);

        return $this->generateContentWithContents([$content]);
    }

    /**
     * @param Content[] $contents
     * @throws ClientExceptionInterface
     */
    public function generateContentWithContents(array $contents): GenerateContentResponse
    {
        $this->ensureArrayOfType($contents, Content::class);

        $request = new GenerateContentRequest(
            $this->modelName,
            $contents,
            $this->safetySettings,
            $this->generationConfig,
        );

        return $this->client->generateContent($request);
    }

    public function startChat(): ChatSession
    {
        return new ChatSession($this);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function countTokens(PartInterface ...$parts): CountTokensResponse
    {
        $content = new Content($parts, Role::User);
        $request = new CountTokensRequest(
            $this->modelName,
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
