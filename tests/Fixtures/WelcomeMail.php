<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Mail\Mailable;

final class WelcomeMail extends Mailable
{
    public function build(): self
    {
        return $this
            ->subject('Welcome')
            ->html('<p>Hello from ZeptoMail.</p>');
    }
}
