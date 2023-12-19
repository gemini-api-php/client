<?php

declare(strict_types=1);

namespace GenerativeAI\Enums;

enum MimeType: string
{
    case IMAGE_PNG = 'image/png';
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_HEIC = 'image/heic';
    case IMAGE_HEIF = 'image/heif';
    case IMAGE_WEBP = 'image/webp';
}
