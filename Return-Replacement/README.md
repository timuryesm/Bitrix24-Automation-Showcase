# 🧾 Return-Replacement (Bitrix24 Deal Splitter)

## Overview
The **Return-Replacement** app automates how Bitrix24 handles deals where a customer both **returns** items and requests **replacements**.

Instead of keeping everything in one confusing deal, the app **splits** it into two:
- a **Return** deal for refunded items  
- a **Replacement** deal for exchanged items  

This helps your sales and accounting teams work faster and keep records clean.

---

## 🚀 How It Works
1. A manager opens a **deal** in Bitrix24.  
2. The embedded app (**index.html**) automatically detects the deal’s ID.  
3. When the user clicks **“Activate”**, it sends that deal ID to your backend script (**index.php**).  
4. The backend runs the main logic via **DealProcessor (DealHandler.php)**:
   - Checks the deal’s **Cancellation Type** field.  
   - If it’s **“Return & Replacement”**, the script:
     - Reads which products are marked for return and which for replacement.  
     - Creates **two new deals** — one “Return,” one “Replacement.”  
     - Moves the correct products into each.  
     - Deletes the original deal once both new ones are ready.
5. The process is logged to a text file and returns a short confirmation in JSON format.

---

## 🧩 Files and Their Roles
| File | Description |
|------|--------------|
| **index.html** | The simple Bitrix24 app interface with one button to start the process. |
| **index.php** | The backend entry point. Handles requests, runs the processor, logs results, and returns JSON responses. |
| **BitrixAPI.php** | Helper class that communicates with Bitrix24 via webhook (get, add, delete deals; manage product rows). |
| **DealHandler.php** | Core business logic that splits a deal into two new ones (“Return” and “Replacement”). |

---

## 🧠 What It Solves
- Keeps **returns and replacements** separate for clarity.  
- Simplifies **accounting and reporting**.  
- Prevents errors from manually duplicating or editing deals.  
- Saves staff time with **one-click automation**.

---

## ⚙️ Requirements
- A valid **Bitrix24 webhook URL** with permission to read, create, delete deals, and manage product rows.  
- The app hosted on a server accessible to your Bitrix portal.  
- Write access to `/home/bitrix/www/local/response.txt` (for logs).

---

## 👩‍💻 Usage
1. Upload all four scripts to a single folder named `Return-Replacement` on your server.  
2. In Bitrix24, open any deal and launch the **Return-Replacement** app panel.  
3. Click **“Activate index.php.”**  
4. If the deal’s cancellation type is **“Return & Replacement”**, two new deals will be created automatically:
   - One titled “Return”  
   - One titled “Replacement”  
5. Check the `/local/response.txt` log file for detailed results.

---

## 🧾 Logging
All activity and errors are written to:
/home/bitrix/www/local/response.txt

This file helps you trace what happened during processing.

---

## 🔒 Safety & Improvement Tips
- **Protect the webhook:** Store it in environment variables, not inside `index.php`.  
- **Add validation:** Only delete the original deal after confirming both new deals were created successfully.  
- **Improve feedback:** Show a success or failure message directly in the app interface.  
- **Limit log size:** Rotate or periodically clear logs to avoid large files.  
- **Field control:** Copy only the deal fields you actually need (avoid sensitive or irrelevant ones).

---

## 📘 Glossary
| Term | Meaning |
|------|----------|
| **Deal** | A sales record or transaction in Bitrix24. |
| **Product Rows** | List of products or services attached to a deal (with price and quantity). |
| **Webhook** | A secure URL your server uses to communicate with Bitrix24. |
| **JSON** | A simple data format used to send results back to the app. |

---

## ✅ Summary
The **Return-Replacement** tool bridges your Bitrix24 CRM and custom PHP automation.  
With one click, it:
- reads the current deal,  
- identifies return and replacement items,  
- creates two organized deals,  
- deletes the messy original, and  
- logs everything safely.  

It’s a clean, fast, and reliable way to manage complex after-sales processes in Bitrix24.

---
