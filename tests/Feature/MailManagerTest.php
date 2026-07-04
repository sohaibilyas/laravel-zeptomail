<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Fixtures\WelcomeMail;

it('defines the zeptomail mailer automatically', function (): void {
    expect(config('mail.mailers.zeptomail.transport'))->toBe('zeptomail');
});

it('registers a zeptomail mail transport with laravel', function (): void {
    Http::fake([
        'https://api.zeptomail.com/v1.1/email' => Http::response(['message' => 'ok'], 201),
    ]);

    Mail::to('sohaib@example.com')->send(new WelcomeMail);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://api.zeptomail.com/v1.1/email'
            && $request->hasHeader('Authorization', 'Zoho-enczapikey test-token')
            && $request['from']['address'] === 'noreply@example.com'
            && $request['from']['name'] === 'Laravel ZeptoMail'
            && $request['to'][0]['email_address']['address'] === 'sohaib@example.com'
            && $request['subject'] === 'Welcome'
            && $request['htmlbody'] === '<p>Hello from ZeptoMail.</p>';
    });
});
