# 🧩 UniWorkie

**UniWorkie** is a web-based student recruiter platform designed to connect university students and recruiters in one place. The system allows students to **showcase their project portfolios**, **submit proposals**, and even **sell their project work** to interested recruiters — creating real-world opportunities directly from their academic work.

---

## 🚀 Features

- 🧠 **Project Proposal Submission** – Students can upload and submit detailed project proposals.  
- 💼 **Portfolio Showcase** – Displays students’ past works for recruiter viewing.  
- 🔍 **Recruiter Browsing** – Employers can browse through student portfolios to find potential talents.  
- 🔒 **Secure Authentication** – Login and registration system for both students and recruiters.  
- 💰 **Project Sales** – Students can offer their completed projects for purchase.

---

## 🛠️ Technologies Used

- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP  
- **Database:** MySQL  

---

## 🧭 Installation (Local - XAMPP)

1. Copy the project folder to your XAMPP `htdocs` (e.g. `C:\xampp\htdocs\UniWorkie_Idea2025`).  
2. Create a MySQL database via phpMyAdmin (e.g. `uniworkie_db`) and import any provided SQL schema.  
3. Update your database credentials in the project config (e.g. `config.php` or `db.php`):
```php
// example config.php
<?php
$host = 'localhost';
$db   = 'uniworkiedatabase';
$user = 'root';
$pass = ''; // default for XAMPP
?>

 4.Start Apache & MySQL in XAMPP.
 5.Visit http://localhost/UniWorkie_Idea2025/ in your browser.
