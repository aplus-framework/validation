# Validation Library *documentation*

Validation logic typically occurs as follows:

```php
$validation = new \Framework\Validation\Validation();
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
```

First load the Validation class. Then the rules are drafted and finally validated.
Then an answer is shown if the validation was valid or not.

## Setting rules

Rules can be set individually by the `setRule` method or several at once by 
`setRules`. The first argument is the name of the field and the second is the
rules, which can be defined by string separating them with a pipe or by having
an array of rules as values.

```php
$validation->setRule('email', 'required|email');
$validation->setRule('firstname', ['required', 'minLength:2']);
$validation->setRules([
    'lastname' => 'required|minLength:2|maxLength:32'
]);
```

## Setting labels

Error messages show field name as default. And often you need to show a custom
label like *First Name* instead of *firstname*.

Labels can be defined individually or by an array.:

```php
$validation->setLabel('email', 'E-mail');
$validation->setLabel('firstname', 'First Name');
// or
$validation->setLabels([
    'email' => 'E-mail',
    'firstname' => 'First Name',
]);
```

## Getting errors

Errors can be obtained individually or all at once, as per the example below:

```php
// Email field error message, or null
$error = $validation->getError('email');
// All errors
$errors = $validation->getErrors();
```

## Validating

After defining the rules and labels, the validation of the received data occurs
through the `validate` method.

If you only need to validate the received fields, you can use the `validateOnly`
method. Useful for updating only a few fields in the database.

```php
// Validates all fields
$validated = $validation->validate($_POST);
// Validates only received fields
$validated = $validation->validateOnly($_POST);
```

### Validator check

To validate only one field is possible to use only the Validator:

```php
$validated = Validator::alpha('name', $_POST);
```

## Working with arrays

Validator uses the [ArraySimple](https://github.com/natanfelles/array-simple) class to extract fields and get the correct data
value.

```php
use Framework\Validation\Validation;

$validation = new Validation();
$validation->setLabel('user[pass]', 'Password')
           ->setRule('user[pass]', 'required');

$validated = $validation->validate([
    'user' => ['pass' => 'secret']
]); // true
```

## Custom Validator

It is possible to create a validator with your custom rules.

```php
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
```

Do not forget to create the validation language file with your rules.

File `Languages/en/validation.php`:

```php
return [
    'phone' => 'The {field} field requires a valid phone number.'
];
```

So, let the Validation know about your customizations:

```php
use Framework\Language\Language;
use Framework\Validation\Validation;
use CustomValidator;

$lang = new Language('en');
$lang->addDirectory(__DIR__ . '/Languages');
$validation = new Validation([CustomValidator::class], $lang);
$validation->setRule('telephone', 'required|phone');
$validated = $validation->validate($_POST);
$errors = $validation->getErrors();
```

## Available Rules

The rules available in Validator are:

| Rule | Has Parameters | Description |
| --- | --- | --- |
| alpha | no | Validates alphabetic caracters |
| number | no | Validates a number |
| alphaNumber | no | Validates a number or alphabetic characters |
| uuid | no | Validates a UUID |
| timezone | no | Validates a timezone |
| base64 | no | Validates a base64 string |
| md5 | no | Validates a md5 hash |
| hex | no | Validates a hexadecimal string |
| json | no | Validates a JSON string |
| regex | yes | Validates a Regex pattern |
| notRegex | yes | Validates a Regex no matching pattern |
| equals | yes | Validate field has value equals other field |
| notEquals | yes | Validate field has not value equals other field |
| between | yes | Validate field between min and max values |
| notBetween | yes | Validate field not between min and max values |
| in | yes | Validate field is in list |
| notIn | yes | Validate field is not in list |
| ip | yes | Validates an IP |
| url | no | Validates an URL |
| datetime | yes | Validates a datetime format |
| email | no | Validates a email |
| latin | no | Validates a latin text |
| maxLength | yes | Validates max length |
| minLength | yes | Validates min length |
| length | yes |  Validates exact length |
| required | no | Validates required value |
| isset | no | Validates field is set |
| optional | no | Not run rules if the field is empty |
