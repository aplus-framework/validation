<?php namespace Tests\Validation;

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
