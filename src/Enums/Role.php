<?php

declare(strict_types=1);

namespace GenerativeAI\Enums;

enum Role: string
{
    case User = 'user';
    case Model = 'model';
}
