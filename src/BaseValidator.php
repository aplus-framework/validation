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

/**
 * Class BaseValidator.
 *
 * @package validation
 */
abstract class BaseValidator
{
    /**
     * Get field value from data.
     *
     * @param string $field
     * @param array<string,mixed> $data
     *
     * @return string|null
     */
    protected static function getData(string $field, array $data) : ?string
    {
        $data = ArraySimple::value($field, $data);
        return \is_scalar($data) ? (string) $data : null;
    }
}
