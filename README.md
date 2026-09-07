# SmartCRM - Enterprise Customer Relationship Management System

SmartCRM is a production-ready, full-stack Enterprise CRM and Sales Automation platform built with **Laravel 11/12** on the backend and **React 18 + Vite + Tailwind CSS** on the frontend. It features live database-driven analytics, Spatie Role-Based Access Control (RBAC), multi-stage Kanban deal pipelines, polymorphic custom fields, activity streams, calendar scheduling, and dynamic reporting.

---

## 📑 Table of Contents
1. [System Architecture Diagram](#1-system-architecture-diagram)
2. [Database Entity Relationship Diagram (ERD)](#2-database-entity-relationship-diagram-erd)
3. [Sidebar Navigation & Module Capabilities](#3-sidebar-navigation--module-capabilities)
4. [End-to-End Workflow Sequence Diagram](#4-end-to-end-workflow-sequence-diagram)
5. [Core Technology Stack](#5-core-technology-stack)
6. [Key Modules & Features](#6-key-modules--features)
7. [Installation & Setup](#7-installation--setup)
8. [API Endpoints Reference](#8-api-endpoints-reference)

---

## 1. System Architecture Diagram

This diagram describes the high-level 4-tier architecture of SmartCRM, illustrating the data flow from the React SPA client to the Laravel REST API backend, authorization layer, and relational database.

![System Architecture](docs/diagrams/system-architecture.jpg)

### Architecture Breakdown:
- **Frontend Client Layer (React 18 + Vite + Tailwind CSS)**: Modern single-page application (SPA) featuring responsive layouts, interactive Kanban boards, and Chart.js analytics. Managed with `React Router` and centralized `AuthContext` for session persistence.
- **Axios Service Layer (`services/api.js`)**: Centralized HTTP client configured with request/response interceptors to automatically attach Bearer tokens and handle 401 unauthorized redirects.
- **HTTP REST API Transport**: Secure JSON data exchange over RESTful API routes (`http://127.0.0.1:8000/api/*`).
- **Backend Application Layer (Laravel 11/12)**:
  - **Laravel Sanctum**: Token-based authentication and secure API guard.
  - **FormRequests**: Strict validation, field type casting, and sanitization.
  - **Spatie RBAC & Policies**: Database-backed permissions enforcing capabilities for Admin, Manager, Sales Representative, and Viewer roles.
  - **Eloquent ORM**: Object-relational mapping managing complex polymorphic associations (activities, custom fields, tasks, calendar events).
- **Database & Storage Layer**: Relational persistence for CRM entities, key-value settings store, and activity audit trails.

```mermaid
flowchart TB
    subgraph ClientLayer["🖥️ Frontend Client Layer (React 18 + Vite + Tailwind CSS)"]
        UI["Modern SPA UI (Responsive Dashboard, Tables, Kanban, Modals)"]
        Router["React Router (Protected Routes & Navigation)"]
        State["Auth Context (Session Token, Profile, Role, Notifications)"]
        AxiosClient["Axios Service Layer (services/api.js + Bearer Token Interceptor)"]
        UI --> Router --> State --> AxiosClient
    end

    subgraph Network["🌐 HTTP REST API Transport (JSON)"]
        APIEndpoints["Base URL: http://127.0.0.1:8000/api/*"]
    end

    subgraph BackendLayer["⚙️ Backend Application Layer (Laravel 11)"]
        Sanctum["Laravel Sanctum (Token Auth & Guard)"]
        FormRequests["FormRequests (Validation & Field Sanitization)"]
        RBAC["Spatie RBAC & Policies (Admin, Manager, Sales Rep, Viewer)"]
        
        subgraph Controllers["API Controllers"]
            AuthController["AuthController (/login, /register, /logout)"]
            DashboardController["DashboardController (/dashboard)"]
            LeadController["LeadController (/leads, /leads/metadata)"]
            ContactController["ContactController (/contacts, /contacts/metadata)"]
            DealController["DealController (/deals, /deal-stages)"]
            TaskController["TaskController (/tasks, /tasks/metadata)"]
            CalendarController["CalendarEventController (/calendar-events)"]
            ReportController["ReportController (/reports)"]
            AdminControllers["UserController, RoleController, SettingController, CustomFieldController"]
        end

        Eloquent["Eloquent ORM (Models & Polymorphic Relations)"]
    end

    subgraph DataStorage["🗄️ Database & Storage Layer"]
        DB[(Primary Database: SQLite / MySQL)]
        AuditStore[("Audit Logs & Activity Streams")]
        SettingsStore[("Key-Value Settings Table")]
    end

    AxiosClient -->|JSON Requests with Bearer Token| Network
    Network --> Sanctum --> FormRequests --> RBAC --> Controllers
    Controllers --> Eloquent
    Eloquent --> DB & AuditStore & SettingsStore
    Eloquent -.->|Computed JSON Responses| Controllers
    Controllers -.->|Formatted JSON Data| AxiosClient
```

---

## 2. Database Entity Relationship Diagram (ERD)

This diagram details all database entities, attributes, primary/foreign keys, and polymorphic relationships across the system.

![Entity Relationship Diagram](docs/diagrams/entity-relationship-diagram.jpg)

### Entity Relationships Summary:
- **Users & Spatie Roles**: Users have many-to-many roles and granular permissions. Users own Leads, Deals, Contacts, Tasks, and Calendar Events.
- **Companies & Accounts**: Central business entity linked one-to-many with Contacts, Leads, and Deals.
- **Leads & Deal Conversion**: Leads have Lead Statuses and Lead Sources. When qualified, leads convert into permanent Contacts and Deals.
- **Deals & Pipeline Stages**: Deals belong to dynamic Deal Stages (`order_index`, `probability`, `is_won`, `is_lost`) and connect to Companies and Contacts.
- **Polymorphic Activities & Custom Fields**: `activities`, `tasks`, `calendar_events`, and `custom_field_values` use polymorphic morph relations (`subject_type`, `subject_id`) to attach seamlessly to any CRM model.

```mermaid
erDiagram
    USERS ||--o{ LEADS : "owns"
    USERS ||--o{ DEALS : "owns"
    USERS ||--o{ CONTACTS : "owns"
    USERS ||--o{ TASKS : "assigned_to"
    USERS ||--o{ CALENDAR_EVENTS : "organizes"
    USERS ||--o{ NOTIFICATIONS : "receives"
    USERS }o--o{ ROLES : "has_roles (Spatie)"

    COMPANIES ||--o{ CONTACTS : "employs"
    COMPANIES ||--o{ LEADS : "associated_with"
    COMPANIES ||--o{ DEALS : "has"

    LEADS ||--o{ CONTACTS : "converts_to"
    LEADS ||--o{ DEALS : "converts_to"
    LEAD_STATUSES ||--o{ LEADS : "status_of"
    LEAD_SOURCES ||--o{ LEADS : "source_of"

    DEAL_STAGES ||--o{ DEALS : "pipeline_stage"
    CONTACTS ||--o{ DEALS : "primary_contact"

    USERS ||--o{ ACTIVITIES : "logged_by"
    LEADS ||--o{ ACTIVITIES : "polymorphic (subject)"
    DEALS ||--o{ ACTIVITIES : "polymorphic (subject)"
    CONTACTS ||--o{ ACTIVITIES : "polymorphic (subject)"

    LEADS ||--o{ CUSTOM_FIELD_VALUES : "polymorphic"
    DEALS ||--o{ CUSTOM_FIELD_VALUES : "polymorphic"
    CONTACTS ||--o{ CUSTOM_FIELD_VALUES : "polymorphic"
    CUSTOM_FIELDS ||--o{ CUSTOM_FIELD_VALUES : "defines"

    USERS {
        bigint id PK
        string name
        string email
        string password
    }

    LEADS {
        bigint id PK
        string first_name
        string last_name
        string email
        string company
        int score
        bigint owner_id FK
        bigint lead_status_id FK
        bigint lead_source_id FK
    }

    DEALS {
        bigint id PK
        string name
        decimal value
        int probability
        bigint deal_stage_id FK
        bigint company_id FK
        bigint contact_id FK
        bigint owner_id FK
    }

    CONTACTS {
        bigint id PK
        string first_name
        string last_name
        string email
        string phone
        string job_title
        bigint company_id FK
    }

    TASKS {
        bigint id PK
        string title
        string priority
        string status
        date due_date
        bigint assign_to_id FK
    }

    CALENDAR_EVENTS {
        bigint id PK
        string title
        datetime start_time
        datetime end_time
        string event_type
        bigint user_id FK
    }
```

---

## 3. Sidebar Navigation & Module Capabilities

This diagram outlines the 8 primary application modules accessible from the sidebar navigation and their specific capabilities.

![Sidebar Navigation & Module Capabilities](docs/diagrams/module-navigation-map.jpg)

### Module Breakdown:
1. **📊 Executive Dashboard (`/dashboard`)**: Live KPI metrics (Total Leads, Open Deals, Won Deals, Won Revenue, Win Rate, Pending Tasks), dynamic chart series, sales performance leaderboard, and real-time activity stream.
2. **👥 Leads Management (`/dashboard/leads`)**: Prospect acquisition, scoring algorithms, status tags, search/filter/sort, CSV bulk import, and 1-click deal conversion.
3. **🤝 Deals Pipeline (`/dashboard/deals`)**: Interactive Kanban board with drag-and-drop stage updates, revenue aggregation per stage, probability indicators, and won/loss management.
4. **📇 Contacts Directory (`/dashboard/contacts`)**: Complete customer directory with company associations, primary contact designations, communication history, and custom field values.
5. **📅 Calendar Schedule (`/dashboard/calendar`)**: Month, week, and day view scheduling for client demos, calls, meetings, and deadlines.
6. **✓ Tasks & Velocity (`/dashboard/tasks`)**: Task tracking categorized by All, Pending, In Progress, Completed, and Overdue, with priority filters and instant completion toggles.
7. **📈 Advanced Reports (`/dashboard/reports`)**: Multi-dimensional analytics filtered by date range, team member, role, lead source, and deal stage with conversion funnels.
8. **⚙️ System Settings (`/dashboard/settings`)**: Full administration panel for System Branding, Company Profile, Users CRUD, Spatie RBAC Permission Matrix, Pipeline Stages, Custom Fields, SMTP Mail, and Webhook Integrations.

```mermaid
flowchart LR
    subgraph Navigation["Sidebar Navigation (/dashboard/*)"]
        direction TB
        M1["📊 Dashboard"]
        M2["👥 Leads Management"]
        M3["🤝 Deals Pipeline (Kanban)"]
        M4["📇 Contacts Directory"]
        M5["📅 Calendar Schedule"]
        M6["✓ Tasks & Velocity"]
        M7["📈 Advanced Reports"]
        M8["⚙️ System Settings"]
    end

    subgraph ModuleActions["Module Capabilities & Actions"]
        M1 --> D_Act["Real-Time KPIs • Revenue Trends • Stage Bars • Leaderboard • Live Activities"]
        M2 --> L_Act["CRUD • Lead Scoring • Status Updates • CSV Import • Convert to Deal"]
        M3 --> K_Act["Multi-Stage Drag & Drop • Win/Loss Tracking • Deal Value Aggregation"]
        M4 --> C_Act["Directory • Company Linking • Primary Flags • Contact Details & Notes"]
        M5 --> Cal_Act["Month / Week / Day Views • Add / Reschedule Events • Demos & Calls"]
        M6 --> T_Act["All / Pending / In Progress / Completed / Overdue • Priority Filters • Quick Complete"]
        M7 --> R_Act["Multi-Dimensional Filters (Date, User, Team, Source, Stage) • Funnels • Analytics"]
        M8 --> S_Act["Users CRUD • Spatie RBAC Matrix • Custom Fields • Deal Stages • SMTP • Webhooks"]
    end
```

---

## 4. End-to-End Workflow Sequence Diagram

This sequence diagram depicts the chronological interaction from user login to lead capture, deal conversion, Kanban movement to closed-won, and real-time dashboard analytics recalculation.

![End-to-End Workflow Sequence Diagram](docs/diagrams/e2e-sequence-diagram.jpg)

```mermaid
sequenceDiagram
    autonumber
    actor User as Sales Rep / Admin
    participant React as React Frontend (SPA)
    participant Auth as Auth & RBAC Interceptor
    participant API as Laravel API Controllers
    participant DB as SQLite / MySQL Database
    participant LiveUI as Dashboard & Reports

    %% 1. Authentication
    User->>React: Enters Login Credentials
    React->>API: POST /api/login
    API->>DB: Verify credentials & load Spatie roles
    DB-->>API: User Record with Roles & Permissions
    API-->>React: 200 OK (access_token + user data)
    React->>Auth: Save token in localStorage & set AuthContext

    %% 2. Lead Ingestion & Qualification
    User->>React: Submits Lead Form (+ Add Lead)
    React->>API: POST /api/leads/auth-store (Bearer Token)
    API->>Auth: Verify user has 'create-leads' permission
    API->>DB: INSERT into `leads` & `activities`
    DB-->>API: Saved Lead Model
    API-->>React: 201 Created (New Lead Object)
    React->>User: Display Lead in Table & Live Notification

    %% 3. Conversion to Deal & Pipeline
    User->>React: Click 'Convert to Deal' ($75,000, Stage: Proposal)
    React->>API: POST /api/deals
    API->>DB: INSERT into `deals`, link `contacts` & `companies`
    DB-->>API: Created Deal
    API-->>React: 201 Created

    %% 4. Kanban Movement to Won
    User->>React: Drags Deal card from 'Proposal' to 'Won'
    React->>API: PATCH /api/deals/{id}/stage (Stage: Won)
    API->>DB: UPDATE deal status to 'won' + INSERT audit_log
    DB-->>API: Updated Deal

    %% 5. Real-Time Dynamic Dashboard Reflection
    React->>API: GET /api/dashboard & GET /api/reports
    API->>DB: Re-calculate Total Revenue, Won Deals, Win Rate, and Chart Series
    DB-->>API: Aggregated Dynamic KPIs ($ Won Revenue, 100% Win Rate)
    API-->>React: 200 OK (JSON Chart Series & KPI Objects)
    React->>LiveUI: Re-renders Revenue Line Chart, Leaderboard & Activity Feed
```

---

## 5. Core Technology Stack

- **Backend Framework**: PHP 8.2+, Laravel 11/12
- **Frontend SPA**: React 18, Vite 8, Tailwind CSS, Chart.js, React ChartJS 2
- **Authentication & Security**: Laravel Sanctum (Bearer Token), Spatie Laravel Permission (RBAC)
- **Database**: SQLite (Development) / MySQL 8.0+ / PostgreSQL (Production)
- **Testing & Quality Assurance**: PHPUnit Feature & Unit Test Suites (32 Suites / 339 Assertions)

---

## 6. Key Modules & Features

- **100% Database Persistence**: Zero hardcoded records or static mock data.
- **Strict Role-Based Access Control (RBAC)**:
  - **Admin**: Full unrestricted system access and configuration.
  - **Manager**: Full CRM management (Leads, Deals, Contacts, Tasks, Calendar, Pipelines, Custom Fields).
  - **Sales Representative**: Daily operations (View, create, edit records; cannot delete or modify system settings).
  - **Viewer**: Read-only access across CRM modules (modification requests blocked with `403 Forbidden`).
- **Polymorphic Activity Stream**: Unified timeline recording notes, calls, meetings, task completions, and deal stage changes.
- **Dynamic Kanban Engine**: Drag-and-drop deals across custom-defined stages with automatic probability calculation.
- **Custom Field Builder**: Add custom text, number, date, select, textarea, or boolean fields to Leads, Deals, Contacts, and Companies without changing database schemas.

---

## 7. Installation & Setup

### 1. Prerequisites
- PHP >= 8.2 with OpenSSL, PDO, Mbstring, Tokenizer, XML, and Ctype extensions.
- Composer >= 2.0
- Node.js >= 18.0 & NPM

### 2. Backend Setup
```bash
# Clone the repository
git clone https://github.com/your-org/smart-crm.git
cd smart-crm

# Install PHP dependencies
composer install

# Environment configuration
cp .env.example .env
php artisan key:generate

# Run database migrations and seeders
php artisan migrate:fresh --seed

# Start the Laravel development server
php artisan serve
```

### 3. Frontend Setup
```bash
# Navigate to the frontend directory
cd portal-frontend

# Install Node dependencies
npm install

# Start the Vite development server
npm run dev
```

### 4. Running Automated Tests
```bash
# Execute the full PHPUnit test suite
php artisan test
```

---

## 8. API Endpoints Reference

| HTTP Verb | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/login` | Authenticate user & receive bearer token | No |
| `POST` | `/api/register` | Register new user account | No |
| `POST` | `/api/leads` | Public lead capture endpoint | No |
| `GET` | `/api/user` | Fetch authenticated user profile & roles | Yes |
| `GET` | `/api/dashboard` | Fetch dynamic KPIs and chart series | Yes |
| `GET` | `/api/reports` | Multi-dimensional report metrics | Yes |
| `GET / POST` | `/api/leads` | List leads (paginated/filtered) / Auth create | Yes |
| `GET / PUT / DELETE` | `/api/leads/{id}` | Lead profile details / update / delete | Yes |
| `GET / POST` | `/api/deals` | List deals / Create opportunity | Yes |
| `PATCH` | `/api/deals/{id}/stage` | Update deal pipeline stage | Yes |
| `GET / POST` | `/api/contacts` | Contacts directory CRUD | Yes |
| `GET / POST` | `/api/tasks` | Tasks management CRUD | Yes |
| `GET / POST` | `/api/calendar-events` | Calendar events & scheduling | Yes |
| `GET / POST` | `/api/settings` | Retrieve / Persist system & email settings | Yes (Admin) |
| `GET / POST` | `/api/users` | Users management & role assignment | Yes (Admin) |
| `GET / POST` | `/api/roles` | Spatie roles & permissions management | Yes (Admin) |
| `GET / POST` | `/api/deal-stages` | Pipeline stages & win probabilities | Yes (Admin/Manager) |
| `GET / POST` | `/api/custom-fields` | Polymorphic custom fields builder | Yes (Admin/Manager) |

---

## 📄 License
This project is licensed under the MIT License - see the LICENSE file for details.
