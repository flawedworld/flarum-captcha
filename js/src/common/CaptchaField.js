import app from 'flarum/common/app';

const CaptchaField = {
  view({ attrs }) {
    const { state, loading } = attrs;
    const t = (k) => app.translator.trans(k);

    return m('div.Form-group.CaptchaField', [

      state.imageError
        ? m('p.CaptchaField-error', { role: 'alert' },
            t('grapheneos-captcha.forum.image_error')
          )
        : m('img.CaptchaField-img', {
            src:          state.imageUrl(),
            alt:          t('grapheneos-captcha.forum.image_alt'),
            role:         'img',
            'aria-label': t('grapheneos-captcha.forum.image_alt'),
            onload:  () => { state.onImageLoad(m.redraw); },
            onerror: () => { state.onImageError(m.redraw); },
          }),

      m('button.CaptchaField-reload', {
        type:         'button',
        'aria-label': t('grapheneos-captcha.forum.reload_label'),
        title:        t('grapheneos-captcha.forum.reload_label'),
        onclick(e) {
          e.preventDefault();
          state.reload(m.redraw);
        },
      }, [
        m('i.fas.fa-redo', { 'aria-hidden': 'true' }),
        m('span', t('grapheneos-captcha.forum.reload_short')),
      ]),

      m('input.FormControl.CaptchaField-input', {
        id:              'captcha-answer',
        type:            'text',
        autocomplete:    'off',
        autocorrect:     'off',
        autocapitalize:  'none',
        spellcheck:      false,
        'aria-required': 'true',
        'aria-label':    t('grapheneos-captcha.forum.captcha_label'),
        placeholder:     t('grapheneos-captcha.forum.input_placeholder'),
        disabled:        loading || state.loading,
        value:           state.answer,
        oninput(e) { state.answer = e.target.value; },
      }),

      state.powError
        ? m('p.CaptchaField-error', { role: 'alert' },
            t('grapheneos-captcha.forum.pow_error')
          )
        : !state.powReady
          ? m('.CaptchaField-pow-banner' + (state.powBlocked ? '.is-blocked' : ''), { role: 'alert', 'aria-live': 'assertive', 'aria-atomic': 'true' }, [
              m('.CaptchaField-pow-top', [
                m('span.CaptchaField-pow-spinner', { 'aria-hidden': 'true' }),
                m('span.CaptchaField-pow-text', state.powBlocked
                  ? t('grapheneos-captcha.forum.pow_blocked')
                  : t('grapheneos-captcha.forum.pow_working')
                ),
              ]),
              m('.CaptchaField-pow-bar-track',
                m('.CaptchaField-pow-bar-fill', { style: { width: (state.powProgress * 100).toFixed(1) + '%' } })
              ),
            ])
          : null,
    ]);
  },
};

export default CaptchaField;
