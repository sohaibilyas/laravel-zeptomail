# Laravel ZeptoMail

A Laravel mail transport for sending mail through [Zoho ZeptoMail](https://www.zoho.com/zeptomail/).

## Installation

```bash
composer require sohaibilyas/laravel-zeptomail
```

## Configuration

Add your ZeptoMail token to `.env`:

```dotenv
ZEPTOMAIL_TOKEN="Zoho-enczapikey your-token"
ZEPTOMAIL_HOST=api.zeptomail.com
ZEPTOMAIL_API_VERSION=v1.1
```

You may also pass only the raw token value. The transport will add the `Zoho-enczapikey` prefix automatically.

The package automatically registers a `zeptomail` Laravel mailer. Set it as your default mailer:

```dotenv
MAIL_MAILER=zeptomail
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Publishing the config file is optional. Use it only when you want the ZeptoMail package config in your app:

```bash
php artisan vendor:publish --tag=zeptomail-config
```

If you prefer to configure ZeptoMail inside Laravel's mail config instead, you may add a `zeptomail` entry to `config/mail.php`:

```php
'mailers' => [
    'zeptomail' => [
        'transport' => 'zeptomail',
        'token' => env('ZEPTOMAIL_TOKEN'),
        'host' => env('ZEPTOMAIL_HOST', 'api.zeptomail.com'),
        'version' => env('ZEPTOMAIL_API_VERSION', 'v1.1'),
        'timeout' => env('ZEPTOMAIL_TIMEOUT', 30),
    ],
],
```

## Usage

Use Laravel's mail API as usual:

```php
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

Mail::to('info@example.com')->send(new WelcomeMail);
```

The transport sends requests to:

```text
https://api.zeptomail.com/v1.1/email
```

## Testing

```bash
composer test
```
