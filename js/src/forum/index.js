import { extend, override } from 'flarum/common/extend';
import app from 'flarum/common/app';
import LogInModal from 'flarum/components/LogInModal';
import SignUpModal from 'flarum/components/SignUpModal';
import CaptchaWidget from '../common/CaptchaWidget';
import CaptchaField from '../common/CaptchaField';

export default function () {
  // -------------------------------------------------------------------------
  // LogInModal
  // -------------------------------------------------------------------------

  extend(LogInModal.prototype, 'oninit', function () {
    if (isCaptchaEnabled() && isLoginEnabled()) {
      this._captcha = new CaptchaWidget();
    }
  });

  extend(LogInModal.prototype, 'fields', function (items) {
    if (!this._captcha) return;
    items.add(
      'grapheneos-captcha',
      m(CaptchaField, { state: this._captcha, loading: this.loading }),
      -5
    );
  });

  extend(LogInModal.prototype, 'loginParams', function (params) {
    if (this._captcha) {
      Object.assign(params, captchaValues(this._captcha));
    }
  });

  // Override onsubmit so we can preventDefault BEFORE the original fires
  override(LogInModal.prototype, 'onsubmit', function (original, e) {
    if (this._captcha && !this._captcha.isReady()) {
      e.preventDefault();
      this.loading = false;
      this._captcha.powBlocked = true;
      m.redraw();
      return;
    }
    return original(e);
  });

  extend(LogInModal.prototype, 'onerror', function () {
    if (this._captcha) this._captcha.reset(m.redraw);
  });

  // -------------------------------------------------------------------------
  // SignUpModal
  // -------------------------------------------------------------------------

  extend(SignUpModal.prototype, 'oninit', function () {
    if (isCaptchaEnabled() && isRegisterEnabled()) {
      this._captcha = new CaptchaWidget();
    }
  });

  extend(SignUpModal.prototype, 'fields', function (items) {
    if (!this._captcha) return;
    items.add(
      'grapheneos-captcha',
      m(CaptchaField, { state: this._captcha, loading: this.loading }),
      -5
    );
  });

  extend(SignUpModal.prototype, 'submitData', function (data) {
    if (this._captcha) {
      Object.assign(data, captchaValues(this._captcha));
    }
  });

  // Override onsubmit so we can preventDefault BEFORE the original fires
  override(SignUpModal.prototype, 'onsubmit', function (original, e) {
    if (this._captcha && !this._captcha.isReady()) {
      e.preventDefault();
      this.loading = false;
      this._captcha.powBlocked = true;
      m.redraw();
      return;
    }
    return original(e);
  });

  extend(SignUpModal.prototype, 'onerror', function () {
    if (this._captcha) this._captcha.reset(m.redraw);
  });
}

function captchaValues(widget) {
  return {
    captcha_token:  widget.captchaToken,
    captcha_answer: widget.answer,
    pow_solution:   String(widget.powSolution ?? '0'),
  };
}

function isCaptchaEnabled()  { return app.forum.attribute('captcha.enabled')         !== false; }
function isLoginEnabled()    { return app.forum.attribute('captcha.loginEnabled')    !== false; }
function isRegisterEnabled() { return app.forum.attribute('captcha.registerEnabled') !== false; }
