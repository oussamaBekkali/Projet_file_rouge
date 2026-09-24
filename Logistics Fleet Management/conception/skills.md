# skills.md

## Project

Fleet Management System using PHP, MySQL, HTML, JavaScript, and vanilla
CSS.

## Core Skills

### PHP

-   PHP 8+ fundamentals
-   Forms and `$_POST` / `$_GET`
-   Sessions when needed
-   Includes and reusable PHP files
-   Server-side validation
-   Error handling
-   CRUD operations
-   Separation of database logic and presentation

### MySQL

-   Database and table creation
-   Primary keys
-   Foreign keys
-   `AUTO_INCREMENT`
-   `ENUM`
-   `VARCHAR`
-   `INT`
-   `FLOAT`
-   `DATETIME`
-   `INSERT`
-   `SELECT`
-   `UPDATE`
-   `DELETE`
-   `JOIN`
-   `COUNT`
-   Filtering and ordering
-   Referential integrity

### PDO

All database access must use PDO.

Required practices:

-   Prepared statements
-   Bound parameters / parameterized queries
-   No raw user input inside SQL strings
-   Centralized database connection
-   Proper exception/error handling

### HTML

-   Semantic HTML5
-   Forms
-   Tables
-   Navigation
-   Accessible labels
-   Proper input types
-   Reusable page layouts

### CSS

Use modern vanilla CSS.

Skills required:

-   Flexbox
-   CSS Grid
-   Responsive design
-   CSS variables
-   Cards
-   Tables
-   Forms
-   Buttons
-   Status badges
-   Hover/focus states
-   Mobile layouts
-   Clean admin-dashboard styling

Avoid unnecessary CSS frameworks.

### JavaScript

Use vanilla JavaScript for lightweight interactions:

-   Delete confirmations
-   Search/filter interactions
-   Form UX
-   Dynamic UI states
-   Dropdown/filter behavior
-   Basic client-side validation where useful

Do not move the main business logic into JavaScript. PHP remains
responsible for server-side validation and database operations.

## Database Relationships

Understand and implement:

``` text
DRIVER
   │
   │ makes
   ▼
DELIVERY
   ▲
   │ used for
   │
VEHICLE

GOODSTYPE
   │
   │ contains
   ▼
DELIVERY
```

`DELIVERY` contains the foreign keys:

``` text
id_driver
id_vehicle
id_goods_type
```

These must reference:

``` text
driver.id_driver
vehicle.id_vehicle
goodstype.id_goods_type
```

## CRUD Skills

Every main entity should support:

-   Create
-   Read
-   Update
-   Delete

Entities:

-   Drivers
-   Vehicles
-   Deliveries
-   Goods Types

## SQL Query Skills

Examples of required concepts:

### Get deliveries with related information

``` sql
SELECT
    d.id_delivery,
    d.departure_location,
    d.destination,
    d.delivery_date,
    d.status,
    d.weight,
    CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name,
    v.registration_number,
    v.brand,
    v.model,
    g.name AS goods_type
FROM delivery d
JOIN driver dr ON d.id_driver = dr.id_driver
JOIN vehicle v ON d.id_vehicle = v.id_vehicle
JOIN goodstype g ON d.id_goods_type = g.id_goods_type;
```

### Dashboard statistics

Use `COUNT()` queries to calculate:

-   Number of drivers
-   Number of vehicles
-   Number of deliveries
-   Number of goods types

## Security Skills

The project should demonstrate basic secure PHP development:

-   PDO prepared statements
-   Server-side validation
-   Output escaping with `htmlspecialchars()`
-   Never trust client-side validation alone
-   Validate IDs before database operations
-   Confirm destructive operations
-   Do not expose database credentials in public files
-   Keep configuration separate from presentation

## Code Quality

Prioritize:

-   Readable variable names
-   Small reusable functions
-   Consistent naming
-   Avoid duplicated code
-   Clear folder structure
-   Simple logic
-   Comments only where they add useful context
-   No unnecessary frameworks or abstractions

## UI Skills

The interface should provide:

-   Dashboard
-   Navigation
-   Data tables
-   CRUD forms
-   Detail pages
-   Status badges
-   Empty states
-   Confirmation dialogs
-   Responsive layout

The final result should look like a clean, modern fleet-management admin
panel while remaining simple enough to understand and modify.

## Development Rule

Build in this order:

1.  Create the MySQL database.
2.  Create all four tables.
3.  Add primary and foreign keys.
4.  Create the PDO connection.
5.  Build Drivers CRUD.
6.  Build Vehicles CRUD.
7.  Build Goods Types CRUD.
8.  Build Deliveries CRUD.
9.  Add JOIN queries for related information.
10. Build the dashboard.
11. Add JavaScript interactions.
12. Polish responsive CSS.
13. Test every CRUD operation and relationship.

## Definition of Done

The application is complete when:

-   All four entities can be managed.
-   Deliveries correctly reference drivers, vehicles, and goods types.
-   Related data is displayed using SQL JOINs.
-   Foreign keys work correctly.
-   CRUD operations work without SQL injection vulnerabilities.
-   Forms validate input.
-   The dashboard displays useful fleet statistics.
-   The interface is responsive and visually consistent.
-   The code remains understandable to a PHP/MySQL student.
