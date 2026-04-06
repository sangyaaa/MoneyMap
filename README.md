# 💰 MoneyMap – visualize your financial journey

> **Track. Save. Grow.**  
> A full-featured personal finance management system with multi-user support, real-time analytics, and an interactive dashboard.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Chart.js](https://img.shields.io/badge/Chart.js-3.9-FF6384?style=for-the-badge&logo=chart.js&logoColor=white)
![jQuery](https://img.shields.io/badge/jQuery-3.6-0769AD?style=for-the-badge&logo=jquery&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

---

## 📌 Overview

**MoneyMap** is a full-stack personal finance management web application that helps users take control of their finances. It allows users to track both **income** and **expenses**, manage **multiple accounts**, set **monthly budgets**, and visualize spending patterns through **interactive charts** — all in one clean, responsive dashboard.

> 🎯 **The Problem:** Most people struggle to track where their money goes. Spreadsheets are tedious, and existing apps are either too complex or require paid subscriptions.  
> ✅ **The Solution:** MoneyMap provides a simple, intuitive, and completely free solution with real-time insights and full control over your financial data.

---

## ✨ Features

### 🔐 Authentication & Security
- User registration & login (session-based)
- Multi-user support – each user has isolated data
- Simple password storage for testing (easily upgradeable to hashing)

### 💳 Income & Expense Management
- Add, edit, and delete transactions
- Categorize expenses (Food, Transport, Bills, Entertainment, Shopping, Health, Education, etc.)
- Separate income and expense tracking
- Real-time balance updates across accounts

### 🏦 Multiple Account Support
- Create custom accounts (Bank, eSewa, Cash, Credit Card, etc.)
- Each transaction affects the selected account's balance
- View account-wise balance summary

### 📊 Interactive Charts (Chart.js)
- **Income vs Expense Bar Chart** – Compare totals at a glance
- **Category-wise Breakdown** – Toggle between income & expense distribution

### 📅 Smart Calendar View
- Click on any date to view all transactions for that day
- Visual indicators show which dates have transactions
- Income amounts shown in green, expenses in red
- Transaction count badges for quick reference

### 🔍 Advanced Filtering
- Filter by **category**
- Filter by **account**
- Filter by **date range**
- Filter by **specific date** (from calendar)

### 📈 Budget Tracking
- Set monthly budget
- Real-time budget remaining calculation
- Visual warnings when budget is low or exceeded

### 🌙 Dark / Light Mode
- Toggle between themes
- Preference saved in `localStorage`
- Smooth transitions and consistent styling

### 📱 Fully Responsive
- Works seamlessly on desktop, tablet, and mobile
- Touch-friendly buttons and optimized layout

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6), jQuery, AJAX |
| **Backend** | PHP 8.x |
| **Database** | MySQL 8.0 |
| **Charts** | Chart.js |
| **Server** | XAMPP / Apache |
| **Styling** | Custom CSS with CSS Variables |

---

## 📁 Project Structure
MONEYMAP/
│
├── index.php
├── login.php
├── logout.php
├── config.php
├── functions.php
├── script.js
├── style.css
├── get_calendar_data.php
│
├── ajax/
│ ├── get_summary.php
│ ├── get_transactions.php
│ ├── process_account.php
│ ├── process_budget.php
│ └── process_transaction.php


## 🚀 How to Run Locally 

### Prerequisites
- XAMPP (or any PHP/MySQL server) installed
- Web browser 

### Step-by-Step Setup

```bash
# 1. Download or clone the repository
git clone https://github.com/yourusername/moneymap.git
# OR simply copy files to htdocs

# 2. Move to XAMPP htdocs folder
# For Windows:
move moneymap C:\xampp\htdocs\

# For Mac/Linux:
mv moneymap /opt/lampp/htdocs/

# 3. Start XAMPP
# - Open XAMPP Control Panel
# - Click "Start" for Apache and MySQL services

# 4. Create the database
# - Open browser and go to: http://localhost/phpmyadmin
# - Click on "New" to create a database
# - Name it: money_tracker
# - Select "utf8_general_ci" as collation
# - Click "Create"

# 5. Import the database tables
# - Click on the "money_tracker" database
# - Click on the "SQL" tab
# - Copy and paste the SQL code provided in the project
# - Click "Go" to execute

# 6. Configure database connection (if needed)
# - Open config.php in your editor
# - Verify database credentials:
#   $host = 'localhost';
#   $user = 'root';
#   $password = '';
#   $database = 'money_tracker';

# 7. Access the application
# - Open browser and go to: http://localhost/moneymap
# - Login with credentials

# 8. Start tracking your finances!
# - Add your accounts (Bank, eSewa, Cash, etc.)
# - Add income and expense transactions
# - Set your monthly budget
# - Explore charts and calendar view

---

## 🔧 Future Improvements
# - Export to CSV/PDF
# - Recurring transactions
# - Email notifications
# - Multi-currency support
# - Mobile app (React Native / Flutter)
# - REST API integration
# - Two-factor authentication
