<?php

namespace GrapheneOS\Captcha\Controller;

use GrapheneOS\Captcha\CaptchaStore;
use GrapheneOS\Captcha\ImageGenerator;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CaptchaImageController implements RequestHandlerInterface
{
    public function __construct(
        private readonly CaptchaStore   $store,
        private readonly ImageGenerator $generator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $token = $request->getQueryParams()['token'] ?? '';

        if (!$token || !ctype_xdigit($token) || strlen($token) !== CaptchaStore::TOKEN_LENGTH) {
            $body = new Stream('php://temp', 'wb+');
            $body->write('{"error":"invalid_token"}');
            return (new Response($body, 400))->withHeader('Content-Type', 'application/json');
        }

        ['text' => $text, 'png' => $png] = $this->generator->generate();
        $this->store->storeCaptcha($token, $text);

        $body = new Stream('php://temp', 'wb+');
        $body->write($png);

        return (new Response($body, 200))
            ->withHeader('Content-Type', 'image/png')
            ->withHeader('Cache-Control', 'no-store');
    }
}
