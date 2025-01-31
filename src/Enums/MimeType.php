<?php

declare(strict_types=1);

namespace GeminiAPI\Enums;

enum MimeType: string
{
    case APPLICATION_PDF = 'application/pdf';
    case APPLICATION_JAVASCRIPT = 'application/x-javascript';
    case APPLICATION_PYTHON = 'application/x-python';
    
    case TEXT_PLAIN = 'text/plain';
    case TEXT_HTML = 'text/html';
    case TEXT_CSS = 'text/css';
    case TEXT_MARKDOWN = 'text/md';
    case TEXT_CSV = 'text/csv';
    case TEXT_XML = 'text/xml';
    case TEXT_RTF = 'text/rtf';

    case IMAGE_PNG = 'image/png';
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_HEIC = 'image/heic';
    case IMAGE_HEIF = 'image/heif';
    case IMAGE_WEBP = 'image/webp';

    // @deprecated
    case FILE_PDF = 'application/pdf';
}
