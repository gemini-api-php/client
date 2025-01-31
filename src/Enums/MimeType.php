<?php

declare(strict_types=1);

namespace GeminiAPI\Enums;

enum MimeType: string
{
    case FILE_CSV = 'text/csv';
    case FILE_PDF = 'application/pdf';
    case IMAGE_PNG = 'image/png';
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_HEIC = 'image/heic';
    case IMAGE_HEIF = 'image/heif';
    case IMAGE_WEBP = 'image/webp';
}
