<?php

declare(strict_types=1);

namespace GeminiAPI\Tests\Unit\Requests;

use GeminiAPI\Enums\ModelName;
use GeminiAPI\Enums\Role;
use GeminiAPI\Requests\CountTokensRequest;
use GeminiAPI\Resources\Content;
use GeminiAPI\Resources\Parts\TextPart;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CountTokensRequestTest extends TestCase
{
    public function testConstructorWithNoContents()
    {
        $request = new CountTokensRequest(ModelName::Default, []);
        self::assertInstanceOf(CountTokensRequest::class, $request);
    }

    public function testConstructorWithContents()
    {
        $request = new CountTokensRequest(
            ModelName::Default,
            [
                new Content([], Role::User),
                new Content([], Role::Model),
            ],
        );

        self::assertInstanceOf(CountTokensRequest::class, $request);
    }

    public function testConstructorWithInvalidContents()
    {
        $this->expectException(InvalidArgumentException::class);

        new CountTokensRequest(
            ModelName::Default,
            [
                new Content([], Role::User),
                new TextPart('This is a text'),
            ],
        );
    }

    public function testGetOperation()
    {
        $request = new CountTokensRequest(ModelName::Default, []);
        self::assertEquals('models/text-bison-001:countTokens', $request->getOperation());
    }

    public function testGetHttpMethod()
    {
        $request = new CountTokensRequest(ModelName::Default, []);
        self::assertEquals('POST', $request->getHttpMethod());
    }

    public function testGetHttpPayload()
    {
        $request = new CountTokensRequest(
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
        $request = new CountTokensRequest(
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
        $request = new CountTokensRequest(
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
