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

use Framework\Validation\FilesValidator;
use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class LanguagesTest extends TestCase
{
    protected string $langDir = __DIR__ . '/../src/Languages/';

    /**
     * @return array<int,string>
     */
    protected function getCodes() : array
    {
        $codes = \array_filter((array) \glob($this->langDir . '*'), 'is_dir');
        $length = \strlen($this->langDir);
        $result = [];
        foreach ($codes as $dir) {
            if ($dir === false) {
                continue;
            }
            $result[] = \substr($dir, $length);
        }
        return $result;
    }

    public function testKeys() : void
    {
        $validator_rules = \get_class_methods(Validator::class);
        $files_validator_rules = \get_class_methods(FilesValidator::class);
        $rules = \array_merge($validator_rules, $files_validator_rules);
        $rules[] = 'optional';
        \sort($rules);
        foreach ($this->getCodes() as $code) {
            $lines = require $this->langDir . $code . '/validation.php';
            $lines = \array_keys($lines);
            \sort($lines);
            self::assertSame($rules, $lines, 'Language: ' . $code);
        }
    }
}
