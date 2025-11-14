# 🚨 Grohe Bitrix24 Stock Monitoring Integration

A lightweight automation module that connects **Bitrix24 CRM** with **warehouse stock control**.  
When a deal reaches the **“WON”** stage, the system automatically checks warehouse inventory and sends instant notifications to managers if any product falls to **5 units or less**.

---

## ⚙️ Features

- 🔄 Auto-triggered when a deal is marked **Won**
- 📦 Retrieves product quantities across **all warehouses**
- 🎯 Detects **low-stock items** (≤ 5 units)
- 👤 Identifies the warehouse manager from a **custom user field**
- 🔔 Sends an in-Bitrix24 alert via `im.notify.system.add`
- 📝 Detailed logging for debugging and transparency

---

## 🧠 How It Works

1. **Deal won → automation rule passes `deal_id`**  
2. Script fetches the deal and validates the stage  
3. Loads warehouse list + managers (`UF_CAT_STORE_1738047081`)  
4. Retrieves stock quantities using `catalog.storeproduct.list`  
5. For each low-stock product:  
   - Fetches product + warehouse names  
   - Sends manager alert:  
     > 🔴 *Low stock alert for “Product Name” in “Warehouse Name”*  
6. Writes a full execution log to `/local/response.txt`

---

## 📁 Directory Structure
Grohe/
│
├── handlers/
│   └── index.php        # Main automation handler
├── vendor/              # Composer dependencies
├── composer.json
└── README.md

