# Release instructions

## Repository

- Repository: `ITMOSOFT/laravel-disputes`
- Composer package: `itmosoft/laravel-disputes`
- Description: `Dispute management workflows for Laravel applications, with polymorphic parties and subjects, evidence metadata, reviewer assignment, and resolution tracking.`

## Validate locally

```bash
composer validate --strict
composer install
composer test
```

Do not tag a release until local PHPUnit tests pass and GitHub Actions is green.

## User-run Git commands

Run these from the existing, already-connected repository. These commands are for you to execute; no repository changes are performed by the assistant.

```bash
git add .
git commit -m "feat: add dispute management workflows"
git push origin main
```

After tests and CI pass:

```bash
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

## GitHub Release

Title: `v1.0.0 - Dispute Management`

Description:

```markdown
## Features
- Polymorphic dispute subjects, claimants, respondents, and reviewers.
- Open, review, awaiting-response, resolved, rejected, and closed lifecycle.
- Resolution summaries and rejection reasons.
- Evidence metadata with storage path references.
- Party model concern for filed and received disputes.

## Requirements
- PHP 8.3+
- Laravel 12 or 13

## Installation
composer require itmosoft/laravel-disputes
php artisan migrate

## Notes
The package does not implement messaging, refunds, payment reversals, escrow, file upload/security, SLA automation, notifications, or reviewer authorization.
```

## Packagist

1. Sign in to Packagist.
2. Submit `https://github.com/ITMOSOFT/laravel-disputes`.
3. Confirm the package name is `itmosoft/laravel-disputes`.
4. Configure the GitHub service hook if prompted.
5. Verify Packagist detects the `v1.0.0` tag.
