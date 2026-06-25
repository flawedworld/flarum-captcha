# GrapheneOS Captcha

A self-hosted image captcha with proof-of-work (PoW) for Flarum login and registration.

## Features

- Server-generated image captcha — no third-party services or CDNs
- Client-side proof-of-work challenge solved via Web Worker (SHA-256)
- Applies to login and/or registration independently
- Configurable PoW difficulty (14–28 leading zero bits)
- Accessible: ARIA labels, alt text, keyboard navigable

## Requirements

- Flarum 1.5 or 2.0
- Redis/Valkey for captcha token storage (via `fof/redis` or equivalent)

## Installation

This package is distributed as a GitHub release asset rather than via Packagist. Install it by adding a `package` repository entry to your Flarum's `composer.json` that pins the exact version and SHA-256 hash of the release tarball.

**1. Add the repository and requirement to `composer.json`:**

```json
{
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "grapheneos/flarum-captcha",
                "version": "0.0.10",
                "type": "flarum-extension",
                "dist": {
                    "url": "https://github.com/flawedworld/flarum-captcha/releases/download/v0.0.10/grapheneos-flarum-captcha-v0.0.10.tar.gz",
                    "type": "tar",
                    "shasum": "d8a13a4a040c288a6bc6613118e2b209a0895faa20c69c3c9e45d3397c32e206"
                },
                "require": {
                    "flarum/core": "^1.5 || ^2.0"
                },
                "extra": {
                    "flarum-extension": {
                        "title": "GrapheneOS Captcha"
                    }
                }
            }
        }
    ],
    "require": {
        "grapheneos/flarum-captcha": "0.0.10"
    }
}
```

**2. Install:**

```bash
composer update grapheneos/flarum-captcha
php flarum migrate
php flarum cache:clear
```

**3. Enable the extension** in the Flarum admin panel.

### Verifying the release

Each release is built in GitHub Actions and ships with a Sigstore provenance attestation. Verify it with the [GitHub CLI](https://cli.github.com/):

```bash
gh attestation verify grapheneos-flarum-captcha-v0.0.10.tar.gz \
    --repo flawedworld/flarum-captcha
```

### Upgrading

Update the `version`, `url`, and `shasum` fields in `composer.json` to match the new release, then run:

```bash
composer update grapheneos/flarum-captcha
php flarum migrate
php flarum cache:clear
```

The SHA-256 hash of each release tarball is listed on the [releases page](https://github.com/flawedworld/flarum-captcha/releases).

## Configuration

Settings are available in the admin panel under the extension page:

| Setting | Default | Description |
|---|---|---|
| Enable captcha | on | Master switch |
| Require captcha on login | on | Protect the login form |
| Require captcha on registration | on | Protect the registration form |
| Enable proof-of-work | on | Require client-side PoW before submission |
| PoW difficulty | 20 | Leading zero bits required (14–28) |

## Licence

MIT
