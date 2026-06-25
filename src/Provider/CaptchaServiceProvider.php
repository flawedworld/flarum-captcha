<?php

namespace GrapheneOS\Captcha\Provider;

use Flarum\Foundation\AbstractServiceProvider;
use GrapheneOS\Captcha\CaptchaStore;
use GrapheneOS\Captcha\ImageGenerator;

class CaptchaServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(CaptchaStore::class, function ($container) {
            return new CaptchaStore($container->make('cache.store'));
        });

        $this->container->singleton(ImageGenerator::class, function () {
            return new ImageGenerator();
        });
    }
}
