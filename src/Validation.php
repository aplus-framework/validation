<?php namespace Framework\Validation;

class Validation
{
	protected $labels = [];
	protected $rules = [];
	protected $data = [];
	protected $errors = [];
	protected $validators = [];

	public function __construct(array $validators = [Validator::class])
	{
		$this->validators = \array_reverse($validators);
	}

	public function reset()
	{
		$this->labels = [];
		$this->rules = [];
		$this->data = [];
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

	public function getData() : array
	{
		return $this->data;
	}

	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}

	public function getErrors() : array
	{
		return $this->errors;
	}

	public function getError(string $field) : ?array
	{
		return $this->errors[$field] ?? null;
	}

	protected function setError(string $field, string $rule, array $params)
	{
		$this->errors[$field] = [
			'rule' => $rule,
			'params' => $params,
		];
		return $this;
	}

	protected function validateRule(string $rule, string $field, array $params) : bool
	{
		foreach ($this->validators as $validator) {
			if (\is_callable([$validator, $rule])) {
				return $validator::$rule($field, $this->getData(), ...$params);
			}
		}
		throw new \InvalidArgumentException(
			"Validation rule '{$rule}' not found on field '{$field}'"
		);
	}

	protected function validateField(string $field, array $rules) : bool
	{
		$status = true;
		foreach ($rules as $rule) {
			$status = $this->validateRule($rule['rule'], $field, $rule['params']);
			if ($status !== true) {
				$this->setError($field, $rule['rule'], $rule['params']);
				break;
			}
		}
		return $status;
	}

	public function run() : bool
	{
		$result = true;
		foreach ($this->getRules() as $field => $rules) {
			$status = $this->validateField($field, $rules);
			if ( ! $status) {
				$result = false;
			}
		}
		return $result;
	}

	public function getErrorMessage(string $field) : ?string
	{
		$error = $this->getError($field);
		if ($error === null) {
			return null;
		}
		$label = $this->getLabel($field) ?? $field;
		return "The {$label} field is invalid.";
	}
}
