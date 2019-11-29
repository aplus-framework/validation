# Validation Library *documentation*


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

```php
$validation->setRule('email', 'required|email');
$validation->setRule('firstname', ['required', 'minLength:2']);
$validation->setRule('lastname', 'required|minLength:2|maxLength:32');
```

```php
$validation->setLabel('email', 'E-mail');
$validation->setLabel('firstname', 'First Name');
// or
$validation->setLabels([
    'email' => 'E-mail',
    'firstname' => 'First Name',
]);
```

```php
$validation->getError('email');
$validation->getErrors();
```

```php
$validated = $validation->validate($_POST);
$validated = $validation->validateOnly($_POST);
```

## Available Rules

| Rule | Has Parameters | Description |
| --- | --- | --- |
| alpha | no | Validates alphabetic caracters |
| number | no | Validates a number |
| alphaNumber | no | Validates a number or alphabetic characters |
| uuid | no | Validates a UUID |
| timezone | no | Validates a timezone |
| base64 | no | Validates a base64 string |
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
| latin | yes | Validates a latin text |
| maxLength | yes | Validates max length |
| minLength | yes | Validates min length |
| length | yes |  Validates exact length |
| required | no | Validates required value |
| isset | no | Validates field is set |
| optional | no | Not run rules if the field is empty |
