# Copilot Instructions for Multi-Vendor Ecommerce Platform

## Project Overview
- This is a PHP-based multi-vendor ecommerce platform. The architecture is organized by user roles (admin, vendor, customer) with separate directories for each (`admin/`, `vendor/`, `pages/`).
- The main entry points are `index.php` (site root), `landing.php` (public landing), and role-specific dashboards (e.g., `admin/dashboard.php`, `vendor/dashboard.php`).
- Shared logic and configuration are in `includes/` (e.g., `auth.php`, `config.php`, `database.php`).
- Static assets are under `assets/` (CSS, JS, images, fonts).

## Data Flow & Integration
- Database schema is defined in `database/ecommerce.sql`. All PHP modules interact with MySQL via `includes/database.php`.
- Authentication and session management are handled in `includes/auth.php` and `includes/session.php`.
- Email integration is via `includes/mail.php`.
- File uploads (e.g., product images, vendor logos) are stored in `uploads/`.

## Developer Workflows
- **Setup:** Import `database/ecommerce.sql` into MySQL. Configure DB credentials in `includes/config.php`.
- **Debugging:** Use `debug.php` for ad-hoc debugging. Errors are not centrally logged; add custom logging as needed.
- **No build step:** This is a pure PHP project; changes are reflected immediately.
- **Testing:** No automated tests present. Manual testing via browser is standard.

## Project-Specific Patterns
- Role-based access: Each role has its own directory and dashboard. Use `includes/middleware.php` for access control.
- Page structure: Most pages start with `include 'header.php'` and end with `include 'footer.php'`.
- CSS is modularized by type (base, components, layout, pages, utilities) under `assets/css/`.
- JS is split by function (`admin.js`, `cart.js`, `script.js`).

## External Dependencies
- No package manager (e.g., Composer) is actively used, despite presence of `composer.json`.
- No frontend framework; all UI is custom HTML/CSS/JS.

## Examples
- To add a new admin page: create `admin/newpage.php`, include shared headers/footers, and use functions from `includes/`.
- To add a new product image: upload to `uploads/products/` and reference in the database.

## Key Files & Directories
- `includes/`: Shared PHP logic
- `admin/`, `vendor/`, `pages/`: Role-specific modules
- `assets/`: Static files
- `database/ecommerce.sql`: DB schema
- `uploads/`: User and product files

---

For questions about unclear conventions or missing documentation, ask the user for clarification or examples from existing code.
