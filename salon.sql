-- First, create and select database
CREATE DATABASE IF NOT EXISTS salon;
USE salon;

-- Drop tables in correct order (with foreign key checks disabled)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS admin_reminders;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS appointment_services;
DROP TABLE IF EXISTS appointment;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS stylists;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS user;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. First create user table (referenced by appointment)
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    gender VARCHAR(10),
    birthdate DATE,
    phone VARCHAR(20),
    email VARCHAR(100) UNIQUE NOT NULL,
    address TEXT,
    profile_image VARCHAR(255),
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    preferred_stylist VARCHAR(100),
    frequent_services TEXT,
    preferred_time VARCHAR(20),
    allergies TEXT,
    medical_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_admin TINYINT(1) DEFAULT 0
);

-- 2. Create admin table with all fields at once
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) DEFAULT NULL,
    email VARCHAR(100) UNIQUE DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- 3. Create stylists table (referenced by appointment)
CREATE TABLE stylists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialization TEXT,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email)
);

-- Add indexes for better performance
ALTER TABLE stylists
ADD INDEX idx_status (status);

-- Create services table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL,
    category ENUM('Hair Services', 'Facial & Skin Care Services', 'Nail Care Services', 'Makeup Services') NOT NULL,
    image_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add indexes for better performance
ALTER TABLE services
ADD INDEX idx_category (category),
ADD INDEX idx_status (status);

-- Clear existing services
TRUNCATE TABLE services;

-- Insert Hair Services
INSERT INTO services (name, category, description, duration, price, image_url) VALUES
('Haircut (Men, Women, Kids)', 'Hair Services', 'Get a stylish and professional haircut tailored to your look.', 60, 45.00, 'images/haircut.jpg'),
('Hair Coloring', 'Hair Services', 'Transform your hair with rich, vibrant, and long-lasting colors.', 120, 85.00, 'images/haircolor.jpg'),
('Hair Highlights & Balayage', 'Hair Services', 'Add natural-looking depth and dimension to your hair.', 150, 120.00, 'images/highlights.jpg'),
('Hair Spa & Treatments', 'Hair Services', 'Deep conditioning treatments like Keratin, Protein, and Botox.', 90, 100.00, 'images/hairspa.jpg'),
('Hair Straightening & Smoothening', 'Hair Services', 'Achieve silky, straight hair with professional techniques.', 180, 200.00, 'images/straightening.jpg'),
('Blow-dry & Styling', 'Hair Services', 'Get a polished, voluminous look for any occasion.', 45, 45.00, 'images/blowdry.jpg'),
('Bridal & Party Hairstyles', 'Hair Services', 'Elegant hairstyles designed for weddings and special events.', 120, 150.00, 'images/bridal.jpg');

-- Insert Facial & Skin Care Services
INSERT INTO services (name, category, description, duration, price, image_url) VALUES
('Basic & Advanced Facials', 'Facial & Skin Care Services', 'Nourish and rejuvenate your skin with our facial treatments.', 90, 85.00, 'images/facial.jpg'),
('Acne Treatment', 'Facial & Skin Care Services', 'Reduce acne and improve skin texture with expert care.', 60, 75.00, 'images/acne.jpg'),
('Face Cleanup', 'Facial & Skin Care Services', 'Remove dirt, excess oil, and impurities for fresh skin.', 45, 55.00, 'images/cleanup.jpg'),
('Skin Polishing', 'Facial & Skin Care Services', 'Exfoliate and brighten your skin with a gentle polish.', 60, 65.00, 'images/skinpolish.jpg');

-- Insert Nail Care Services
INSERT INTO services (name, category, description, duration, price, image_url) VALUES
('Manicure', 'Nail Care Services', 'Pamper your hands with basic, gel, acrylic, and French styles.', 45, 35.00, 'images/manicure.jpg'),
('Pedicure', 'Nail Care Services', 'Give your feet a refreshing and nourishing experience.', 60, 45.00, 'images/pedicure.jpg'),
('Nail Extensions & Nail Art', 'Nail Care Services', 'Enhance your nails with creative and stylish designs.', 90, 75.00, 'images/nailart.jpg');

-- Insert Makeup Services
INSERT INTO services (name, category, description, duration, price, image_url) VALUES
('Bridal Makeup', 'Makeup Services', 'Look flawless on your big day with professional makeup.', 120, 200.00, 'images/bridalmakeup.jpg'),
('Party & Evening Makeup', 'Makeup Services', 'Glam up for any party or event.', 60, 85.00, 'images/partymakeup.jpg');

-- 4. Now create appointment table (after user and stylists tables exist)
CREATE TABLE appointment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stylist_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    total_duration INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    special_instructions TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    updated_by INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (stylist_id) REFERENCES stylists(id),
    FOREIGN KEY (updated_by) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Add indexes for appointment table
ALTER TABLE appointment 
ADD INDEX idx_appointment_date_status (appointment_date, status),
ADD INDEX idx_user_status (user_id, status) USING BTREE,
ADD INDEX idx_upcoming (appointment_date, status, stylist_id) USING BTREE;

-- Create appointment_services junction table
CREATE TABLE appointment_services (
    appointment_id INT NOT NULL,
    service_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (appointment_id, service_id),
    FOREIGN KEY (appointment_id) REFERENCES appointment(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Create feedback table
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    service_quality ENUM('excellent', 'good', 'average', 'poor') NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointment(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id),
    UNIQUE KEY unique_appointment_feedback (appointment_id)
);

-- Add performance indexes for feedback
ALTER TABLE feedback 
ADD INDEX idx_recent_feedback (created_at DESC);

-- Create notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    receiver_type ENUM('admin', 'client') NOT NULL,
    notification_type ENUM('new_appointment', 'status_update', 'feedback', 'reminder') NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    related_id INT,
    feedback_id INT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE SET NULL
);

-- Add notification indexes
ALTER TABLE notifications 
ADD INDEX idx_user_receiver (user_id, receiver_type, is_read),
ADD INDEX idx_receiver_type (receiver_type, is_read),
ADD INDEX idx_notifications_date (created_at),
ADD INDEX idx_unread_admin_notifications (is_read, receiver_type, created_at);

-- Create admin_reminders table
CREATE TABLE admin_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    reminder_date DATE NOT NULL,
    reminder_time TIME NOT NULL,
    status ENUM('pending', 'sent') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointment(id)
);

-- Add index for admin_reminders
CREATE INDEX idx_reminder_date ON admin_reminders (reminder_date, status);

-- Create admin audit log table
CREATE TABLE admin_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE CASCADE
);

-- Insert default admin users
INSERT INTO admin (username, password) VALUES
('Admin1', '$2y$10$9M3vbJKqJk2T1XdXYV4HxuMqK7XoKRfVm94wd0UWj9TLsV2IQW7Hy'), -- Password: Admin@123
('Admin2', '$2y$10$l7.5sJd2cMgkEh34YBnE6.0d3EDRqyDL1Yq0anGYU3MlKP/T/89yO'); -- Password: Admin@1234

-- Insert sample stylists
INSERT INTO stylists (name, specialization, phone, email) VALUES 
('Sarah Johnson', 'Hair Styling, Coloring', '555-0101', 'sarah@salon.com'),
('Mike Wilson', 'Cuts, Styling', '555-0102', 'mike@salon.com'),
('Lisa Brown', 'Nail Care Specialist', '555-0103', 'lisa@salon.com');