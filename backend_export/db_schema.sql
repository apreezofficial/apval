-- Database Schema for Apval
CREATE DATABASE IF NOT EXISTS apval_db;
USE apval_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    subscriptionTier VARCHAR(20) DEFAULT 'free',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Valentines Table
CREATE TABLE IF NOT EXISTS valentines (
    id VARCHAR(50) PRIMARY KEY,
    userId VARCHAR(50) NOT NULL,
    templateId VARCHAR(50),
    recipient VARCHAR(255),
    sender VARCHAR(255),
    customSlug VARCHAR(100) UNIQUE,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    data LONGTEXT, -- Stores all other fields as JSON
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
);

-- Upgrade Requests Table
CREATE TABLE IF NOT EXISTS upgrade_requests (
    id VARCHAR(50) PRIMARY KEY,
    userId VARCHAR(50) NOT NULL,
    userEmail VARCHAR(255) NOT NULL,
    receipt LONGTEXT, -- Base64 receipt image
    timestamp VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pending',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
);

