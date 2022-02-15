# About Project

A mini loan repayment application. that allows users to payback their load on weekly basis.

## Technology used

-   Laravel 9, PHP 8.1

-   Auth Sanctum Package used for api authentication

## Installation

Clone or pull the repository.

After cloning the repository, copy the `.env.example` file and rename it to `.env`.

Setup `.env` environment variables

Run the following commands from the project directory:

```bash
  composer install
  --------------
  php artisan key:generate
  --------------
  php artisan migrate
  --------------
  php artisan serve
  --------------
  php artisan schedule:run
```

## Usage

I have provided postman collection to check the application.

## Features

-   User Login, Register.
-   User loan auto aproval (initially I have approved 5000).
-   User can check Loan details that includes Credit Limit, Available balance and Spent
-   When user spent money, money will split into 4 emis
-   User can see upcoming emi to be paid (previous bounce emi added if any).
-   I have added schedule job to check the emi bounce.

## Instructions on testing with Postman

-   Register a user first. This will automatically login the user `/register`
-   To login, you need to logout if already logged in `/login`
-   User apply for loan using `\loan-application` url.
-   To fetch loan details `\get-loan-details`.
-   To use loan money `/spend-loan-money`.
-   To pay back (repayment) `/pay-back`.
