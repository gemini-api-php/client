<?php

declare(strict_types=1);

namespace GenerativeAI\Requests;

interface RequestInterface
{
    public function getOperation(): string;
    public function getHttpMethod(): string;
    public function getHttpPayload(): string;
}
