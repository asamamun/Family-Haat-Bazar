# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-01-18

### Added

#### User Profile & Settings System
- **User Profile Management**: Complete user profile system with personal information, addresses, and preferences
- **User Settings Page**: Tabbed interface for profile, addresses, password change, and preferences
- **Auto-populate Checkout**: Order forms now auto-fill with saved user profile data
- **Password Change**: Secure password update functionality with current password verification
- **Database Schema**: New `user_profiles` table with comprehensive user data storage

#### Enhanced User Experience
- **User Dropdown Menu**: Replaced simple logout button with professional dropdown containing:
  - My Orders (links to order history)
  - User Settings (links to profile management)
  - Logout option
- **Order History Page**: Complete order management with:
  - Order listing with status badges
  - Detailed order view in modal
  - Order cancellation for pending orders
  - Beautiful card-based layout
- **Improved Order Details Modal**: 
  - Side-by-side billing and shipping addresses
  - Professional card-based layout with icons
  - Better visual hierarchy and spacing

#### POS System Enhancements
- **Discount System**: 
  - Fixed amount discount input ($10.00)
  - Percentage-based discount input (15%)
  - Smart calculation (uses higher of amount vs percentage)
  - Real-time total updates
  - Validation to prevent discount exceeding subtotal
- **Order Notes**: Text area for custom order notes and special instructions
- **Today's Sales Widget**: Real-time sales statistics display showing:
  - Total sales amount
  - Number of orders
  - Average transaction value
  - Items sold count
  - Manual refresh capability
- **Enhanced UI**: Better layout utilization and professional appearance

#### Configuration & Infrastructure
- **VAT Configuration**: Structured VAT rates configuration system
- **Config Function Enhancement**: Improved error handling and validation
- **Order Processing**: Enhanced order validation and processing logic

### Fixed
- **Order Placement Validation**: Fixed "Missing required field: discount_amount" error
- **Config Function**: Improved error handling for missing configuration files
- **Order Processing**: Better validation for numeric fields in order processing

### Changed
- **Header Navigation**: Updated to use dropdown menu instead of simple logout button
- **Order Processing**: Enhanced validation logic for better error handling
- **POS Layout**: Reorganized layout to better utilize screen space
- **User Authentication Flow**: Improved user experience with profile integration

### Technical Improvements
- **Database Structure**: Added user_profiles table with foreign key relationships
- **API Endpoints**: New REST endpoints for user management and order operations
- **Security**: Enhanced password validation and user data protection
- **Code Organization**: Better separation of concerns and modular structure

### Database Changes
- **New Table**: `user_profiles` - Stores user personal information, addresses, and preferences
- **Enhanced Orders**: Better integration with user profiles for address management

### Files Added
- `user-settings.php` - User profile and settings management page
- `user-orders.php` - Order history and management page
- `apis/update-user-settings.php` - API for updating user settings
- `apis/get-order-details.php` - API for fetching detailed order information
- `apis/cancel-order.php` - API for order cancellation
- `admin/apis/todays-sales.php` - API for POS sales statistics
- `create_user_profiles_table.sql` - Database schema for user profiles
- `config/vat.php` - VAT configuration file

### Files Modified
- `admin/pos.php` - Added discount system, order notes, and sales widget
- `place_order.php` - Added auto-population from user profiles
- `components/header.php` - Updated with user dropdown menu
- `apis/processOrder.php` - Enhanced validation and error handling
- `src/settings.php` - Improved config function with error handling

## [1.0.0] - Previous Release
- Initial e-commerce and POS system implementation
- Basic product management
- Order processing
- User authentication
- Admin dashboard