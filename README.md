# 🐾 PetNest — Pet Store REST API

A fully-featured e-commerce REST API for a pet store, built with Laravel 12. This project was built to practice and demonstrate real-world backend development concepts including authentication, authorization, caching, queues, transactions, soft deletes, and cloud deployment.

> **Live API:** https://petnest-production.up.railway.app

---

## 📌 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture Decisions](#architecture-decisions)
- [API Endpoints](#api-endpoints)
- [Project Structure](#project-structure)
- [Local Setup](#local-setup)
- [Deployment](#deployment)
- [What I Learned](#what-i-learned)

---

## Overview

PetNest is an API-only Laravel application (no MVC views) that powers a pet store platform. It supports two roles — **Admin** and **Customer** — with different levels of access. Customers can browse products, place orders, and manage their account. Admins can manage the full catalog, update order statuses, and oversee the platform.

---

## Features

### Auth
- Register & login with JWT (Tymon JWTAuth)
- Email verification on registration
- Forgot & reset password via email
- Token refresh & logout with blacklisting

### Products & Categories
- Public browsing with search, filtering by category, and sorting
- Pagination on all listing endpoints
- Caching with `Cache::remember()` for performance
- Soft delete — records are hidden not permanently removed
- Admin CRUD with restore support for soft-deleted records
- Slug auto-generation from name

### Orders
- Place orders with multiple items in one request
- Stock validation and automatic stock deduction on order
- DB Transactions — if anything fails mid-order, everything rolls back
- Concurrency protection with `lockForUpdate()` to prevent race conditions on stock
- Cancel orders with automatic stock restoration
- Admin order status management (pending → processing → shipped → delivered)
- Order confirmation email via Laravel Mailables

### Authorization
- Role-based middleware (`role:admin`, `role:customer`)
- Policies for fine-grained control (e.g. users can only access their own orders)
- Admin routes fully separated under `/api/admin/*`

### Other
- API Resources for consistent, clean response formatting
- Form Requests for all validation
- Soft Deletes on Products, Categories, and Orders
- Railway cloud deployment with Docker + Nginx + PHP-FPM

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.4 |
| Framework | Laravel 12 |
| Authentication | Tymon JWTAuth |
| Database | MySQL |
| Cache | File (Laravel Cache facade) |
| Mail | Laravel Mailables (Mailtrap for testing) |
| Web Server | Nginx + PHP-FPM |
| Containerization | Docker |
| Deployment | Railway |
| API Testing | Postman |

---

## Architecture Decisions

### API-Only, No MVC Views
This project uses Laravel purely as a backend API. There are no Blade views, no web routes for pages. Every response is JSON. This keeps the backend decoupled and ready to serve any frontend (React, mobile app, etc.).

### Role System with Integer Constants
Instead of a polymorphic roles package, roles are kept simple — a `role_id` integer on the `users` table with a separate `roles` table. Constants on the User model (`ROLE_ADMIN = 1`, `ROLE_CUSTOMER = 2`) keep the code readable without magic numbers scattered everywhere.

### Admin Routes Separation
All admin endpoints live under `/api/admin/*` behind two middleware layers: `auth:api` (JWT check) and `role:admin` (role check). This makes it immediately clear in the routes file what's public, what's authenticated, and what's admin-only.

### Transactions + Concurrency on Orders
The order placement flow uses `DB::transaction()` wrapping the entire operation. Inside the transaction, `lockForUpdate()` is used on each product row — this prevents two simultaneous requests from both seeing available stock and both succeeding when only one unit remains. If anything fails (out of stock, inactive product), the whole transaction rolls back.

### Soft Deletes Over Hard Deletes
Products, categories, and orders use `SoftDeletes`. This means deleting a product doesn't break historical order records that reference it. Admins can also restore accidentally deleted records. The public endpoints automatically exclude soft-deleted records via Eloquent's global scope.

### Caching Strategy
Public product and category listings are cached with `Cache::remember()` using dynamic cache keys built from query parameters. This means `?search=dog&category_id=2` gets its own cache entry separate from `?category_id=2`. Cache is busted whenever an admin creates, updates, or deletes a record.

### Docker + Nginx + PHP-FPM for Production
Rather than `php artisan serve` (dev only), production uses Nginx as the web server forwarding requests to PHP-FPM. A `start.sh` script handles migrations, seeding, and cache warming on every deploy before starting the services.

---

## API Endpoints

### Public
```
POST   /api/register
POST   /api/login
POST   /api/forgot-password
POST   /api/reset-password
GET    /api/email/verify/{id}/{hash}

GET    /api/products
GET    /api/products/{id}
GET    /api/categories
GET    /api/categories/{id}
```

### Authenticated (Customer)
```
POST   /api/logout
POST   /api/refresh
GET    /api/profile

GET    /api/orders
GET    /api/orders/{id}
POST   /api/orders
PATCH  /api/orders/{id}/cancel
```

### Admin Only
```
GET    /api/admin/products
POST   /api/admin/products
GET    /api/admin/products/{id}
PUT    /api/admin/products/{id}
DELETE /api/admin/products/{id}
POST   /api/admin/products/{id}/restore

GET    /api/admin/categories
POST   /api/admin/categories
GET    /api/admin/categories/{id}
PUT    /api/admin/categories/{id}
DELETE /api/admin/categories/{id}
POST   /api/admin/categories/{id}/restore

GET    /api/admin/orders
GET    /api/admin/orders/{id}
PATCH  /api/admin/orders/{id}/status
```

### Query Parameters (Products)
```
GET /api/products?search=dog&category_id=2&sort_by=price&sort_dir=asc&per_page=10&page=1
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── AdminAuthController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── OrderController.php
│   │   │   └── ProductController.php
│   │   ├── AuthController.php
│   │   ├── CategoryController.php
│   │   ├── OrderController.php
│   │   └── ProductController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   ├── Requests/
│   │   ├── RegisterRequest.php
│   │   ├── StoreCategoryRequest.php
│   │   ├── StoreProductRequest.php
│   │   ├── StoreOrderRequest.php
│   │   ├── UpdateCategoryRequest.php
│   │   └── UpdateProductRequest.php
│   └── Resources/
│       ├── CategoryResource.php
│       ├── OrderItemResource.php
│       ├── OrderResource.php
│       ├── ProductResource.php
│       └── UserResource.php
├── Mail/
│   └── OrderConfirmationMail.php
├── Models/
│   ├── Category.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Product.php
│   ├── Role.php
│   └── User.php
└── Policies/
    ├── CategoryPolicy.php
    ├── OrderPolicy.php
    └── ProductPolicy.php

docker/
├── nginx.conf
└── start.sh

Dockerfile
railway.json
```

---

## Local Setup

### Requirements
- PHP 8.4
- Composer
- MySQL
- Laravel CLI

### Steps

```bash
# 1. Clone the repo
git clone https://github.com/your-username/petnest.git
cd petnest

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Set your variables in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=petnest
DB_USERNAME=root
DB_PASSWORD=

# 5. Generate keys
php artisan key:generate
php artisan jwt:secret

# 6. Run migrations and seeders
php artisan migrate --seed

# 7. Start the server
php artisan serve
```

### Default Admin Credentials
```
Email:    admin@petnest.com
Password: secret123
```

---

## Deployment

This project is deployed on [Railway](https://railway.app) using Docker.

### Stack
- **PHP 8.4-FPM** — processes PHP
- **Nginx** — handles HTTP and forwards to PHP-FPM
- **MySQL** — Railway managed database service
- **Docker** — containerizes the whole app

### On Every Deploy (`start.sh` runs automatically)
1. Generates `.env` from Railway environment variables
2. Runs `php artisan migrate --force`
3. Seeds roles and admin user (skips if already exist)
4. Caches config, routes, and views for performance
5. Creates storage symlink
6. Starts PHP-FPM then Nginx

### Required Environment Variables on Railway
```
APP_NAME, APP_ENV, APP_KEY, APP_URL, APP_DEBUG
DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
JWT_SECRET
MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS
QUEUE_CONNECTION, CACHE_STORE
```

---

## What I Learned

**JWT Authentication** — Implementing stateless auth with Tymon JWTAuth, handling token blacklisting on logout, refresh token flow, and protecting routes with custom guards.

**API-Only Laravel** — Structuring a Laravel project without any views, using Form Requests for validation, API Resources for response shaping, and consistent JSON response patterns.

**Database Transactions & Concurrency** — Using `DB::transaction()` to ensure atomicity across multiple DB operations, and `lockForUpdate()` to handle race conditions when multiple users try to order the same limited-stock product simultaneously.

**Soft Deletes** — Understanding why hard deletes are dangerous in relational data (breaking order history), and using Laravel's `SoftDeletes` trait to implement recoverable deletion.

**Caching** — Using `Cache::remember()` with dynamic keys to cache filtered query results, and cache busting strategies when underlying data changes.

**Role-Based Authorization** — Building a custom middleware for role checking, writing Policies for resource-level authorization, and separating admin and customer route groups cleanly.

**Docker + Production Deployment** — Writing a Dockerfile from scratch, configuring Nginx + PHP-FPM for production, and deploying to Railway with environment variable injection and automated startup scripts.

**Email with Mailables** — Structuring Laravel Mailables with Markdown templates and integrating with Mailtrap for development email testing.

---

> Built by [Alaa](https://github.com/your-username) — Business Information Systems student & Backend instructor at Threedos
