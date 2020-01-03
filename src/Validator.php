<?php namespace Framework\Validation;

/**
 * Class Validator.
 */
class Validator
{
	protected static function getData(string $field, array $data) : ?string
	{
		$data = \ArraySimple::value($field, $data);
		return $data === null || ! \is_scalar($data) ? null : $data;
	}

	/**
	 * Validates alphabetic caracters.
	 *
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public static function alpha(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \ctype_alpha($data);
	}

	/**
	 * Validates a number.
	 *
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public static function number(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \is_numeric($data);
	}

	/**
	 * Validates a number or alphabetic characters.
	 *
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public static function alphaNumber(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \ctype_alnum($data);
	}

	/**
	 * Validates a UUID.
	 *
	 * @param string $field
	 * @param array  $data
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
	 * @param array  $data
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
	 * @param array  $data
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
		return $decoded ?
			\base64_encode($decoded) === $data
			: false;
	}

	/**
	 * Validates a md5 hash.
	 *
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public static function md5(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		if ($data === null) {
			return false;
		}
		return \preg_match('/^[a-f0-9]{32}$/', $data);
	}

	/**
	 * Validates a hexadecimal string.
	 *
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public static function hex(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		if ($data === null) {
			return false;
		}
		return \ctype_xdigit($data);
	}

	/**
	 * Validates a JSON string.
	 *
	 * @param string $field
	 * @param array  $data
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
	 * @param array  $data
	 * @param string $pattern
	 *
	 * @return bool
	 */
	public static function regex(string $field, array $data, string $pattern) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \preg_match($pattern, $data) === 1;
	}

	/**
	 * Validates a Regex no matching pattern.
	 *
	 * @param string $field
	 * @param array  $data
	 * @param string $pattern
	 *
	 * @return bool
	 */
	public static function notRegex(string $field, array $data, string $pattern) : bool
	{
		return ! static::regex($field, $data, $pattern);
	}

	/**
	 * Validate field has value equals other field.
	 *
	 * @param string $field
	 * @param array  $data
	 * @param string $equals_field
	 *
	 * @return bool
	 */
	public static function equals(string $field, array $data, string $equals_field) : bool
	{
		$field = \ArraySimple::value($field, $data);
		if ($field === null || ! \is_scalar($field)) {
			return false;
		}
		$equals_field = \ArraySimple::value($equals_field, $data);
		if ($equals_field === null || ! \is_scalar($equals_field)) {
			return false;
		}
		return (string) $field === (string) $equals_field;
	}

	/**
	 * Validate field has not value equals other field.
	 *
	 * @param string $field
	 * @param array  $data
	 * @param string $diff_field
	 *
	 * @return bool
	 */
	public static function notEquals(string $field, array $data, string $diff_field) : bool
	{
		return ! static::equals($field, $data, $diff_field);
	}

	/**
	 * Validate field between min and max values.
	 *
	 * @param string     $field
	 * @param array      $data
	 * @param int|string $min
	 * @param int|string $max
	 *
	 * @return bool
	 */
	public static function between(string $field, array $data, $min, $max) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : $data >= $min && $data <= $max;
	}

	/**
	 * Validate field not between min and max values.
	 *
	 * @param string     $field
	 * @param array      $data
	 * @param int|string $min
	 * @param int|string $max
	 *
	 * @return bool
	 */
	public static function notBetween(string $field, array $data, $min, $max) : bool
	{
		return ! static::between($field, $data, $min, $max);
	}

	/**
	 * Validate field is in list.
	 *
	 * @param string $field
	 * @param array  $data
	 * @param        $in
	 *
	 * @return bool
	 */
	public static function in(string $field, array $data, string ...$in) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \in_array($data, $in, true);
	}

	/**
	 * Validate field is not in list.
	 *
	 * @param string $field
	 * @param array  $data
	 * @param        $not_in
	 *
	 * @return bool
	 */
	public static function notIn(string $field, array $data, string ...$not_in) : bool
	{
		return ! static::in($field, $data, ...$not_in);
	}

	/**
	 * Validates an IP.
	 *
	 * @param string   $field
	 * @param array    $data
	 * @param int|null $version 4 or 6
	 *
	 * @return bool
	 */
	public static function ip(string $field, array $data, int $version = null) : bool
	{
		$data = static::getData($field, $data);
		if ($data === null) {
			return false;
		}
		if ($version) {
			switch ($version) {
				case 4:
					$version = \FILTER_FLAG_IPV4;
					break;
				case 6:
					$version = \FILTER_FLAG_IPV6;
					break;
				default:
					throw new \InvalidArgumentException(
						"Invalid IP Version: {$version}"
					);
			}
		}
		return \filter_var($data, \FILTER_VALIDATE_IP, $version) !== false;
	}

	/**
	 * Validates an URL.
	 *
	 * @param string $field
	 * @param array  $data
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
	 * @param array  $data
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
		$data = \DateTime::createFromFormat($format, $data);
		return (bool) $data
			&& \DateTime::getLastErrors()['warning_count'] === 0
			&& \DateTime::getLastErrors()['error_count'] === 0;
	}

	/**
	 * Validates a email.
	 *
	 * @param string $field
	 * @param array  $data
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
			$data = $matches[1] . '@' .
				\idn_to_ascii($matches[2], 0, \INTL_IDNA_VARIANT_UTS46);
		}
		return (bool) \filter_var($data, \FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Validates a latin text.
	 *
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public static function latin(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		if ($data === null) {
			return false;
		}
		return \preg_match('/^[\p{Latin}]+$/u', $data);
	}

	/**
	 * Validates max length.
	 *
	 * @param string $field
	 * @param array  $data
	 * @param int    $max_length
	 *
	 * @return bool
	 */
	public static function maxLength(string $field, array $data, int $max_length) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \mb_strlen($data) <= $max_length;
	}

	/**
	 * Validates min length.
	 *
	 * @param string $field
	 * @param array  $data
	 * @param int    $min_length
	 *
	 * @return bool
	 */
	public static function minLength(string $field, array $data, int $min_length) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \mb_strlen($data) >= $min_length;
	}

	/**
	 * Validates exact length.
	 *
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public static function length(string $field, array $data, int $length) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \mb_strlen($data) === $length;
	}

	/**
	 * Validates required value.
	 *
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public static function required(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \trim($data) !== '';
	}

	/**
	 * Validates field is set.
	 *
	 * @param string $field
	 * @param array  $data
	 *
	 * @return bool
	 */
	public static function isset(string $field, array $data) : bool
	{
		return static::getData($field, $data) !== null;
	}
}
