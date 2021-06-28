<?php
/*
 * This file is part of The Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Validation;

use Framework\Validation\FilesValidator;

class FilesValidatorMock extends FilesValidator
{
	public static function getOrganizedFiles() : array
	{
		return parent::getOrganizedFiles();
	}

	public static function uploaded(string $field, array $data = []) : bool
	{
		$file = static::getFile($field);
		if ($file === null) {
			return false;
		}
		return \is_file($file['tmp_name']);
	}
}
