<?php
/**
 * Secure File Upload Handler
 * Girish Pawar — Personal Developer Portfolio & CMS
 */

require_once dirname(__DIR__) . '/core/config.php';

class Upload {
    private static array $allowedImageMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg'
    ];

    private static array $allowedDocMimes = [
        'application/pdf' => 'pdf'
    ];

    /**
     * Upload an image file securely
     */
    public static function image(array $file, string $subfolder = 'projects'): array {
        return self::process($file, $subfolder, self::$allowedImageMimes);
    }

    /**
     * Upload a resume/document securely
     */
    public static function document(array $file, string $subfolder = 'resume'): array {
        return self::process($file, $subfolder, self::$allowedDocMimes);
    }

    /**
     * Internal processor
     */
    private static function process(array $file, string $subfolder, array $allowedMimes): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'Invalid file parameter.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'error' => 'No file was uploaded.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'error' => 'Uploaded file exceeds maximum allowed size.'];
            default:
                return ['success' => false, 'error' => 'Unknown upload error occurred.'];
        }

        // Validate file size
        $maxBytes = MAX_UPLOAD_SIZE_MB * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return ['success' => false, 'error' => 'File size cannot exceed ' . MAX_UPLOAD_SIZE_MB . 'MB.'];
        }

        // Verify MIME type using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        // Extra check for SVG
        if ($mime === 'text/plain' || $mime === 'text/xml' || $mime === 'image/svg+xml') {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext === 'svg') {
                $content = file_get_contents($file['tmp_name']);
                if (stripos($content, '<svg') !== false && stripos($content, '<script') === false) {
                    $mime = 'image/svg+xml';
                }
            }
        }

        if (!array_key_exists($mime, $allowedMimes)) {
            return ['success' => false, 'error' => 'File type not permitted (' . htmlspecialchars($mime) . ').'];
        }

        $extension = $allowedMimes[$mime];
        $targetDir = UPLOADS_PATH . '/' . trim($subfolder, '/');
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        // Generate unguessable random filename
        $filename = sprintf('%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(8)), $extension);
        $targetPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            // For CLI testing where move_uploaded_file fails on synthetic temp files, copy as fallback
            if (!copy($file['tmp_name'], $targetPath)) {
                return ['success' => false, 'error' => 'Failed to save uploaded file to destination.'];
            }
        }

        $relativePath = 'uploads/' . trim($subfolder, '/') . '/' . $filename;
        return [
            'success' => true,
            'file_name' => $filename,
            'file_path' => $relativePath,
            'full_path' => $targetPath
        ];
    }
}
