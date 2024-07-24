Validation
==========

.. image:: image.png
    :alt: Aplus Framework Validation Library

Aplus Framework Validation Library.

- `Installation`_
- `Basic Usage`_
- `Setting Rules`_
- `Setting Labels`_
- `Getting Errors`_
- `Validating`_
- `Working with Arrays`_
- `Custom Validator`_
- `Available Rules`_
- `Conclusion`_

Installation
------------

The installation of this library can be done with Composer:

.. code-block::

    composer require aplus/validation

Basic Usage
-----------

Validation logic typically occurs as follows:

.. code-block:: php

    use Framework\Validation\Validation;

    $validation = new Validation();
    $validation->setRule('email', 'required|email'); // static

    $validated = $validation->validate($_POST); // bool

    if ($validated) {
        echo 'Validated!';
    } else {
        echo 'Invalid data:';
        echo '<ul>';
        foreach ($validation->getErrors() as $error) {
            echo "<li>{$error}</li>";
        }
        echo '</ul>';
    }

First load the Validation class. Then the rules are set and finally validated.
Then a response is shown if the validation was valid or not.

Setting Rules
-------------

Rules can be set individually by the ``setRule`` method or several at once by
``setRules``. The first argument is the name of the field and the second is the
rules, which can be defined by string separating them with a pipe or by having
an array of rules as values.

.. code-block:: php

    $validation->setRule('email', 'required|email'); // static
    $validation->setRule('firstname', ['required', 'minLength:2']); // static

    $validation->setRules([
        'lastname' => 'required|minLength:2|maxLength:32'
    ]); // static

Setting Labels
--------------

Error messages show field name as default. And often you need to show a custom
label like **First Name** instead of **firstname**.

Labels can be defined individually or by an array.:

.. code-block:: php

    $validation->setLabel('email', 'E-mail'); // static
    $validation->setLabel('firstname', 'First Name'); // static
    // or
    $validation->setLabels([
        'email' => 'E-mail',
        'firstname' => 'First Name',
    ]); // static

Getting Errors
--------------

Errors can be obtained individually or all at once, as per the example below:

.. code-block:: php

    // Email field error message, or null
    $error = $validation->getError('email'); // string or null

    // All errors
    $errors = $validation->getErrors(); // array

Validating
----------

After defining the rules and labels, the validation of the received data occurs
through the ``validate`` method.

If you only need to validate the received fields, you can use the ``validateOnly``
method. Useful for updating only a few fields in the database.

.. code-block:: php

    // Validates all fields
    $validated = $validation->validate($data); // bool

    // Validates only received fields
    $validated = $validation->validateOnly($data); // bool

Validator Check
###############

To validate only one field is possible to use only the Validator:

.. code-block:: php

    use Framework\Validation\Validator;

    $validated = Validator::alpha('name', $data); // bool

Working with Arrays
-------------------

Validator uses the `ArraySimple <https://github.com/aplus-framework/helpers>`_
class to extract fields and get the correct data value.

.. code-block:: php

    use Framework\Validation\Validation;
    
    $validation = new Validation();
    $validation->setLabel('user[pass]', 'Password') // static
               ->setRule('user[pass]', 'required'); // static

    $data = [
        'user' => [
            'pass' => 'secret',
        ],
    ];

    $validated = $validation->validate($data); // true

Custom Validator
----------------

It is possible to create a validator with your custom rules.

.. code-block:: php

    use Framework\Validation\Validator;
    
    class CustomValidator extends Validator
    {
        public static function phone(string $field, array $data): bool
        {
            $data = static::getData($field, $data);
            if ($data === null) {
                return false;
            }
            return \preg_match('/^\d{4}-\d{4}$/', $data);        
        }
    }

Do not forget to create the validation language file with your rules.

File **Languages/en/validation.php**:

.. code-block:: php

    return [
        'phone' => 'The {field} field requires a valid phone number.'
    ];

So, let the Validation know about your customizations:

.. code-block:: php

    use CustomValidator;
    use Framework\Language\Language;
    use Framework\Validation\Validation;
    
    $language = new Language();
    $language->addDirectory(__DIR__ . '/Languages');

    $validation = new Validation([CustomValidator::class], $language);
    
    $validation->setRule('telephone', 'required|phone'); // static

    $validated = $validation->validate($_POST); // bool

    $errors = $validation->getErrors(); // array

Available Rules
---------------

The available rules are:

- `alpha`_
- `alphaNumber`_
- `array`_
- `base64`_
- `between`_
- `blank`_
- `bool`_
- `datetime`_
- `dim`_
- `email`_
- `empty`_
- `equals`_
- `ext`_
- `float`_
- `greater`_
- `greaterOrEqual`_
- `hex`_
- `hexColor`_
- `image`_
- `in`_
- `int`_
- `ip`_
- `isset`_
- `json`_
- `latin`_
- `length`_
- `less`_
- `lessOrEqual`_
- `maxDim`_
- `maxLength`_
- `maxSize`_
- `md5`_
- `mimes`_
- `minDim`_
- `minLength`_
- `notBetween`_
- `notEquals`_
- `notIn`_
- `notRegex`_
- `null`_
- `number`_
- `object`_
- `optional`_
- `regex`_
- `required`_
- `slug`_
- `specialChar`_
- `string`_
- `timezone`_
- `uploaded`_
- `url`_
- `uuid`_

alpha
#####

The field requires only alphabetic characters.

.. code-block::

    alpha

alphaNumber
###########

The field requires only alphabetic and numeric characters.

.. code-block::

    alphaNumber

array
#####

The field requires an array.

.. code-block::

    array

base64
######

The field requires a valid base64 string.

.. code-block::

    base64

between
#######

The field must be between ``{0}`` and ``{1}``.

.. code-block:: php

    between:$min,$max

The rule must take two parameters: ``$min`` and ``$max``.

``$min`` is the minimum value.

``$max`` is the maximum value.

blank
#####

If the field has a blank string, the validation passes.

.. code-block::

    blank

bool
####

The field requires a boolean value.

.. code-block::

    bool

datetime
########

The field must match a required datetime format.

.. code-block:: php

    datetime
    datetime:$format

The rule can take one parameter: ``$format``.

``$format`` is the date format. 

By default the format is ``Y-m-d H:i:s``.

dim
###

The field requires an image with the exact dimensions of ``{0}`` in width and ``{1}`` in height.

.. code-block:: php

    dim:$width,$height

The rule must take two parameters: ``$width`` and ``$height``.

``$width`` is the exact width of the image.

``$height`` is the exact height of the image.

email
#####

The field requires a valid email address.

.. code-block::

    email

empty
#####

If the field is defined and has an `empty <https://www.php.net/empty>`_ value,
the validation passes.

.. code-block::

    empty

equals
######

The field must be equals the ``{0}`` field.

.. code-block:: php

    equals:$equalsField

The rule must take one parameter: ``$equalsField``.

``$equalsField`` is the name of the field which must be equal to this one.

ext
###

The field requires a file with an accepted extension: ``{args}``.

.. code-block:: php

    ext:...$allowedExtensions

The rule can take several parameters: ``...$allowedExtensions``.

``...$allowedExtensions`` is a comma-separated list of file extensions.

float
#####

The field requires a floating point number.

.. code-block::

    float

greater
#######

The field must be greater than ``{0}``.

.. code-block:: php

    greater:$greaterThan

The rule must take one parameter: ``$greaterThan``.

``$greaterThan`` is the value the field must be greater than this. 

greaterOrEqual
##############

The field must be greater than or equal to ``{0}``.

.. code-block:: php

    greaterOrEqual:$greaterThanOrEqualTo

The rule must take one parameter: ``$greaterThanOrEqualTo``.

``$greaterThanOrEqualTo`` is the value that the field has greater than or equal to this. 

hex
###

The field requires a valid hexadecimal string.

.. code-block::

    hex

hexColor
########

The field requires a valid hexadecimal color.

.. code-block::

    hexColor

image
#####

The field requires an image.

.. code-block::

    image

in
##

The field must have one of the listed values.

.. code-block:: php

    in:$in,...$others

The rule must take one parameter: ``$in``. And also ``...$others``.

``$in`` is a value required to be in.

``...$others`` are other valid values to be in.

int
###

The field requires an integer.

.. code-block::

    int

ip
##

The field requires a valid IP address.

.. code-block:: php

    ip
    ip:$version

The rule can take one parameter: ``$version``.

``$version`` can be ``0`` for IPv4 and IPv6. ``4`` for IPv4 or ``6`` for IPv6. 

isset
#####

The field must be sent.

.. code-block::

    isset

json
####

The field requires a valid JSON string.

.. code-block::

    json

latin
#####

The field requires only latin characters.

.. code-block::

    latin

length
######

The field requires exactly ``{0}`` characters in length.

.. code-block:: php

    length:$length

The rule can take one parameter: ``$length``.

``$length`` is the exact number of characters the field must receive.

less
####

The field must be less than ``{0}``.

.. code-block:: php

    less:$lessThan

The rule can take one parameter: ``$lessThan``.

``$lessThan`` is the value that the field has less than this.

lessOrEqual
###########

The field must be less than or equal to ``{0}``.

.. code-block:: php

    lessOrEqual:$lessThanOrEqualTo

The rule can take one parameter: ``$lessThanOrEqualTo``.

``$lessThanOrEqualTo`` is the value that the field has less than or equal to this. 

maxDim
######

The field requires an image that does not exceed the maximum dimensions of ``{0}`` in width and ``{1}`` in height.

.. code-block:: php

    maxDim:$width,$height

The rule can take two parameters: ``$width`` and ``$height``.

``$width`` is the maximum width the image can be.

``$height`` is the maximum height the image can be.

maxLength
#########

The field requires ``{0}`` or less characters in length.

.. code-block:: php

    maxLength:$maxLength

The rule can take one parameter: ``$maxLength``.

``$maxLength`` is the maximum amount of characters that the field must receive.

maxSize
#######

The field requires a file that does not exceed the maximum size of ``{0}`` kilobytes.

.. code-block:: php

    maxSize:$kilobytes

The rule can take one parameter: ``$kilobytes``.

``$kilobytes`` is the maximum number of kilobytes that the field file can receive.

md5
###

The field requires a valid MD5 hash.

.. code-block::

    md5

mimes
#####

The field requires a file with an accepted MIME type: ``{args}``.

.. code-block:: php

    mimes:...$allowedTypes

The rule can take many parameters: ``...$allowedTypes``.

``...$allowedTypes`` are the MIME types of files the field can receive.

minDim
######

The field requires an image having the minimum dimensions of ``{0}`` in width and ``{1}`` in height.

.. code-block:: php

    minDim:$width,$height

The rule can take two parameters: ``$width`` and ``$height``.

``$width`` is the minimum width the image can be.

``$height`` is the minimum height the image can be.

minLength
#########

The field requires ``{0}`` or more characters in length.

.. code-block:: php

    minLength:$minLength

The rule can take one parameter: ``$minLength``.

``$minLength`` is the minimum number of characters the field must receive.

notBetween
##########

The field can not be between ``{0}`` and ``{1}``.

.. code-block:: php

    notBetween:$min,$max

The rule can take two parameters: ``$min`` and ``$max``.

``$min`` is the minimum value that the field value must not have.

``$max`` is the maximum value the field value must not have.

notEquals
#########

The field can not be equals the ``{0}`` field.

.. code-block:: php

    notEquals:$diffField

The rule can take one parameter: ``$diffField``.

``$diffField`` is the name of the field that must have a value different from this one.

notIn
#####

The field must have a value other than those listed.

.. code-block:: php

    notIn:$notIn,...$others

The rule can take one parameter: ``$notIn``. And also ``...$others``.

``$notIn`` is the value required not to be in.

``...$others`` are other values to not be in.

notRegex
########

The field matches a invalid pattern.

.. code-block:: php

    notRegex:$pattern

The rule can take one parameter: ``$pattern``.

``$pattern`` is the regular expression that the field value must not match.

null
####

If the field value is null, the validation passes.

.. code-block::

    null

number
######

The field requires only numeric characters.

.. code-block::

    number

object
######

The field requires an object.

.. code-block::

    object

optional
########

The field is optional. If undefined, validation passes.

.. code-block::

    optional

regex
#####

The field must match the required pattern.

.. code-block:: php

    regex:$pattern

The rule can take one parameter: ``$pattern``.

``$pattern`` is the regular expression that the value of the field must match.

required
########

The field is required.

.. code-block::

    required

slug
####

The field requires a valid slug.

.. code-block::

    slug

specialChar
###########

The field requires special characters.

.. code-block:: php

    specialChar
    specialChar:$quantity
    specialChar:$quantity,$characters

The rule can take two parameters:: ``$quantity`` and ``$characters``.

``$quantity`` is the number of special characters the field value must have.
By default the value is ``1``.

``$characters`` are the characters considered special. By default they are these:
``!"#$%&\'()*+,-./:;=<>?@[\]^_`{|}~``. 

string
######

The field requires a string.

.. code-block::

    string

timezone
########

The field requires a valid timezone.

.. code-block::

    timezone

uploaded
########

The field requires a file to be uploaded.

.. code-block::

    uploaded

url
###

The field requires a valid URL address.

.. code-block::

    url

uuid
####

The field requires a valid UUID.

.. code-block::

    uuid

Conclusion
----------

Aplus Validation Library is an easy-to-use tool for, beginners and experienced, PHP developers. 
It is perfect for validating data coming from a form or API. 
The more you use it, the more you will learn.

.. note::
    Did you find something wrong? 
    Be sure to let us know about it with an
    `issue <https://github.com/aplus-framework/validation/issues>`_. 
    Thank you!
