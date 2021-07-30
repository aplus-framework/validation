<?php
/*
 * This file is part of Aplus Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
return [
    'alpha' => 'The {field} field requires only alphabetic characters.',
    'alphaNumber' => 'The {field} field requires only alphabetic and numeric characters.',
    'number' => 'The {field} field requires only numeric characters.',
    'uuid' => 'The {field} field requires a valid UUID.',
    'timezone' => 'The {field} field requires a valid timezone.',
    'base64' => 'The {field} field requires a valid base64 string.',
    'md5' => 'The {field} field requires a valid MD5 hash.',
    'hex' => 'The {field} field requires a valid hexadecimal string.',
    'json' => 'The {field} field requires a valid JSON string.',
    'regex' => 'The {field} field does not matches the required pattern.',
    'notRegex' => 'The {field} field matches a invalid pattern.',
    'email' => 'The {field} field requires a valid email address.',
    'in' => 'The {field} field does not have an allowed value.',
    'notIn' => 'The {field} field has a disallowed value.',
    'ip' => 'The {field} field requires a valid IP address.',
    'url' => 'The {field} field requires a valid URL address.',
    'datetime' => 'The {field} field does not match the required datetime format.',
    'between' => 'The {field} field must be between {0} and {1}.',
    'notBetween' => 'The {field} field can not be between {0} and {1}.',
    'equals' => 'The {field} field must be equals the {0} field.',
    'notEquals' => 'The {field} field can not be equals the {0} field.',
    'maxLength' => 'The {field} field requires {0} or less characters in length.',
    'minLength' => 'The {field} field requires {0} or more characters in length.',
    'length' => 'The {field} field requires exactly {0} characters in length.',
    'required' => 'The {field} field is required.',
    'isset' => 'The {field} field must be sent.',
    'latin' => 'The {field} field requires only latin characters.',
    'uploaded' => 'The {field} field requires a file to be uploaded.',
    'maxSize' => 'The {field} field requires a file that does not exceed the maximum size of {0} kilobytes.',
    'ext' => 'The {field} field requires a file with an accepted extension: {args}.',
    'mimes' => 'The {field} field requires a file with an accepted MIME type: {args}.',
    'image' => 'The {field} field requires an image.',
    'maxDim' => 'The {field} field requires an image that does not exceed the maximum dimensions of {0} in width and {1} in height.',
    'minDim' => 'The {field} field requires an image having the minimum dimensions of {0} in width and {1} in height.',
    'dim' => 'The {field} field requires an image with the exact dimensions of {0} in width and {1} in height.',
];
