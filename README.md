💇‍♀️ Sulochana Salon Website

A fully-featured web application for managing salon operations, client appointments, services, and staff management.

This PHP-based web application provides a centralized platform for salon owners, staff, and clients to manage daily tasks efficiently while improving user experience and operational workflows.

📌 Table of Contents

About the Website

Features

User Roles

Technology Stack

File Structure

Database

Installation

Future Enhancements

License

About the Website

The Sulochana Salon Website is designed to help salons manage appointments, services, staff, and client interactions digitally. It allows customers to book appointments, view services, and provide feedback, while giving admins and staff tools to manage the business efficiently.

The system is ideal for small to medium-sized salons looking to digitize operations, improve client engagement, and streamline service management.

Features
Customer Features

View salon services and stylists with details and images.

Book and cancel appointments online.

Track appointment history and payment status.

Provide feedback for services.

Receive notifications about appointments and promotions.

Admin Features

Dashboard to manage appointments, payments, feedback, and notifications.

Add, edit, or remove services and stylists.

Manage admin users and client accounts.

Generate reports on appointments, payments, and client activity.

Send notifications and reminders to staff and clients.

Staff Features

View assigned appointments and update status.

Confirm, modify, or cancel bookings.

Access client information and notifications.

User Roles
Role	Access & Permissions
Admin	Full control: manage users, appointments, services, payments, notifications, and reports.
Staff	Manage appointments and interact with clients within assigned permissions.
Customer	Book appointments, view services, provide feedback, and receive notifications.
Technology Stack

Frontend: HTML, CSS, JavaScript, Bootstrap

Backend: PHP

Database: MySQL

Libraries & Tools:

jQuery, Owl Carousel, Venobox, Animate.css

Custom JS for appointments, notifications, and UI effects

File Structure

The project is organized as follows:

salon-website/
├── api/                  # REST-like APIs for user and appointment data
├── assets/               # CSS, JS, images, fonts
├── bin/                  # Utility scripts
├── includes/             # Helper functions
├── layouts/              # Shared headers/footers
├── modals/               # Modal dialogs
├── tick/                 # Ticker animations
├── uploads/              # Profile and admin images
├── venobox/              # Venobox lightbox plugin
├── *.php                 # Main pages (client, admin, services, bookings, authentication)
├── *.css / *.js / *.dat  # Layout and style files
├── salon.sql             # Database structure and sample data
└── project doc.pdf       # Documentation & pseudo code

Database

Database: MySQL

Key Tables:

users – stores admin, staff, and client accounts

appointments – tracks booking details and statuses

services – lists salon services with pricing and images

feedback – stores customer feedback

notifications – stores system notifications for admins, staff, and clients

Database structure and sample data are provided in salon.sql.

Installation

Clone the repository:

git clone https://github.com/upethalaksiluni/salon-website.git


Copy files to your PHP server directory (e.g., htdocs for XAMPP).

Import the salon.sql database in MySQL.

Update db_connect.php with your database credentials.

Open the website in a browser:

http://localhost/salon-website/

Future Enhancements

Integration with online payment gateways.

Push notifications for appointments and promotions.

Multi-language support.

Advanced reporting and analytics.

Cloud-hosted database for multi-device access.
📬 Contact

Maintainer: Upetha Laksiluni
GitHub: https://github.com/upethalaksiluni
LinkedIn: 
Email: upethalaksiluni@gmail.com

License

This project is licensed under the MIT License.
