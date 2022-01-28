<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\Validation\Debug;

use Framework\Debug\Collector;
use Framework\Validation\Validation;
use ReflectionMethod;

/**
 * Class ValidationCollector.
 *
 * @package validation
 */
class ValidationCollector extends Collector
{
    protected Validation $validation;
    /**
     * @var array<string,array<string,mixed>>
     */
    protected array $validatorsRules;

    public function setValidation(Validation $validation) : static
    {
        $this->validation = $validation;
        return $this;
    }

    public function getContents() : string
    {
        if ( ! isset($this->validation)) {
            return '<p>A Validation instance has not been set in this collector.</p>';
        }
        \ob_start(); ?>
        <?= $this->renderInfo() ?>
        <h1>Errors</h1>
        <?= $this->renderErrors() ?>
        <?php $this->validatorsRules = $this->getValidatorsRules(); ?>
        <h1>Rule Set</h1>
        <?= $this->renderRuleset() ?>
        <h1>Validators Rules</h1>
        <?php
        echo $this->renderValidatorsRules();
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderInfo() : string
    {
        if ( ! $this->hasData()) {
            return '<p>Validation did not run.</p>';
        }
        $count = \count($this->getData());
        return '<p>Validation ran ' . $count . ' time' . ($count === 1 ? '' : 's') . '.<p>';
    }

    protected function renderErrors() : string
    {
        if ( ! $this->hasData()) {
            return '<p>No data has been validated.</p>';
        }
        $data = $this->getData();
        $data = $data[\array_key_last($data)];
        $errors = $this->validation->getErrors();
        $countErrors = \count($errors);
        \ob_start(); ?>
        <p>In the last validation, it validated <?=
            $data['type'] === 'all'
                ? 'all rules against the data'
                : 'only the rules with fields set in the data'
            ?> in <?= \round($data['end'] - $data['start'], 6) ?> seconds and <?=
            $countErrors
                ? $countErrors . ' error' . ($countErrors === 1 ? '' : 's') . ' occurred:'
                : 'no error occurred.'
            ?></p>
        <?php
        if ( ! $errors) {
            return \ob_get_clean(); // @phpstan-ignore-line
        }
        $currentError = 1; ?>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Field</th>
                <th>Error Message</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($errors as $field => $error): ?>
                <tr>
                    <td><?= $currentError++ ?></td>
                    <td><?= \htmlentities($field) ?></td>
                    <td><?= \htmlentities($error) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderRuleset() : string
    {
        if ( ! $this->validation->getRules()) {
            return '<p>No rules set.</p>';
        }
        \ob_start(); ?>
        <p>The following rules have been set:</p>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Field</th>
                <th>Label</th>
                <th>Rules</th>
                <th>Possible Error Messages</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->validation->getRuleset() as $index => $set): ?>
                <?php $count = \count($set['rules']); ?>
                <tr>
                    <td rowspan="<?= $count ?>"><?= $index + 1 ?></td>
                    <td rowspan="<?= $count ?>"><?= \htmlentities($set['field']) ?></td>
                    <td rowspan="<?= $count ?>"><?= \htmlentities((string) $set['label']) ?></td>
                    <td><?= $this->sRule($set['rules'][0]['rule'], $this->validatorsRules) ?></td>
                    <td><?= \htmlentities($set['rules'][0]['message']) ?></td>
                </tr>
                <?php for ($i = 1; $i < $count; $i++): ?>
                    <tr>
                        <td><?= $this->sRule($set['rules'][$i]['rule'], $this->validatorsRules) ?></td>
                        <td><?= \htmlentities($set['rules'][$i]['message']) ?></td>
                    </tr>
                <?php endfor ?>
            <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderValidatorsRules() : string
    {
        if (empty($this->validatorsRules)) {
            return '<p>No Validators rules set.</p>';
        }
        \ob_start() ?>
        <p>There are <?= \count($this->validatorsRules) ?> rules available:</p>
        <table>
            <thead>
            <tr>
                <th>Rule</th>
                <th>Params</th>
                <th>Message Pattern</th>
                <th>Validator</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->validatorsRules as $rule => $data) : ?>
                <tr>
                    <td><?= \htmlentities($rule) ?></td>
                    <td>
                        <?php if ($data['params']): ?>
                            <pre><code class="language-php"><?= \htmlentities($data['params']) ?></code></pre>
                        <?php endif ?>
                    </td>
                    <td>
                        <pre><code class="language-icu-message-format"><?=
                                \htmlentities(
                                    $this->validation->getLanguage()->render('validation', $rule)
                                ) ?></code></pre>
                    </td>
                    <td><?= \htmlentities($data['validator']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <?php
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    protected function getValidatorsRules() : array
    {
        $rules = [];
        foreach (\array_reverse($this->validation->getValidators()) as $validator) {
            foreach (\get_class_methods($validator) as $method) {
                $method = (string) $method;
                if (\is_callable([$validator, $method])) {
                    $params = [];
                    $reflection = new ReflectionMethod($validator, $method);
                    foreach ($reflection->getParameters() as $parameter) {
                        $name = $parameter->getName();
                        if (\in_array($name, ['field', 'data'], true)) {
                            continue;
                        }
                        $value = '';
                        if ($parameter->isDefaultValueAvailable()) {
                            $value = $parameter->getDefaultValue();
                            $type = \get_debug_type($value);
                            if ($type === 'string') {
                                $value = "'" . \strtr($value, [
                                        "'" => "\\'",
                                    ]) . "'";
                            } elseif ($type === 'null') {
                                $value = 'null';
                            }
                            $value = ' = ' . $value;
                        }
                        $name = '$' . $name;
                        if ($parameter->isVariadic()) {
                            $name = '...' . $name;
                        }
                        $params[] = $name . $value;
                    }
                    $rules[$method] = [
                        'validator' => $validator,
                        'params' => \implode(', ', $params),
                    ];
                }
            }
        }
        \ksort($rules);
        return $rules;
    }

    /**
     * @param string $rule
     * @param array<string,mixed> $validatorsRules
     *
     * @return string
     */
    protected function sRule(string $rule, array $validatorsRules) : string
    {
        if ($rule === 'optional'
            || \array_key_exists(\explode(':', $rule)[0], $validatorsRules)
        ) {
            return \htmlentities($rule);
        }
        return '<s title="Rule not available">' . \htmlentities($rule) . '</s>';
    }
}
