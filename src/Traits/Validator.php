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

use JetBrains\PhpStorm\Language;

/**
 * Trait Validator.
 *
 * @package validation
 */
trait Validator
{
    /**
     * Validates alphabetic characters.
     *
     * @return static
     */
    public function alpha() : static
    {
        $this->rules[] = 'alpha';
        return $this;
    }

    /**
     * Validates a number.
     *
     * @return static
     */
    public function number() : static
    {
        $this->rules[] = 'number';
        return $this;
    }

    /**
     * Validates a number or alphabetic characters.
     *
     * @return static
     */
    public function alphaNumber() : static
    {
        $this->rules[] = 'alphaNumber';
        return $this;
    }

    /**
     * Validates a UUID.
     *
     * @return static
     */
    public function uuid() : static
    {
        $this->rules[] = 'uuid';
        return $this;
    }

    /**
     * Validates a timezone.
     *
     * @return static
     */
    public function timezone() : static
    {
        $this->rules[] = 'timezone';
        return $this;
    }

    /**
     * Validates a base64 string.
     *
     * @return static
     */
    public function base64() : static
    {
        $this->rules[] = 'base64';
        return $this;
    }

    /**
     * Validates a md5 hash.
     *
     * @return static
     */
    public function md5() : static
    {
        $this->rules[] = 'md5';
        return $this;
    }

    /**
     * Validates a hexadecimal string.
     *
     * @return static
     */
    public function hex() : static
    {
        $this->rules[] = 'hex';
        return $this;
    }

    /**
     * Validates a hexadecimal color.
     *
     * @return static
     */
    public function hexColor() : static
    {
        $this->rules[] = 'hexColor';
        return $this;
    }

    /**
     * Validates a JSON string.
     *
     * @return static
     */
    public function json() : static
    {
        $this->rules[] = 'json';
        return $this;
    }

    /**
     * Validates a Regex pattern.
     *
     * @param string $pattern
     *
     * @return static
     */
    public function regex(#[Language('RegExp')] string $pattern) : static
    {
        $this->rules[] = 'regex:' . $this->esc($pattern);
        return $this;
    }

    /**
     * Validates a Regex no matching pattern.
     *
     * @param string $pattern
     *
     * @return static
     */
    public function notRegex(#[Language('RegExp')] string $pattern) : static
    {
        $this->rules[] = 'notRegex:' . $this->esc($pattern);
        return $this;
    }

    /**
     * Validate field has value equals other field.
     *
     * @param string $equalsField
     *
     * @return static
     */
    public function equals(string $equalsField) : static
    {
        $this->rules[] = 'equals:' . $this->esc($equalsField);
        return $this;
    }

    /**
     * Validate field has not value equals other field.
     *
     * @param string $diffField
     *
     * @return static
     */
    public function notEquals(string $diffField) : static
    {
        $this->rules[] = 'notEquals:' . $this->esc($diffField);
        return $this;
    }

    /**
     * Validate field between min and max values.
     *
     * @param int|string $min
     * @param int|string $max
     *
     * @return static
     */
    public function between(int | string $min, int | string $max) : static
    {
        $this->rules[] = 'between:' . $this->esc((string) $min) . ',' . $this->esc((string) $max);
        return $this;
    }

    /**
     * Validate field not between min and max values.
     *
     * @param int|string $min
     * @param int|string $max
     *
     * @return static
     */
    public function notBetween(int | string $min, int | string $max) : static
    {
        $this->rules[] = 'notBetween:' . $this->esc((string) $min) . ',' . $this->esc((string) $max);
        return $this;
    }

    /**
     * Validate field is in list.
     *
     * @param string $in
     * @param string ...$others
     *
     * @return static
     */
    public function in(string $in, string ...$others) : static
    {
        $this->rules[] = 'in:' . $this->implode([$in, ...$others]);
        return $this;
    }

    /**
     * Validate field is not in list.
     *
     * @param string $notIn
     * @param string ...$others
     *
     * @return static
     */
    public function notIn(string $notIn, string ...$others) : static
    {
        $this->rules[] = 'notIn:' . $this->implode([$notIn, ...$others]);
        return $this;
    }

    /**
     * Validates an IP.
     *
     * @param int $version 4, 6 or 0 to both
     *
     * @return static
     */
    public function ip(int $version = 0) : static
    {
        $this->rules[] = 'ip:' . $version;
        return $this;
    }

    /**
     * Validates an URL.
     *
     * @return static
     */
    public function url() : static
    {
        $this->rules[] = 'url';
        return $this;
    }

    /**
     * Validates a datetime format.
     *
     * @param string $format
     *
     * @return static
     */
    public function datetime(string $format = 'Y-m-d H:i:s') : static
    {
        $this->rules[] = 'datetime:' . $this->esc($format);
        return $this;
    }

    /**
     * Validates a email.
     *
     * @return static
     */
    public function email() : static
    {
        $this->rules[] = 'email';
        return $this;
    }

    /**
     * Validates is greater than.
     *
     * @param int|string $greaterThan
     *
     * @return static
     */
    public function greater(int | string $greaterThan) : static
    {
        $this->rules[] = 'greater:' . $this->esc((string) $greaterThan);
        return $this;
    }

    /**
     * Validates is greater than or equal to.
     *
     * @param int|string $greaterThanOrEqualTo
     *
     * @return static
     */
    public function greaterOrEqual(int | string $greaterThanOrEqualTo) : static
    {
        $this->rules[] = 'greaterOrEqual:' . $this->esc((string) $greaterThanOrEqualTo);
        return $this;
    }

    /**
     * Validates is less than.
     *
     * @param int|string $lessThan
     *
     * @return static
     */
    public function less(int | string $lessThan) : static
    {
        $this->rules[] = 'less:' . $this->esc((string) $lessThan);
        return $this;
    }

    /**
     * Validates is less than or equal to.
     *
     * @param int|string $lessThanOrEqualTo
     *
     * @return static
     */
    public function lessOrEqual(int | string $lessThanOrEqualTo) : static
    {
        $this->rules[] = 'lessOrEqual:' . $this->esc((string) $lessThanOrEqualTo);
        return $this;
    }

    /**
     * Validates a latin text.
     *
     * @return static
     */
    public function latin() : static
    {
        $this->rules[] = 'latin';
        return $this;
    }

    /**
     * Validates max length.
     *
     * @param int $maxLength
     *
     * @return static
     */
    public function maxLength(int $maxLength) : static
    {
        $this->rules[] = 'maxLength:' . $maxLength;
        return $this;
    }

    /**
     * Validates min length.
     *
     * @param int $minLength
     *
     * @return static
     */
    public function minLength(int $minLength) : static
    {
        $this->rules[] = 'minLength:' . $minLength;
        return $this;
    }

    /**
     * Validates exact length.
     *
     * @param int $length
     *
     * @return static
     */
    public function length(int $length) : static
    {
        $this->rules[] = 'length:' . $length;
        return $this;
    }

    /**
     * Validates required value.
     *
     * @return static
     */
    public function required() : static
    {
        $this->rules[] = 'required';
        return $this;
    }

    /**
     * Validates field is set.
     *
     * @return static
     */
    public function isset() : static
    {
        $this->rules[] = 'isset';
        return $this;
    }

    /**
     * Validates array.
     *
     * @since 2.2
     *
     * @return static
     */
    public function array() : static
    {
        $this->rules[] = 'array';
        return $this;
    }

    /**
     * Validates boolean.
     *
     * @since 2.2
     *
     * @return static
     */
    public function bool() : static
    {
        $this->rules[] = 'bool';
        return $this;
    }

    /**
     * Validates float.
     *
     * @since 2.2
     *
     * @return static
     */
    public function float() : static
    {
        $this->rules[] = 'float';
        return $this;
    }

    /**
     * Validates integer.
     *
     * @since 2.2
     *
     * @return static
     */
    public function int() : static
    {
        $this->rules[] = 'int';
        return $this;
    }

    /**
     * Validates object.
     *
     * @since 2.2
     *
     * @return static
     */
    public function object() : static
    {
        $this->rules[] = 'object';
        return $this;
    }

    /**
     * Validates string.
     *
     * @since 2.2
     *
     * @return static
     */
    public function string() : static
    {
        $this->rules[] = 'string';
        return $this;
    }

    /**
     * Validates special characters.
     *
     * @see https://owasp.org/www-community/password-special-characters
     *
     * @param int $quantity
     * @param string $characters
     *
     * @return static
     */
    public function specialChar(
        int $quantity = 1,
        string $characters = '!"#$%&\'()*+,-./:;=<>?@[\]^_`{|}~'
    ) : static {
        $this->rules[] = 'specialChar:' . $quantity . ',' . $this->esc($characters);
        return $this;
    }
}
