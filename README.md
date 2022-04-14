# Jibit KYC

**PHP client for the Jibit Identicator Project (KYC) API**

![Packagist Version](https://img.shields.io/packagist/v/armezit/kyc-jibit.svg)
![PHP from Packagist](https://img.shields.io/packagist/php-v/armezit/kyc-jibit.svg)
![Packagist](https://img.shields.io/packagist/l/armezit/kyc-jibit.svg)

## Installation

To install, simply require `armezit/kyc-jibit` with Composer:

```
composer require armezit/kyc-jibit
```

## Usage

Create an instance of the `\Armezit\Kyc\Jibit\Provider`:

```php
$provider = new \Armezit\Kyc\Jibit\Provider();
$provider->setApiKey('API_KEY');
$provider->setSecretKey('SECRET_KEY');
```

Execute any of the available methods. For example:

```php
$response = $provider->matchNationalCodeWithMobileNumber([
    'nationalCode' => $nationalId,
    'mobileNumber' => $mobileNumber,
])->send();

if ($response->isSuccessful() && $response->isMatched()) {
    // national code and mobile number matched
}
```

## Status of the project

Currently, the following methods are implemented by this package:

- [X] Match Card Number with National Code
- [X] Match National Code with Mobile Number

### Testing

```sh
composer test
```

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/armezit/kyc-jibit/issues),
or better yet, fork the library and submit a pull request.
