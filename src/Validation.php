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
use Framework\Validation\Debug\ValidationCollector;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * Class Validation.
 *
 * @package validation
 */
class Validation
{
    /**
     * The labels used to replace field names.
     *
     * @var array<string,string> The field names as keys and the labels as values
     */
    protected array $labels = [];
    /**
     * The Validator rules.
     *
     * @var array<string,array<int,array<string,array<int,string>|string>>> The
     * field names as keys and the rules and arguments as values
     */
    protected array $rules = [];
    /**
     * The last errors.
     *
     * @var array<string,array<string,array<string,array<int,string>|string>>> The
     * field names as keys and the rule and arguments as values
     */
    protected array $errors = [];
    /**
     * Custom error messages.
     *
     * @var array<string,array<string,string>> The field name as keys and an
     * associative array of rule names as keys and messages as values
     */
    protected array $messages = [];
    /**
     * The current Validators.
     *
     * @var array<int,string|Validator> Values are the Validators FQCN or
     * instances
     */
    protected array $validators = [];
    /**
     * The Language instance.
     *
     * @var Language
     */
    protected Language $language;
    protected ValidationCollector $debugCollector;

    /**
     * Validation constructor.
     *
     * @param array<int,string|Validator>|null $validators
     * @param Language|null $language
     */
    public function __construct(array $validators = null, Language $language = null)
    {
        $defaultValidators = [
            Validator::class,
            FilesValidator::class,
        ];
        $this->validators = empty($validators)
            ? $defaultValidators
            : \array_reverse($validators);
        if ($language) {
            $this->setLanguage($language);
        }
    }

    public function setLanguage(Language $language = null) : static
    {
        if ($language === null) {
            $language = new Language();
        }
        $language->addDirectory(__DIR__ . '/Languages');
        $this->language = $language;
        return $this;
    }

    public function getLanguage() : Language
    {
        if ( ! isset($this->language)) {
            $this->setLanguage();
        }
        return $this->language;
    }

    /**
     * @return array<int,string|Validator>
     */
    public function getValidators() : array
    {
        return $this->validators;
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
        $this->labels = [];
        foreach ($labels as $field => $label) {
            $this->setLabel($field, $label);
        }
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getRuleset() : array
    {
        $result = [];
        foreach ($this->getRules() as $field => $rules) {
            $label = $this->getLabel($field);
            $tmp = [
                'field' => $field,
                'label' => $label,
                'rules' => [],
            ];
            foreach ($rules as $rule) {
                $rule['args'] = $this->escapeArgs($rule['args']); // @phpstan-ignore-line
                $args = \implode(',', $rule['args']);
                $ruleString = $rule['rule'] . ($args === '' ? '' : ':' . $args); // @phpstan-ignore-line
                $tmp['rules'][] = [
                    'rule' => $ruleString,
                    'message' => $this->getFilledMessage(
                        $field,
                        $rule['rule'], // @phpstan-ignore-line
                        \array_merge(
                            ['field' => $label ?? $field],
                            $rule['args']
                        )
                    ),
                ];
            }
            $result[] = $tmp;
        }
        return $result;
    }

    /**
     * @param array<string> $args
     *
     * @return array<string>
     */
    protected function escapeArgs(array $args) : array
    {
        foreach ($args as &$arg) {
            $arg = \strtr($arg, [',' => '\,']);
        }
        return $args;
    }

    /**
     * @param string $rule
     *
     * @return array<string,array<int,string>|string>
     */
    protected function parseRule(string $rule) : array
    {
        $args = [];
        if (\str_contains($rule, ':')) {
            [$rule, $args] = \explode(':', $rule, 2);
            $args = (array) \preg_split('#(?<!\\\)\,#', $args);
            foreach ($args as &$arg) {
                $arg = \strtr((string) $arg, ['\,' => ',']);
            }
        }
        return ['rule' => $rule, 'args' => $args]; // @phpstan-ignore-line
    }

    /**
     * @param string $rules
     *
     * @return array<int,array<string,array<int,string>|string>>
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
     * @param string $field
     * @param string $rule
     * @param array<mixed> $args
     *
     * @return string
     */
    public function getFilledMessage(string $field, string $rule, array $args = []) : string
    {
        $message = $this->getMessage($field, $rule);
        if ($message === null) {
            return $this->getLanguage()->render('validation', $rule, $args);
        }
        return $this->getLanguage()->formatMessage($message, $args);
    }

    /**
     * Get a list of current rules.
     *
     * @return array<string,array<int,array<string,array<int,string>|string>>>
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
     * @param array<string>|string $rules
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
            $this->rules[$field] = $rules; // @phpstan-ignore-line
            return $this;
        }
        $this->rules[$field] = $this->extractRules($rules);
        return $this;
    }

    /**
     * Set field rules.
     *
     * @param array<string,array<string>|string> $rules An associative array
     * with field as keys and values as rules
     *
     * @return static
     */
    public function setRules(array $rules) : static
    {
        $this->rules = [];
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
        $error['args']['args'] = $error['args'] ? \implode(', ', $error['args']) : ''; // @phpstan-ignore-line
        $error['args']['field'] = $this->getLabel($field) ?? $field;
        return $this->getFilledMessage($field, $error['rule'], $error['args']); // @phpstan-ignore-line
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
     * @param array<int,string> $args
     *
     * @return static
     */
    public function setError(string $field, string $rule, array $args = []) : static
    {
        // @phpstan-ignore-next-line
        $this->errors[$field] = [
            'rule' => $rule,
            'args' => $args,
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
     * @param array<int|string,mixed> $args
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    protected function validateRule(string $rule, string $field, array $args, array $data) : bool
    {
        foreach ($this->validators as $validator) {
            if (\is_callable([$validator, $rule])) {
                return $validator::$rule($field, $data, ...$args);
            }
        }
        throw new InvalidArgumentException(
            "Validation rule '{$rule}' not found on field '{$field}'"
        );
    }

    /**
     * @param string $field
     * @param array<string,array<mixed>> $rules
     * @param array<string,mixed> $data
     *
     * @since 2.2 Added "blank", "null" and "empty" rules
     *
     * @return bool
     */
    protected function validateField(string $field, array $rules, array $data) : bool
    {
        $removeKeys = [];
        foreach ($rules as $key => $rule) {
            $fieldExists = \array_key_exists($field, $data);
            // Field is optional. If the field is undefined, validation passes.
            if ($rule['rule'] === 'optional') {
                $removeKeys[] = $key;
                if ( ! $fieldExists) {
                    return true;
                }
            }
            // Field must be defined and can have a blank string.
            if ($rule['rule'] === 'blank') {
                $removeKeys[] = $key;
                if ($fieldExists && $data[$field] === '') {
                    return true;
                }
            }
            // Field must be defined and can have a null value.
            if ($rule['rule'] === 'null') {
                $removeKeys[] = $key;
                if ($fieldExists && $data[$field] === null) {
                    return true;
                }
            }
            // Field must be defined and can have an empty value
            if ($rule['rule'] === 'empty') {
                $removeKeys[] = $key;
                if ($fieldExists && empty($data[$field])) {
                    return true;
                }
            }
        }
        foreach ($removeKeys as $removeKey) {
            unset($rules[$removeKey]);
        }
        $status = true;
        foreach ($rules as $rule) {
            $rule['args'] = $this->replaceArgs($rule['args'], $data);
            $status = $this->validateRule($rule['rule'], $field, $rule['args'], $data);
            if ($status !== true) {
                $rule = $this->setEqualsField($rule);
                $this->setError($field, $rule['rule'], $rule['args']);
                break;
            }
        }
        return $status;
    }

    /**
     * Replace argument placeholders with data values.
     *
     * @param array<mixed> $args
     * @param array<string,mixed> $data
     *
     * @return array<mixed>
     */
    protected function replaceArgs(array $args, array $data) : array
    {
        $result = [];
        foreach ($args as $arg) {
            if (\preg_match('#^{(\w+)}$#', $arg)) {
                $key = \substr($arg, 1, -1);
                if (isset($data[$key])) {
                    $result[] = $data[$key];
                    continue;
                }
            }
            $result[] = $arg;
        }
        return $result;
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
            $rule['args'][0] = $this->getLabel($rule['args'][0]) ?? $rule['args'][0];
        }
        return $rule;
    }

    /**
     * @param array<string,array<mixed>> $fieldRules
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    protected function run(array $fieldRules, array $data) : bool
    {
        $this->errors = [];
        $result = true;
        foreach ($fieldRules as $field => $rules) {
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
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            $validated = $this->run($this->getRules(), $data);
            $end = \microtime(true);
            $this->debugCollector->addData([
                'start' => $start,
                'end' => $end,
                'validated' => $validated,
                'errors' => $this->getErrors(),
                'type' => 'all',
            ]);
            return $validated;
        }
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
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            $validated = $this->validateOnlySet($data);
            $end = \microtime(true);
            $this->debugCollector->addData([
                'start' => $start,
                'end' => $end,
                'validated' => $validated,
                'errors' => $this->getErrors(),
                'type' => 'only',
            ]);
            return $validated;
        }
        return $this->validateOnlySet($data);
    }

    /**
     * @param array<string,mixed> $data
     *
     * @return bool
     */
    protected function validateOnlySet(array $data) : bool
    {
        $fieldRules = \array_intersect_key(
            $this->getRules(),
            ArraySimple::convert($data)
        );
        return $this->run($fieldRules, $data);
    }

    /**
     * Tells if a rule is available in the current validators.
     *
     * @param string $rule
     *
     * @return bool
     */
    public function isRuleAvailable(string $rule) : bool
    {
        if (\in_array($rule, [
            'blank',
            'empty',
            'null',
            'optional',
        ])) {
            return true;
        }
        foreach ($this->validators as $validator) {
            if (\is_callable([$validator, $rule])) {
                return true;
            }
        }
        return false;
    }

    public function setDebugCollector(ValidationCollector $debugCollector) : static
    {
        $this->debugCollector = $debugCollector;
        $this->debugCollector->setValidation($this);
        return $this;
    }

    public function getDebugCollector() : ValidationCollector | null
    {
        return $this->debugCollector ?? null;
    }
}
