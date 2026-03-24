# Stratum DevHealthCheck

A Magento 2 CLI module that runs a colour-coded, sectioned health check of your environment and deployment configuration, and scores overall system health on a 0–100 scale.

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
    ✓  Stuck Cron Jobs       no stuck jobs detected
    ✓  Duplicate Cron Entries no duplicate scheduled entries

[Search]
    ✓  Search Engine         elasticsearch8
    ✓  Search Connectivity   connected to 127.0.0.1:9200 (elasticsearch8)

[Security]
    ✓  .git in Webroot       .git/ not inside pub/
    ✓  PHP Info Files        no phpinfo files found in pub/
    ✗  Two-Factor Auth       Magento_TwoFactorAuth is disabled
    –  Webroot Location      document root not available from CLI
    ✓  Log Dir Exposure      var/log and var/report outside pub/

[Performance]
    –  Asset Minification    minification check only relevant in production
    ⚠  Flat Catalog          flat tables disabled for: products, categories
    ✓  Module Count          142 enabled modules
    ✓  MySQL Query Cache      query cache removed in MySQL 8 (no action needed)
    ✓  Full Page Cache       Varnish

[Logging]
    ✓  Exception Log         0.1MB — last modified 142 minutes ago
    ✓  Error Reports         0 error report files
    –  Debug Logging         log level check only relevant in production

[Storage]
    ✓  Media Size            2.4GB
    ✓  Var Directory Size    0.8GB

[Config Integrity]
    ✓  config.php Sync       all 142 modules present in config.php
    ✓  Store Code in URL     disabled

[Extensions]
    ✓  Class Rewrites        8 <preference> rewrites
    ✓  Duplicate Modules     no identity conflicts between app/code/ and vendor/

[Infrastructure]
    ✓  RequireJS Config      24 RequireJS config file(s) deployed
    ✓  Session Backend       Redis

────────────────────────────────────────────
  Summary:  35 OK  |  4 WARN  |  1 FAIL  |  7 SKIP
  Score:    82/100  Grade: B
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
bin/magento dev:healthcheck --section="Security"

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

## Scoring

Every check contributes to a weighted 0–100 health score. Sections have different weights based on their impact on production stability and security:

| Section | Weight |
|---------|--------|
| Security | 20 |
| Database | 15 |
| Cache | 10 |
| Cron | 10 |
| Deploy & Mode | 8 |
| PHP | 8 |
| Environment | 7 |
| Filesystem | 7 |
| Indexers | 5 |
| Search | 5 |
| Infrastructure | 5 |
| Performance | 4 |
| Logging | 3 |
| Config Integrity | 3 |
| Extensions | 3 |
| Storage | 2 |

Points are split equally among checks within each section. SKIP checks are excluded entirely and do not affect the score. WARN results earn 50% of their available points; FAIL earns 0%.

| Grade | Score |
|-------|-------|
| A | 90–100 |
| B | 75–89 |
| C | 60–74 |
| D | 45–59 |
| F | 0–44 |

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
| Cron | Stuck Cron Jobs | FAIL if any job stuck in "running" state for > 30 minutes |
| Cron | Duplicate Cron Entries | WARN if multiple pending entries for same job at same time |
| Search | Search Engine | Reports configured engine; warns on deprecated MySQL search |
| Search | Search Connectivity | HTTP connect to engine host:port with 2s timeout |
| Security | .git in Webroot | FAIL if .git/ directory found inside pub/ |
| Security | PHP Info Files | FAIL if info.php or phpinfo.php found in pub/ |
| Security | Two-Factor Auth | FAIL if Magento_TwoFactorAuth disabled; WARN if no provider configured |
| Security | Webroot Location | Verifies pub/index.php exists and sensitive files are outside pub/; skipped from CLI |
| Security | Log Dir Exposure | Verifies var/log/ and var/report/ are outside pub/ and protected by .htaccess |
| Performance | Asset Minification | JS/CSS minification and merging enabled in production |
| Performance | Flat Catalog | Flat product and category tables enabled |
| Performance | Module Count | WARN >= 300 modules, FAIL >= 400 |
| Performance | MySQL Query Cache | Warns if query_cache enabled (harmful under load) |
| Performance | Full Page Cache | Varnish OK, built-in WARN in production, unconfigured FAIL |
| Logging | Exception Log | WARN if recently modified or > 10MB; FAIL if > 50MB |
| Logging | Error Reports | WARN >= 10 files in var/report/, FAIL >= 100 |
| Logging | Debug Logging | Warns if dev/debug/debug_logging enabled in production |
| Storage | Media Size | WARN >= 20GB, FAIL >= 50GB |
| Storage | Var Directory Size | WARN >= 5GB, FAIL >= 20GB |
| Config Integrity | config.php Sync | Detects modules registered but absent from config.php; run `app:config:dump` to fix |
| Config Integrity | Store Code in URL | Warns if web/url/use_store enabled (breaks Varnish/CDN) |
| Extensions | Class Rewrites | Counts `<preference>` entries across all di.xml; WARN >= 20, FAIL >= 50 |
| Extensions | Duplicate Modules | FAIL if same module name exists in both app/code/ and vendor/ |
| Infrastructure | RequireJS Config | FAIL if pub/static/_requirejs missing in production |
| Infrastructure | Session Backend | WARN if using file-based sessions |

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

MIT — see [LICENSE](LICENSE)
