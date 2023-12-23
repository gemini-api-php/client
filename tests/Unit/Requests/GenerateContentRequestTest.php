<?php

declare(strict_types=1);

namespace GeminiAPI\Tests\Unit\Requests;

use GeminiAPI\Enums\HarmBlockThreshold;
use GeminiAPI\Enums\HarmCategory;
use GeminiAPI\Enums\HarmProbability;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Enums\Role;
use GeminiAPI\GenerationConfig;
use GeminiAPI\Requests\GenerateContentRequest;
use GeminiAPI\Resources\Content;
use GeminiAPI\Resources\Parts\TextPart;
use GeminiAPI\Resources\SafetyRating;
use GeminiAPI\SafetySetting;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GenerateContentRequestTest extends TestCase
{
    public function testConstructorWithNoContents()
    {
        $request = new GenerateContentRequest(
            ModelName::Default,
            [],
            [],
            null,
        );
        self::assertInstanceOf(GenerateContentRequest::class, $request);
    }

    public function testConstructorWithContents()
    {
        $request = new GenerateContentRequest(
            ModelName::Default,
            [
                new Content([], Role::User),
                new Content([], Role::Model),
            ],
            [],
            null,
        );
        self::assertInstanceOf(GenerateContentRequest::class, $request);
    }

    public function testConstructorWithInvalidContents()
    {
        $this->expectException(InvalidArgumentException::class);

        new GenerateContentRequest(
            ModelName::Default,
            [
                new Content([], Role::User),
                new TextPart('This is a text'),
            ],
            [],
            null,
        );
    }

    public function testConstructorWithSafetySettings()
    {
        $request = new GenerateContentRequest(
            ModelName::Default,
            [],
            [
                new SafetySetting(
                    HarmCategory::HARM_CATEGORY_HATE_SPEECH,
                    HarmBlockThreshold::BLOCK_LOW_AND_ABOVE,
                ),
                new SafetySetting(
                    HarmCategory::HARM_CATEGORY_MEDICAL,
                    HarmBlockThreshold::BLOCK_MEDIUM_AND_ABOVE,
                ),
            ],
            null,
        );
        self::assertInstanceOf(GenerateContentRequest::class, $request);
    }

    public function testConstructorWithInvalidSafetySettings()
    {
        $this->expectException(InvalidArgumentException::class);

        new GenerateContentRequest(
            ModelName::Default,
            [],
            [
                new SafetySetting(
                    HarmCategory::HARM_CATEGORY_UNSPECIFIED,
                    HarmBlockThreshold::HARM_BLOCK_THRESHOLD_UNSPECIFIED,
                ),
                new SafetyRating(
                    HarmCategory::HARM_CATEGORY_UNSPECIFIED,
                    HarmProbability::HARM_PROBABILITY_UNSPECIFIED,
                    null,
                )
            ],
            null,
        );
    }

    public function testConstructorWithGenerationConfig()
    {
        $request = new GenerateContentRequest(
            ModelName::Default,
            [],
            [],
            new GenerationConfig(),
        );
        self::assertInstanceOf(GenerateContentRequest::class, $request);
    }

    public function testGetOperation()
    {
        $request = new GenerateContentRequest(ModelName::Default, []);
        self::assertEquals('models/text-bison-001:generateContent', $request->getOperation());
    }

    public function testGetHttpMethod()
    {
        $request = new GenerateContentRequest(ModelName::Default, []);
        self::assertEquals('POST', $request->getHttpMethod());
    }

    public function testGetHttpPayload()
    {
        $request = new GenerateContentRequest(
            ModelName::Default,
            [
                new Content([new TextPart('This is a text')], Role::User),
            ],
        );
        $expected = '{"model":"models\/text-bison-001","contents":[{"parts":[{"text":"This is a text"}],"role":"user"}]}';
        self::assertEquals($expected, $request->getHttpPayload());
    }

    public function testJsonSerialize()
    {
        $request = new GenerateContentRequest(
            ModelName::Default,
            [
                new Content([new TextPart('This is a text')], Role::User),
            ],
        );

        $expected = [
            'model' => 'models/text-bison-001',
            'contents' => [
                new Content([new TextPart('This is a text')], Role::User),
            ],
        ];
        self::assertEquals($expected, $request->jsonSerialize());
    }

    public function test__toString()
    {
        $request = new GenerateContentRequest(
            ModelName::Default,
            [
                new Content(
                    [new TextPart('This is a text')],
                    Role::User,
                )
            ],
        );

        $expected = '{"model":"models\/text-bison-001","contents":[{"parts":[{"text":"This is a text"}],"role":"user"}]}';
        self::assertEquals($expected, (string) $request);
    }
}
