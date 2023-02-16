<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\Validation;

use Framework\Helpers\ArraySimple;

/**
 * Class FilesValidator.
 *
 * @package validation
 */
class FilesValidator
{
    /**
     * @var array<string,mixed>
     */
    protected static array $files;

    /**
     * Get $_FILES in a re-organized way.
     *
     * @return array<string,mixed>
     */
    protected static function getOrganizedFiles() : array
    {
        return static::$files ?? (static::$files = ArraySimple::files());
    }

    /**
     * @param string $field
     *
     * @return array<string,mixed>|null
     */
    protected static function getFile(string $field) : array | null
    {
        $files = static::getOrganizedFiles();
        return ArraySimple::value($field, $files);
    }

    /**
     * Validates file is uploaded.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function uploaded(string $field, array $data = []) : bool
    {
        $file = static::getFile($field);
        if ($file === null) {
            return false;
        }
        return $file['error'] === \UPLOAD_ERR_OK && \is_uploaded_file($file['tmp_name']);
    }

    /**
     * Validates file size.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int $kilobytes
     *
     * @return bool
     */
    public static function maxSize(string $field, array $data, int $kilobytes) : bool
    {
        $uploaded = static::uploaded($field);
        if ( ! $uploaded) {
            return false;
        }
        $file = static::getFile($field);
        return $file['size'] <= ($kilobytes * 1024);
    }

    /**
     * Validates file accepted MIME types.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param string ...$allowedTypes
     *
     * @return bool
     */
    public static function mimes(string $field, array $data, string ...$allowedTypes) : bool
    {
        $uploaded = static::uploaded($field);
        if ( ! $uploaded) {
            return false;
        }
        $file = static::getFile($field);
        $mimeType = \mime_content_type($file['tmp_name']);
        return \in_array($mimeType, $allowedTypes, true);
    }

    /**
     * Validates file accepted extensions.
     *
     * NOTE: For greater security use the {@see FilesValidator::mimes()} method
     * to filter the file type.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param string ...$allowedExtensions
     *
     * @return bool
     */
    public static function ext(string $field, array $data, string ...$allowedExtensions) : bool
    {
        $uploaded = static::uploaded($field);
        if ( ! $uploaded) {
            return false;
        }
        $file = static::getFile($field);
        foreach ($allowedExtensions as $extension) {
            if (\str_ends_with($file['name'], '.' . $extension)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validates file is an image.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function image(string $field, array $data = []) : bool
    {
        $uploaded = static::uploaded($field);
        if ( ! $uploaded) {
            return false;
        }
        $file = static::getFile($field);
        $mime = \mime_content_type($file['tmp_name']) ?: 'application/octet-stream';
        return \str_starts_with($mime, 'image/');
    }

    /**
     * Validates image max dimensions.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int $width
     * @param int $height
     *
     * @return bool
     */
    public static function maxDim(string $field, array $data, int $width, int $height) : bool
    {
        $isImage = static::image($field);
        if ( ! $isImage) {
            return false;
        }
        $file = static::getFile($field);
        $sizes = \getimagesize($file['tmp_name']);
        return ! ($sizes === false || $sizes[0] > $width || $sizes[1] > $height);
    }

    /**
     * Validates image min dimensions.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int $width
     * @param int $height
     *
     * @return bool
     */
    public static function minDim(string $field, array $data, int $width, int $height) : bool
    {
        $isImage = static::image($field);
        if ( ! $isImage) {
            return false;
        }
        $file = static::getFile($field);
        $sizes = \getimagesize($file['tmp_name']);
        return ! ($sizes === false || $sizes[0] < $width || $sizes[1] < $height);
    }

    /**
     * Validates image dimensions.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int $width
     * @param int $height
     *
     * @return bool
     */
    public static function dim(string $field, array $data, int $width, int $height) : bool
    {
        $isImage = static::image($field);
        if ( ! $isImage) {
            return false;
        }
        $file = static::getFile($field);
        $sizes = \getimagesize($file['tmp_name']);
        return ! ($sizes === false || $sizes[0] !== $width || $sizes[1] !== $height);
    }
}
