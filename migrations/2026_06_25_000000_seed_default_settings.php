<?php

use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $db = $schema->getConnection();

        $defaults = [
            'grapheneos-captcha.enabled'         => '1',
            'grapheneos-captcha.loginEnabled'    => '1',
            'grapheneos-captcha.registerEnabled' => '1',
            'grapheneos-captcha.powEnabled'      => '1',
            'grapheneos-captcha.powDifficulty'   => '20',
        ];

        foreach ($defaults as $key => $value) {
            $exists = $db->table('settings')->where('key', $key)->exists();
            if (!$exists) {
                $db->table('settings')->insert(['key' => $key, 'value' => $value]);
            }
        }
    },
    'down' => function (Builder $schema) {
        $db = $schema->getConnection();

        $db->table('settings')->whereIn('key', [
            'grapheneos-captcha.enabled',
            'grapheneos-captcha.loginEnabled',
            'grapheneos-captcha.registerEnabled',
            'grapheneos-captcha.powEnabled',
            'grapheneos-captcha.powDifficulty',
        ])->delete();
    },
];
