# ISP Solution - API Documentation

**Version:** 1.0.0  
**Base URL:** `/api`  
**Authentication:** Laravel Sanctum (Token-based)

---

## Table of Contents

1. [Authentication](#authentication)
2. [Data API](#data-api)
3. [Chart API](#chart-api)
4. [Network Management API](#network-management-api)
5. [IPAM API](#ipam-api)
6. [Monitoring API](#monitoring-api)
7. [Error Handling](#error-handling)

---

## Authentication

All API endpoints require authentication via Laravel Sanctum tokens.

### Headers Required
```
Authorization: Bearer {your-token}
Content-Type: application/json
Accept: application/json
```

---

## Data API

### Get Users
**Endpoint:** `GET /api/data/users`

**Description:** Retrieve paginated list of users with optional filtering.

**Query Parameters:**
- `search` (string, optional): Search by name, email, or username
- `role` (string, optional): Filter by role name
- `status` (string, optional): Filter by status ('active' or 'inactive')
- `per_page` (integer, optional): Results per page (default: 20, max: 100)
- `page` (integer, optional): Page number

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "username": "johndoe",
      "is_active": true,
      "role": {
        "id": 2,
        "name": "admin"
      },
      "created_at": "2026-01-15T10:00:00.000000Z"
    }
  ],
  "total": 100,
  "per_page": 20
}
```

---

### Get Network Users
**Endpoint:** `GET /api/data/network-users`

**Description:** Retrieve network users (PPPoE, Hotspot, etc.)

**Query Parameters:**
- `search` (string, optional): Search by username or IP address
- `status` (string, optional): Filter by status
- `per_page` (integer, optional): Results per page

**Response:** Paginated network users with package details

---

### Get Invoices
**Endpoint:** `GET /api/data/invoices`

**Description:** Retrieve invoices with filtering options

**Query Parameters:**
- `search` (string, optional): Search by invoice number or user name
- `status` (string, optional): Filter by status (pending, paid, overdue)
- `from_date` (date, optional): Filter from date (Y-m-d format)
- `to_date` (date, optional): Filter to date (Y-m-d format)
- `per_page` (integer, optional): Results per page

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "invoice_number": "INV-20260115-00001",
      "user": {
        "id": 5,
        "name": "Jane Smith"
      },
      "amount": 1500.00,
      "tax_amount": 150.00,
      "total_amount": 1650.00,
      "status": "pending",
      "due_date": "2026-02-15",
      "created_at": "2026-01-15T10:00:00.000000Z"
    }
  ]
}
```

---

### Get Payments
**Endpoint:** `GET /api/data/payments`

**Description:** Retrieve payment records

**Query Parameters:**
- `search` (string, optional): Search by payment number, transaction ID, or user
- `method` (string, optional): Filter by payment method
- `status` (string, optional): Filter by status
- `from_date` (date, optional): Filter from date
- `to_date` (date, optional): Filter to date
- `per_page` (integer, optional): Results per page

---

### Get Dashboard Stats
**Endpoint:** `GET /api/data/dashboard-stats`

**Description:** Get comprehensive dashboard statistics

**Response:**
```json
{
  "users": {
    "total": 1250,
    "active": 1180
  },
  "invoices": {
    "total": 5432,
    "pending": 234,
    "overdue": 45,
    "paid": 5153
  },
  "revenue": {
    "today": 15000.00,
    "this_month": 450000.00,
    "this_year": 5400000.00
  },
  "network_users": {
    "total": 980,
    "active": 856,
    "suspended": 124
  }
}
```

---

### Get Recent Activities
**Endpoint:** `GET /api/data/recent-activities`

**Description:** Get recent system activities

**Query Parameters:**
- `limit` (integer, optional): Number of activities to return (default: 10)

**Response:**
```json
[
  {
    "type": "payment",
    "message": "Payment received from John Doe",
    "amount": 1500.00,
    "timestamp": "2026-01-15T10:30:00.000000Z"
  },
  {
    "type": "invoice",
    "message": "Invoice generated for Jane Smith",
    "amount": 2000.00,
    "timestamp": "2026-01-15T10:25:00.000000Z"
  }
]
```

---

## Chart API

### Get Revenue Chart
**Endpoint:** `GET /api/charts/revenue`

**Description:** Get monthly revenue data for the year

**Query Parameters:**
- `year` (integer, optional): Year to retrieve data for (default: current year)

**Response:**
```json
{
  "categories": ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
  "series": [
    {
      "name": "Revenue",
      "data": [45000, 52000, 48000, 55000, 60000, 58000, 62000, 65000, 63000, 70000, 75000, 80000]
    }
  ]
}
```

---

### Get Invoice Status Chart
**Endpoint:** `GET /api/charts/invoice-status`

**Description:** Get invoice distribution by status

**Response:**
```json
{
  "labels": ["Pending", "Overdue", "Paid"],
  "series": [234, 45, 5153]
}
```

---

### Get User Growth Chart
**Endpoint:** `GET /api/charts/user-growth`

**Description:** Get user growth over time

**Query Parameters:**
- `months` (integer, optional): Number of months to show (default: 12)

**Response:**
```json
{
  "categories": ["Jan 2025", "Feb 2025", "Mar 2025", ...],
  "series": [
    {
      "name": "Total Users",
      "data": [850, 920, 985, 1050, 1120, 1180, 1250]
    }
  ]
}
```

---

### Get Payment Method Chart
**Endpoint:** `GET /api/charts/payment-methods`

**Description:** Get payment distribution by method

**Response:**
```json
{
  "labels": ["Cash", "Bkash", "Nagad", "Bank Transfer", "Stripe"],
  "series": [125000, 85000, 72000, 45000, 28000]
}
```

---

### Get Daily Revenue Chart
**Endpoint:** `GET /api/charts/daily-revenue`

**Description:** Get daily revenue for recent days

**Query Parameters:**
- `days` (integer, optional): Number of days to show (default: 30)

---

### Get Package Distribution Chart
**Endpoint:** `GET /api/charts/package-distribution`

**Description:** Get user distribution across packages

---

### Get Commission Chart
**Endpoint:** `GET /api/charts/commission`

**Description:** Get commission earnings over time

**Query Parameters:**
- `reseller_id` (integer, optional): Specific reseller ID (default: authenticated user)
- `months` (integer, optional): Number of months (default: 12)

---

### Get Dashboard Charts
**Endpoint:** `GET /api/charts/dashboard`

**Description:** Get all dashboard charts in one request

**Response:** Combined response of revenue, invoice status, user growth, and payment methods charts

---

## Network Management API

### IPAM - IP Pool Management

#### List IP Pools
**Endpoint:** `GET /api/v1/ipam/pools`

#### Create IP Pool
**Endpoint:** `POST /api/v1/ipam/pools`

**Request Body:**
```json
{
  "name": "Main Pool",
  "network": "10.0.0.0",
  "prefix_length": 24,
  "gateway": "10.0.0.1",
  "dns_servers": ["8.8.8.8", "8.8.4.4"]
}
```

#### Get IP Pool
**Endpoint:** `GET /api/v1/ipam/pools/{id}`

#### Update IP Pool
**Endpoint:** `PUT /api/v1/ipam/pools/{id}`

#### Delete IP Pool
**Endpoint:** `DELETE /api/v1/ipam/pools/{id}`

---

### IPAM - IP Allocation

#### List Allocations
**Endpoint:** `GET /api/v1/ipam/allocations`

#### Allocate IP
**Endpoint:** `POST /api/v1/ipam/allocations`

**Request Body:**
```json
{
  "subnet_id": 1,
  "username": "customer123",
  "mac_address": "00:11:22:33:44:55"
}
```

#### Release IP
**Endpoint:** `DELETE /api/v1/ipam/allocations/{id}`

---

## MikroTik API

### List Routers
**Endpoint:** `GET /api/v1/mikrotik/routers`

### Get Router Details
**Endpoint:** `GET /api/v1/mikrotik/routers/{id}`

### Create PPPoE Secret
**Endpoint:** `POST /api/v1/mikrotik/routers/{id}/pppoe-secrets`

### List Active Sessions
**Endpoint:** `GET /api/v1/mikrotik/routers/{id}/active-sessions`

---

## RADIUS API

### Sync Users
**Endpoint:** `POST /api/v1/radius/sync-users`

### Get User Accounting
**Endpoint:** `GET /api/v1/radius/users/{username}/accounting`

---

## OLT API

### List OLTs
**Endpoint:** `GET /api/v1/olt/devices`

### Get ONU Status
**Endpoint:** `GET /api/v1/olt/devices/{id}/onus`

### Provision ONU
**Endpoint:** `POST /api/v1/olt/devices/{id}/onus`

---

## Monitoring API

### Get Device Status
**Endpoint:** `GET /api/v1/monitoring/devices/{id}/status`

### Get Bandwidth Usage
**Endpoint:** `GET /api/v1/monitoring/devices/{id}/bandwidth`

**Query Parameters:**
- `from` (datetime): Start time
- `to` (datetime): End time
- `interval` (string): Aggregation interval (5min, hour, day)

---

## Error Handling

All API errors follow a consistent format:

### Error Response Format
```json
{
  "message": "Error description",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

### HTTP Status Codes
- `200 OK`: Successful request
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required or failed
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation failed
- `500 Internal Server Error`: Server error

---

## Rate Limiting

API requests are rate-limited to prevent abuse:
- **Authenticated requests:** 60 requests per minute
- **Unauthenticated requests:** 10 requests per minute

Rate limit headers are included in all responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1610000000
```

---

## Pagination

Paginated endpoints return the following structure:

```json
{
  "current_page": 1,
  "data": [...],
  "first_page_url": "http://api.example.com/endpoint?page=1",
  "from": 1,
  "last_page": 10,
  "last_page_url": "http://api.example.com/endpoint?page=10",
  "next_page_url": "http://api.example.com/endpoint?page=2",
  "path": "http://api.example.com/endpoint",
  "per_page": 20,
  "prev_page_url": null,
  "to": 20,
  "total": 200
}
```

---

## Support

For API support and questions:
- **Email:** support@ispsolution.com
- **Documentation:** https://docs.ispsolution.com
- **Issues:** https://github.com/i4edubd/ispsolution/issues

---

**Last Updated:** 2026-01-18
