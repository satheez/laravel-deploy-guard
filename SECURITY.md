# Security Policy

## Supported Versions

Security fixes are planned for the latest stable release line.

| Version | Supported |
|---|---|
| `1.x` | Yes |
| `<1.0` | No |

## Reporting a Vulnerability

Do not publish vulnerability details in public issues.

Report security concerns through the repository owner contact channel or a private GitHub security advisory when available. Include:

- affected version
- Laravel and PHP versions
- concise reproduction steps
- expected and actual behavior
- any safe proof of impact

## Secret Safety

This package must not print secrets in console or JSON output.

Do not include real secrets in reports, issues, screenshots, tests, or example output. Redact values such as:

- `APP_KEY`
- database passwords
- mail passwords
- API tokens
- private keys
- cloud access keys
- Redis passwords

## Scope

The package is a deployment readiness checker. It is not a vulnerability scanner, secret scanner, dependency scanner, or server security audit tool.
