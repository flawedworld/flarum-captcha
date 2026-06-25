<?php

namespace GrapheneOS\Captcha;

use Illuminate\Contracts\Cache\Repository;

class CaptchaStore
{
    public const  TOKEN_LENGTH           = 32;
    public const  MAX_POW_SOLUTION_LENGTH = 20;
    private const TTL    = 900;
    private const PFXIMG = 'grapheneos_captcha:';
    private const PFXPOW = 'grapheneos_pow:';

    public function __construct(private readonly Repository $cache) {}

    public function storeCaptcha(string $token, string $answer): void
    {
        $this->cache->put(self::PFXIMG . $token, strtolower($answer), self::TTL);
    }

    public function verifyCaptcha(string $token, string $answer): bool
    {
        $key    = self::PFXIMG . $token;
        $stored = $this->cache->get($key);

        if ($stored === null) {
            return false;
        }

        return hash_equals($stored, strtolower(trim($answer)));
    }

    public function storePow(string $token, string $challenge, int $difficulty): void
    {
        $this->cache->put(self::PFXPOW . $token, compact('challenge', 'difficulty'), self::TTL);
    }

    public function verifyPow(string $token, string $solution): bool
    {
        $key  = self::PFXPOW . $token;
        $data = $this->cache->get($key);

        if ($data === null) {
            return false;
        }

        if (!ctype_digit($solution) || strlen($solution) > self::MAX_POW_SOLUTION_LENGTH) {
            return false;
        }

        $hash = hash('sha256', $data['challenge'] . ':' . $solution);
        return $this->hasLeadingZeroBits($hash, (int) $data['difficulty']);
    }

    private function hasLeadingZeroBits(string $hexHash, int $bits): bool
    {
        $fullNibbles = intdiv($bits, 4);
        $remBits     = $bits % 4;

        if (substr($hexHash, 0, $fullNibbles) !== str_repeat('0', $fullNibbles)) {
            return false;
        }

        if ($remBits === 0) {
            return true;
        }

        return hexdec($hexHash[$fullNibbles]) < (0x10 >> $remBits);
    }
}
