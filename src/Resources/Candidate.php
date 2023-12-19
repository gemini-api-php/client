<?php

declare(strict_types=1);

namespace GenerativeAI\Resources;

use GenerativeAI\Enums\FinishReason;
use GenerativeAI\Traits\ArrayTypeValidator;
use UnexpectedValueException;

class Candidate
{
    use ArrayTypeValidator;

    /**
     * @param Content $content
     * @param FinishReason $finishReason
     * @param CitationMetadata $citationMetadata
     * @param SafetyRating[] $safetyRatings
     * @param int $tokenCount
     * @param int $index
     */
    public function __construct(
        public readonly Content $content,
        public readonly FinishReason $finishReason,
        public readonly CitationMetadata $citationMetadata,
        public readonly array $safetyRatings,
        public readonly int $tokenCount,
        public readonly int $index,
    ) {
        if ($tokenCount < 0) {
            throw new UnexpectedValueException('tokenCount cannot be negative');
        }

        if ($index < 0) {
            throw new UnexpectedValueException('index cannot be negative');
        }

        $this->ensureArrayOfType($safetyRatings, SafetyRating::class);
    }

    /**
     * @param array{
     *     citationMetadata: array<string, mixed>,
     *     safetyRatings: array<int, array{category: string, probability: string, blocked: bool|null}>,
     *     content: array{parts: array<int, array{text: string, inlineData: array{mimeType: string, data: string}}>, role: string},
     *     finishReason: string,
     *     tokenCount: int,
     *     index: int,
     * } $candidate
     * @return self
     */
    public static function fromArray(array $candidate): self
    {
        $citationMetadata = isset($candidate['citationMetadata'])
            ? CitationMetadata::fromArray($candidate['citationMetadata'])
            : new CitationMetadata();

        $safetyRatings = array_map(
            static fn (array $rating): SafetyRating => SafetyRating::fromArray($rating),
            $candidate['safetyRatings'] ?? [],
        );

        return new self(
            Content::fromArray($candidate['content']),
            FinishReason::from($candidate['finishReason']),
            $citationMetadata,
            $safetyRatings,
            $candidate['tokenCount'] ?? 0,
            $candidate['index'] ?? 0,
        );
    }
}
