<?php namespace Framework\Validation;

use Framework\Language\Language;
use InvalidArgumentException;

/**
 * Class Validation.
 */
class Validation
{
	/**
	 * @var array|string[]
	 */
	protected array $labels = [];
	/**
	 * @var array|array[]
	 */
	protected array $rules = [];
	/**
	 * @var array|string[]
	 */
	protected array $errors = [];
	/**
	 * @var array|Validator[]
	 */
	protected array $validators = [];
	protected Language $language;

	/**
	 * Validation constructor.
	 *
	 * @param array|Validator[]|null $validators
	 * @param Language|null          $language
	 */
	public function __construct(array $validators = null, Language $language = null)
	{
		$default_validators = [
			Validator::class,
			FilesValidator::class,
		];
		$this->validators = empty($validators)
			? $default_validators
			: \array_reverse($validators);
		if ($language === null) {
			$language = new Language('en');
		}
		$language->addDirectory(__DIR__ . '/Languages');
		$this->language = $language;
	}

	/**
	 * Reset the validation.
	 *
	 * @return $this
	 */
	public function reset()
	{
		$this->labels = [];
		$this->rules = [];
		$this->errors = [];
		return $this;
	}

	/**
	 * Set label for a field.
	 *
	 * @param string $field
	 * @param string $label
	 *
	 * @return $this
	 */
	public function setLabel(string $field, string $label)
	{
		$this->labels[$field] = $label;
		return $this;
	}

	/**
	 * Get the label for a given field.
	 *
	 * @param string $field
	 *
	 * @return string|null
	 */
	public function getLabel(string $field) : ?string
	{
		return $this->labels[$field] ?? null;
	}

	/**
	 * Get a list of all labels.
	 *
	 * @return array
	 */
	public function getLabels() : array
	{
		return $this->labels;
	}

	/**
	 * Set fields labels.
	 *
	 * @param array $labels An associative array with fields as keys and label as values
	 *
	 * @return $this
	 */
	public function setLabels(array $labels)
	{
		foreach ($labels as $field => $label) {
			$this->setLabel($field, $label);
		}
		return $this;
	}

	protected function parseRule(string $rule) : array
	{
		$params = [];
		if (\str_contains($rule, ':')) {
			[$rule, $params] = \explode(':', $rule, 2);
			$params = (array) \preg_split('#(?<!\\\)\,#', $params);
			foreach ($params as &$param) {
				$param = \strtr($param, ['\,' => ',']);
			}
		}
		return ['rule' => $rule, 'params' => $params];
	}

	protected function extractRules(string $rules) : array
	{
		$rules = (array) \preg_split('#(?<!\\\)\|#', $rules);
		foreach ($rules as &$rule) {
			$rule = \strtr($rule, ['\|' => '|']);
			$rule = $this->parseRule($rule);
		}
		return $rules;
	}

	/**
	 * Get a list of current rules.
	 *
	 * @return array
	 */
	public function getRules() : array
	{
		return $this->rules;
	}

	/**
	 * Set rules for a given field.
	 *
	 * @param string       $field
	 * @param array|string $rules
	 *
	 * @return $this
	 */
	public function setRule(string $field, array | string $rules)
	{
		if (\is_array($rules)) {
			foreach ($rules as &$rule) {
				$rule = $this->parseRule($rule);
			}
			unset($rule);
			$this->rules[$field] = $rules;
			return $this;
		}
		$this->rules[$field] = $this->extractRules($rules);
		return $this;
	}

	/**
	 * Set field rules.
	 *
	 * @param array $rules an associative array with field as keys and values as rules
	 *
	 * @return $this
	 */
	public function setRules(array $rules)
	{
		foreach ($rules as $field => $rule) {
			$this->setRule($field, $rule);
		}
		return $this;
	}

	/**
	 * Get latest error for a given field.
	 *
	 * @param string $field
	 *
	 * @return string|null
	 */
	public function getError(string $field) : ?string
	{
		$error = $this->errors[$field] ?? null;
		if ($error === null) {
			return null;
		}
		$error['params']['params'] = $error['params'] ? \implode(', ', $error['params']) : '';
		$error['params']['field'] = $this->getLabel($field) ?? $field;
		return $this->language->render('validation', $error['rule'], $error['params']);
	}

	/**
	 * Get latest errors.
	 *
	 * @return array
	 */
	public function getErrors() : array
	{
		$messages = [];
		foreach (\array_keys($this->errors) as $field) {
			$messages[$field] = $this->getError($field);
		}
		return $messages;
	}

	protected function setError(string $field, string $rule, array $params)
	{
		$this->errors[$field] = [
			'rule' => $rule,
			'params' => $params,
		];
		return $this;
	}

	protected function validateRule(string $rule, string $field, array $params, array $data) : bool
	{
		foreach ($this->validators as $validator) {
			if (\is_callable([$validator, $rule])) {
				return $validator::$rule($field, $data, ...$params);
			}
		}
		throw new InvalidArgumentException(
			"Validation rule '{$rule}' not found on field '{$field}'"
		);
	}

	protected function validateField(string $field, array $rules, array $data) : bool
	{
		foreach ($rules as $key => $rule) {
			if ($rule['rule'] === 'optional') {
				$ruleKey = $key;
				if (empty($data[$field])) {
					return true;
				}
			}
		}
		if (isset($ruleKey)) {
			unset($rules[$ruleKey]);
		}
		$status = true;
		foreach ($rules as $rule) {
			$status = $this->validateRule($rule['rule'], $field, $rule['params'], $data);
			if ($status !== true) {
				$rule = $this->setEqualsField($rule);
				$this->setError($field, $rule['rule'], $rule['params']);
				break;
			}
		}
		return $status;
	}

	protected function setEqualsField(array $rule) : array
	{
		if ($rule['rule'] === 'equals' || $rule['rule'] === 'notEquals') {
			$rule['params'][0] = $this->getLabel($rule['params'][0]) ?? $rule['params'][0];
		}
		return $rule;
	}

	protected function run(array $field_rules, array $data) : bool
	{
		$this->errors = [];
		$result = true;
		foreach ($field_rules as $field => $rules) {
			$status = $this->validateField($field, $rules, $data);
			if ( ! $status) {
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Validate data with all rules.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function validate(array $data) : bool
	{
		return $this->run($this->getRules(), $data);
	}

	/**
	 * Validate only fields set on data.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function validateOnly(array $data) : bool
	{
		$field_rules = \array_intersect_key(
			$this->getRules(),
			\ArraySimple::convert($data)
		);
		return $this->run($field_rules, $data);
	}
}
