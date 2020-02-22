<?php namespace Tests\Validation;

use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
	protected array $array;

	protected function setUp() : void
	{
		$this->array = [
			'alpha' => 'abc',
			'equals-alpha' => 'abc',
			'number' => 123,
			'alphaNumber' => 'abc123',
			'timezone' => 'America/Sao_Paulo',
			'base64' => 'YQ==', // a
			'md5' => '0cc175b9c0f1b6a831c399e269772661', // a
			'hex' => '61', // a
			'json' => '{"a":1}',
			'empty' => '',
			'email' => 'a@b.c',
			'email-false' => 'a@b',
			'year' => '2018',
			'datetime' => '2018-04-02 13:50:00',
			'latin' => 'Coração',
			'phrase' => 'Mens sana in corpore sano',
			'ipv4' => '127.0.0.1',
			'ipv6' => 'ff02::1',
			'url' => 'http://domain.tld/path?foo=bar#id',
			'url-false' => 'httd://domain.tld/path?foo=bar#id',
			'uuid' => 'b2b6ec94-5679-11e9-8647-d663bd873d93',
			'uuid-zero' => '00000000-0000-0000-0000-000000000000',
		];
	}

	public function testAlpha()
	{
		$this->assertTrue(Validator::alpha('alpha', $this->array));
		$this->assertFalse(Validator::alpha('alphaNumber', $this->array));
		$this->assertFalse(Validator::alpha('unknown', $this->array));
	}

	public function testNumber()
	{
		$this->assertTrue(Validator::number('number', $this->array));
		$this->assertFalse(Validator::number('alphaNumber', $this->array));
		$this->assertFalse(Validator::number('unknown', $this->array));
	}

	public function testAlphaNumber()
	{
		$this->assertTrue(Validator::alphaNumber('alpha', $this->array));
		$this->assertTrue(Validator::alphaNumber('number', $this->array));
		$this->assertTrue(Validator::alphaNumber('number', $this->array));
		$this->assertFalse(Validator::alphaNumber('timezone', $this->array));
		$this->assertFalse(Validator::alphaNumber('unknown', $this->array));
	}

	public function testTimezone()
	{
		$this->assertTrue(Validator::timezone('timezone', $this->array));
		$this->assertFalse(Validator::timezone('alpha', $this->array));
		$this->assertFalse(Validator::timezone('unknown', $this->array));
	}

	public function testBase64()
	{
		$this->assertTrue(Validator::base64('base64', $this->array));
		$this->assertFalse(Validator::base64('alpha', $this->array));
		$this->assertFalse(Validator::base64('unknown', $this->array));
	}

	public function testMD5()
	{
		$this->assertTrue(Validator::md5('md5', $this->array));
		$this->assertFalse(Validator::md5('alpha', $this->array));
		$this->assertFalse(Validator::md5('unknown', $this->array));
	}

	public function testHex()
	{
		$this->assertTrue(Validator::hex('hex', $this->array));
		$this->assertTrue(Validator::hex('alpha', $this->array));
		$this->assertFalse(Validator::hex('unknown', $this->array));
	}

	public function testJson()
	{
		$this->assertTrue(Validator::json('json', $this->array));
		$this->assertFalse(Validator::json('alpha', $this->array));
		$this->assertFalse(Validator::json('unknown', $this->array));
	}

	public function testMaxLength()
	{
		$this->assertTrue(Validator::maxLength('alpha', $this->array, 3));
		$this->assertTrue(Validator::maxLength('number', $this->array, 3));
		$this->assertFalse(Validator::maxLength('alphaNumber', $this->array, 3));
		$this->assertFalse(Validator::maxLength('unknown', $this->array, 3));
	}

	public function testMinLength()
	{
		$this->assertTrue(Validator::minLength('alpha', $this->array, 3));
		$this->assertTrue(Validator::minLength('number', $this->array, 3));
		$this->assertFalse(Validator::minLength('alpha', $this->array, 4));
		$this->assertFalse(Validator::minLength('unknown', $this->array, 3));
	}

	public function testLength()
	{
		$this->assertTrue(Validator::length('alpha', $this->array, 3));
		$this->assertTrue(Validator::length('number', $this->array, 3));
		$this->assertFalse(Validator::length('alpha', $this->array, 4));
		$this->assertFalse(Validator::length('unknown', $this->array, 3));
	}

	public function testRequired()
	{
		$this->assertTrue(Validator::required('alpha', $this->array));
		$this->assertTrue(Validator::required('number', $this->array));
		$this->assertFalse(Validator::required('empty', $this->array));
		$this->assertFalse(Validator::required('unknown', $this->array));
	}

	public function testIsset()
	{
		$this->assertTrue(Validator::isset('alpha', $this->array));
		$this->assertTrue(Validator::isset('empty', $this->array));
		$this->assertFalse(Validator::isset('unknown', $this->array));
		$this->assertFalse(Validator::isset('unknown-2', $this->array));
	}

	public function testEmail()
	{
		$this->assertTrue(Validator::email('email', $this->array));
		$this->assertFalse(Validator::email('email-false', $this->array));
		$this->assertFalse(Validator::email('unknown', $this->array));
	}

	public function testDatetime()
	{
		$this->assertTrue(Validator::datetime('datetime', $this->array));
		$this->assertTrue(Validator::datetime('year', $this->array, 'Y'));
		$this->assertFalse(Validator::datetime('alpha', $this->array));
		$this->assertFalse(Validator::datetime('unknown', $this->array));
	}

	public function testEquals()
	{
		$this->assertTrue(Validator::equals('alpha', $this->array, 'equals-alpha'));
		$this->assertTrue(Validator::equals('equals-alpha', $this->array, 'alpha'));
		$this->assertFalse(Validator::equals('alpha', $this->array, 'number'));
		$this->assertFalse(Validator::equals('alpha', $this->array, 'unknown'));
		$this->assertFalse(Validator::equals('unknown', $this->array, 'alpha'));
	}

	public function testNotEquals()
	{
		$this->assertFalse(Validator::notEquals('alpha', $this->array, 'equals-alpha'));
		$this->assertFalse(Validator::notEquals('equals-alpha', $this->array, 'alpha'));
		$this->assertTrue(Validator::notEquals('alpha', $this->array, 'number'));
		$this->assertTrue(Validator::notEquals('alpha', $this->array, 'unknown'));
		$this->assertTrue(Validator::notEquals('unknown', $this->array, 'alpha'));
	}

	public function testBetween()
	{
		$this->assertTrue(Validator::between('alpha', $this->array, 'a', 'b'));
		$this->assertTrue(Validator::between('number', $this->array, 120, 123));
		$this->assertFalse(Validator::between('alpha', $this->array, 'b', 'c'));
		$this->assertFalse(Validator::between('unknown', $this->array, 1, 2));
	}

	public function testNotBetween()
	{
		$this->assertFalse(Validator::notBetween('alpha', $this->array, 'a', 'b'));
		$this->assertFalse(Validator::notBetween('number', $this->array, 120, 123));
		$this->assertTrue(Validator::notBetween('alpha', $this->array, 'b', 'c'));
		$this->assertTrue(Validator::notBetween('unknown', $this->array, 1, 2));
	}

	public function testIn()
	{
		$this->assertTrue(Validator::in('alpha', $this->array, 'a', 'abc', 'def'));
		$this->assertTrue(Validator::in('number', $this->array, 120, 123, 456));
		$this->assertFalse(Validator::in('alpha', $this->array, 'b', 'c', 'd'));
		$this->assertFalse(Validator::in('unknown', $this->array, 1, 2, 3));
	}

	public function testNotIn()
	{
		$this->assertFalse(Validator::notIn('alpha', $this->array, 'a', 'abc', 'def'));
		$this->assertFalse(Validator::notIn('number', $this->array, 120, 123, 456));
		$this->assertTrue(Validator::notIn('alpha', $this->array, 'b', 'c', 'd'));
		$this->assertTrue(Validator::notIn('unknown', $this->array, 1, 2, 3));
	}

	public function testLatin()
	{
		$this->assertTrue(Validator::latin('alpha', $this->array));
		$this->assertTrue(Validator::latin('latin', $this->array));
		$this->assertFalse(Validator::latin('phrase', $this->array));
		$this->assertFalse(Validator::latin('number', $this->array));
		$this->assertFalse(Validator::latin('unknown', $this->array));
	}

	public function testIp()
	{
		$this->assertTrue(Validator::ip('ipv4', $this->array));
		$this->assertTrue(Validator::ip('ipv6', $this->array));
		$this->assertTrue(Validator::ip('ipv4', $this->array, 4));
		$this->assertTrue(Validator::ip('ipv6', $this->array, 6));
		$this->assertFalse(Validator::ip('ipv4', $this->array, 6));
		$this->assertFalse(Validator::ip('ipv6', $this->array, 4));
		$this->assertFalse(Validator::ip('unknown', $this->array));
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid IP Version: 7');
		Validator::ip('ipv4', $this->array, 7);
	}

	public function testUrl()
	{
		$this->assertTrue(Validator::url('url', $this->array));
		$this->assertTrue(Validator::url('alpha', $this->array));
		$this->assertFalse(Validator::url('url-false', $this->array));
		$this->assertFalse(Validator::url('json', $this->array));
		$this->assertFalse(Validator::url('unknown', $this->array));
	}

	public function testRegex()
	{
		$this->assertTrue(Validator::regex('alpha', $this->array, '/[a-z]/'));
		$this->assertTrue(Validator::regex('number', $this->array, '/[0-9]/'));
		$this->assertFalse(Validator::regex('alpha', $this->array, '/[0-9]/'));
		$this->assertFalse(Validator::regex('unknown', $this->array, '/[0-9]/'));
	}

	public function testNotRegex()
	{
		$this->assertFalse(Validator::notRegex('alpha', $this->array, '/[a-z]/'));
		$this->assertFalse(Validator::notRegex('number', $this->array, '/[0-9]/'));
		$this->assertTrue(Validator::notRegex('alpha', $this->array, '/[0-9]/'));
		$this->assertTrue(Validator::notRegex('unknown', $this->array, '/[0-9]/'));
	}

	public function testUuid()
	{
		$this->assertTrue(Validator::uuid('uuid', $this->array));
		$this->assertFalse(Validator::uuid('alpha', $this->array));
		$this->assertFalse(Validator::uuid('uuid-zero', $this->array));
		$this->assertFalse(Validator::uuid('unknown', $this->array));
	}
}
