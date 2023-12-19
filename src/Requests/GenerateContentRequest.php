<?php

declare(strict_types=1);

namespace GenerativeAI\Requests;

use GenerativeAI\Enums\Model;
use GenerativeAI\GenerationConfig;
use GenerativeAI\SafetySetting;
use GenerativeAI\Traits\ArrayTypeValidator;
use GenerativeAI\Resources\Content;
use JsonSerializable;

use function json_encode;

class GenerateContentRequest implements JsonSerializable
{
    use ArrayTypeValidator;

    /**
     * @param Model $model
     * @param Content[] $contents
     * @param SafetySetting[] $safetySettings
     * @param GenerationConfig|null $generationConfig
     */
    public function __construct(
        public readonly Model $model,
        public readonly array $contents,
        public readonly array $safetySettings = [],
        public readonly ?GenerationConfig $generationConfig = null,
    ) {
        $this->ensureArrayOfType($this->contents, Content::class);
        $this->ensureArrayOfType($this->safetySettings, SafetySetting::class);
    }

    /**
     * @return array{
     *     model: string,
     *     contents: Content[],
     *     safetySettings?: SafetySetting[],
     *     generationConfig?: GenerationConfig,
     * }
     */
    public function jsonSerialize(): array
    {
        $arr = [
            'model' => $this->model->value,
            'contents' => $this->contents,
        ];

        if (!empty($this->safetySettings)) {
            $arr['safetySettings'] = $this->safetySettings;
        }

        if ($this->generationConfig) {
            $arr['generationConfig'] = $this->generationConfig;
        }

        return $arr;
    }

    public function __toString(): string
    {
        return json_encode($this) ?: '';
    }
}
