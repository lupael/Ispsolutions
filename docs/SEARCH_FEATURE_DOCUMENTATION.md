# Global Search Feature Documentation

## Overview
The global search feature allows users to search for customers and invoices directly from the sidebar menu. The search respects user permissions and ownership, ensuring users can only access data they are authorized to view.

## Access
- **Location**: Search input is located at the top of the sidebar, below the logo
- **Route**: `/panel/search`
- **Access Level**: All authenticated users

## Search Capabilities

### Search Criteria
You can search using any of the following:
- **Customer Name**: Full or partial name
- **Email**: Customer email address
- **Username**: Customer username
- **Invoice Number**: Invoice identification number

### Search Results
Results are displayed in two sections:
1. **Customers Section**: Shows matching customers with their details
2. **Invoices Section**: Shows matching invoices with customer and billing information

## Permission-Based Access

### Developer (Level 0)
- Can search and view all customers and invoices across all tenants
- No restrictions applied

### Super Admin (Level 10), Admin (Level 20), Manager (Level 50), Accountant (Level 70)
- Can search and view customers and invoices within their tenant only
- Tenant filter automatically applied

### Operator (Level 30), Sub-Operator (Level 40), Staff (Level 80)
- Can search and view only customers and invoices they created
- Both tenant and ownership filters applied
- Results limited to their own created records

### Customer (Level 100)
- Cannot search for other customers
- Can only view their own invoices

## Features

### Customer Search Results Display
- Customer Name
- Email
- Username
- Current Package
- Account Status (Active/Inactive)
- Tenant (for Developer only)
- View Link (when available)

### Invoice Search Results Display
- Invoice Number
- Customer Name
- Customer Email
- Package
- Amount
- Due Date
- Status (Paid, Pending, Overdue, Cancelled, Draft)
- View Link (when available)

## Security Features
- SQL injection protection with escaped LIKE wildcards
- Permission-based filtering at database query level
- Route protection with authentication middleware
- Tenant isolation enforced
- Ownership validation for operators and staff

## Usage Example

### Basic Search
1. Click on the search input in the sidebar
2. Type your search query (e.g., customer name, email, mobile, or invoice number)
3. Press Enter or click outside to submit
4. View results on the search results page

### Search Tips
- Use partial matches for better results
- Search is case-insensitive
- Results are limited to 20 per category for performance
- Special characters in search query are automatically escaped

## Technical Details

### Files Modified/Created
1. **Controller**: `app/Http/Controllers/Panel/SearchController.php`
2. **Route**: Added to `routes/web.php`
3. **View**: `resources/views/panels/search/results.blade.php`
4. **Sidebar**: Updated `resources/views/panels/partials/sidebar.blade.php`

### Performance Considerations
- Results are limited to 20 items per category
- Only necessary fields are selected from database
- Eager loading used for relationships to prevent N+1 queries
- Indexed fields (email, username, mobile) for faster searches

## Future Enhancements
Potential improvements that could be added:
- Pagination for more than 20 results
- Advanced filters (date range, status, etc.)
- Live search with AJAX
- Search history
- Export search results
- Saved searches
