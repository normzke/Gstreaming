<?php
/**
 * File Uploader
 * 
 * A utility class for handling file uploads with validation and security checks
 */

namespace Bingetv\Lib;

class FileUploader {
    /**
     * @var array The uploaded file data from $_FILES
     */
    private $file;
    
    /**
     * @var array Allowed MIME types
     */
    private $allowedTypes = [];
    
    /**
     * @var int Maximum file size in bytes
     */
    private $maxSize = 0;
    
    /**
     * @var string Upload directory
     */
    private $uploadDir = '';
    
    /**
     * @var string New filename (without extension)
     */
    private $newFilename = '';
    
    /**
     * @var bool Whether to overwrite existing files
     */
    private $overwrite = false;
    
    /**
     * @var array Validation errors
     */
    private $errors = [];
    
    /**
     * @var array Valid MIME types for common file types
     */
    private static $mimeTypes = [
        // Images
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'bmp' => 'image/bmp',
        'svg' => 'image/svg+xml',
        'tiff' => 'image/tiff',
        
        // Documents
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'rtf' => 'application/rtf',
        
        // Archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        '7z' => 'application/x-7z-compressed',
        'tar' => 'application/x-tar',
        'gz' => 'application/gzip',
        'bz2' => 'application/x-bzip2',
        
        // Audio
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/mp4',
        'aac' => 'audio/aac',
        
        // Video
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo',
        'wmv' => 'video/x-ms-wmv',
        'flv' => 'video/x-flv',
        'mkv' => 'video/x-matroska',
        '3gp' => 'video/3gpp',
    ];
    
    /**
     * Constructor
     * 
     * @param array $file The uploaded file data from $_FILES
     * @param array $options Configuration options
     */
    public function __construct($file, $options = []) {
        $this->file = $file;
        
        // Set options
        $this->allowedTypes = $options['allowed_types'] ?? [];
        $this->maxSize = $options['max_size'] ?? 0;
        $this->uploadDir = rtrim($options['upload_dir'] ?? 'uploads', '/') . '/';
        $this->overwrite = $options['overwrite'] ?? false;
        $this->newFilename = $options['new_filename'] ?? '';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Validate the uploaded file
     * 
     * @return bool True if the file is valid, false otherwise
     */
    public function validate() {
        $this->errors = [];
        
        // Check for upload errors
        if (!isset($this->file['error']) || is_array($this->file['error'])) {
            $this->errors[] = 'Invalid parameters.';
            return false;
        }
        
        // Check for specific upload errors
        switch ($this->file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = 'The uploaded file exceeds the maximum file size.';
                return false;
            case UPLOAD_ERR_PARTIAL:
                $this->errors[] = 'The uploaded file was only partially uploaded.';
                return false;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No file was uploaded.';
                return false;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->errors[] = 'Missing a temporary folder.';
                return false;
            case UPLOAD_ERR_CANT_WRITE:
                $this->errors[] = 'Failed to write file to disk.';
                return false;
            case UPLOAD_ERR_EXTENSION:
                $this->errors[] = 'A PHP extension stopped the file upload.';
                return false;
            default:
                $this->errors[] = 'Unknown upload error.';
                return false;
        }
        
        // Check file size
        if ($this->maxSize > 0 && $this->file['size'] > $this->maxSize) {
            $this->errors[] = sprintf(
                'The uploaded file is too large. Maximum size is %s.',
                $this->formatBytes($this->maxSize)
            );
            return false;
        }
        
        // Check if file was uploaded via HTTP POST
        if (!is_uploaded_file($this->file['tmp_name'])) {
            $this->errors[] = 'The file was not uploaded via HTTP POST.';
            return false;
        }
        
        // Get file info
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($this->file['tmp_name']);
        
        // Check if the file type is allowed
        if (!empty($this->allowedTypes) && !in_array($mimeType, $this->allowedTypes)) {
            $this->errors[] = 'The file type is not allowed.';
            return false;
        }
        
        // Additional security check for image files
        if (strpos($mimeType, 'image/') === 0) {
            $imageInfo = @getimagesize($this->file['tmp_name']);
            if ($imageInfo === false) {
                $this->errors[] = 'The uploaded file is not a valid image.';
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Save the uploaded file
     * 
     * @return string|false The path to the saved file, or false on failure
     */
    public function save() {
        if (!$this->validate()) {
            return false;
        }
        
        // Get file extension from original filename
        $extension = pathinfo($this->file['name'], PATHINFO_EXTENSION);
        
        // Generate a unique filename if not provided
        if (empty($this->newFilename)) {
            $filename = uniqid() . '.' . strtolower($extension);
        } else {
            $filename = $this->newFilename . '.' . strtolower($extension);
        }
        
        $destination = $this->uploadDir . $filename;
        
        // Check if the file already exists
        if (!$this->overwrite && file_exists($destination)) {
            $this->errors[] = 'A file with that name already exists.';
            return false;
        }
        
        // Move the uploaded file to its destination
        if (move_uploaded_file($this->file['tmp_name'], $destination)) {
            // Set proper permissions
            chmod($destination, 0644);
            return $destination;
        }
        
        $this->errors[] = 'Failed to move the uploaded file.';
        return false;
    }
    
    /**
     * Get the file extension from a MIME type
     * 
     * @param string $mimeType The MIME type
     * @return string|false The file extension, or false if not found
     */
    public static function getExtensionFromMimeType($mimeType) {
        return array_search($mimeType, self::$mimeTypes);
    }
    
    /**
     * Get the MIME type from a file extension
     * 
     * @param string $extension The file extension (with or without dot)
     * @return string|false The MIME type, or false if not found
     */
    public static function getMimeTypeFromExtension($extension) {
        $extension = ltrim($extension, '.');
        return self::$mimeTypes[strtolower($extension)] ?? false;
    }
    
    /**
     * Format bytes to a human-readable format
     * 
     * @param int $bytes The number of bytes
     * @param int $precision The number of decimal places
     * @return string The formatted size
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Get the validation errors
     * 
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get the first validation error
     * 
     * @return string The first error message, or an empty string if no errors
     */
    public function getFirstError() {
        return $this->errors[0] ?? '';
    }
    
    /**
     * Check if there are any validation errors
     * 
     * @return bool True if there are errors, false otherwise
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Sanitize a filename
     * 
     * @param string $filename The filename to sanitize
     * @return string The sanitized filename
     */
    public static function sanitizeFilename($filename) {
        // Remove any path information
        $filename = basename($filename);
        
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);
        
        // Remove any characters that are not alphanumeric, underscores, dots, or hyphens
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
        
        // Remove multiple dots (except for the file extension)
        $filename = preg_replace('/\.(?=.*\.)/', '', $filename);
        
        // Convert to lowercase
        $filename = strtolower($filename);
        
        return $filename;
    }
}
