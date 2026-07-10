# KivuBoost

KivuBoost is a social media marketing (SMM) panel built for the Bukavu market (Democratic Republic of Congo). It allows users to purchase social media engagement services (followers, views, likes, etc.) on platforms such as Instagram, TikTok, YouTube, Facebook, and others. It supports multiple wholesale API providers, multi-currency billing (USD / CDF), and an integrated admin dashboard.

---

## Table of Contents

- [Features](#features)
- [Technical Stack](#technical-stack)
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Deployment](#deployment)
- [Routes Overview](#routes-overview)
- [Security Notes](#security-notes)

---

## Features

**Client-side**
- User registration and login (email/password + Google OAuth2)
- Wallet system with balance in USD or CDF
- Service catalog with live filtering by platform and category
- Order placement (single and mass orders)
- Subscription-based orders
- Order history with status tracking
- Loyalty rewards system with point redemption
- Mobile money recharge (M-Pesa, Airtel Money, Orange Money, Vodacom)
- Online payment via BkaPay (hosted checkout with webhook)
- Support ticket system (create, reply, close)
- Public news feed
- Client API access with key generation and documentation

**Admin-side**
- Secure administration panel restricted to admin and superadmin roles
- Pending recharge approval and rejection with reason
- Multi-provider (multi-API) wholesale management
- Service synchronization from external SMM providers
- Dynamic pricing rules with bulk application
- Service visibility management (show/hide per client)
- Bulk service actions (toggle, delete)
- User management with balance adjustment (superadmin only)
- Order retry and status synchronization with external providers
- Email campaign sender (SMTP via PHPMailer)
- Financial report dashboard
- Audit log of all admin actions
- Support agent management (WhatsApp-based)
- News article creation and management

---

## Technical Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.1+ |
| Architecture | Custom MVC (no framework) |
| Database | MySQL / MariaDB |
| Frontend | HTML, Tailwind CSS (CDN), Vanilla JS |
| Email | PHPMailer (SMTP) |
| Authentication | Session-based + Google OAuth2 |
| Payment | BkaPay hosted checkout + webhook |
| Hosting (production) | InfinityFree (shared hosting) |
| Timezone | Africa/Lubumbashi (UTC+2) |

---

## Project Structure

```
kivuboost/
├── index.php                  # Front controller (single entry point)
├── config/
│   ├── config.php             # Central configuration (DB, URLs, OAuth, autoloader)
│   └── mailer_config.php      # SMTP / PHPMailer configuration
├── app/
│   ├── Controllers/           # HTTP request handlers
│   │   ├── AdminController.php
│   │   ├── ApiController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── GoogleAuthController.php
│   │   ├── NewsController.php
│   │   ├── OrderController.php
│   │   ├── RechargeController.php
│   │   └── SupportController.php
│   ├── Models/                # Database access layer
│   │   ├── User.php
│   │   ├── Order.php
│   │   ├── Recharge.php
│   │   ├── Service.php
│   │   ├── Provider.php
│   │   ├── Setting.php
│   │   ├── News.php
│   │   ├── Loyalty.php
│   │   ├── PaymentGateway.php
│   │   ├── PricingRule.php
│   │   ├── SupportTicket.php
│   │   ├── SupportMessage.php
│   │   └── SupportAgent.php
│   ├── Views/                 # PHP template files
│   │   ├── admin/             # Admin panel views
│   │   ├── auth/              # Login, register pages
│   │   ├── client/            # Client-facing pages
│   │   ├── dashboard/         # User dashboard
│   │   ├── emails/            # Email templates
│   │   ├── errors/            # Error pages (403, 404...)
│   │   ├── layouts/           # Shared layout templates
│   │   ├── news/              # Public news pages
│   │   ├── recharge/          # Wallet recharge flow
│   │   └── support/           # Support ticket pages
│   ├── Core/                  # Framework core classes
│   │   ├── Auth.php           # Session auth helper, CSRF
│   │   ├── Audit.php          # Admin action logging
│   │   ├── Controller.php     # Base controller
│   │   ├── Currency.php       # USD/CDF conversion and formatting
│   │   ├── Database.php       # PDO wrapper
│   │   ├── RateLimiter.php    # Request rate limiting
│   │   └── Router.php         # URL dispatcher
│   ├── Services/
│   │   └── SmmApi.php         # SMM provider API client
│   └── Libraries/             # Third-party libraries
├── api/
│   └── v1/
│       └── index.php          # Public REST API for external clients
├── database/
├── database.sql               # Initial database schema and seed data
├── migrate_multidevise.sql    # Migration for multi-currency support
├── support_migration.sql      # Migration for support ticket tables
├── assets/                    # Static assets (CSS, JS, images)
├── public/                    # Publicly accessible files
├── logs/                      # Application logs
└── cron/                      # Cron job scripts
```

---

## Installation

### Requirements

- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled (or Nginx with equivalent rewrite rules)
- A working SMTP server or Gmail credentials for email sending

### Steps

1. Clone the repository:

```bash
git clone https://github.com/johnmoka111/KivuBoost.git
cd KivuBoost
```

2. Place the project folder in your web server root (e.g., `htdocs/KivuBoost` for XAMPP).

3. Create the database and import the schema:

```bash
mysql -u root -p < database.sql
```

4. Run additional migrations if needed:

```bash
mysql -u root -p bukavuboost < migrate_multidevise.sql
mysql -u root -p bukavuboost < support_migration.sql
```

5. Configure the application (see section below).

6. Open `http://localhost/KivuBoost/setup.php` in your browser to create the superadmin account, then delete or protect `setup.php`.

---

## Configuration

Open `config/config.php` and review the following sections.

### Database

The configuration automatically detects the environment (local vs. production) based on the hostname.

```php
// Local development
define('DB_HOST', 'localhost');
define('DB_NAME', 'bukavuboost');
define('DB_USER', 'root');
define('DB_PASS', '');

// Production (InfinityFree example)
define('DB_HOST', 'sql101.infinityfree.com');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_password');
```

### Google OAuth2

To enable Google login, create an OAuth2 application at [https://console.cloud.google.com](https://console.cloud.google.com) and fill in:

```php
define('GOOGLE_CLIENT_ID',     'your-client-id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'your-client-secret');
define('GOOGLE_REDIRECT_URI',  APP_URL . '/auth/google/callback');
```

The redirect URI must be registered exactly in the Google Cloud Console.

### Payment Gateway (BkaPay)

BkaPay credentials are managed through the admin settings panel at `/admin/settings`, stored in the database. No hardcoded keys are required.

### SMTP / Email

Configure your SMTP server in `config/mailer_config.php`.

---

## Database Setup

The main schema is in `database.sql`. It creates the following tables:

| Table | Description |
|---|---|
| `users` | Registered clients and admins |
| `providers` | Wholesale SMM API providers |
| `services` | Available SMM services with pricing |
| `orders` | Client orders linked to services |
| `recharges` | Wallet top-up requests |
| `settings` | Key-value application configuration |

Additional tables are created by the migration files for support tickets, loyalty points, payment gateways, pricing rules, and news articles.

---

## Deployment

### Apache (.htaccess)

A `.htaccess` file is included at the root. It redirects all requests to `index.php` to support clean URLs. Ensure `AllowOverride All` is enabled in your Apache virtual host.

### Production checklist

- Set `display_errors` to `0` in `index.php` (currently enabled for debugging).
- Protect or delete `setup.php` after first use.
- Ensure the `logs/` directory is not publicly accessible.
- Use HTTPS. The session cookie is automatically set to `secure` when HTTPS is detected.
- Register the BkaPay webhook URL in your BkaPay dashboard: `https://yourdomain.com/api/webhook/bkapay`

---

## Routes Overview

| Method | URL | Description |
|---|---|---|
| GET | `/` | Public homepage with news |
| GET/POST | `/login` | User login |
| GET/POST | `/register` | User registration |
| GET | `/auth/google` | Google OAuth2 login |
| GET | `/dashboard` | Client dashboard |
| GET | `/services` | Service catalog |
| POST | `/orders/place` | Place a single order |
| POST | `/orders/mass-place` | Place multiple orders |
| GET | `/recharge` | Wallet recharge page |
| POST | `/recharge/online/initiate` | Start BkaPay online payment |
| GET | `/rewards` | Loyalty rewards page |
| GET | `/tickets` | Support tickets list |
| GET | `/admin` | Admin dashboard |
| GET | `/admin/configuration` | Admin configuration |
| GET | `/admin/financial-report` | Financial report |
| GET | `/admin/audit` | Audit log |
| GET | `/admin/pricing-rules` | Pricing rules management |
| GET | `/api/v1/` | External REST API endpoint |

---

## Security Notes

- CSRF protection is applied to all POST forms via `Auth::csrfField()`.
- Passwords are hashed using `password_hash()` with the default bcrypt algorithm.
- Session cookies are `HttpOnly`, `SameSite=Lax`, and `Secure` on HTTPS.
- Admin and superadmin routes are protected by role checks in each controller.
- Rate limiting is available via `Core/RateLimiter.php`.
- The `SMM_PLACEHOLDER_KEY` constant prevents placeholder API keys from being sent to external providers.

---

## License

This project is proprietary software. All rights reserved.
