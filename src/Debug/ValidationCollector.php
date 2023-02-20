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

    public function getActivities() : array
    {
        $activities = [];
        foreach ($this->getData() as $index => $data) {
            $activities[] = [
                'collector' => $this->getName(),
                'class' => static::class,
                'description' => 'Run validation ' . ($index + 1),
                'start' => $data['start'],
                'end' => $data['end'],
            ];
        }
        return $activities;
    }

    public function setErrorInDebugData(string $field, string $error, int $index = -1) : static
    {
        $data = $this->getData();
        if ($index === -1) {
            $index = \array_key_last($data);
            if ($index === null) {
                return $this;
            }
        }
        $data[$index]['errors'][$field] = $error;
        $this->data = $data;
        return $this;
    }

    public function getContents() : string
    {
        if ( ! isset($this->validation)) {
            return '<p>A Validation instance has not been set in this collector.</p>';
        }
        \ob_start(); ?>
        <?= $this->renderValidations() ?>
        <?php $this->validatorsRules = $this->getValidatorsRules(); ?>
        <h1>Ruleset</h1>
        <?= $this->renderRuleset() ?>
        <h1>Validators Rules</h1>
        <?php
        echo $this->renderValidatorsRules();
        return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderValidations() : string
    {
        if ( ! $this->hasData()) {
            return '<p>Validation did not run.</p>';
        }
        $count = \count($this->getData());
        \ob_start(); ?>
        <p>Validation ran <?= $count ?> time<?= $count === 1 ? '' : 's' ?>.
        <p>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Errors Count</th>
                <th>Error Field</th>
                <th>Error Message</th>
                <th title="Seconds">Time</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($this->getData() as $index => $item):
                $count = \count($item['errors']);
                $errors = [];
                foreach ($item['errors'] as $field => $error) :
                    $errors[] = [
                        'field' => $field,
                        'error' => $error,
                    ];
                endforeach; ?>
                <tr>
                    <td rowspan="<?= $count ?>"><?= $index + 1 ?></td>
                    <td rowspan="<?= $count ?>"><?= $item['type'] ?></td>
                    <td rowspan="<?= $count ?>"><?= $count ?></td>
                    <td><?= $errors[0]['field'] ?? '' ?></td>
                    <td><?= $errors[0]['error'] ?? '' ?></td>
                    <td rowspan="<?= $count ?>"><?= \round($item['end'] - $item['start'], 6) ?></td>
                </tr>
                <?php for ($i = 1; $i < $count; $i++): ?>
                    <tr>
                        <td><?= $errors[$i]['field'] ?></td>
                        <td><?= $errors[$i]['error'] ?></td>
                    </tr>
                <?php endfor;
            endforeach; ?>
            </tbody>
        </table>
        <?php return \ob_get_clean(); // @phpstan-ignore-line
    }

    protected function renderRuleset() : string
    {
        if ( ! $this->validation->getRules()) {
            return '<p>No rules have been set.</p>';
        }
        \ob_start(); ?>
        <p>The following rules have been set:</p>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Field</th>
                <th>Label</th>
                <th>Rule</th>
                <th>Possible Error Message</th>
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
                        'validator' => \is_string($validator) ? $validator : $validator::class,
                        'params' => \implode(', ', $params),
                    ];
                }
            }
        }
        $rules['optional'] = [
            'validator' => '',
            'params' => '',
        ];
        $rules['blank'] = [
            'validator' => '',
            'params' => '',
        ];
        $rules['null'] = [
            'validator' => '',
            'params' => '',
        ];
        $rules['empty'] = [
            'validator' => '',
            'params' => '',
        ];
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
