# Laravel ZeptoMail

A Laravel mail transport for sending mail through [Zoho ZeptoMail](https://www.zoho.com/zeptomail/).

## Installation

```bash
composer require sohaibilyas/laravel-zeptomail
```

Publish the config file when you want to customize the API endpoint or request timeout:

```bash
php artisan vendor:publish --tag=zeptomail-config
```

## Configuration

Add your ZeptoMail token to `.env`:

```dotenv
ZEPTOMAIL_TOKEN="Zoho-enczapikey your-token"
ZEPTOMAIL_HOST=api.zeptomail.com
ZEPTOMAIL_API_VERSION=v1.1
```

You may also pass only the raw token value. The transport will add the `Zoho-enczapikey` prefix automatically.

Add a ZeptoMail mailer to `config/mail.php`:

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

Then set it as your default mailer:

```dotenv
MAIL_MAILER=zeptomail
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
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
