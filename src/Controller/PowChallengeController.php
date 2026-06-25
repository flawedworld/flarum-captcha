<?php

namespace GrapheneOS\Captcha\Controller;

use Flarum\Settings\SettingsRepositoryInterface;
use GrapheneOS\Captcha\CaptchaStore;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PowChallengeController implements RequestHandlerInterface
{
    private const CHALLENGE_BYTES   = 16;
    private const POW_DIFFICULTY_MIN = 14;
    private const POW_DIFFICULTY_MAX = 28;

    public function __construct(
        private readonly CaptchaStore                $store,
        private readonly SettingsRepositoryInterface $settings
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $token = $request->getQueryParams()['token'] ?? '';

        if (!$token || !ctype_xdigit($token) || strlen($token) !== CaptchaStore::TOKEN_LENGTH) {
            return new JsonResponse(['error' => 'invalid_token'], 400);
        }

        $difficulty = max(self::POW_DIFFICULTY_MIN, min(self::POW_DIFFICULTY_MAX, (int) $this->settings->get('grapheneos-captcha.powDifficulty', 20)));
        $challenge  = bin2hex(random_bytes(self::CHALLENGE_BYTES));

        $this->store->storePow($token, $challenge, $difficulty);

        return new JsonResponse(
            ['challenge' => $challenge, 'difficulty' => $difficulty],
            200,
            ['Cache-Control' => 'no-store']
        );
    }
}
