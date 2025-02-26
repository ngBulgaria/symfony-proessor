# Symfony Console Application for Transaction Processing

This is a Symfony Console application that processes transactions from a file, fetches exchange rates, and calculates transaction fees.

## Installation

1. Clone the repository:
   git clone https://github.com/ngBulgaria/symfony-proessor.git
   cd symfony-processor

2. Install dependencies:
   composer install

3. Copy environment variables (if applicable):
   cp .env.example .env

4. Dump autoload (optional, if VS Code does not recognize classes):
   composer dump-autoload

5. Set the API key in the .env file

## Shortcut commands
composer test            Runs PHPUnit tests
composer lint            Checks code style with PHP CodeSniffer
composer analyze         Runs static analysis with PHPStan
