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
use InvalidArgumentException;
use JetBrains\PhpStorm\Language;

/**
 * Class Validator.
 *
 * @package validation
 */
class Validator
{
    /**
     * Get field value from data.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return string|null
     */
    protected static function getData(string $field, array $data) : ?string
    {
        $data = ArraySimple::value($field, $data);
        return \is_scalar($data) ? (string) $data : null;
    }

    /**
     * Validates alphabetic characters.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function alpha(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        return $data !== null && \ctype_alpha($data);
    }

    /**
     * Validates a number.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function number(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        return \is_numeric($data);
    }

    /**
     * Validates a number or alphabetic characters.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function alphaNumber(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        return $data !== null && \ctype_alnum($data);
    }

    /**
     * Validates a UUID.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function uuid(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        if ($data === null) {
            return false;
        }
        if ($data === '00000000-0000-0000-0000-000000000000') {
            return false;
        }
        return \preg_match(
            '/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/',
            $data
        ) === 1;
    }

    /**
     * Validates a timezone.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function timezone(string $field, array $data) : bool
    {
        return static::in($field, $data, ...\DateTimeZone::listIdentifiers());
    }

    /**
     * Validates a base64 string.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function base64(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        if ($data === null) {
            return false;
        }
        $decoded = \base64_decode($data);
        return $decoded && \base64_encode($decoded) === $data;
    }

    /**
     * Validates a md5 hash.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function md5(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        if ($data === null) {
            return false;
        }
        return (bool) \preg_match('/^[a-f0-9]{32}$/', $data);
    }

    /**
     * Validates a hexadecimal string.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function hex(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        return $data !== null && \ctype_xdigit($data);
    }

    /**
     * Validates a hexadecimal color.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function hexColor(string $field, array $data) : bool
    {
        return static::regex($field, $data, '/^#([0-9A-Fa-f]{3}){1,2}$/');
    }

    /**
     * Validates a JSON string.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function json(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        if ($data === null) {
            return false;
        }
        \json_decode($data);
        return \json_last_error() === \JSON_ERROR_NONE;
    }

    /**
     * Validates a Regex pattern.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param string $pattern
     *
     * @return bool
     */
    public static function regex(
        string $field,
        array $data,
        #[Language('RegExp')] string $pattern
    ) : bool {
        $data = static::getData($field, $data);
        return $data !== null && \preg_match($pattern, $data) === 1;
    }

    /**
     * Validates a Regex no matching pattern.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param string $pattern
     *
     * @return bool
     */
    public static function notRegex(
        string $field,
        array $data,
        #[Language('RegExp')] string $pattern
    ) : bool {
        return ! static::regex($field, $data, $pattern);
    }

    /**
     * Validate field has value equals other field.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param string $equalsField
     *
     * @return bool
     */
    public static function equals(string $field, array $data, string $equalsField) : bool
    {
        $field = ArraySimple::value($field, $data);
        if ( ! \is_scalar($field)) {
            return false;
        }
        $equalsField = ArraySimple::value($equalsField, $data);
        if ( ! \is_scalar($equalsField)) {
            return false;
        }
        return (string) $field === (string) $equalsField;
    }

    /**
     * Validate field has not value equals other field.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param string $diffField
     *
     * @return bool
     */
    public static function notEquals(string $field, array $data, string $diffField) : bool
    {
        return ! static::equals($field, $data, $diffField);
    }

    /**
     * Validate field between min and max values.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $min
     * @param int|string $max
     *
     * @return bool
     */
    public static function between(
        string $field,
        array $data,
        int | string $min,
        int | string $max
    ) : bool {
        $data = static::getData($field, $data);
        return $data !== null && $data >= $min && $data <= $max;
    }

    /**
     * Validate field not between min and max values.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $min
     * @param int|string $max
     *
     * @return bool
     */
    public static function notBetween(
        string $field,
        array $data,
        int | string $min,
        int | string $max
    ) : bool {
        return ! static::between($field, $data, $min, $max);
    }

    /**
     * Validate field is in list.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param string $in
     * @param string ...$others
     *
     * @return bool
     */
    public static function in(string $field, array $data, string $in, string ...$others) : bool
    {
        $data = static::getData($field, $data);
        return $data !== null && \in_array($data, [$in, ...$others], true);
    }

    /**
     * Validate field is not in list.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param string $notIn
     * @param string ...$others
     *
     * @return bool
     */
    public static function notIn(string $field, array $data, string $notIn, string ...$others) : bool
    {
        return ! static::in($field, $data, $notIn, ...$others);
    }

    /**
     * Validates an IP.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $version 4, 6 or 0 to both
     *
     * @return bool
     */
    public static function ip(string $field, array $data, int | string $version = 0) : bool
    {
        $data = static::getData($field, $data);
        if ($data === null) {
            return false;
        }
        $version = (int) $version;
        if ($version !== 0) {
            $version = match ($version) {
                4 => \FILTER_FLAG_IPV4,
                6 => \FILTER_FLAG_IPV6,
                default => throw new InvalidArgumentException(
                    "Invalid IP Version: {$version}"
                ),
            };
        }
        return \filter_var($data, \FILTER_VALIDATE_IP, $version) !== false;
    }

    /**
     * Validates an URL.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function url(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        if ($data === null) {
            return false;
        }
        if (\preg_match('/^(?:([^:]*)\:)?\/\/(.+)$/', $data, $matches)) {
            if ( ! \in_array($matches[1], ['http', 'https'], true)) {
                return false;
            }
            $data = $matches[2];
        }
        $data = 'http://' . $data;
        return \filter_var($data, \FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validates a datetime format.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param string $format
     *
     * @return bool
     */
    public static function datetime(
        string $field,
        array $data,
        string $format = 'Y-m-d H:i:s'
    ) : bool {
        $data = static::getData($field, $data);
        if ($data === null) {
            return false;
        }
        $datetime = \DateTime::createFromFormat($format, $data);
        if ($datetime === false) {
            return false;
        }
        if ($datetime->format($format) !== $data) {
            return false;
        }
        $lastErrors = \DateTime::getLastErrors();
        if ($lastErrors === false) {
            return true;
        }
        return $lastErrors['warning_count'] === 0
            && $lastErrors['error_count'] === 0;
    }

    /**
     * Validates a email.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function email(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        if ($data === null) {
            return false;
        }
        if (\preg_match('#\A([^@]+)@(.+)\z#', $data, $matches)) {
            $data = $matches[1] . '@' . \idn_to_ascii($matches[2]);
        }
        return (bool) \filter_var($data, \FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validates is greater than.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $greaterThan
     *
     * @return bool
     */
    public static function greater(
        string $field,
        array $data,
        int | string $greaterThan
    ) : bool {
        $data = static::getData($field, $data);
        return $data !== null && $data > $greaterThan;
    }

    /**
     * Validates is greater than or equal to.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $greaterThanOrEqualTo
     *
     * @return bool
     */
    public static function greaterOrEqual(
        string $field,
        array $data,
        int | string $greaterThanOrEqualTo
    ) : bool {
        $data = static::getData($field, $data);
        return $data !== null && $data >= $greaterThanOrEqualTo;
    }

    /**
     * Validates is less than.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $lessThan
     *
     * @return bool
     */
    public static function less(
        string $field,
        array $data,
        int | string $lessThan
    ) : bool {
        $data = static::getData($field, $data);
        return $data !== null && $data < $lessThan;
    }

    /**
     * Validates is less than or equal to.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $lessThanOrEqualTo
     *
     * @return bool
     */
    public static function lessOrEqual(
        string $field,
        array $data,
        int | string $lessThanOrEqualTo
    ) : bool {
        $data = static::getData($field, $data);
        return $data !== null && $data <= $lessThanOrEqualTo;
    }

    /**
     * Validates a latin text.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function latin(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        return $data !== null && \preg_match('/^[\p{Latin}]+$/u', $data);
    }

    /**
     * Validates max length.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $maxLength
     *
     * @return bool
     */
    public static function maxLength(string $field, array $data, int | string $maxLength) : bool
    {
        $data = static::getData($field, $data);
        return $data !== null && \mb_strlen($data) <= (int) $maxLength;
    }

    /**
     * Validates min length.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $minLength
     *
     * @return bool
     */
    public static function minLength(string $field, array $data, int | string $minLength) : bool
    {
        $data = static::getData($field, $data);
        return $data !== null && \mb_strlen($data) >= (int) $minLength;
    }

    /**
     * Validates exact length.
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $length
     *
     * @return bool
     */
    public static function length(string $field, array $data, int | string $length) : bool
    {
        $data = static::getData($field, $data);
        return $data !== null && \mb_strlen($data) === (int) $length;
    }

    /**
     * Validates required value.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function required(string $field, array $data) : bool
    {
        $data = static::getData($field, $data);
        return $data !== null && \trim($data) !== '';
    }

    /**
     * Validates field is set.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public static function isset(string $field, array $data) : bool
    {
        return static::getData($field, $data) !== null;
    }

    /**
     * Validates array.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @since 2.2
     *
     * @return bool
     */
    public static function array(string $field, array $data) : bool
    {
        return \is_array(ArraySimple::value($field, $data));
    }

    /**
     * Validates boolean.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @since 2.2
     *
     * @return bool
     */
    public static function bool(string $field, array $data) : bool
    {
        return \is_bool(ArraySimple::value($field, $data));
    }

    /**
     * Validates float.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @since 2.2
     *
     * @return bool
     */
    public static function float(string $field, array $data) : bool
    {
        return \is_float(ArraySimple::value($field, $data));
    }

    /**
     * Validates integer.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @since 2.2
     *
     * @return bool
     */
    public static function int(string $field, array $data) : bool
    {
        return \is_int(ArraySimple::value($field, $data));
    }

    /**
     * Validates object.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @since 2.2
     *
     * @return bool
     */
    public static function object(string $field, array $data) : bool
    {
        return \is_object(ArraySimple::value($field, $data));
    }

    /**
     * Validates string.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @since 2.2
     *
     * @return bool
     */
    public static function string(string $field, array $data) : bool
    {
        return \is_string(ArraySimple::value($field, $data));
    }

    /**
     * Validates special characters.
     *
     * @see https://owasp.org/www-community/password-special-characters
     *
     * @param string $field
     * @param array<string,mixed> $data
     * @param int|string $quantity
     * @param string $characters
     *
     * @return bool
     */
    public static function specialChar(
        string $field,
        array $data,
        int | string $quantity = 1,
        string $characters = '!"#$%&\'()*+,-./:;=<>?@[\]^_`{|}~'
    ) : bool {
        $quantity = (int) $quantity;
        if ($quantity < 1) {
            throw new InvalidArgumentException('Special characters quantity must be greater than 0');
        }
        $data = static::getData($field, $data);
        if ($data === null) {
            return false;
        }
        $data = (array) \preg_split('//u', $data, -1, \PREG_SPLIT_NO_EMPTY);
        $characters = (array) \preg_split('//u', $characters, -1, \PREG_SPLIT_NO_EMPTY);
        $found = 0;
        foreach ($characters as $char) {
            foreach ($data as $item) {
                if ($char === $item) {
                    $found++;
                    if ($found === $quantity) {
                        return true;
                    }
                    break;
                }
            }
        }
        return false;
    }
}
