import app from 'flarum/common/app';

const TOKEN_BYTES = 16;

export default class CaptchaWidget {
  constructor() {
    this.captchaToken  = this._genToken();
    this.answer        = '';
    this.loading       = true;
    this.powReady      = false;
    this.powSolution   = null;
    this.powProgress   = 0.01;
    this.powBlocked    = false;
    this.powError      = false;
    this.imageError    = false;
    this._worker       = null;
    this._powPromise   = null;

    this._startPoW();
  }

  isReady() {
    return this.powReady && !this.loading;
  }

  reload(redraw) {
    if (this._worker) {
      this._worker.terminate();
      this._worker = null;
    }
    this.captchaToken = this._genToken();
    this.answer       = '';
    this.loading      = true;
    this.imageError   = false;
    this.powReady     = false;
    this.powSolution  = null;
    this.powProgress  = 0.01;
    this.powBlocked   = false;
    this.powError     = false;
    this._powPromise  = null;
    this._startPoW();
    if (redraw) redraw();
  }

  reset(redraw) {
    this.reload(redraw);
  }

  imageUrl() {
    return `${app.forum.attribute('baseUrl')}/captcha/image/${this.captchaToken}`;
  }

  onImageLoad(redraw) {
    this.loading = false;
    if (redraw) redraw();
  }

  onImageError(redraw) {
    this.loading    = false;
    this.imageError = true;
    if (redraw) redraw();
  }

  _genToken() {
    const arr = new Uint8Array(TOKEN_BYTES);
    crypto.getRandomValues(arr);
    return Array.from(arr, b => b.toString(16).padStart(2, '0')).join('');
  }

  _startPoW() {
    if (app.forum.attribute('captcha.powEnabled') === false) {
      this.powReady    = true;
      this.powSolution = '0';
      this._powPromise = Promise.resolve();
      return;
    }

    this._powPromise = fetch(
      `${app.forum.attribute('baseUrl')}/captcha/pow/${this.captchaToken}`
    )
      .then(r => r.json())
      .then(({ challenge, difficulty }) => this._solvePoW(challenge, difficulty))
      .then(solution => {
        this.powSolution = solution;
        this.powProgress = 1;
        this.powReady    = true;
        this.powBlocked  = false;
        m.redraw();
      })
      .catch(() => {
        this.powError = true;
        m.redraw();
      });
  }

  _solvePoW(challenge, difficulty) {
    return new Promise((resolve) => {
      try {
        const blob = new Blob([POW_WORKER_SOURCE], { type: 'application/javascript' });
        const url  = URL.createObjectURL(blob);
        const w    = new Worker(url);
        this._worker = w;

        w.onmessage = e => {
          if (e.data.progress !== undefined) {
            this.powProgress = e.data.progress;
            m.redraw();
            return;
          }
          URL.revokeObjectURL(url);
          w.terminate();
          resolve(e.data.solution);
        };
        w.onerror = () => {
          URL.revokeObjectURL(url);
          resolve('0');
        };
        w.postMessage({ challenge, difficulty });
      } catch (_) {
        resolve('0');
      }
    });
  }
}

const POW_WORKER_SOURCE = `
const BATCH = 256;
const REPORT_EVERY = 16;

self.onmessage = async function(e) {
  const { challenge, difficulty } = e.data;
  const enc = new TextEncoder();
  const expected = Math.pow(2, difficulty);
  let n = 0;
  let batchCount = 0;

  while (true) {
    const results = await Promise.all(
      Array.from({ length: BATCH }, (_, i) => {
        const msg = enc.encode(challenge + ':' + (n + i));
        return crypto.subtle.digest('SHA-256', msg);
      })
    );

    for (let i = 0; i < BATCH; i++) {
      if (hasLeadingZeros(results[i], difficulty)) {
        self.postMessage({ solution: String(n + i) });
        return;
      }
    }

    n += BATCH;
    batchCount++;

    if (batchCount % REPORT_EVERY === 0) {
      self.postMessage({ progress: Math.min(0.99, n / expected) });
      await new Promise(r => setTimeout(r, 0));
    }
  }
};

function hasLeadingZeros(buf, bits) {
  const bytes = new Uint8Array(buf);
  let rem = bits;
  for (let i = 0; i < bytes.length && rem > 0; i++) {
    const need = Math.min(rem, 8);
    const mask = 0xFF & (0xFF << (8 - need));
    if ((bytes[i] & mask) !== 0) return false;
    rem -= 8;
  }
  return true;
}
`;
