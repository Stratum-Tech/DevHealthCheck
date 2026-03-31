# Contributing to DevHealthCheck

Thanks for contributing. Please read these guidelines before opening a PR or issue.

## Getting Started

```bash
git clone https://github.com/Stratum-Tech/DevHealthCheck.git
cd DevHealthCheck
composer install
```

Run the test suite to confirm your environment is working:

```bash
./vendor/bin/phpunit
```

## Reporting Bugs

Open a [bug report issue](.github/ISSUE_TEMPLATE/bug_report.md) and include:

- Magento version and edition
- PHP version
- The command you ran and the full output
- What you expected vs. what happened

## Requesting Features or New Checks

Open a [feature request issue](.github/ISSUE_TEMPLATE/feature_request.md). For new health checks, describe:

- What is being checked and why it matters
- What section it belongs to (or propose a new one)
- Expected pass/warn/fail thresholds

## Submitting a Pull Request

1. Fork the repo and create a branch from `main`
2. Write or update tests for any changed logic
3. Run `./vendor/bin/phpunit` — all tests must pass
4. Open a PR against `main` using the pull request template

### Adding a New Check

1. Implement `Stratum\DevHealthCheck\Model\Check\CheckInterface`
2. Place the class under `Model/Check/<Section>/`
3. Register it in `etc/di.xml`
4. Add a unit test under `Test/Unit/Model/Check/<Section>/`

### Coding Standards

- PHP 8.2+ syntax
- Strict types (`declare(strict_types=1)`)
- No direct `$_SERVER`, `shell_exec`, or `exec` calls — use Magento framework abstractions
- Keep checks read-only — they must never modify system state

## Branch Strategy

| Branch | Purpose |
|--------|---------|
| `main` | Stable, released code |
| `feature/*` | New checks or features |
| `fix/*` | Bug fixes |

## Commit Messages

Use short, imperative subject lines:

```
Add cron stuck-job detection check
Fix disk space threshold calculation
```

No co-author trailers needed.
