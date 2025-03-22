# Contributing to SAM6-Claude

Thank you for considering contributing to SAM6-Claude! This document outlines the process for contributing to this project.

## Code of Conduct

Please be respectful and considerate of others when contributing to this project.

## How Can I Contribute?

### Reporting Bugs

- Check if the bug has already been reported
- Use the bug report template when creating an issue
- Include detailed steps to reproduce the bug
- Include screenshots if relevant
- Describe the expected behavior and what actually happened

### Suggesting Enhancements

- Check if the enhancement has already been suggested
- Use the feature request template when creating an issue
- Provide a clear description of the enhancement
- Explain why this enhancement would be useful

### Pull Requests

1. Fork the repository
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Commit your changes (`git commit -m 'Add some amazing feature'`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

## Development Setup

1. Clone the repository
2. Install dependencies with `composer install`
3. Copy .env.example to .env and configure your environment
4. Run database migrations: `php artisan migrate`
5. Start the development server: `php artisan serve`

## Coding Standards

- Follow PSR-2 coding style
- Write comprehensive PhpDoc comments
- Write tests for new features

## Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests after the first line

## Pull Request Process

1. Update the README.md with details of changes if appropriate
2. Update the documentation if necessary
3. The PR should work for all supported PHP versions
4. Your PR will be merged once approved by a maintainer

## License

By contributing, you agree that your contributions will be licensed under the project's license.