<?php

declare(strict_types=1);

namespace GenerativeAI\Tests\Unit\Resources\Parts;

use GenerativeAI\Enums\MimeType;
use GenerativeAI\Resources\Parts\ImagePart;
use PHPUnit\Framework\TestCase;

class ImagePartTest extends TestCase
{
    public function testConstructor()
    {
        $part = new ImagePart(MimeType::IMAGE_JPEG, '');
        self::assertInstanceOf(ImagePart::class, $part);
    }

    public function testJsonSerialize()
    {
        $part = new ImagePart(MimeType::IMAGE_JPEG, 'this is an image');
        $expected = [
            'inlineData' => [
                'mimeType' => 'image/jpeg',
                'data' => 'this is an image',
            ],
        ];
        self::assertEquals($expected, $part->jsonSerialize());
    }

    public function test__toString()
    {
        $part = new ImagePart(MimeType::IMAGE_JPEG, 'this is an image');
        $expected = '{"inlineData":{"mimeType":"image\/jpeg","data":"this is an image"}}';
        self::assertEquals($expected, (string) $part);
    }
}
