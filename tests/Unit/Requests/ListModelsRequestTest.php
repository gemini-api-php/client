<?php

declare(strict_types=1);

namespace GenerativeAI\Tests\Unit\Requests;

use GenerativeAI\Requests\ListModelsRequest;
use PHPUnit\Framework\TestCase;

class ListModelsRequestTest extends TestCase
{
    public function testGetOperation()
    {
        $request = new ListModelsRequest();
        self::assertEquals('models', $request->getOperation());
    }

    public function testGetHttpMethod()
    {
        $request = new ListModelsRequest();
        self::assertEquals('GET', $request->getHttpMethod());
    }

    public function testGetHttpPayload()
    {
        $request = new ListModelsRequest();
        self::assertEmpty($request->getHttpPayload());
    }
}
