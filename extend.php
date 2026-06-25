<?php

use Flarum\Extend;
use GrapheneOS\Captcha\Controller\CaptchaImageController;
use GrapheneOS\Captcha\Controller\PowChallengeController;
use GrapheneOS\Captcha\Middleware\ValidateCaptchaMiddleware;
use GrapheneOS\Captcha\Provider\CaptchaServiceProvider;

return [
    (new Extend\ServiceProvider())
        ->register(CaptchaServiceProvider::class),

    (new Extend\Routes('forum'))
        ->get('/captcha/image/{token}', 'captcha.image', CaptchaImageController::class)
        ->get('/captcha/pow/{token}', 'captcha.pow', PowChallengeController::class),

    (new Extend\Csrf())
        ->exemptRoute('captcha.image')
        ->exemptRoute('captcha.pow'),

    (new Extend\Middleware('forum'))
        ->add(ValidateCaptchaMiddleware::class),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Settings())
        ->serializeToForum('captcha.enabled', 'grapheneos-captcha.enabled', 'boolval', true)
        ->serializeToForum('captcha.loginEnabled', 'grapheneos-captcha.loginEnabled', 'boolval', true)
        ->serializeToForum('captcha.registerEnabled', 'grapheneos-captcha.registerEnabled', 'boolval', true)
        ->serializeToForum('captcha.powEnabled', 'grapheneos-captcha.powEnabled', 'boolval', true)
        ->serializeToForum('captcha.powDifficulty', 'grapheneos-captcha.powDifficulty', 'intval', 20),
];
