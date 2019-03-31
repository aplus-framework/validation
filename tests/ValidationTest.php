<?php namespace Tests\Validation;

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
	/**
	 * @var ValidationMock
	 */
	protected $validation;

	public function setup()
	{
		$this->validation = new ValidationMock([]);
	}

	public function testParseRule()
	{
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => [],
			],
			$this->validation->parseRule('foo')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => [
					'bar:baz',
				],
			],
			$this->validation->parseRule('foo:bar:baz')
		);
		$this->assertEquals(
			[
				'rule' => 'fo,o',
				'params' => [
					'bar:baz',
				],
			],
			$this->validation->parseRule('fo,o:bar:baz')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => [
					'param',
				],
			],
			$this->validation->parseRule('foo:param')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => [
					'param',
					'param2',
				],
			],
			$this->validation->parseRule('foo:param,param2')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => [
					'  param',
					' param2 ',
				],
			],
			$this->validation->parseRule('foo:  param, param2 ')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => [
					'param',
					'param2',
					'param3',
				],
			],
			$this->validation->parseRule('foo:param,param2,param3')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => [
					'param',
					'param2,param3',
				],
			],
			$this->validation->parseRule('foo:param,param2\,param3')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => [
					'param',
					'param2\,param3',
				],
			],
			$this->validation->parseRule('foo:param,param2\\\,param3')
		);
	}
}
