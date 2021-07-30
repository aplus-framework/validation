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
use Framework\Language\Language;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * Class Validation.
 */
class Validation
{
    /**
     * @var array<string,string>
     */
    protected array $labels = [];
    /**
     * @var array<string,array>
     */
    protected array $rules = [];
    /**
     * @var array<string,array|string>
     */
    protected array $errors = [];
    /**
     * Custom error messages.
     *
     * @var array<string,array<string,string>>
     */
    protected array $messages = [];
    /**
     * @var array<int,string|Validator>
     */
    protected array $validators = [];
    protected Language $language;

    /**
     * Validation constructor.
     *
     * @param array<int,string|Validator>|null $validators
     * @param Language|null $language
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
     * @return static
     */
    public function reset() : static
    {
        $this->labels = [];
        $this->rules = [];
        $this->errors = [];
        $this->messages = [];
        return $this;
    }

    /**
     * Set label for a field.
     *
     * @param string $field
     * @param string $label
     *
     * @return static
     */
    public function setLabel(string $field, string $label) : static
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
    #[Pure]
    public function getLabel(string $field) : ?string
    {
        return $this->labels[$field] ?? null;
    }

    /**
     * Get a list of all labels.
     *
     * @return array<string,string>
     */
    #[Pure]
    public function getLabels() : array
    {
        return $this->labels;
    }

    /**
     * Set fields labels.
     *
     * @param array<string,string> $labels An associative array with fields as
     * keys and label as values
     *
     * @return static
     */
    public function setLabels(array $labels) : static
    {
        foreach ($labels as $field => $label) {
            $this->setLabel($field, $label);
        }
        return $this;
    }

    /**
     * @param string $rule
     *
     * @return array<string,array|string>
     */
    #[Pure]
    protected function parseRule(string $rule) : array
    {
        $params = [];
        if (\str_contains($rule, ':')) {
            [$rule, $params] = \explode(':', $rule, 2);
            $params = (array) \preg_split('#(?<!\\\)\,#', $params);
            foreach ($params as &$param) {
                $param = \strtr((string) $param, ['\,' => ',']);
            }
        }
        return ['rule' => $rule, 'params' => $params];
    }

    /**
     * @param string $rules
     *
     * @return array<int,array>
     */
    #[Pure]
    protected function extractRules(string $rules) : array
    {
        $result = [];
        $rules = (array) \preg_split('#(?<!\\\)\|#', $rules);
        foreach ($rules as $rule) {
            $rule = \strtr((string) $rule, ['\|' => '|']);
            $result[] = $this->parseRule($rule);
        }
        return $result;
    }

    /**
     * Get a list of current rules.
     *
     * @return array<string,array>
     */
    #[Pure]
    public function getRules() : array
    {
        return $this->rules;
    }

    /**
     * Set rules for a given field.
     *
     * @param string $field
     * @param array<int|string,string>|string $rules
     *
     * @return static
     */
    public function setRule(string $field, array | string $rules) : static
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
     * @param array<string,array|string> $rules An associative array with field
     * as keys and values as rules
     *
     * @return static
     */
    public function setRules(array $rules) : static
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
        // @phpstan-ignore-next-line
        $error['params']['params'] = $error['params'] ? \implode(', ', $error['params']) : '';
        $error['params']['field'] = $this->getLabel($field) ?? $field;
        $message = $this->getMessage($field, $error['rule']); // @phpstan-ignore-line
        if ($message === null) {
            // @phpstan-ignore-next-line
            return $this->language->render('validation', $error['rule'], $error['params']);
        }
        return $this->language->formatMessage($message, $error['params']);
    }

    /**
     * Get latest errors.
     *
     * @return array<string,string>
     */
    public function getErrors() : array
    {
        $messages = [];
        foreach (\array_keys($this->errors) as $field) {
            $messages[$field] = $this->getError($field);
        }
        return $messages;
    }

    /**
     * @param string $field
     * @param string $rule
     * @param array<int|string,array|string> $params
     *
     * @return static
     */
    protected function setError(string $field, string $rule, array $params) : static
    {
        $this->errors[$field] = [
            'rule' => $rule,
            'params' => $params,
        ];
        return $this;
    }

    /**
     * Set a custom error message for a field rule.
     *
     * @param string $field The field name
     * @param string $rule The field rule name
     * @param string $message The custom error message for the field rule
     *
     * @return static
     */
    public function setMessage(string $field, string $rule, string $message) : static
    {
        $this->messages[$field][$rule] = $message;
        return $this;
    }

    /**
     * Get the custom error message from a field rule.
     *
     * @param string $field The field name
     * @param string $rule The rule name
     *
     * @return string|null The message string or null if the message is not set
     */
    public function getMessage(string $field, string $rule) : ?string
    {
        return $this->messages[$field][$rule] ?? null;
    }

    /**
     * Set many custom error messages.
     *
     * @param array<string,array<string,string>> $messages A multi-dimensional
     * array with field names as keys and values as arrays where the keys are
     * rule names and values are the custom error message strings
     *
     * @return static
     */
    public function setMessages(array $messages) : static
    {
        $this->messages = [];
        foreach ($messages as $field => $rules) {
            foreach ($rules as $rule => $message) {
                $this->setMessage($field, $rule, $message);
            }
        }
        return $this;
    }

    /**
     * Get all custom error messages set.
     *
     * @return array<string,array<string,string>>
     */
    public function getMessages() : array
    {
        return $this->messages;
    }

    /**
     * @param string $rule
     * @param string $field
     * @param array<int|string,mixed> $params
     * @param array<string,mixed> $data
     *
     * @return bool
     */
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

    /**
     * @param string $field
     * @param array<string,array> $rules
     * @param array<string,mixed> $data
     *
     * @return bool
     */
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

    /**
     * @param array<string,mixed> $rule
     *
     * @return array<string,mixed>
     */
    #[Pure]
    protected function setEqualsField(array $rule) : array
    {
        if ($rule['rule'] === 'equals' || $rule['rule'] === 'notEquals') {
            $rule['params'][0] = $this->getLabel($rule['params'][0]) ?? $rule['params'][0];
        }
        return $rule;
    }

    /**
     * @param array<string,array> $field_rules
     * @param array<string,mixed> $data
     *
     * @return bool
     */
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
     * @param array<string,mixed> $data
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
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    public function validateOnly(array $data) : bool
    {
        $field_rules = \array_intersect_key(
            $this->getRules(),
            ArraySimple::convert($data)
        );
        return $this->run($field_rules, $data);
    }
}
