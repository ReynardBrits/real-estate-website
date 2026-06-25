CREATE DATABASE IF NOT EXISTS real_estate_db;
USE real_estate_db;

DROP TABLE IF EXISTS favourites;
DROP TABLE IF EXISTS enquiries;
DROP TABLE IF EXISTS property_images;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS agents;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('client', 'agent', 'admin') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clients (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address VARCHAR(255),
    date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE agents (
    agent_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    agency_name VARCHAR(100),
    position VARCHAR(100),
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    property_type VARCHAR(50) NOT NULL,
    listing_type ENUM('For Sale', 'For Rent') NOT NULL,
    location VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    bedrooms INT DEFAULT 0,
    bathrooms INT DEFAULT 0,
    garages INT DEFAULT 0,
    floor_size INT,
    erf_size INT,
    status ENUM('Available', 'Sold', 'Rented') DEFAULT 'Available',
    agent_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(agent_id) ON DELETE SET NULL
);

CREATE TABLE property_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 1,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

CREATE TABLE enquiries (
    enquiry_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NULL,
    property_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    status ENUM('New', 'Contacted', 'Viewing Scheduled', 'Closed', 'Lost') NOT NULL DEFAULT 'New',
    date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE SET NULL,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE SET NULL
);

CREATE TABLE favourites (
    favourite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    date_saved TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favourite (user_id, property_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);


INSERT INTO users (full_name, email, password, phone, role) VALUES
(
    'System Admin',
    'admin@britsrealty.co.za',
    '$2y$12$N.ktzzU/HYRTyQA/7T/Ht.I47w5VdsDuiW/3rNVqiINOUF6k09VHy',
    '0123456789',
    'admin'
),
(
    'Real Estate Agent',
    'agent@britsrealty.co.za',
    '$2y$12$xDXqzULQ3AIjqPcTp9LjCOkCRXFnW77UpAvfz.iv8be1f.93mKn5q',
    '0821234567',
    'agent'
),
(
    'Test Client',
    'client@test.co.za',
    '$2y$12$D9p1GIMbsgfm1qrGgudWV.hb49IiXq.pGaw38D9xfWasvbmDXc0c2',
    '0835551234',
    'client'
);

INSERT INTO clients (user_id, address) VALUES
(3, 'Pretoria, Gauteng');

INSERT INTO agents (user_id, agency_name, position, bio) VALUES
(
    2,
    'Brits Realty',
    'Senior Property Agent',
    'Experienced property agent specialising in residential homes and apartments.'
);

INSERT INTO properties 
(
    title,
    description,
    price,
    property_type,
    listing_type,
    location,
    address,
    bedrooms,
    bathrooms,
    garages,
    floor_size,
    erf_size,
    status,
    agent_id
) 
VALUES
(
    'Modern Family Home in Pretoria East',
    'A spacious family home with modern finishes, a large garden, open-plan living area, and secure parking. This property is ideal for a family looking for comfort and space.',
    1850000.00,
    'House',
    'For Sale',
    'Pretoria East',
    'Pretoria East, Pretoria, Gauteng',
    4,
    3,
    2,
    280,
    900,
    'Available',
    1
),
(
    'Luxury Apartment in Sandton',
    'A stylish apartment located close to business centres, shopping malls, and restaurants. This apartment is ideal for young professionals or property investors.',
    1250000.00,
    'Apartment',
    'For Sale',
    'Sandton',
    'Sandton, Johannesburg, Gauteng',
    2,
    2,
    1,
    95,
    NULL,
    'Available',
    1
),
(
    'Townhouse in Centurion',
    'A neat townhouse in a secure complex with easy access to schools, shops, and main roads. Suitable for a small family or first-time buyer.',
    1450000.00,
    'Townhouse',
    'For Sale',
    'Centurion',
    'Centurion, Gauteng',
    3,
    2,
    2,
    160,
    300,
    'Available',
    1
),
(
    'Student Flat in Hatfield',
    'Affordable rental flat close to university campuses, shops, and public transport. A good option for students or young working people.',
    7500.00,
    'Apartment',
    'For Rent',
    'Hatfield',
    'Hatfield, Pretoria, Gauteng',
    1,
    1,
    0,
    45,
    NULL,
    'Available',
    1
);

INSERT INTO property_images 
(property_id, image_url, is_primary, display_order) 
VALUES

(
    1,
    'assets/images/properties/house1-1.jpg',
    TRUE,
    1
),
(
    1,
    'assets/images/properties/house1-2.jpg',
    FALSE,
    2
),
(
    1,
    'assets/images/properties/house1-3.jpg',
    FALSE,
    3
),
(
    1,
    'assets/images/properties/house1-4.jpg',
    FALSE,
    4
),


(
    2,
    'assets/images/properties/apartment1-1.jpg',
    TRUE,
    1
),
(
    2,
    'assets/images/properties/apartment1-2.jpg',
    FALSE,
    2
),
(
    2,
    'assets/images/properties/apartment1-3.jpg',
    FALSE,
    3
),
(
    2,
    'assets/images/properties/apartment1-4.jpg',
    FALSE,
    4
),


(
    3,
    'assets/images/properties/townhouse1-1.jpg',
    TRUE,
    1
),
(
    3,
    'assets/images/properties/townhouse1-2.jpg',
    FALSE,
    2
),
(
    3,
    'assets/images/properties/townhouse1-3.jpg',
    FALSE,
    3
),
(
    3,
    'assets/images/properties/townhouse1-4.jpg',
    FALSE,
    4
),


(
    4,
    'assets/images/properties/flat1-1.jpg',
    TRUE,
    1
),
(
    4,
    'assets/images/properties/flat1-2.jpg',
    FALSE,
    2
),
(
    4,
    'assets/images/properties/flat1-3.jpg',
    FALSE,
    3
);

INSERT INTO enquiries 
(client_id, property_id, name, email, phone, message)
VALUES
(
    1,
    1,
    'Test Client',
    'client@test.co.za',
    '0835551234',
    'I am interested in this property and would like more information.'
);