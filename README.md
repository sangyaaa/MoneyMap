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
| **Server** | XAMPP / Apache  |
| **Styling** | Custom CSS with CSS Variables |

---

## 📁 Project Structure

| Folder / File | Description |
| :--- | :--- |
| **`root/`** | Core application files |
| ├── `index.php` | Main dashboard and landing page |
| ├── `login.php` | User authentication and login logic |
| ├── `logout.php` | Handles session termination |
| ├── `config.php` | Database connection settings |
| ├── `functions.php` | Global PHP helper functions and business logic |
| ├── `script.js` | Main JavaScript for UI interactions and AJAX |
| ├── `style.css` | Global styles, CSS variables, and Dark Mode themes |
| ├── `get_calendar_data.php` | Endpoint for fetching transaction dates for the calendar |
| **`ajax/`** | Asynchronous PHP scripts for real-time updates |
| ├── `get_summary.php` | Fetches card balances and budget totals |
| ├── `get_transactions.php` | Retrieves filtered transaction lists |
| ├── `process_account.php` | Handles account creation and updates |
| ├── `process_budget.php` | Manages monthly budget settings |
| └── `process_transaction.php`| Logic for adding, editing, or deleting entries |
| **`assets/`** | Static frontend resources |
| ├── `css/` | External libraries (Bootstrap, etc.) |
| ├── `js/` | External plugins (Chart.js, jQuery) |
| └── `images/` | Application logos and icons |
| **`sql/`** | Database schema management |
| └── `install.sql` | SQL script to initialize database and tables |
    └── 📄 install.sql           # Database schema and seed data

---

## 🚀 How to Run Locally

### Prerequisites
* **XAMPP / WAMP / MAMP** installed
* **PHP 8.x** or higher
* **MySQL 8.0**

### Step-by-Step Setup

1.  **Clone the Repository**
    ```bash
    git clone [https://github.com/yourusername/moneymap.git](https://github.com/yourusername/moneymap.git)
    ```

2.  **Move to Web Directory**
    Move the `moneymap` folder to your `htdocs` (XAMPP) or `www` (WAMP) directory.

3.  **Database Configuration**
    * Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
    * Create a new database named `money_tracker`.
    * Go to the **Import** tab and upload the `sql/install.sql` file.

4.  **Update Connection Strings**
    Edit `config.php` to match your local environment:
    ```php
    <?php
    $host = 'localhost';
    $user = 'root';
    $password = ''; // Default XAMPP password is empty
    $database = 'money_tracker';
    ?>
    ```

5.  **Run the App**
    Navigate to `http://localhost/moneymap` in your browser.

---

## 🔧 Future Improvements

| Priority | Feature | Description |
| :--- | :--- | :--- |
| 🔴 High | **Export to CSV/PDF** | Generate downloadable financial reports |
| 🔴 High | **Recurring Transactions** | Automate monthly bills and salary entries |
| 🟡 Medium | **Email Notifications** | Budget alerts and weekly summaries |
| 🟡 Medium | **Multi-currency** | Support for global currency exchange rates |
| 🟢 Low | **Receipt Uploads** | Attach images to specific transactions |

---

## 📄 License
This project is open-source and available under the **MIT License**.
