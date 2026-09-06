# SmartCRM - Complete CRM & Lead Management System

SmartCRM is a production-quality CRM and Lead Management System built with PHP Laravel 11/12, MySQL (currently configured with SQLite for development), and Tailwind CSS. It features a modern responsive dashboard, role-based access control, lead management, customer management, sales pipelines, and more.

## Technology Stack

- **Backend:** PHP 8.2+, Laravel 12, Eloquent ORM
- **Frontend:** Laravel Blade, Tailwind CSS, Alpine.js, Chart.js
- **Authentication:** Custom Auth scaffolding with Spatie Laravel Permission
- **Database:** SQLite (Development) / MySQL (Production ready)

---

## Backend Functionality & Modules

The system is architected into specific modules. Below is a breakdown of the backend functionality currently implemented or scaffolded in the application:

### 1. Authentication & Authorization
- **Controllers:** `AuthController`
- **Routes:** `/login`, `/register`, `/logout`
- **Features:** 
  - Custom Login and Registration views.
  - Role-based access control using `spatie/laravel-permission` (Admin, Manager, User roles defined in seeder).
  - Session-based authentication.

### 2. Core Dashboard
- **Controllers:** `AuthController@dashboard`
- **Routes:** `/dashboard`
- **Features:** 
  - Dynamic KPI widgets aggregating data (Total Leads, Deals Won, Pending Follow-ups, Total Revenue).
  - Data pipelines feeding directly into Chart.js (Revenue Trends, Deals by Stage).
  - Recent deals activity table fetching the latest records.

### 3. Lead Management (Phase 2)
- **Models:** `Lead`, `LeadSource`, `LeadStatus`, `LeadActivity`
- **Controllers:** `LeadController`
- **Routes:** `/leads`
- **Features:** 
  - Schema mapping for capturing prospects (Name, Email, Phone, Company, Status, Score).
  - Index view featuring a dynamic data table with status badges and pagination.
  - Seeded with 142 initial dummy leads for demonstration.

### 4. Company & Customer Management (Phase 3)
- **Models:** `Company`, `Contact`, `Customer`
- **Controllers:** `CompanyController`, `ContactController`, `CustomerController`
- **Routes:** `Route::resource('/companies', CompanyController::class)`
- **Features:** 
  - Full CRUD scaffolding for Companies.
  - Data schema includes Name, Industry, Email, Phone, Website, and complete location data (Address, City, State, Zip, Country).
  - Form validation for creating new entities.
  - Interactive index tables.

### 5. Follow-Ups & Activities (Phase 4)
- **Models:** `FollowUp`
- **Features:** 
  - Schema mapping for tasks/meetings tied to leads.
  - Fields include Lead ID, Type (Call, Email, Meeting), Date, Notes, Status (Pending, Completed).

### 6. Sales Pipeline & Deal Management (Phase 5)
- **Models:** `Deal`, `DealStage`
- **Controllers:** `DealController`
- **Features:** 
  - Schema tracking Deal Name, Lead ID, Value, Stage (New, Qualified, Proposal, Negotiation, Won, Lost), Close Date.
  - Dynamic integration with Dashboard metrics and charts.
  - Seeded with initial realistic dummy deals.

### 7. Upcoming Scaffolding (Placeholders created)
The following modules have been planned, controllers generated, and sidebar routing mapped to placeholders, ready for future development:
- **Product Management:** `Product`, `ProductCategory`
- **Quotation Management:** `Quotation`, `QuotationItem`
- **Invoice Management:** `Invoice`, `InvoiceItem`
- **Payment Management:** `Payment`
- **System Management:** `Attachment`, `ActivityLog`, `Setting`

---

## Installation & Setup

1. **Clone the repository and install dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration:**
   Copy `.env.example` to `.env` and generate the app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Migration & Seeding:**
   This project uses SQLite for local development. Make sure your `.env` is configured for it, then run:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *(Note: The `DatabaseSeeder` will populate roles, an admin user, 142 leads, 38 won deals totaling $4.2M, and 12 pending follow-ups to match the dashboard).*

4. **Compile Frontend Assets:**
   ```bash
   npm run build
   ```
   *(Or run `npm run dev` for hot module replacement during development).*

5. **Start the Development Server:**
   ```bash
   php artisan serve
   ```
   Visit `http://localhost:8000` and login.
