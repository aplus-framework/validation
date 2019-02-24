<?php namespace Tests\Validation;

use Framework\Validation\Validation;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
	/**
	 * @var Validation
	 */
	protected $validation;

	public function setup()
	{
		$this->validation = new Validation();
	}

	public function testSample()
	{
		$this->assertEquals(
			'Framework\Validation\Validation::test',
			$this->validation->test()
		);
	}
}
