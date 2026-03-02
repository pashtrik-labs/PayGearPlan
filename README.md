# PayGearPlan: Secure E-Commerce Lab
Category: Full-Stack Web Development & Security Research

---

## Project Overview
- PayGearPlan is a PHP/MySQL web application designed to simulate a modern e-commerce platform. It features client registration, dynamic product management, and a comprehensive admin dashboard.
Beyond functionality, this project serves as a Security Hardening Lab where I identify, exploit, and remediate common web vulnerabilities in a controlled environment.

---

## Security Research Focus
This project is built to test defenses against the OWASP Top 10, specifically:

- **SQL Injection (SQLi):** Auditing database queries for improper input sanitization.
- **Broken Access Control:** Ensuring the Admin Dashboard is strictly isolated from standard user accounts.
- **Cryptographic Failures:** Implementing secure password hashing (BCrypt/Argon2) instead of plain-text storage.

---

## Technical Stack

- **Backend:** PHP 8.x
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Environment:** Local development using XAMPP/LAMP

---

## Key Features

- **Secure Authentication:** User registration and login system with session management.
- **Product Management:** CRUD (Create, Read, Update, Delete) operations for store items.
- **Admin Control Panel:** Specialized interface for managing site-wide data and security logs.

---

## Disclaimer
This application was developed for educational purposes to demonstrate secure coding practices and vulnerability remediation.
