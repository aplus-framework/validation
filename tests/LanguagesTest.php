<?php namespace Tests\Validation;

use Framework\Validation\FilesValidator;
use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class LanguagesTest extends TestCase
{
	protected string $langDir = __DIR__ . '/../src/Languages/';

	protected function getCodes()
	{
		$codes = \array_filter(\glob($this->langDir . '*'), 'is_dir');
		$length = \strlen($this->langDir);
		foreach ($codes as &$dir) {
			$dir = \substr($dir, $length);
		}
		return $codes;
	}

	public function testKeys() : void
	{
		$validator_rules = \get_class_methods(Validator::class);
		$files_validator_rules = \get_class_methods(FilesValidator::class);
		$rules = \array_merge($validator_rules, $files_validator_rules);
		\sort($rules);
		foreach ($this->getCodes() as $code) {
			$lines = require $this->langDir . $code . '/validation.php';
			$lines = \array_keys($lines);
			\sort($lines);
			self::assertSame($rules, $lines, 'Language: ' . $code);
		}
	}
}
