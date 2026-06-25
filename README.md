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

```bash
composer require grapheneos/flarum-captcha
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
