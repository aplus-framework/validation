<?php namespace Tests\Validation;

use PHPUnit\Framework\TestCase;

class LanguagesTest extends TestCase
{
	protected $codes = [
		'en',
		'es',
		'pt-br',
	];

	public function testKeys()
	{
		foreach ($this->codes as $code) {
			$lines = require __DIR__ . '/../src/Languages/' . $code . '/validation.php';
			$this->assertEquals([
				'alpha',
				'alphaNumber',
				'number',
				'uuid',
				'timezone',
				'base64',
				'json',
				'regex',
				'email',
				'in',
				'notIn',
				'ip',
				'url',
				'datetime',
				'between',
				'notBetween',
				'equals',
				'notEquals',
				'maxLength',
				'minLength',
				'length',
				'required',
				'isset',
				'latin',
			], \array_keys($lines));
		}
	}
}
