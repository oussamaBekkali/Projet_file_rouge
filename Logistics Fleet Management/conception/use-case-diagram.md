# Fleet Management System - Use Case Diagram

```mermaid
flowchart LR
    actor "Fleet Manager" as FM

    package "Fleet Management System" {
        usecase "View Dashboard" as UC1
        usecase "Manage Drivers" as UC2
        usecase "Add Driver" as UC2_1
        usecase "Edit Driver" as UC2_2
        usecase "Delete Driver" as UC2_3
        usecase "View Driver Details" as UC2_4

        usecase "Manage Vehicles" as UC3
        usecase "Add Vehicle" as UC3_1
        usecase "Edit Vehicle" as UC3_2
        usecase "Delete Vehicle" as UC3_3
        usecase "View Vehicle Details" as UC3_4

        usecase "Manage Goods Types" as UC4
        usecase "Add Goods Type" as UC4_1
        usecase "Edit Goods Type" as UC4_2
        usecase "Delete Goods Type" as UC4_3
        usecase "View Goods Type Details" as UC4_4

        usecase "Manage Deliveries" as UC5
        usecase "Create Delivery" as UC5_1
        usecase "Edit Delivery" as UC5_2
        usecase "Delete Delivery" as UC5_3
        usecase "View Delivery Details" as UC5_4
        usecase "Check Capacity Constraints" as UC5_5

        usecase "Search and Filter Data" as UC6
        usecase "Protect Referential Integrity" as UC7
    }

    FM --> UC1
    FM --> UC2
    FM --> UC3
    FM --> UC4
    FM --> UC5
    FM --> UC6

    UC2 ..> UC2_1
    UC2 ..> UC2_2
    UC2 ..> UC2_3
    UC2 ..> UC2_4

    UC3 ..> UC3_1
    UC3 ..> UC3_2
    UC3 ..> UC3_3
    UC3 ..> UC3_4

    UC4 ..> UC4_1
    UC4 ..> UC4_2
    UC4 ..> UC4_3
    UC4 ..> UC4_4

    UC5 ..> UC5_1
    UC5 ..> UC5_2
    UC5 ..> UC5_3
    UC5 ..> UC5_4
    UC5 ..> UC5_5

    UC5_1 ..> UC5_5
    UC5_2 ..> UC5_5
    UC5_3 ..> UC7
    UC2_3 ..> UC7
    UC3_3 ..> UC7
    UC4_3 ..> UC7

    UC6 -.-> UC2
    UC6 -.-> UC3
    UC6 -.-> UC4
    UC6 -.-> UC5
```
