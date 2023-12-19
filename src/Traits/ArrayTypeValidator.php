<?php

declare(strict_types=1);

namespace GenerativeAI\Traits;

use InvalidArgumentException;

trait ArrayTypeValidator
{
    /**
     * @param array<int, mixed> $items
     * @param class-string $classString
     * @return void
     */
    private function ensureArrayOfType(array $items, string $classString): void
    {
        foreach ($items as $item) {
            if (!$item instanceof $classString) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected type %s but found %s',
                        $classString,
                        $item::class,
                    ),
                );
            }
        }
    }

    /**
     * @param array<int, mixed> $items
     * @return void
     */
    private function ensureArrayOfString(array $items): void
    {
        foreach ($items as $item) {
            if (!is_string($item)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected string but found %s',
                        $item::class,
                    ),
                );
            }
        }
    }
}
