<?php

namespace GrapheneOS\Captcha\Middleware;

use Flarum\Settings\SettingsRepositoryInterface;
use GrapheneOS\Captcha\CaptchaStore;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ValidateCaptchaMiddleware implements MiddlewareInterface
{
    private const LOGIN_PATH     = '/login';
    private const REGISTER_PATH  = '/register';
    private const CAPTCHA_LENGTH = 6;

    public function __construct(
        private readonly CaptchaStore                $store,
        private readonly SettingsRepositoryInterface $settings,
        private readonly TranslatorInterface         $translator
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $handler->handle($request);
        }

        if (!$this->settings->get('grapheneos-captcha.enabled', true)) {
            return $handler->handle($request);
        }

        $path    = $request->getUri()->getPath();
        $isLogin = $path === self::LOGIN_PATH;
        $isReg   = $path === self::REGISTER_PATH;

        if (!$isLogin && !$isReg) {
            return $handler->handle($request);
        }

        if ($isLogin && !$this->settings->get('grapheneos-captcha.loginEnabled', true)) {
            return $handler->handle($request);
        }

        if ($isReg && !$this->settings->get('grapheneos-captcha.registerEnabled', true)) {
            return $handler->handle($request);
        }

        $body = (array) $request->getParsedBody();
        $captchaToken  = $body['captcha_token']  ?? '';
        $captchaAnswer = $body['captcha_answer'] ?? '';

        if (!$captchaToken || !ctype_xdigit($captchaToken) || strlen($captchaToken) !== CaptchaStore::TOKEN_LENGTH
            || strlen($captchaAnswer) > self::CAPTCHA_LENGTH
            || !$this->store->verifyCaptcha($captchaToken, $captchaAnswer)) {
            return $this->reject('captcha_invalid', $this->translator->trans('grapheneos-captcha.api.captcha_invalid'));
        }

        if ($this->settings->get('grapheneos-captcha.powEnabled', true)) {
            $powSolution = $body['pow_solution'] ?? '';

            if (!$this->store->verifyPow($captchaToken, $powSolution)) {
                return $this->reject('pow_invalid', $this->translator->trans('grapheneos-captcha.api.pow_invalid'));
            }
        }

        return $handler->handle($request);
    }

    private function reject(string $code, string $message): ResponseInterface
    {
        return new JsonResponse(
            ['errors' => [['code' => $code, 'detail' => $message, 'status' => '422']]],
            422
        );
    }
}
