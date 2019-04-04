<?php namespace Framework\Validation;

class Validator
{
	protected static function getData(string $field, array $data) : ?string
	{
		$data = \ArraySimple::value($field, $data);
		return $data === null || ! \is_scalar($data) ? null : $data;
	}

	/**
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

	public static function number(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \is_numeric($data);
	}

	public static function alphaNumber(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \ctype_alnum($data);
	}

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

	public static function timezone(string $field, array $data) : bool
	{
		return static::in($field, $data, ...\DateTimeZone::listIdentifiers());
	}

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

	public static function json(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		if ($data === null) {
			return false;
		}
		\json_decode($data);
		return \json_last_error() === \JSON_ERROR_NONE;
	}

	public static function regex(string $field, array $data, string $pattern) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \preg_match($pattern, $data) === 1;
	}

	public static function notRegex(string $field, array $data, string $pattern) : bool
	{
		return ! static::regex($field, $data, $pattern);
	}

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

	public static function notEquals(string $field, array $data, string $diff_field) : bool
	{
		return ! static::equals($field, $data, $diff_field);
	}

	public static function between(string $field, array $data, $min, $max) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : $data >= $min && $data <= $max;
	}

	public static function notBetween(string $field, array $data, $min, $max) : bool
	{
		return ! static::between($field, $data, $min, $max);
	}

	public static function in(string $field, array $data, string ...$in) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \in_array($data, $in, true);
	}

	public static function notIn(string $field, array $data, string ...$not_in) : bool
	{
		return ! static::in($field, $data, ...$not_in);
	}

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

	public static function latin(string $field, array $data, $allow_whitespaces = false) : bool
	{
		$data = static::getData($field, $data);
		if ($data === null) {
			return false;
		}
		if ( ! \is_bool($allow_whitespaces)) {
			$allow_whitespaces = \strtolower($allow_whitespaces) === 'true';
		}
		$pattern = $allow_whitespaces ? '/^[\p{Latin}\s]+$/u' : '/^[\p{Latin}]+$/u';
		return \preg_match($pattern, $data);
	}

	public static function maxLength(string $field, array $data, int $max_length) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \mb_strlen($data) <= $max_length;
	}

	public static function minLength(string $field, array $data, int $min_length) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \mb_strlen($data) >= $min_length;
	}

	public static function length(string $field, array $data, int $length) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \mb_strlen($data) === $length;
	}

	public static function required(string $field, array $data) : bool
	{
		$data = static::getData($field, $data);
		return $data === null ? false : \trim($data) !== '';
	}

	public static function isset(string $field, array $data) : bool
	{
		return static::getData($field, $data) !== null;
	}
}
