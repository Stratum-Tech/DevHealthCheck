# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 1.x     | Yes       |

## Reporting a Vulnerability

**Do not open a public GitHub issue for security vulnerabilities.**

Please report security issues by emailing **security@stratum-tech.com**. Include:

- A description of the vulnerability and its potential impact
- Steps to reproduce or a proof-of-concept
- Any suggested remediation if known

You can expect an acknowledgement within **48 hours** and a status update within **7 days**.

Once a fix is confirmed, we will coordinate a release and credit the reporter (unless anonymity is requested).

## Scope

This module runs as a Magento CLI tool. Relevant security concerns include:

- Information disclosure via check output (e.g. leaking credentials, paths, or config values)
- Privilege escalation via Magento DI injection
- Unsafe handling of file paths or shell commands within check implementations
