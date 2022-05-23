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
    $validation->setRule('email', 'required|email');

    $validated = $validation->validate($_POST);

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

First load the Validation class. Then the rules are drafted and finally validated.
Then a response is shown if the validation was valid or not.

Setting Rules
-------------

Rules can be set individually by the ``setRule`` method or several at once by
``setRules``. The first argument is the name of the field and the second is the
rules, which can be defined by string separating them with a pipe or by having
an array of rules as values.

.. code-block:: php

    $validation->setRule('email', 'required|email');
    $validation->setRule('firstname', ['required', 'minLength:2']);

    $validation->setRules([
        'lastname' => 'required|minLength:2|maxLength:32'
    ]);

Setting Labels
--------------

Error messages show field name as default. And often you need to show a custom
label like **First Name** instead of **firstname**.

Labels can be defined individually or by an array.:

.. code-block:: php

    $validation->setLabel('email', 'E-mail');
    $validation->setLabel('firstname', 'First Name');
    // or
    $validation->setLabels([
        'email' => 'E-mail',
        'firstname' => 'First Name',
    ]);

Getting Errors
--------------

Errors can be obtained individually or all at once, as per the example below:

.. code-block:: php

    // Email field error message, or null
    $error = $validation->getError('email');

    // All errors
    $errors = $validation->getErrors();

Validating
----------

After defining the rules and labels, the validation of the received data occurs
through the ``validate`` method.

If you only need to validate the received fields, you can use the ``validateOnly``
method. Useful for updating only a few fields in the database.

.. code-block:: php

    // Validates all fields
    $validated = $validation->validate($data);

    // Validates only received fields
    $validated = $validation->validateOnly($data);

Validator Check
###############

To validate only one field is possible to use only the Validator:

.. code-block:: php

    use Framework\Validation\Validator;

    $validated = Validator::alpha('name', $data);

Working with Arrays
-------------------

Validator uses the `ArraySimple <https://gitlab.com/aplus-framework/libraries/helpers>`_
class to extract fields and get the correct data value.

.. code-block:: php

    use Framework\Validation\Validation;
    
    $validation = new Validation();
    $validation->setLabel('user[pass]', 'Password')
               ->setRule('user[pass]', 'required');

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
    
    $validation->setRule('telephone', 'required|phone');

    $validated = $validation->validate($_POST);

    $errors = $validation->getErrors();

Available Rules
---------------

The available rules are:

alpha
#####

The field requires only alphabetic characters.

alphaNumber
###########

The field requires only alphabetic and numeric characters.

base64
######

The field requires a valid base64 string.

between
#######

The field must be between ``{0}`` and ``{1}``.

datetime
########

The field does not match the required datetime format.

dim
###

The field requires an image with the exact dimensions of ``{0}`` in width and ``{1}`` in height.

email
#####

The field requires a valid email address.

equals
######

The field must be equals the ``{0}`` field.

ext
###

The field requires a file with an accepted extension: ``{args}``.

greater
#######

The field must be greater than ``{0}``.

greaterOrEqual
##############

The field must be greater than or equal to ``{0}``.

hex
###

The field requires a valid hexadecimal string.

hexColor
########

The field requires a valid hexadecimal color.

image
#####

The field requires an image.

in
##

The field does not have an allowed value.

ip
##

The field requires a valid IP address.

isset
#####

The field must be sent.

json
####

The field requires a valid JSON string.

latin
#####

The field requires only latin characters.

length
######

The field requires exactly ``{0}`` characters in length.

less
####

The field must be less than ``{0}``.

lessOrEqual
###########

The field must be less than or equal to ``{0}``.

maxDim
######

The field requires an image that does not exceed the maximum dimensions of ``{0}`` in width and ``{1}`` in height.

maxLength
#########

The field requires ``{0}`` or less characters in length.

maxSize
#######

The field requires a file that does not exceed the maximum size of ``{0}`` kilobytes.

md5
###

The field requires a valid MD5 hash.

mimes
#####

The field requires a file with an accepted MIME type: ``{args}``.

minDim
######

The field requires an image having the minimum dimensions of ``{0}`` in width and ``{1}`` in height.

minLength
#########

The field requires ``{0}`` or more characters in length.

notBetween
##########

The field can not be between ``{0}`` and ``{1}``.

notEquals
#########

The field can not be equals the ``{0}`` field.

notIn
#####

The field has a disallowed value.

notRegex
########

The field matches a invalid pattern.

number
######

The field requires only numeric characters.

optional
########

The field is optional. If not present, validation passes.

regex
#####

The field does not matches the required pattern.

required
########

The field is required.

specialChar
###########

The field requires special characters.

timezone
########

The field requires a valid timezone.

uploaded
########

The field requires a file to be uploaded.

url
###

The field requires a valid URL address.

uuid
####

The field requires a valid UUID.

Conclusion
----------

Aplus Validation Library is an easy-to-use tool for, beginners and experienced, PHP developers. 
It is perfect for validating data coming from a form or API. 
The more you use it, the more you will learn.

.. note::
    Did you find something wrong? 
    Be sure to let us know about it with an
    `issue <https://gitlab.com/aplus-framework/libraries/validation/issues>`_. 
    Thank you!
