# Laravel Recaptcha v3
Laravel package for [Google Recaptcha v3](https://developers.google.com/recaptcha/docs/v3)


## Some of the features for Recaptcha:
- Configurable and Customizable
- Callback function
- Service Container
- Auto discovery (only laravel 5.5+)

## Requirements:
- Laravel 5.3 or higher
- PHP 5.6.4 or higher

## Installation

1. ##### Install the package via composer:
    ```shell
    composer require mostafaznv/recaptcha
    ```

2. ##### Register Provider and Facade in config/app.php (Required for Laravel 5.3 and 5.4):
    ```shell
    'providers' => [
      ...
      Mostafaznv\Recaptcha\RecaptchaServiceProvider::class,
    ],
    
    
    'aliases' => [
      ...
      'Recaptcha' => Mostafaznv\Recaptcha\Recaptcha::class,
    ]
    ```

3. ##### Publish config and views:
    ```shell
    php artisan vendor:publish --provider="Mostafaznv\Recaptcha\RecaptchaServiceProvider"
    ```

4. ##### Done

## Configuration
Add `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY` to `.env` file:
```shell
RECAPTCHA_SITE_KEY=your-site-key
RECAPTCHA_SECRET_KEY=your-secret-key
```

 to set more, open config/recaptcha.php and set your own configurations.
```php
return [
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'site_key'   => env('RECAPTCHA_SITE_KEY'),

    'is_active' => true,

    'score' => 0.5,

    'options' => [
        'timeout' => 5.0,
    ]
];
```

## Usage
1. ##### Render JS
    ```php
    {!! Recaptcha::renderJs() !!}
    ```

2. ##### Create Recaptcha field
    this is a hidden input field that gets filled with a recaptcha token.
    ```php
    {!! Recaptcha::field('login') !!}
    ```
3. ##### Validate
    ```php
    $validate = Validator::make(Input::all(), [
       'g-recaptcha-response' => 'required|recaptcha:login'
    ]);
    ```
4. ##### Done

## Custom Validation Message
Add the following values to the `custom` array in the `validation` language file:
```php
'custom' => [     
    'g-recaptcha-response' => [
        'required' => 'Please verify that you are not a robot.',
        'recaptcha' => 'Captcha error! try again later or contact site admin.',
    ],
],
```
Then check for captcha errors in the Form:
```php
@if ($errors->has('g-recaptcha-response'))
    <p>{{ $errors->first('g-recaptcha-response') }}</p>
@endif
```

## Validate Score Manually
Alternatively, you can get the score and take variable action:
```php
$token = $request->get('g-recaptcha-response');
$action = 'home';
$score = 0;

$score = app('recaptcha')->verify($token, $action, $score);

if($score > 0.7) {
    // is valid
} 
else if($score > 0.3) {
    // require additional email verification
} 
else {
    return abort(400, 'Bad request');
}
```

## Methods

#### RenderJs

| Argument Index | Argument Name | Default | Example | Type   | Description |
|----------------|---------------|---------|---------|--------|-------------|
| 0              | language      | null    | fa      | string |             |

#### Field
| Argument Index | Argument Name | Default              | Example                                                         | Type   | Description                                                                                |
|----------------|---------------|----------------------|-----------------------------------------------------------------|--------|--------------------------------------------------------------------------------------------|
| 0              | action        |                      | home                                                            | string | Name of your action                                                                        |
| 1              | name          | g-recaptcha-response | recaptcha_field                                                 | string | Value of name attribute for hidden file input                                              |
| 2              | attributes    | []                   | ['id' => 'recaptcha-id', 'class' => 'form-element', 'required'] | array  | Array of attributes to render inside input field                                           |
| 3              | callback      | null                 | recaptchaCallback                                               | string | Sometimes you want to use token with js. package will send token to your callback function |

#### Validation
| Argument Index | Argument Name | Default            | Example | Type   | Description         |
|----------------|---------------|--------------------|---------|--------|---------------------|
| 0              | action        |                    | home    | string | Name of your action |
| 1              | score         | loaded from config | 0.7     | float  | It's optional       |



## Complete Example
##### View:
```php
<html>
    <head>
        <title>Recaptcha v3</title>
        
        {!! Recaptcha::renderJs('fa') !!}
    </head>

    <body>
        <form action="{{ url('recaptcha-page') }}" method="post">
            {!! csrf_field() !!}
            {!! Recaptcha::field('home', 'g-recaptcha-response', ['id' => 'recaptcha-id', 'class' => 'form-element'], 'recaptchaCallback') !!}
        
            <button type="submit">Submit</button>
        </form>
        
        @if($errors->any())
            @foreach($errors->all() as $key => $error)
                <p>{{ $key }} - {{ $error }}</p>
            @endforeach
        @endif
        
        
        <script>
            function recaptchaCallback(token) {
                console.log('token retrieved:');
                console.log(token);
            }
        </script>
    </body>
</html>
```

##### Controller:
```php
class DevController extends Controller
{
    public function verifyRecaptcha(Request $request)
    {
        $this->validate($request, ['g-recaptcha-response' => 'required|recaptcha:home,0.5']);
        dd($request->all());
    }
}
```

## Migration Guide
##### from 1.0.0 to 1.1.0
- delete `app/recaptcha.php` file
- delete `resources/views/vendor/recaptcha` directory
- publish vendor again
    ```shell
    php artisan vendor:publish --provider="Mostafaznv\Recaptcha\RecaptchaServiceProvider"
    ```


## Changelog
Refer to the [Changelog](CHANGELOG.md) for a full history of the project.

## License
This software is released under [The MIT License (MIT)](LICENSE).

(c) 2018 Mostafaznv, All rights reserved.