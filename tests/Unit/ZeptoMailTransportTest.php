<?php

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Reseek\LaravelZeptoMail\ZeptoMailTransport;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

it('maps a symfony email into a zeptomail payload', function (): void {
    $transport = new ZeptoMailTransport(app(HttpFactory::class), ['token' => 'test-token']);

    $payload = $transport->payload((new Email)
        ->from(new Address('noreply@example.com', 'Reseek'))
        ->to(new Address('info@example.com', 'Sohaib'))
        ->cc('copy@example.com')
        ->bcc('hidden@example.com')
        ->replyTo('reply@example.com')
        ->subject('Test Email')
        ->html('<div><b>Test email sent successfully.</b></div>')
        ->text('Test email sent successfully.')
        ->attach('invoice contents', 'invoice.txt', 'text/plain'));

    expect($payload)->toMatchArray([
        'from' => [
            'address' => 'noreply@example.com',
            'name' => 'Reseek',
        ],
        'to' => [[
            'email_address' => [
                'address' => 'info@example.com',
                'name' => 'Sohaib',
            ],
        ]],
        'cc' => [[
            'email_address' => [
                'address' => 'copy@example.com',
            ],
        ]],
        'bcc' => [[
            'email_address' => [
                'address' => 'hidden@example.com',
            ],
        ]],
        'reply_to' => [[
            'address' => 'reply@example.com',
        ]],
        'subject' => 'Test Email',
        'htmlbody' => '<div><b>Test email sent successfully.</b></div>',
        'textbody' => 'Test email sent successfully.',
        'attachments' => [[
            'name' => 'invoice.txt',
            'content' => base64_encode('invoice contents'),
            'mime_type' => 'text/plain',
        ]],
    ]);
});

it('sends mail to the configured endpoint', function (): void {
    Http::fake([
        'https://mail.example.test/v1.1/email' => Http::response(['message' => 'ok'], 201),
    ]);

    $transport = new ZeptoMailTransport(app(HttpFactory::class), [
        'token' => 'Zoho-enczapikey test-token',
        'endpoint' => 'https://mail.example.test/v1.1/email',
    ]);

    $transport->send((new Email)
        ->from('noreply@example.com')
        ->to('info@example.com')
        ->subject('Hello')
        ->html('<p>Hello.</p>'));

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://mail.example.test/v1.1/email'
            && $request->hasHeader('Authorization', 'Zoho-enczapikey test-token')
            && $request['to'][0]['email_address']['address'] === 'info@example.com';
    });
});

it('fails when the token is missing', function (): void {
    $transport = new ZeptoMailTransport(app(HttpFactory::class), []);

    expect(fn () => $transport->send((new Email)
        ->from('noreply@example.com')
        ->to('info@example.com')
        ->subject('Hello')
        ->text('Hello.')))->toThrow(TransportException::class, 'ZeptoMail API token is missing.');
});
