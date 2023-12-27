<?php

declare(strict_types=1);

namespace GeminiAPI\Tests\Unit\Requests;

use BadMethodCallException;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Enums\TaskType;
use GeminiAPI\Requests\EmbedContentRequest;
use GeminiAPI\Resources\Content;
use PHPUnit\Framework\TestCase;

class EmbedContentRequestTest extends TestCase
{
    public function testConstructor()
    {
        $request = new EmbedContentRequest(
            ModelName::Embedding,
            Content::text('this is a test'),
        );
        self::assertInstanceOf(EmbedContentRequest::class, $request);
    }

    public function testConstructorWithTaskType()
    {
        $request = new EmbedContentRequest(
            ModelName::Embedding,
            Content::text('this is a test'),
            TaskType::RETRIEVAL_DOCUMENT,
        );
        self::assertInstanceOf(EmbedContentRequest::class, $request);
    }

    public function testConstructorWithTitle()
    {
        $request = new EmbedContentRequest(
            ModelName::Embedding,
            Content::text('this is a test'),
            TaskType::RETRIEVAL_DOCUMENT,
            'this is a title',
        );
        self::assertInstanceOf(EmbedContentRequest::class, $request);
    }

    public function testConstructorWithTaskTypeAndNonEmbeddingModel()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('TaskType can only be set when ModelName is Embedding');

        new EmbedContentRequest(
            ModelName::GeminiPro,
            Content::text('this is a test'),
            TaskType::RETRIEVAL_DOCUMENT,
        );
    }

    public function testConstructorWithTitleAndWrongTaskType()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Title is only applicable when TaskType is RETRIEVAL_DOCUMENT');

        new EmbedContentRequest(
            ModelName::Embedding,
            Content::text('this is a test'),
            TaskType::RETRIEVAL_QUERY,
            'this is a title',
        );
    }

    public function testGetHttpPayload()
    {
        $request = new EmbedContentRequest(
            ModelName::Embedding,
            Content::text('this is a test'),
        );
        self::assertEquals('{"content":{"parts":[{"text":"this is a test"}],"role":"user"}}', $request->getHttpPayload());
    }

    public function testGetHttpMethod()
    {
        $request = new EmbedContentRequest(
            ModelName::Embedding,
            Content::text('this is a test'),
        );
        self::assertEquals('POST', $request->getHttpMethod());
    }

    public function testGetOperation()
    {
        $request = new EmbedContentRequest(
            ModelName::Embedding,
            Content::text('this is a test'),
        );
        self::assertEquals('models/embedding-001:embedContent', $request->getOperation());
    }

    public function testJsonSerialize()
    {
        $request = new EmbedContentRequest(
            ModelName::Embedding,
            $content = Content::text('this is a test'),
            TaskType::RETRIEVAL_DOCUMENT,
            'this is a title',
        );
        $expected = [
            'content' => $content,
            'taskType' => TaskType::RETRIEVAL_DOCUMENT,
            'title' => 'this is a title',
        ];
        self::assertEquals($expected, $request->jsonSerialize());
    }

    public function test__toString()
    {
        $request = new EmbedContentRequest(
            ModelName::Embedding,
            Content::text('this is a test'),
        );
        self::assertEquals('{"content":{"parts":[{"text":"this is a test"}],"role":"user"}}', (string) $request);
    }
}
