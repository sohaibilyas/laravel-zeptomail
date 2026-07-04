<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Reseek\LaravelZeptoMail\ZeptoMailServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ZeptoMailServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('mail.default', 'zeptomail');
        $app['config']->set('mail.from.address', 'noreply@example.com');
        $app['config']->set('mail.from.name', 'Laravel ZeptoMail');
        $app['config']->set('mail.mailers.zeptomail', [
            'transport' => 'zeptomail',
            'token' => 'test-token',
        ]);
    }
}
