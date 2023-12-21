<?php

declare(strict_types=1);

namespace GenerativeAI\Tests\Unit\Resources;

use GenerativeAI\Enums\FinishReason;
use GenerativeAI\Enums\HarmBlockThreshold;
use GenerativeAI\Enums\HarmCategory;
use GenerativeAI\Enums\HarmProbability;
use GenerativeAI\Enums\Role;
use GenerativeAI\Resources\Candidate;
use GenerativeAI\Resources\CitationMetadata;
use GenerativeAI\Resources\Content;
use GenerativeAI\Resources\SafetyRating;
use GenerativeAI\SafetySetting;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CandidateTest extends TestCase
{
    public function testConstructor()
    {
        $candidate = new Candidate(
            new Content([], Role::User),
            FinishReason::OTHER,
            new CitationMetadata(),
            [],
            1,
            1,
        );
        self::assertInstanceOf(Candidate::class, $candidate);
    }

    public function testConstructorWithSafetyRatings()
    {
        $candidate = new Candidate(
            new Content([], Role::User),
            FinishReason::OTHER,
            new CitationMetadata(),
            [
                new SafetyRating(
                    HarmCategory::HARM_CATEGORY_MEDICAL,
                    HarmProbability::HIGH,
                    true,
                ),
                new SafetyRating(
                    HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT,
                    HarmProbability::LOW,
                    false,
                ),
            ],
            1,
            1,
        );
        self::assertInstanceOf(Candidate::class, $candidate);
    }

    public function testConstructorWithInvalidSafetyRatings()
    {
        $this->expectException(InvalidArgumentException::class);

        new Candidate(
            new Content([], Role::User),
            FinishReason::OTHER,
            new CitationMetadata(),
            [
                new SafetyRating(
                    HarmCategory::HARM_CATEGORY_MEDICAL,
                    HarmProbability::HIGH,
                    false,
                ),
                new SafetySetting(
                    HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT,
                    HarmBlockThreshold::BLOCK_LOW_AND_ABOVE,
                ),
            ],
            1,
            1,
        );
    }

    public function testFromArray()
    {
        $candidate = Candidate::fromArray([
            'content' => ['parts' => [], 'role' => 'user'],
            'safetyRatings' => [],
            'citationMetadata' => [],
            'index' => 1,
            'tokenCount' => 1,
            'finishReason' => 'OTHER',
        ]);

        self::assertInstanceOf(Candidate::class, $candidate);
    }
}
