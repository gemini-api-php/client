<?php

declare(strict_types=1);

namespace GenerativeAI\Enums;

enum BlockReason: string
{
    case BLOCK_REASON_UNSPECIFIED = 'BLOCK_REASON_UNSPECIFIED';
    case SAFETY = 'SAFETY';
    case OTHER = 'OTHER';
}
