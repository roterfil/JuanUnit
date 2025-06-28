-- JuanUnit Database Schema
-- Create the database
CREATE DATABASE IF NOT EXISTS juanunit_db;
USE juanunit_db;

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Units table
CREATE TABLE units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_number VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    monthly_rent DECIMAL(10,2) NOT NULL,
    status ENUM('Available', 'Occupied', 'Under Maintenance') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tenants table
CREATE TABLE tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    unit_id INT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE,
    due_date DATE NOT NULL,
    status ENUM('Paid', 'Unpaid') DEFAULT 'Unpaid',
    proof_of_payment VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Announcements table
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Maintenance requests table
CREATE TABLE maintenance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('admin', 'tenant') NOT NULL,
    message VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE maintenance_requests ADD COLUMN is_archived TINYINT(1) DEFAULT 0;

-- Add the missing image_path columns
ALTER TABLE units ADD COLUMN image_path VARCHAR(255) NULL;
ALTER TABLE maintenance_requests ADD COLUMN image_path VARCHAR(255) NULL;

-- Insert default admin account (password: admin123)
INSERT INTO admins (username, password, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Sample units
INSERT INTO units (unit_number, description, monthly_rent) VALUES 
('101', 'Single room with private bathroom', 8000.00),
('102', 'Double occupancy room with shared facilities', 6000.00),
('201', 'Premium single room with balcony', 10000.00),
('202', 'Standard single room', 7500.00);

-- Sample announcements
INSERT INTO announcements (title, content) VALUES 
('Welcome to JuanUnit!', 'We are excited to have you as part of our community. Please read the house rules and feel free to contact management for any concerns.'),
('Maintenance Schedule', 'Regular maintenance will be conducted every first Sunday of the month from 9 AM to 12 PM.');