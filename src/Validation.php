<?php namespace Framework\Validation;

use Framework\Language\Language;

class Validation
{
	protected $labels = [];
	protected $rules = [];
	protected $errors = [];
	protected $validators = [];
	/**
	 * @var Language
	 */
	protected $language;

	public function __construct(array $validators = null, Language $language = null)
	{
		$this->validators = $validators === null
			? [Validator::class]
			: \array_reverse($validators);
		if ($language === null) {
			$language = new Language('en');
		}
		$language->setDirectories(\array_merge([
			__DIR__ . '/Languages',
		], $language->getDirectories()));
		$this->language = $language;
	}

	public function reset()
	{
		$this->labels = [];
		$this->rules = [];
		$this->errors = [];
		return $this;
	}

	public function setLabel(string $field, string $label)
	{
		$this->labels[$field] = $label;
		return $this;
	}

	public function getLabel(string $field) : ?string
	{
		return $this->labels[$field] ?? null;
	}

	public function getLabels() : array
	{
		return $this->labels;
	}

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
		if (\strpos($rule, ':') !== false) {
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
			$rule = $this->parseRule(\strtr($rule, ['\|' => '|']));
		}
		return $rules;
	}

	public function getRules() : array
	{
		return $this->rules;
	}

	public function setRule(string $field, $rules)
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

	public function setRules(array $rules)
	{
		foreach ($rules as $field => $rule) {
			$this->setRule($field, $rule);
		}
		return $this;
	}

	public function getError(string $field) : ?string
	{
		$error = $this->errors[$field] ?? null;
		if ($error === null) {
			return null;
		}
		$error['params']['field'] = $this->getLabel($field) ?? $field;
		return $this->language->render('validation', $error['rule'], $error['params']);
	}

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
		throw new \InvalidArgumentException(
			"Validation rule '{$rule}' not found on field '{$field}'"
		);
	}

	protected function validateField(string $field, array $rules, array $data) : bool
	{
		$status = true;
		foreach ($rules as $rule) {
			$status = $this->validateRule($rule['rule'], $field, $rule['params'], $data);
			if ($status !== true) {
				$this->setError($field, $rule['rule'], $rule['params']);
				break;
			}
		}
		return $status;
	}

	protected function run(array $field_rules, array $data) : bool
	{
		$result = true;
		foreach ($field_rules as $field => $rules) {
			$status = $this->validateField($field, $rules, $data);
			if ( ! $status) {
				$result = false;
			}
		}
		return $result;
	}

	public function validate(array $data) : bool
	{
		return $this->run($this->getRules(), $data);
	}

	public function validateOnly(array $data) : bool
	{
		$field_rules = \array_intersect_key(
			$this->getRules(),
			\ArraySimple::convert($data)
		);
		return $this->run($field_rules, $data);
	}
}
