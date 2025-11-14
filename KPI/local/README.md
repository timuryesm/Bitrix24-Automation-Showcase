# 📊 KPI Report Module — Bitrix24 Custom Extension  
A complete, production-ready Bitrix24 module that automatically calculates, displays, and (optionally) logs employee KPIs, bonuses, and salary components.

This module integrates directly into the Bitrix24 admin panel and provides managers with real-time visibility into employee performance, workload, and compensation.

---

## 🚀 Project Overview

The **kpi.report** module automates Key Performance Indicator (KPI) calculations for Bitrix24 users.  
It combines data from multiple Bitrix24 subsystems:

- **CRM** (deals, stages, results)  
- **Telephony** (completed calls)  
- **Highload Block** (KPI target plan)  
- **Timeman** (actual hours worked)

The system then computes:

- Plan vs. Fact KPIs  
- Completion percentages  
- Bonuses per metric  
- Total bonus  
- Worked hours × hourly rate (base salary)  
- **Final compensation** (salary + bonus)

All results are shown as a clear report inside the Bitrix24 admin panel.

---

## 🧭 How It Works — In Simple Terms

1. The admin opens the KPI report page and selects:
   - **User**
   - **Date from**
   - **Date to**

2. The module retrieves:
   - KPI targets from HL-block (`KpiPlanTable`)
   - Real performance data from CRM & telephony
   - Worked hours from Timeman

3. The module calculates:
   - `% completion` for each KPI metric  
   - `bonus` for each metric based on completion scale  
   - `average total %`  
   - `total bonus`  
   - `hours worked × hourly rate` → salary  
   - **final payout**  

4. Results are displayed in a formatted table in the admin dashboard.  
   Optionally, they are stored in a log table (`kpi_report_log`) for monthly history.

---

## 📂 Folder Structure & Key Components
local/modules/kpi.report/
├── admin/
│   └── kpi_report.php          # Admin UI + KPI results table
├── install/
│   └── installer.php           # Module installation / uninstallation logic
├── lib/
│   ├── Main.php                # Core KPI calculation engine (the brain)
│   ├── HL/
│   │   └── KpiPlanTable.php    # HL-block wrapper for KPI target plan
│   ├── Reports/
│   │   └── KpiReportTable.php  # Optional KPI result log table
│   └── Timeman/
│       └── WorkHours.php       # Work hour calculation via Timeman
├── include.php                 # Autoload configuration for Bitrix
└── vendor/
└── composer/               # PHP autoloading infrastructure

---

## 📝 File Descriptions

### **1. install/installer.php**
- Handles installation & removal of the module.
- Registers the module via `ModuleManager::registerModule()`.
- Acts as the module’s setup script.

---

### **2. include.php**
- Registers autoload rules for:
  - `Kpi\Report\Main`
  - `Kpi\Report\HL\KpiPlanTable`
  - `Kpi\Report\Reports\KpiReportTable`
  - `Kpi\Report\Timeman\WorkHours`
- Ensures Bitrix loads classes automatically.

---

### **3. admin/kpi_report.php**
- Provides the **interactive admin page**.
- Filters by date range + user ID.
- Renders a KPI table containing:
  - Metric name  
  - Plan  
  - Fact  
  - % Completion  
  - Bonus  
- Displays:
  - Average %  
  - Total bonus  
  - Hours worked  
  - Salary  
  - Total payout  

This is the manager’s dashboard.

---

### **4. lib/Main.php**
The **core computation engine** of the KPI module.

Handles all business logic:

- Loads plan (targets) from HL-block  
- Fetches CRM deal counts by stage  
- Fetches telephony (phone calls)  
- Computes KPI completion percentages  
- Calculates bonuses based on scaling rules  
- Adds salary derived from Timeman hours  
- Returns a fully structured KPI dataset

---

### **5. lib/HL/KpiPlanTable.php**
- Wraps the KPI plan HL-block using Bitrix ORM.
- Defines the table schema:
  - UF_STAGE_CODE  
  - UF_REQUIRED_QUANTITY  
  - UF_BONUS_MAX  
- Acts as the **Plan** data source.

---

### **6. lib/Timeman/WorkHours.php**
- Includes the `timeman` module.
- Retrieves all employee work entries for the date period.
- Sums duration and returns total hours (rounded).

---

### **7. lib/Reports/KpiReportTable.php** (optional)
- Defines table for **KPI historical storage**.
- Schema includes:
  - USER_ID  
  - DATE_FROM  
  - DATE_TO  
  - TOTAL_PERCENT  
  - TOTAL_BONUS  
  - CREATED_AT  
- Useful for monthly performance logs.

---

## 📌 In Summary

The **KPI Report Module** is a fully functional Bitrix24 extension that automates employee performance analytics.  
It centralizes data from CRM, telephony, HL-blocks, and time tracking — transforms it into meaningful KPIs — and provides clear financial outcomes (bonus + salary).

This module is reliable, extensible, and ready for real-world use in performance monitoring and compensation management.
