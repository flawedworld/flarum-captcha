import app from 'flarum/admin/app';

export default function () {
  app.extensionData
    .for('grapheneos-captcha')
    .registerSetting({
      setting: 'grapheneos-captcha.enabled',
      type:    'boolean',
      label:   app.translator.trans('grapheneos-captcha.admin.settings.enabled_label'),
    })
    .registerSetting({
      setting: 'grapheneos-captcha.loginEnabled',
      type:    'boolean',
      label:   app.translator.trans('grapheneos-captcha.admin.settings.login_label'),
    })
    .registerSetting({
      setting: 'grapheneos-captcha.registerEnabled',
      type:    'boolean',
      label:   app.translator.trans('grapheneos-captcha.admin.settings.register_label'),
    })
    .registerSetting({
      setting: 'grapheneos-captcha.powEnabled',
      type:    'boolean',
      label:   app.translator.trans('grapheneos-captcha.admin.settings.pow_label'),
    })
    .registerSetting({
      setting:     'grapheneos-captcha.powDifficulty',
      type:        'number',
      label:       app.translator.trans('grapheneos-captcha.admin.settings.pow_difficulty_label'),
      help:        app.translator.trans('grapheneos-captcha.admin.settings.pow_difficulty_help'),
      min:         14,
      max:         28,
      placeholder: '20',
    });
}
