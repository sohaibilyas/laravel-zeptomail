<?php

declare(strict_types=1);

namespace Reseek\LaravelZeptoMail;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

final class ZeptoMailTransport extends AbstractTransport
{
    public function __construct(
        private readonly HttpFactory $client,
        private readonly array $config,
    ) {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'zeptomail';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new TransportException('ZeptoMail only supports Symfony email messages.');
        }

        $response = $this->client
            ->timeout($this->timeout())
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Authorization' => $this->authorizationHeader(),
            ])
            ->post($this->endpoint(), $this->payload($email));

        if ($response->failed()) {
            throw new TransportException($this->exceptionMessage($response), $response->status());
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(Email $email): array
    {
        $from = $email->getFrom();

        if ($from === []) {
            throw new TransportException('ZeptoMail requires a from address.');
        }

        $payload = [
            'from' => $this->address($from[0]),
            'to' => $this->recipients($email->getTo()),
            'subject' => $email->getSubject() ?? '',
        ];

        if ($payload['to'] === []) {
            throw new TransportException('ZeptoMail requires at least one recipient.');
        }

        $this->putIfNotEmpty($payload, 'cc', $this->recipients($email->getCc()));
        $this->putIfNotEmpty($payload, 'bcc', $this->recipients($email->getBcc()));
        $this->putIfNotEmpty($payload, 'reply_to', array_map(
            fn (Address $address): array => $this->address($address),
            $email->getReplyTo(),
        ));

        if ($email->getHtmlBody() !== null) {
            $payload['htmlbody'] = $email->getHtmlBody();
        }

        if ($email->getTextBody() !== null) {
            $payload['textbody'] = $email->getTextBody();
        }

        $this->putIfNotEmpty($payload, 'attachments', $this->attachments($email));

        foreach (['track_opens', 'track_clicks', 'client_reference'] as $option) {
            if (array_key_exists($option, $this->config)) {
                $payload[$option] = $this->config[$option];
            }
        }

        return $payload;
    }

    private function authorizationHeader(): string
    {
        $token = trim((string) ($this->config['token'] ?? ''));

        if ($token === '') {
            throw new TransportException('ZeptoMail API token is missing.');
        }

        if (str_starts_with($token, 'Zoho-enczapikey ')) {
            return $token;
        }

        return 'Zoho-enczapikey '.$token;
    }

    private function endpoint(): string
    {
        if (isset($this->config['endpoint']) && is_string($this->config['endpoint']) && $this->config['endpoint'] !== '') {
            return $this->config['endpoint'];
        }

        $host = trim((string) ($this->config['host'] ?? 'api.zeptomail.com'), '/');
        $version = trim((string) ($this->config['version'] ?? 'v1.1'), '/');

        return sprintf('https://%s/%s/email', $host, $version);
    }

    private function timeout(): int|float
    {
        $timeout = $this->config['timeout'] ?? 30;

        if (! is_numeric($timeout)) {
            return 30;
        }

        return $timeout + 0;
    }

    /**
     * @return array{address: string, name?: string}
     */
    private function address(Address $address): array
    {
        $payload = ['address' => $address->getAddress()];

        if ($address->getName() !== '') {
            $payload['name'] = $address->getName();
        }

        return $payload;
    }

    /**
     * @param  Address[]  $addresses
     * @return array<int, array{email_address: array{address: string, name?: string}}>
     */
    private function recipients(array $addresses): array
    {
        return array_map(
            fn (Address $address): array => ['email_address' => $this->address($address)],
            $addresses,
        );
    }

    /**
     * @return array<int, array{name: string, content: string, mime_type: string}>
     */
    private function attachments(Email $email): array
    {
        return array_map(function (DataPart $attachment): array {
            return [
                'name' => $attachment->getFilename() ?? 'attachment',
                'content' => preg_replace('/\s+/', '', $attachment->bodyToString()) ?? '',
                'mime_type' => $attachment->getContentType(),
            ];
        }, $email->getAttachments());
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<mixed>  $value
     */
    private function putIfNotEmpty(array &$payload, string $key, array $value): void
    {
        if ($value !== []) {
            $payload[$key] = $value;
        }
    }

    private function exceptionMessage(Response $response): string
    {
        $body = $response->body();

        if ($body === '') {
            return sprintf('ZeptoMail request failed with status %d.', $response->status());
        }

        return sprintf('ZeptoMail request failed with status %d: %s', $response->status(), $body);
    }
}
