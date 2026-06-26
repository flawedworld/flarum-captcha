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

This package is distributed as a GitHub release asset rather than via Packagist. Install it by adding a `package` repository entry to your Flarum's `composer.json` that pins the exact version and SHA-1 hash of the release tarball. (Composer's `shasum` field requires SHA-1; use the `.sha256` release asset and `gh attestation verify` for stronger integrity verification.)

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
                    "shasum": "9579fb355c376c3c640e2a4a853084d1e95b70fb"
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

The SHA-1 and SHA-256 checksums for each release are published as `.sha1` and `.sha256` assets on the [releases page](https://github.com/flawedworld/flarum-captcha/releases). You can also compute them locally:

```bash
curl -L https://github.com/flawedworld/flarum-captcha/releases/download/vX.X.X/grapheneos-flarum-captcha-vX.X.X.tar.gz -o captcha.tar.gz
sha1sum   captcha.tar.gz   # use in composer.json shasum field
sha256sum captcha.tar.gz   # cross-check against the .sha256 release asset
```

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
