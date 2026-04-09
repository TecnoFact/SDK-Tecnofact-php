# Security Policy

## Supported Versions

We actively support the following versions with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to: **security@tecnofact.com**

You should receive a response within 48 hours. If for some reason you do not, please follow up via email to ensure we received your original message.

Please include the following information:

- Type of issue (e.g., buffer overflow, SQL injection, cross-site scripting, etc.)
- Full paths of source file(s) related to the manifestation of the issue
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

## Security Best Practices

When using this SDK, please follow these security best practices:

### 1. API Credentials Management

**✅ DO:**
- Store API keys and secrets in environment variables
- Use `.env` files (excluded from version control)
- Rotate credentials regularly
- Use different credentials for sandbox and production

**❌ DON'T:**
- Hardcode credentials in source code
- Commit credentials to version control
- Share credentials via insecure channels
- Use production credentials in development

### 2. HTTPS Only

- Always use HTTPS endpoints (enforced by SDK)
- Verify SSL certificates (enabled by default)
- Never disable SSL verification in production

### 3. Input Validation

- Validate all user input before passing to SDK methods
- Sanitize data to prevent injection attacks
- Use type hints and strict types (enforced by SDK)

### 4. Error Handling

- Never expose sensitive information in error messages
- Log errors securely without exposing credentials
- Use try-catch blocks to handle exceptions properly

### 5. Dependency Management

- Keep dependencies up to date
- Review security advisories regularly
- Use Composer's `audit` command: `composer audit`
- Enable Dependabot for automated updates

### 6. Code Quality

- Run PHPStan at level 8+ before deployment
- Use PHP CS Fixer to maintain code standards
- Enable strict types: `declare(strict_types=1)`
- Review code changes before merging

## Security Features

This SDK implements the following security measures:

### Authentication
- API Key and Secret authentication
- Token-based session management
- Automatic token refresh
- Secure credential validation

### Transport Security
- TLS 1.2+ required
- Certificate validation enabled
- Secure headers (X-API-Key, X-API-Secret)
- Request/response encryption

### Input Validation
- Strict type checking (PHP 8.0+)
- RFC validation for tax IDs
- Amount and decimal validation
- Enum-based value constraints

### Error Handling
- Specific exception types
- Request ID tracking
- No credential exposure in errors
- Sanitized error messages

### Rate Limiting
- Automatic retry with exponential backoff
- Rate limit exception handling
- Configurable retry attempts
- 429 status code detection

## Vulnerability Disclosure Timeline

1. **Day 0**: Vulnerability reported to security@tecnofact.com
2. **Day 1-2**: Initial response and acknowledgment
3. **Day 3-7**: Vulnerability assessment and validation
4. **Day 8-30**: Patch development and testing
5. **Day 31**: Security advisory published
6. **Day 31+**: Patch released and users notified

## Security Scanning

This repository uses automated security scanning:

- **CodeQL**: Static analysis for security vulnerabilities
- **Dependabot**: Automated dependency updates
- **Symfony Security Checker**: Known vulnerability detection
- **TruffleHog**: Secret scanning in commits
- **PHPStan**: Static analysis at level 8+

## Compliance

This SDK is designed to help you comply with:

- **SAT (Mexico)**: CFDI 4.0 electronic invoicing requirements
- **GDPR**: Data protection and privacy
- **PCI DSS**: Payment card industry standards (when applicable)

## Contact

For security concerns, contact:
- **Email**: security@tecnofact.com
- **Website**: https://www.tecnofact.com
- **Support**: soporte@tecnofact.com

---

**Last Updated**: 2024-04-09
