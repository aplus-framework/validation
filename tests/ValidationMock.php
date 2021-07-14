<?php
/*
 * This file is part of Aplus Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Validation;

use Framework\Validation\Validation;
use JetBrains\PhpStorm\Pure;

class ValidationMock extends Validation
{
    #[Pure]
    public function parseRule(string $rule) : array
    {
        return parent::parseRule($rule);
    }

    #[Pure]
    public function extractRules(string $rules) : array
    {
        return parent::extractRules($rules);
    }

    public function setError(string $field, string $rule, array $params) : static
    {
        return parent::setError($field, $rule, $params);
    }
}
