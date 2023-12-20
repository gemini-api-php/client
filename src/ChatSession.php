<?php

declare(strict_types=1);

namespace GenerativeAI;

use GenerativeAI\Enums\Role;
use GenerativeAI\Resources\Content;
use GenerativeAI\Resources\Parts\PartInterface;
use GenerativeAI\Responses\GenerateContentResponse;
use Psr\Http\Client\ClientExceptionInterface;

class ChatSession
{
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
}
