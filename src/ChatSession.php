<?php

declare(strict_types=1);

namespace GeminiAPI;

use GeminiAPI\Enums\Role;
use GeminiAPI\Resources\Content;
use GeminiAPI\Resources\Parts\PartInterface;
use GeminiAPI\Responses\GenerateContentResponse;
use GeminiAPI\Traits\ArrayTypeValidator;
use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;

class ChatSession
{
    use ArrayTypeValidator;

    /** @var Content[] */
    private array $history;

    public function __construct(
        private readonly GenerativeModel $model,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function sendMessage(PartInterface ...$parts): GenerateContentResponse
    {
        $this->history[] = new Content($parts, Role::User);

        $config = (new GenerationConfig())
        ->withCandidateCount(1);
        $response = $this->model
            ->withGenerationConfig($config)
            ->generateContentWithContents($this->history);

        if(!empty($response->candidates)) {
            $parts = $response->candidates[0]->content->parts;
            $this->history[] = new Content($parts, Role::Model);
        }

        return $response;
    }

    /**
     * @return Content[]
     */
    public function history(): array
    {
        return $this->history;
    }

    /**
     * @param Content[] $history
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withHistory(array $history): self
    {
        $this->ensureArrayOfType($history, Content::class);

        $clone = clone $this;
        $clone->history = $history;

        return $clone;
    }
}
