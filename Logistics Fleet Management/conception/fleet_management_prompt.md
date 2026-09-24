# Fleet Management System --- Development Prompt

Build a **Fleet Management System** based on the provided database/ER
diagram.

## Goal

Create a clean, responsive web application that allows users to:

-   Display fleet information in a dashboard.
-   Add, edit, view, and delete **drivers**.
-   Add, edit, view, and delete **vehicles**.
-   Add, edit, view, and delete **deliveries**.
-   Add, edit, view, and delete **goods types**.
-   Connect all entities through the database relationships shown in the
    ER diagram.
-   Manage the data through a simple, intuitive interface.

## Tech Stack

Use:

-   **PHP** for the backend and server-side logic.
-   **MySQL** for the database.
-   **HTML5** for structure.
-   **JavaScript (vanilla JS)** for client-side interactions.
-   **CSS3 / modern vanilla CSS** for styling.

Do not use a heavy frontend framework. Keep the project simple and
suitable for a student learning PHP/MySQL.

## Database Structure

Follow the provided ER diagram.

### DRIVER

-   `id_driver` --- INT, Primary Key
-   `first_name` --- VARCHAR
-   `last_name` --- VARCHAR
-   `phone` --- VARCHAR
-   `email` --- VARCHAR
-   `license_number` --- VARCHAR

### VEHICLE

-   `id_vehicle` --- INT, Primary Key
-   `registration_number` --- VARCHAR
-   `model` --- VARCHAR
-   `brand` --- VARCHAR
-   `vehicle_type` --- ENUM
-   `capacity` --- FLOAT
-   `status` --- ENUM

### GOODSTYPE

-   `id_goods_type` --- INT, Primary Key
-   `name` --- VARCHAR
-   `description` --- VARCHAR

### DELIVERY

-   `id_delivery` --- INT, Primary Key
-   `departure_location` --- VARCHAR
-   `destination` --- VARCHAR
-   `delivery_date` --- DATETIME
-   `status` --- ENUM
-   `weight` --- FLOAT
-   `id_driver` --- INT, Foreign Key → `driver.id_driver`
-   `id_vehicle` --- INT, Foreign Key → `vehicle.id_vehicle`
-   `id_goods_type` --- INT, Foreign Key → `goodstype.id_goods_type`

## Relationships

Implement the relationships represented by the ER diagram:

-   A **driver** makes deliveries.
-   A **vehicle** is used for deliveries.
-   A **goods type** is contained in deliveries.
-   A **delivery** references one driver, one vehicle, and one goods
    type.

Use proper MySQL foreign keys and maintain referential integrity.

## Main Pages

Create a simple navigation system with:

1.  **Dashboard**
    -   Total drivers
    -   Total vehicles
    -   Total deliveries
    -   Total goods types
    -   Recent deliveries
    -   Useful fleet status information
2.  **Drivers**
    -   List all drivers
    -   Add driver
    -   Edit driver
    -   Delete driver
    -   View driver details
    -   Show related deliveries
3.  **Vehicles**
    -   List all vehicles
    -   Add vehicle
    -   Edit vehicle
    -   Delete vehicle
    -   View vehicle details
    -   Show related deliveries
4.  **Deliveries**
    -   List all deliveries
    -   Add delivery
    -   Edit delivery
    -   Delete delivery
    -   View delivery details
    -   When creating a delivery, select the driver, vehicle, and goods
        type from database-backed dropdowns.
5.  **Goods Types**
    -   List all goods types
    -   Add goods type
    -   Edit goods type
    -   Delete goods type
    -   View related deliveries

## UI / UX

Create a modern but simple interface.

Use:

-   Responsive layout
-   Sidebar or top navigation
-   Cards for dashboard statistics
-   Clean tables
-   Clear forms
-   Buttons for CRUD actions
-   Status badges
-   Confirmation before destructive actions
-   Empty states when there is no data
-   Basic search/filtering where useful
-   Mobile-friendly design
-   Consistent spacing, typography, borders, and hover states

Use **vanilla CSS** rather than a large UI framework. The design should
feel like a modern admin dashboard without becoming unnecessarily
complicated.

## Backend Requirements

-   Use PHP with a clear separation between database connection,
    reusable functions, pages, and views.
-   Use **PDO** for MySQL.
-   Use prepared statements for all database queries.
-   Never concatenate user input directly into SQL queries.
-   Validate form data on the server side.
-   Escape output with `htmlspecialchars()` where appropriate.
-   Handle database errors cleanly.
-   Use reusable CRUD functions where possible.
-   Use foreign-key relationships instead of duplicating driver,
    vehicle, or goods-type information inside deliveries.

## Suggested Project Structure

``` text
fleet-management/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   └── functions.php
├── drivers/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── view.php
│   └── delete.php
├── vehicles/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── view.php
│   └── delete.php
├── deliveries/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── view.php
│   └── delete.php
├── goods-types/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── view.php
│   └── delete.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── index.php
└── database.sql
```

## Important

Do not over-engineer the project.

Keep the implementation understandable for someone learning:

-   PHP
-   MySQL
-   SQL relationships
-   CRUD
-   JavaScript
-   HTML/CSS

Build the project incrementally and keep the code readable and
maintainable.
