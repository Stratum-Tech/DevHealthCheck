# Stratum DevHealthCheck

A Magento 2 CLI module that runs a colour-coded, sectioned health check of your environment and deployment configuration.

```
╔════════════════════════════════════════╗
║   Magento Environment Health Check    ║
╚════════════════════════════════════════╝

[PHP]
    ✓  PHP Version          8.3.4
    ✓  PHP Extensions       all required extensions loaded
    –  OPcache              opcache_get_configuration() not available (CLI SAPI restriction)

[Deploy & Mode]
    ✓  Deploy Mode          production
    ✓  Maintenance Mode     disabled
    ✓  Static Assets        12 locale/theme directories deployed
    ✓  Generated Code       present and non-empty

[Filesystem]
    ✓  Writable Directories  var, pub/media, pub/static, generated
    ⚠  env.php Permissions   permissions 644 — world-readable
    ✓  Disk Space            48.2GB free of 500.0GB (96%)

[Database]
    ✓  DB Connection         connected
    ✓  DB Version            MySQL 8.0.36
    ✓  Pending Upgrades      all modules up to date

[Cache]
    ✓  Cache Types           all 12 types enabled
    ✓  Cache Backend         Redis (Cm_Cache_Backend_Redis)
    ✓  Redis Connectivity    connected to 127.0.0.1:6379

[Indexers]
    ✓  Indexer Status        all 11 indexers valid
    –  Indexer Mode          mode check only relevant in production

[Environment]
    ✓  Crypt Key             present
    ⚠  Base URL              http://localhost/
    ⚠  Admin URL             /backend
    –  Cookie Security       cookie security check only relevant in production
    –  HTTPS Redirect        HTTPS check only relevant in production

[Cron]
    ✓  Cron Last Run         last run 2 minutes ago
    ✓  Cron Error Jobs       no errors in last 60 minutes
    ✓  Cron Backlog          0 stale pending jobs

[Search]
    ✓  Search Engine         elasticsearch8
    ✓  Search Connectivity   connected to 127.0.0.1:9200 (elasticsearch8)

────────────────────────────────────────────
  Summary:  18 OK  |  3 WARN  |  0 FAIL  |  0 INFO  |  5 SKIP
────────────────────────────────────────────
```

## Requirements

- Magento 2.4.x
- PHP 8.2+

## Installation

**Via Composer:**
```bash
composer require stratum/module-dev-healthcheck
bin/magento module:enable Stratum_DevHealthCheck
bin/magento setup:upgrade
```

**Manual:**
```bash
# Copy to app/code/Stratum/DevHealthCheck, then:
bin/magento module:enable Stratum_DevHealthCheck
bin/magento setup:upgrade
```

## Usage

```bash
# Run all checks
bin/magento dev:healthcheck

# Run a single section
bin/magento dev:healthcheck --section="Cron"

# Show detail messages (verbose)
bin/magento dev:healthcheck -v

# JSON output
bin/magento dev:healthcheck --format=json

# Exit with code 2 if any WARNs exist (useful in CI)
bin/magento dev:healthcheck --fail-on-warn
```

### Exit codes

| Code | Meaning |
|------|---------|
| `0` | All checks passed (OK / INFO / SKIP) |
| `1` | One or more FAIL results |
| `2` | One or more WARN results (only with `--fail-on-warn`) |

## Checks

| Section | Check | What it verifies |
|---------|-------|-----------------|
| PHP | PHP Version | >= 8.3 OK, 8.2 WARN |
| PHP | PHP Extensions | intl, soap, bcmath, gd/imagick, pdo_mysql, mbstring, openssl, zip, ctype, curl |
| PHP | OPcache | Enabled, memory >= 128MB, validate_timestamps off in production |
| Deploy & Mode | Deploy Mode | production / developer / default |
| Deploy & Mode | Maintenance Mode | On or off |
| Deploy & Mode | Static Assets | pub/static populated; skipped in developer mode |
| Deploy & Mode | Generated Code | generated/code non-empty; skipped in developer mode |
| Filesystem | Writable Directories | var/, pub/media/, pub/static/, generated/ |
| Filesystem | env.php Permissions | Warns if world-readable (> 0644) |
| Filesystem | Disk Space | WARN < 10% free, FAIL < 2% |
| Database | DB Connection | SELECT 1 |
| Database | DB Version | Warns on MySQL < 8.0 |
| Database | Pending Upgrades | Compares setup_module table against installed module versions |
| Cache | Cache Types | Lists any disabled cache types |
| Cache | Cache Backend | Reports backend; warns if not Redis |
| Cache | Redis Connectivity | Socket connect with 2s timeout; skipped if not configured |
| Indexers | Indexer Status | Reports any invalid indexers |
| Indexers | Indexer Mode | Warns on realtime indexers in production |
| Environment | Crypt Key | crypt/key present in env.php |
| Environment | Base URL | Warns on localhost/127.0.0.1 in production |
| Environment | Admin URL | Warns on well-known default paths (admin, backend, etc.) |
| Environment | Cookie Security | HttpOnly and Secure flags in production |
| Environment | HTTPS Redirect | use_in_frontend and use_in_adminhtml in production |
| Cron | Cron Last Run | FAIL if no successful job in last 15 minutes |
| Cron | Cron Error Jobs | Counts error status jobs in last 60 minutes |
| Cron | Cron Backlog | WARN >= 10 stale pending jobs, FAIL >= 50 |
| Search | Search Engine | Reports configured engine; warns on deprecated MySQL search |
| Search | Search Connectivity | HTTP connect to engine host:port with 2s timeout |

## Adding a custom check

1. Create a class implementing `Stratum\DevHealthCheck\Model\Check\CheckInterface`
2. Register it in your module's `di.xml`:

```xml
<type name="Stratum\DevHealthCheck\Model\HealthCheckRunner">
    <arguments>
        <argument name="checks" xsi:type="array">
            <item name="myCheck" xsi:type="object">Vendor\Module\Model\Check\MyCheck</item>
        </argument>
    </arguments>
</type>
```

## License

MIT
