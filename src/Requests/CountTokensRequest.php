<?php

declare(strict_types=1);

namespace GenerativeAI\Requests;

use GenerativeAI\Enums\Model;
use GenerativeAI\Traits\ArrayTypeValidator;
use GenerativeAI\Resources\Content;
use JsonSerializable;

use function json_encode;

class CountTokensRequest implements JsonSerializable
{
    use ArrayTypeValidator;

    /**
     * @param Model $model
     * @param Content[] $contents
     */
    public function __construct(
        public readonly Model $model,
        public readonly array $contents,
    ) {
        $this->ensureArrayOfType($this->contents, Content::class);
    }

    /**
     * @return array{
     *     model: string,
     *     contents: Content[],
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'model' => $this->model->value,
            'contents' => $this->contents,
        ];
    }

    public function __toString(): string
    {
        return json_encode($this) ?: '';
    }
}
