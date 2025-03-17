# Gym Management System

## Table of Contents
1. [Project Description](#project-description)
2. [Key Features](#key-features)
3. [Installation Guide](#installation-guide)
4. [Usage Instructions](#usage-instructions)
   - [User Workflow](#user-workflow)
   - [Admin Workflow](#admin-workflow)
5. [API Documentation](#api-documentation)
6. [Additional Information](#additional-information)

## Project Description
This project is a web-based Gym Management System designed to handle user registrations, bookings, and administrative tasks. It is developed as part of the "Network-Centric Information Systems" course at the University of Piraeus for the academic year 2024-2025.

### Key Features:
- **User Registration & Management**: Users can register and log in, while administrators manage approvals and role assignments.
- **Booking System**: Users can book gym sessions based on availability and cancel under specific conditions.
- **Admin Panel**: Administrators can manage users, gym instructors, schedules, and promotional announcements.
- **Announcements & Offers**: Admins can post news and offers visible only to registered users.
- **REST API Integration**: Facilitates backend communication with a SQL/NoSQL database and retrieves country/city data dynamically.

## Installation Guide
The installation steps are provided in the project documentation included in the exercise. Please refer to the provided documentation for a detailed setup guide.

## Usage Instructions

### User Workflow:
1. **Register an account**: Users provide details including name, email, and password.
2. **Login**: Access the system via username and password.
3. **Browse services**: View available gym programs and schedules.
4. **Make a booking**: Select a session based on availability.
5. **Cancel bookings**: Users can cancel bookings up to 2 hours in advance.
6. **View announcements**: Stay updated with gym-related news and offers.

### Admin Workflow:
1. **Approve/reject user registrations**.
2. **Manage gym programs, trainers, and schedules**.
3. **Post announcements and promotional offers**.
4. **View and manage user activity and bookings**.

## API Documentation
- All backend transactions occur via REST APIs.
- APIs follow standard HTTP methods (`GET`, `POST`, `PUT`, `DELETE`).

## Additional Information
For further details on system design, refer to the project documentation included in the repository.
