# FleetTrack — Fleet Management System

A web-based **Fleet Management System** built with **PHP 8**, **MySQL (PDO)**, **HTML5**, **Vanilla JavaScript**, and modern **Vanilla CSS**.

---

## 🚀 Features

- **📊 Comprehensive Dashboard**: Real-time fleet metrics (active drivers, vehicle availability, shipments count, goods categories, recent dispatches).
- **🚚 Vehicle Fleet Management**: Full CRUD (Create, Read, Update, Delete) with registration numbers, brand, model, vehicle types (Truck, Van, Pickup, Trailer, Semi-Trailer), capacity limits, and operational statuses (`Available`, `On Trip`, `Maintenance`, `Out of Service`).
- **👨‍✈️ Driver Management**: Full CRUD for certified drivers with phone, email, and unique driving license numbers.
- **📦 Goods / Cargo Types**: Classify transport goods (Perishable, Electronics, Construction, Pharmaceuticals, etc.) with handling notes.
- **📋 Deliveries Dispatch**: Relational dispatching connecting Driver + Vehicle + Goods Type with origin, destination, scheduled date & time, weight, and delivery statuses (`Pending`, `In Transit`, `Delivered`, `Cancelled`). Includes automated payload capacity verification.
- **🛡️ Referential Integrity Protection**: Prevents accidental deletion of drivers, vehicles, or goods types when active delivery records depend on them.
- **🔍 Real-time Search & Filtering**: Client-side instantaneous table search across all records without reloading.
- **📱 Fully Responsive**: Fluid grid and flexbox layout with collapsible drawer navigation for mobile devices.

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.5+ with PDO (Prepared statements, parameterized queries, and strict error handling)
- **Database**: MySQL 8.0 (InnoDB with UTF-8 Unicode, Primary Keys, Foreign Keys with CASCADE/RESTRICT rules)
- **Frontend**: HTML5, Modern Vanilla CSS (CSS Custom Properties, CSS Grid, Flexbox), Vanilla JavaScript (No heavy frameworks)
- **Icons**: FontAwesome 6 Free CDN

---

## 📂 Project Structure

```text
Logistics Fleet Management/
├── config/
│   └── database.php          # Database credentials & PDO connection singleton
├── includes/
│   ├── header.php            # HTML head, topbar, alert flash container
│   ├── sidebar.php           # Sidebar navigation & badges
│   ├── footer.php            # Footer & confirmation modal
│   └── functions.php         # Helpers (sanitization, dates, metrics, integrity checks)
├── drivers/
│   ├── index.php             # List drivers with search & stats
│   ├── create.php            # Add new driver form
│   ├── edit.php              # Edit driver form
│   ├── view.php              # Driver profile & assigned deliveries
│   └── delete.php            # Safe delete handler
├── vehicles/
│   ├── index.php             # List vehicles with status/type filters
│   ├── create.php            # Add new vehicle form
│   ├── edit.php              # Edit vehicle form
│   ├── view.php              # Vehicle details & trip history
│   └── delete.php            # Safe delete handler
├── goods-types/
│   ├── index.php             # List cargo categories
│   ├── create.php            # Add goods type form
│   ├── edit.php              # Edit goods type form
│   ├── view.php              # Category details & deliveries
│   └── delete.php            # Safe delete handler
├── deliveries/
│   ├── index.php             # Shipments table with status filter
│   ├── create.php            # Schedule delivery (database-backed dropdowns)
│   ├── edit.php              # Edit delivery form
│   ├── view.php              # Full delivery tracking & capacity gauge
│   └── delete.php            # Delete delivery handler
├── assets/
│   ├── css/
│   │   └── style.css         # Modern, responsive admin styling
│   └── js/
│       └── app.js            # Real-time search, mobile drawer, modals
├── conception/               # Original specifications & ER diagram
├── database.sql              # MySQL schema & seed data
├── index.php                 # Main Dashboard
└── README.md
```

---

## ⚙️ Quick Start

### 1. Database Setup

The database schema and sample data are in `database.sql`. To import:
```bash
mysql -u root -p fleet_management < database.sql
```
Credentials can be configured in `config/database.php` or using environment variables (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`).

### 2. Run with PHP Built-in Server

Run the development server from the project root:
```bash
php -S localhost:8000
```
Then open your browser at:
```
http://localhost:8000
```
