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

use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    /**
     * @var array<string,int|string>
     */
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

    public function testAlpha() : void
    {
        self::assertTrue(Validator::alpha('alpha', $this->array));
        self::assertFalse(Validator::alpha('alphaNumber', $this->array));
        self::assertFalse(Validator::alpha('unknown', $this->array));
    }

    public function testNumber() : void
    {
        self::assertTrue(Validator::number('number', $this->array));
        self::assertFalse(Validator::number('alphaNumber', $this->array));
        self::assertFalse(Validator::number('unknown', $this->array));
    }

    public function testAlphaNumber() : void
    {
        self::assertTrue(Validator::alphaNumber('alpha', $this->array));
        self::assertTrue(Validator::alphaNumber('number', $this->array));
        self::assertTrue(Validator::alphaNumber('number', $this->array));
        self::assertFalse(Validator::alphaNumber('timezone', $this->array));
        self::assertFalse(Validator::alphaNumber('unknown', $this->array));
    }

    public function testTimezone() : void
    {
        self::assertTrue(Validator::timezone('timezone', $this->array));
        self::assertFalse(Validator::timezone('alpha', $this->array));
        self::assertFalse(Validator::timezone('unknown', $this->array));
    }

    public function testBase64() : void
    {
        self::assertTrue(Validator::base64('base64', $this->array));
        self::assertFalse(Validator::base64('alpha', $this->array));
        self::assertFalse(Validator::base64('unknown', $this->array));
    }

    public function testMD5() : void
    {
        self::assertTrue(Validator::md5('md5', $this->array));
        self::assertFalse(Validator::md5('alpha', $this->array));
        self::assertFalse(Validator::md5('unknown', $this->array));
    }

    public function testHex() : void
    {
        self::assertTrue(Validator::hex('hex', $this->array));
        self::assertTrue(Validator::hex('alpha', $this->array));
        self::assertFalse(Validator::hex('unknown', $this->array));
    }

    public function testHexColor() : void
    {
        $data = [
            '3-nohash' => 'abc',
            '3-ok' => '#abc',
            '3-invalid' => '#abg',
            '6-nohash' => 'abc123',
            '6-ok' => '#abc123',
            '6-invalid' => '#abh123',
        ];
        self::assertFalse(Validator::hexColor('3-nohash', $data));
        self::assertTrue(Validator::hexColor('3-ok', $data));
        self::assertFalse(Validator::hexColor('3-invalid', $data));
        self::assertFalse(Validator::hexColor('6-nohash', $data));
        self::assertTrue(Validator::hexColor('6-ok', $data));
        self::assertFalse(Validator::hexColor('6-invalid', $data));
    }

    public function testJson() : void
    {
        self::assertTrue(Validator::json('json', $this->array));
        self::assertFalse(Validator::json('alpha', $this->array));
        self::assertFalse(Validator::json('unknown', $this->array));
    }

    public function testMaxLength() : void
    {
        self::assertTrue(Validator::maxLength('alpha', $this->array, 3));
        self::assertTrue(Validator::maxLength('number', $this->array, 3));
        self::assertFalse(Validator::maxLength('alphaNumber', $this->array, 3));
        self::assertFalse(Validator::maxLength('unknown', $this->array, 3));
    }

    public function testMinLength() : void
    {
        self::assertTrue(Validator::minLength('alpha', $this->array, 3));
        self::assertTrue(Validator::minLength('number', $this->array, 3));
        self::assertFalse(Validator::minLength('alpha', $this->array, 4));
        self::assertFalse(Validator::minLength('unknown', $this->array, 3));
    }

    public function testLength() : void
    {
        self::assertTrue(Validator::length('alpha', $this->array, 3));
        self::assertTrue(Validator::length('number', $this->array, 3));
        self::assertFalse(Validator::length('alpha', $this->array, 4));
        self::assertFalse(Validator::length('unknown', $this->array, 3));
    }

    public function testRequired() : void
    {
        self::assertTrue(Validator::required('alpha', $this->array));
        self::assertTrue(Validator::required('number', $this->array));
        self::assertFalse(Validator::required('empty', $this->array));
        self::assertFalse(Validator::required('unknown', $this->array));
    }

    public function testIsset() : void
    {
        self::assertTrue(Validator::isset('alpha', $this->array));
        self::assertTrue(Validator::isset('empty', $this->array));
        self::assertFalse(Validator::isset('unknown', $this->array));
        self::assertFalse(Validator::isset('unknown-2', $this->array));
    }

    public function testEmail() : void
    {
        self::assertTrue(Validator::email('email', $this->array));
        self::assertFalse(Validator::email('email-false', $this->array));
        self::assertFalse(Validator::email('unknown', $this->array));
    }

    public function testDatetime() : void
    {
        self::assertTrue(Validator::datetime('datetime', $this->array));
        self::assertTrue(Validator::datetime('year', $this->array, 'Y'));
        self::assertFalse(Validator::datetime('alpha', $this->array));
        self::assertFalse(Validator::datetime('unknown', $this->array));
    }

    public function testEquals() : void
    {
        self::assertTrue(Validator::equals('alpha', $this->array, 'equals-alpha'));
        self::assertTrue(Validator::equals('equals-alpha', $this->array, 'alpha'));
        self::assertFalse(Validator::equals('alpha', $this->array, 'number'));
        self::assertFalse(Validator::equals('alpha', $this->array, 'unknown'));
        self::assertFalse(Validator::equals('unknown', $this->array, 'alpha'));
    }

    public function testNotEquals() : void
    {
        self::assertFalse(Validator::notEquals('alpha', $this->array, 'equals-alpha'));
        self::assertFalse(Validator::notEquals('equals-alpha', $this->array, 'alpha'));
        self::assertTrue(Validator::notEquals('alpha', $this->array, 'number'));
        self::assertTrue(Validator::notEquals('alpha', $this->array, 'unknown'));
        self::assertTrue(Validator::notEquals('unknown', $this->array, 'alpha'));
    }

    public function testBetween() : void
    {
        self::assertTrue(Validator::between('alpha', $this->array, 'a', 'b'));
        self::assertTrue(Validator::between('number', $this->array, 120, 123));
        self::assertFalse(Validator::between('alpha', $this->array, 'b', 'c'));
        self::assertFalse(Validator::between('unknown', $this->array, 1, 2));
    }

    public function testNotBetween() : void
    {
        self::assertFalse(Validator::notBetween('alpha', $this->array, 'a', 'b'));
        self::assertFalse(Validator::notBetween('number', $this->array, 120, 123));
        self::assertTrue(Validator::notBetween('alpha', $this->array, 'b', 'c'));
        self::assertTrue(Validator::notBetween('unknown', $this->array, 1, 2));
    }

    public function testIn() : void
    {
        self::assertTrue(Validator::in('alpha', $this->array, 'a', 'abc', 'def'));
        // @phpstan-ignore-next-line
        self::assertTrue(Validator::in('number', $this->array, 120, 123, 456));
        self::assertFalse(Validator::in('alpha', $this->array, 'b', 'c', 'd'));
        // @phpstan-ignore-next-line
        self::assertFalse(Validator::in('unknown', $this->array, 1, 2, 3));
    }

    public function testNotIn() : void
    {
        self::assertFalse(Validator::notIn('alpha', $this->array, 'a', 'abc', 'def'));
        // @phpstan-ignore-next-line
        self::assertFalse(Validator::notIn('number', $this->array, 120, 123, 456));
        self::assertTrue(Validator::notIn('alpha', $this->array, 'b', 'c', 'd'));
        // @phpstan-ignore-next-line
        self::assertTrue(Validator::notIn('unknown', $this->array, 1, 2, 3));
    }

    public function testLatin() : void
    {
        self::assertTrue(Validator::latin('alpha', $this->array));
        self::assertTrue(Validator::latin('latin', $this->array));
        self::assertFalse(Validator::latin('phrase', $this->array));
        self::assertFalse(Validator::latin('number', $this->array));
        self::assertFalse(Validator::latin('unknown', $this->array));
    }

    public function testIp() : void
    {
        self::assertTrue(Validator::ip('ipv4', $this->array));
        self::assertTrue(Validator::ip('ipv6', $this->array));
        self::assertTrue(Validator::ip('ipv4', $this->array, 4));
        self::assertTrue(Validator::ip('ipv6', $this->array, 6));
        self::assertFalse(Validator::ip('ipv4', $this->array, 6));
        self::assertFalse(Validator::ip('ipv6', $this->array, 4));
        self::assertFalse(Validator::ip('unknown', $this->array));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid IP Version: 7');
        Validator::ip('ipv4', $this->array, 7);
    }

    public function testUrl() : void
    {
        self::assertTrue(Validator::url('url', $this->array));
        self::assertTrue(Validator::url('alpha', $this->array));
        self::assertFalse(Validator::url('url-false', $this->array));
        self::assertFalse(Validator::url('json', $this->array));
        self::assertFalse(Validator::url('unknown', $this->array));
    }

    public function testRegex() : void
    {
        self::assertTrue(Validator::regex('alpha', $this->array, '/[a-z]/'));
        self::assertTrue(Validator::regex('number', $this->array, '/[0-9]/'));
        self::assertFalse(Validator::regex('alpha', $this->array, '/[0-9]/'));
        self::assertFalse(Validator::regex('unknown', $this->array, '/[0-9]/'));
    }

    public function testNotRegex() : void
    {
        self::assertFalse(Validator::notRegex('alpha', $this->array, '/[a-z]/'));
        self::assertFalse(Validator::notRegex('number', $this->array, '/[0-9]/'));
        self::assertTrue(Validator::notRegex('alpha', $this->array, '/[0-9]/'));
        self::assertTrue(Validator::notRegex('unknown', $this->array, '/[0-9]/'));
    }

    public function testUuid() : void
    {
        self::assertTrue(Validator::uuid('uuid', $this->array));
        self::assertFalse(Validator::uuid('alpha', $this->array));
        self::assertFalse(Validator::uuid('uuid-zero', $this->array));
        self::assertFalse(Validator::uuid('unknown', $this->array));
    }

    public function testGreater() : void
    {
        self::assertTrue(Validator::greater('number', $this->array, 122));
        self::assertFalse(Validator::greater('number', $this->array, 123));
        self::assertTrue(Validator::greater('alpha', $this->array, 'abb'));
        self::assertFalse(Validator::greater('alpha', $this->array, 'abc'));
    }

    public function testGreaterOrEqual() : void
    {
        self::assertTrue(Validator::greaterOrEqual('number', $this->array, 122));
        self::assertTrue(Validator::greaterOrEqual('number', $this->array, 123));
        self::assertFalse(Validator::greaterOrEqual('number', $this->array, 124));
        self::assertTrue(Validator::greaterOrEqual('alpha', $this->array, 'abb'));
        self::assertTrue(Validator::greaterOrEqual('alpha', $this->array, 'abc'));
        self::assertFalse(Validator::greaterOrEqual('alpha', $this->array, 'abd'));
    }

    public function testLess() : void
    {
        self::assertTrue(Validator::less('number', $this->array, 124));
        self::assertFalse(Validator::less('number', $this->array, 123));
        self::assertTrue(Validator::less('alpha', $this->array, 'abd'));
        self::assertFalse(Validator::less('alpha', $this->array, 'abc'));
    }

    public function testLessOrEqual() : void
    {
        self::assertTrue(Validator::lessOrEqual('number', $this->array, 124));
        self::assertTrue(Validator::lessOrEqual('number', $this->array, 123));
        self::assertFalse(Validator::lessOrEqual('number', $this->array, 122));
        self::assertTrue(Validator::lessOrEqual('alpha', $this->array, 'abd'));
        self::assertTrue(Validator::lessOrEqual('alpha', $this->array, 'abc'));
        self::assertFalse(Validator::lessOrEqual('alpha', $this->array, 'abb'));
    }

    public function testSpecialChar() : void
    {
        $data = [
            'p1' => 'abcde',
            'p2' => 'a@cde',
            'p3' => 'a@c%!',
            'p4' => 'çb♦a♥',
            'p5' => '  0 0',
        ];
        self::assertFalse(Validator::specialChar('p0', $data));
        self::assertFalse(Validator::specialChar('p1', $data));
        self::assertTrue(Validator::specialChar('p2', $data));
        self::assertFalse(Validator::specialChar('p2', $data, 3));
        self::assertTrue(Validator::specialChar('p3', $data, 3));
        self::assertFalse(Validator::specialChar('p3', $data, 3, '♦♥ç'));
        self::assertTrue(Validator::specialChar('p4', $data, 3, '♦♥ç'));
        self::assertFalse(Validator::specialChar('p5', $data, 3, ' 0'));
        self::assertFalse(Validator::specialChar('p5', $data, 2, '0'));
        self::assertTrue(Validator::specialChar('p5', $data, 1, '0'));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Special characters quantity must be greater than 0');
        Validator::specialChar('p4', $data, 0);
    }
}
