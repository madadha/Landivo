# Landivo

Landivo is a professional Laravel platform for building, publishing, and managing multilingual landing pages, product offers, dynamic order forms, leads, and order workflows.

## Features

- Arabic and English landing pages with locale-aware content and flags.
- Dynamic Form Builder with text, email, phone, number, textarea, select, radio, checkbox, date, file, image, address, city, country, quantity, and product variant fields.
- Conditional fields, translated labels/placeholders, validation rules, and configurable offer choices.
- Product images in Arabic and English, pricing, discounts, galleries, sliders, social links, countdowns, SEO, custom HTML, and favicon support.
- Public landing-page directory at `/` and `/landing/`.
- Lead/order capture with customer details, selected offer, tracking parameters, IP address, browser data, activities, attachments, invoices, and WhatsApp actions.
- Order notification emails configurable per landing page.
- Filament admin panel with dashboard, users, roles, permissions, system settings, company branding, logo, favicon, and profile photo.
- Responsive public pages for desktop, tablet, and mobile.

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- SQLite, MySQL, or another Laravel-supported database

## Local installation

```bash
git clone git@github.com:madadha/Landivo.git
cd Landivo
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open:

- Public landing-page directory: `http://127.0.0.1:8000/`
- Admin panel: `http://127.0.0.1:8000/admin`

## Local Laragon

For Laragon, place the project in `C:\laragon\www\Landivo` and use:

`http://landivo.test:8000`

Make sure the database values in `.env` match your local environment. The project can also run with SQLite by setting `DB_CONNECTION=sqlite` and configuring the database path.

## Admin configuration

After signing in:

- Create or edit products from **Products**.
- Create and publish landing pages from **Landing Pages**.
- Configure the dynamic form and offer choices inside the landing page editor.
- Add notification email addresses in **Landing Page → Basic Information**.
- Configure company name, details, default language, country phone code, logo, and favicon from **System Settings**.
- Manage users and roles from the account menu.

## Email notifications

Set the mail transport in `.env` before using production email delivery. For example:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-user
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Never commit real credentials or the `.env` file.

## Useful commands

```bash
php artisan optimize:clear
php artisan migrate
php artisan test
npm run build
```

## License

This project is maintained for the Landivo platform. Add the final commercial license terms before public distribution.
