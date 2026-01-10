<?php

namespace App\Support;

class FilenameHelper
{
    /**
     * Maximum allowed display filename length
     */
    public const MAX_DISPLAY_LENGTH = 255;

    /**
     * Sanitize filename for safe display (removes HTML, scripts, etc.)
     * Preserves Arabic and English characters.
     */
    public static function sanitizeForDisplay(string $filename): string
    {
        // Remove null bytes
        $filename = str_replace("\0", '', $filename);

        // Strip HTML tags
        $filename = strip_tags($filename);

        // Remove path traversal attempts - get only the filename
        $filename = basename($filename);

        // Trim whitespace
        $filename = trim($filename);

        // Normalize multiple spaces to single space
        $filename = preg_replace('/\s+/', ' ', $filename);

        // Truncate if too long (preserve extension)
        if (mb_strlen($filename) > self::MAX_DISPLAY_LENGTH) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $maxNameLength = self::MAX_DISPLAY_LENGTH - mb_strlen($extension) - 1;
            $filename = mb_substr($name, 0, $maxNameLength) . '.' . $extension;
        }

        return $filename;
    }

    /**
     * Get a fallback filename when original is empty
     */
    public static function getFallbackName(string $extension): string
    {
        $ext = ltrim($extension, '.');
        return 'file_' . date('Y-m-d_His') . ($ext ? '.' . $ext : '');
    }

    /**
     * Handle edge case of extension-only or empty filename
     */
    public static function ensureValidName(string $filename): string
    {
        $filename = trim($filename);

        // Handle completely empty filename
        if (empty($filename)) {
            return 'file';
        }

        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // Handle extension-only filename (e.g., ".pdf")
        if (empty($name) || trim($name) === '') {
            return 'file' . ($extension ? '.' . $extension : '');
        }

        return $filename;
    }
}
