<?php

declare(strict_types=1);

namespace Reseek\LaravelZeptoMail;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

final class ZeptoMailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/zeptomail.php', 'zeptomail');

        /** @var ConfigRepository $config */
        $config = $this->app->make('config');

        $config->set('mail.mailers.zeptomail', array_merge([
            'transport' => 'zeptomail',
        ], $config->get('mail.mailers.zeptomail', [])));
    }

    public function boot(): void
    {
        $this->app->afterResolving('mail.manager', function (MailManager $manager): void {
            $manager->extend('zeptomail', function (array $config = []) {
                return new ZeptoMailTransport(
                    $this->app->make(HttpFactory::class),
                    array_merge(config('zeptomail', []), $config),
                );
            });
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/zeptomail.php' => config_path('zeptomail.php'),
            ], 'zeptomail-config');
        }
    }
}
