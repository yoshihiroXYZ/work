# Quiz App

This repository contains a simple PHP quiz application. The `public/` directory
is intended to be served as the document root, and the `includes/` directory
provides helper functions for database access, authentication, and CSRF
protection.

## Setup

1. Configure the database connection values in `includes/db.php`.
2. Serve the `public/` directory with a PHP-capable web server.

## Testing

Run the included tests with:

```bash
php tests/csrf_test.php
```

## Persistent Login

To keep administrators logged in for longer periods, increase
`session.cookie_lifetime` or implement a token-based "Remember me" feature.

