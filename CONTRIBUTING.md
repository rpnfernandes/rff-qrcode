# Contributing to RFF QR Code

Thank you for your interest in contributing to **RFF QR Code**!

## Code of Conduct

Please be respectful, constructive, and considerate when opening issues or pull requests.

## Bug Reports & Feature Requests

- Check if an issue already exists before opening a new one.
- Provide a clear, reproducible code snippet showing input, expected output, and actual behavior.
- Specify your PHP version and operating system.

## Pull Request Guidelines

1. **Fork the repository** and create your branch from `main`:
   ```bash
   git checkout -b feature/my-feature
   ```
2. **Follow PSR-12 and PSR-4 coding standards**:
   - Keep `declare(strict_types=1);` at the top of PHP files.
   - Strictly adhere to zero-dependency principles for runtime code.
3. **Write and run tests**:
   Ensure all tests pass before submitting a PR:
   ```bash
   php tests/run_tests.php
   # or with phpunit
   vendor/bin/phpunit
   ```
4. **Update documentation**:
   - Update `README.md` and `CHANGELOG.md` if public API or behavior changes.
5. **Open a Pull Request** with a detailed explanation of changes.
