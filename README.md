# LightSpeed Provider for OAuth 2.0 Client

[![Build Status](https://travis-ci.org/ursuleacv/oauth2-lightspeed.png?branch=master)](https://travis-ci.org/ursuleacv/oauth2-lightspeed)

This package provides LightSpeed OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

This package is compliant with [PSR-1][], [PSR-2][], [PSR-4][], and [PSR-7][]. If you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md


## Requirements

The following versions of PHP are supported.

* PHP 5.6
* PHP 7.0
* PHP 7.1
* PHP 7.2
* HHVM

## Installation

Add the following to your `composer.json` file.

```json
{
    "require": {
        "ursuleacv/oauth2-lightspeed": "~2.0"
    }
}
```

## Usage

### Authorization Code Flow

```php
session_start();

$provider = new League\OAuth2\Client\Provider\Lightspeed([
    'clientId'                => LIGHTSPEED_CLIENT_ID,
    'clientSecret'            => LIGHTSPEED_CLIENT_SECRET,
    'redirectUri'             => LIGHTSPEED_REDIRECT_URI,
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => ['employee:all', '...', '...'],
    ]);
    $_SESSION['oauth2state'] = $provider->getState();
    
    echo '<a href="'.$authUrl.'">Log in with LightSpeed!</a>';
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    echo 'Invalid state.';
    exit;

}

// Try to get an access token (using the authorization code grant)
$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

try {

    // We got an access token, let's now get the Account ID and sale details
    $client = $provider->getResourceOwner($token);
    $merchantos = $provider->merchantosApi($token, $client->getId());

    $clientId = $client->getId();
    $sale = $merchantos->getSale(1);

    echo '<pre>';
    print_r($client); echo '<br>';
    print_r($sale); echo '<br>';
    echo '</pre>';

} catch (Exception $e) {
    exit($e->getMessage());
}

echo '<pre>';
// Use this to interact with an API on the client behalf
var_dump($token->getToken());

echo '</pre>';
```

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/ursuleacv/oauth2-lightspeed/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Valentin Ursuleac](https://github.com/ursuleacv)

## License

The MIT License (MIT). Please see [License File](https://github.com/ursuleacv/oauth2-lightspeed/blob/master/LICENSE) for more information.
