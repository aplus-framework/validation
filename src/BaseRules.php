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

use Stringable;

/**
 * Class BaseRules.
 *
 * @package validation
 */
abstract class BaseRules implements Stringable
{
    /**
     * @var array<int,string>
     */
    public protected(set) array $rules = [];

    /**
     * @since 2.3
     */
    final public function __construct()
    {
    }

    public function __toString() : string
    {
        return \implode('|', $this->rules);
    }

    /**
     * Set field as optional.
     *
     * If field is undefined, validation passes.
     *
     * @return static
     */
    public function optional() : static
    {
        $this->rules[] = 'optional';
        return $this;
    }

    /**
     * If the field has a blank string, the validation passes.
     *
     * @since 2.2
     *
     * @return static
     */
    public function blank() : static
    {
        $this->rules[] = 'blank';
        return $this;
    }

    /**
     * If the field value is null, the validation passes.
     *
     * @since 2.2
     *
     * @return static
     */
    public function null() : static
    {
        $this->rules[] = 'null';
        return $this;
    }

    /**
     * If the field has an empty value, the validation passes.
     *
     * @since 2.2
     *
     * @return static
     */
    public function empty() : static
    {
        $this->rules[] = 'empty';
        return $this;
    }

    protected function esc(string $value) : string
    {
        return \strtr($value, [',' => '\,']);
    }

    /**
     * @param array<scalar> $values
     *
     * @return string
     */
    protected function implode(array $values) : string
    {
        foreach ($values as &$value) {
            $value = $this->esc((string) $value);
        }
        return \implode(',', $values);
    }

    public static function create() : static
    {
        return new static();
    }
}
