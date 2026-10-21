# PawConnect - Pet Adoption Platform 🐾

**Connecting Loving Homes With Pets In Need**

PawConnect is a full-featured, modern web application designed to make pet adoption simple, accessible, transparent, and organized. It provides a seamless discovery experience for adopters while empowering rescue shelters and administrators to manage pet profiles and process adoption applications efficiently.

---

## 🛠️ Tools, Technology & Languages

- **Frontend:**
  - HTML5 & Modern Semantic Markup
  - CSS3 with Custom Modern Design System & Micro-animations
  - JavaScript (Vanilla ES6+ for dynamic filters, photo preview, wishlist AJAX, and client-side interactions)
  - Bootstrap 5.3 Framework & Bootstrap Icons
  - Google Fonts (*Plus Jakarta Sans*)
- **Backend:**
  - PHP 8.x (Modular, Object-Oriented PDO, prepared statements, session security, CSRF protection)
- **Database:**
  - MySQL / MariaDB (`pawconnect_db`)
- **Development Assistance:**
  - Advanced AI Tools

---

## 🔄 System Workflow (Working of the System)

The system is organized into a clear, 4-stage lifecycle matching the user adoption journey:

```
[ REGISTER / LOGIN ] ──> [ SEARCH PETS ] ──> [ VIEW PET DETAILS ] ──> [ APPLY FOR ADOPTION ] ──> [ REQUEST REVIEW ] ──> [ ADOPTION STATUS ]
```

1. **Register / Login:**
   - Adopters create an account with contact details, living situation, and secure credentials.
   - Built-in demo one-click logins are provided for both **Adopter** and **Administrator**.
2. **Search Pets:**
   - Multi-faceted filter system: Species (Dogs, Cats, Rabbits, Birds), Breed, Age category, Gender, Size, Adoption Status, and Medical badges (Vaccinated, Good with Kids, Good with other pets).
3. **View Pet Details:**
   - High-resolution photo showcase, health indicators (Vaccinations, Spayed/Neutered, Microchipped, House-trained), story, behavioral traits, and caring shelter information.
4. **Apply for Adoption:**
   - Comprehensive structured application covering applicant info, housing type (Own/Rent, Fenced Yard, Landlord Approval), household dynamics (adults, children), other pets, daily hours alone, veterinarian reference, and adoption statement.
5. **Request Review (Admin / Shelter):**
   - Shelter coordinators review incoming applications in real time.
   - Coordinators update status (`Pending` &rarr; `Under Review` &rarr; `Approved` / `Rejected` / `Completed`), write internal notes, and send direct feedback messages to the applicant.
6. **Adoption Status (User Tracking):**
   - Adopters view a real-time 3-step visual progress tracker (`Submitted` &rarr; `Shelter Review` &rarr; `Approved / Homecoming`), view shelter feedback notes, and contact the shelter.

---

## 📦 Main Modules

### 1. User & Authentication Module
- User registration with password hashing (`password_hash` with BCRYPT).
- Secure authentication & session management with CSRF validation.
- User profile dashboard (update contact details, address, change password).
- Role-based access control (`user` vs `admin`).

### 2. Pet Discovery & Catalog Module
- Dynamic keyword search and multi-criteria filtering sidebar.
- Responsive pet cards with hover elevation, category labels, and status badges.
- Pet details profile with medical record highlights, compatibility matrix, and similar pet suggestions.
- Interactive wishlist / "Save to Favorites" toggle with AJAX.

### 3. Adoption Application & Processing Module
- Structured, multi-section adoption questionnaire.
- Auto-generation of unique application tracking codes (`PAW-2026-XXXX`).
- Real-time application tracker (`my-applications.php`) with step-by-step progress nodes and shelter messaging.
- Capability to withdraw pending applications.

### 4. Shelter & Admin Management Portal (`admin/`)
- **Dashboard Overview:** KPI metrics (Total Pets, Available Pets, Adopted Pets, Pending Requests, Approved Adoptions, Total Adopters).
- **Pet Inventory:** Complete CRUD (Add, Edit, View, Delete pet profiles) with image upload or URL support, health toggles, and fee settings.
- **Application Evaluation Center:** Detailed applicant questionnaire review, one-click status transitions, shelter internal notes, and direct applicant message dispatch.
- **User Directory:** Overview of all registered adopters and application counts.

### 5. Shelter Network & Engagement Module
- Partner shelter directory with contact numbers, addresses, and hours.
- Inquiry contact form storing messages into `contact_messages`.
- Adoption policies, FAQ, and 24/7 rescue helpline.

### 6. One-Click Setup & Migration Utility
- `install.php`: Web-based installer with diagnostic checks and one-click database initialization.
- `setup_db.php`: CLI setup script.

---

## 🗄️ Database Schema (`pawconnect_db`)

- `users`: ID, Name, Email, Password, Phone, Address, City, Role, Timestamps.
- `categories`: Species (Dogs, Cats, Rabbits, Birds), Slugs, Icons, Descriptions.
- `shelters`: Shelter Name, Email, Phone, Address, City, Website.
- `pets`: Name, Category, Shelter, Breed, Age (years & months), Gender, Size, Color, Status (`Available`, `Pending`, `Adopted`), Health Badges, Compatibility, Description, Image URL, Fee.
- `adoption_applications`: Application No, User ID, Pet ID, Full Name, Contact info, Housing details, Fenced Yard, Household counts, Pet experience, Vet contact, Reason, Status (`Pending`, `Under Review`, `Approved`, `Rejected`, `Completed`), Admin notes, Applicant feedback, Reviewer ID, Timestamps.
- `favorites`: User ID, Pet ID, Timestamp.
- `contact_messages`: Name, Email, Subject, Message, Is Read, Timestamp.

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.x
- MySQL / MariaDB (e.g. XAMPP)

### Option 1: Quick Run (Recommended)
1. Ensure MySQL is running in XAMPP.
2. Double-click `run.bat` (or run `C:\xampp\php\php.exe -S localhost:8000`).
3. Open your browser at **`http://localhost:8000`**.

### Option 2: Database Setup
Visit **`http://localhost:8000/install.php`** or run:
```bash
php setup_db.php
```

---

## 🔑 Default Login Credentials

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@pawconnect.org` | `admin123` |
| **Adopter User** | `john@example.com` | `user123` |
