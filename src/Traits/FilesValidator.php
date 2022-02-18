<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\Validation\Traits;

/**
 * Trait FilesValidator.
 *
 * @package validation
 */
trait FilesValidator
{
    /**
     * Validates file is uploaded.
     *
     * @return static
     */
    public function uploaded() : static
    {
        $this->rules[] = 'uploaded';
        return $this;
    }

    /**
     * Validates file size.
     *
     * @param int $kilobytes
     *
     * @return static
     */
    public function maxSize(int $kilobytes) : static
    {
        $this->rules[] = 'maxSize:' . $kilobytes;
        return $this;
    }

    /**
     * Validates file accepted MIME types.
     *
     * @param string ...$allowedTypes
     *
     * @return static
     */
    public function mimes(string ...$allowedTypes) : static
    {
        $this->rules[] = 'mimes:' . $this->implode($allowedTypes);
        return $this;
    }

    /**
     * Validates file accepted extensions.
     *
     * NOTE: For greater security use the {@see FilesValidator::mimes()} method
     * to filter the file type.
     *
     * @param string ...$allowedExtensions
     *
     * @return static
     */
    public function ext(string ...$allowedExtensions) : static
    {
        $this->rules[] = 'ext:' . $this->implode($allowedExtensions);
        return $this;
    }

    /**
     * Validates file is an image.
     *
     * @return static
     */
    public function image() : static
    {
        $this->rules[] = 'image';
        return $this;
    }

    /**
     * Validates image max dimensions.
     *
     * @param int $width
     * @param int $height
     *
     * @return static
     */
    public function maxDim(int $width, int $height) : static
    {
        $this->rules[] = 'maxDim:' . $width . ',' . $height;
        return $this;
    }

    /**
     * Validates image min dimensions.
     *
     * @param int $width
     * @param int $height
     *
     * @return static
     */
    public function minDim(int $width, int $height) : static
    {
        $this->rules[] = 'minDim:' . $width . ',' . $height;
        return $this;
    }

    /**
     * Validates image dimensions.
     *
     * @param int $width
     * @param int $height
     *
     * @return static
     */
    public function dim(int $width, int $height) : static
    {
        $this->rules[] = 'dim:' . $width . ',' . $height;
        return $this;
    }
}
