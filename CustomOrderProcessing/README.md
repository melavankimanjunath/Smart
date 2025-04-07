# Vendor_CustomOrderProcessing

A Magento 2 module that provides a custom API endpoint for updating order statuses and logs status changes.

## Features

- Custom REST API endpoint for updating order statuses
- Order status change logging to a custom database table
- Automatic email notifications when orders are marked as shipped
- Proper validation of order status transitions
- Admin UI for viewing status change logs
- Caching strategy for improved performance
- Rate limiting to prevent API abuse
- Security headers for API responses

## Installation

1. Copy the module to `app/code/Vendor/CustomOrderProcessing`
2. Enable the module:
   ```bash
   bin/magento module:enable Vendor_CustomOrderProcessing
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento cache:clean
   ```

## API Usage

### Update Order Status

**Endpoint:** `POST /rest/V1/orders/update-status`

**Headers:**
- `Content-Type: application/json`
- `Authorization: Bearer <token>`

**Request Body:**
```json
{
  "statusUpdate": {
    "increment_id": "000000001",
    "new_status": "processing"
  }
}
```

**Response:**
- `true` if successful
- Error message if unsuccessful

## Rate Limiting

The API is rate-limited to 100 requests per hour per IP address to prevent abuse. If you exceed this limit, you'll receive a 429 Too Many Requests response.

## Caching Strategy

The module implements caching for status log data to improve performance:
- Individual status logs are cached by ID
- Status logs for a specific order are cached by order ID
- Cache is automatically invalidated when new logs are added

## Security Features

The API endpoint includes several security features:
- Content-Type enforcement
- XSS protection headers
- Frame protection headers
- HSTS headers
- Content Security Policy
- Referrer Policy
- Cache control headers

## Admin UI

The module adds an admin grid for viewing order status change logs:
- Navigate to Sales > Order Status Logs
- Filter and sort logs by various criteria
- View related orders directly from the grid

## Architectural Decisions

1. **Repository Pattern**: Used repositories for data access to follow Magento 2 best practices and ensure proper separation of concerns.

2. **Dependency Injection**: Utilized constructor injection for dependencies instead of using the ObjectManager directly.

3. **Event Observer**: Implemented an observer for the `sales_order_save_after` event to detect status changes and perform actions.

4. **API Design**: Created a clean API interface with proper validation to ensure data integrity.

5. **Database Schema**: Used declarative schema (db_schema.xml) for database table creation, which is the recommended approach in Magento 2.3+.

6. **Email Notifications**: Implemented a custom email template and used the TransportBuilder for sending emails.

7. **Error Handling**: Added proper exception handling and logging throughout the module.

8. **Status Validation**: Implemented validation logic to ensure only valid status transitions are allowed.

9. **Caching Strategy**: Implemented caching for frequently accessed data to improve performance.

10. **Rate Limiting**: Added rate limiting to prevent API abuse.

11. **Security Headers**: Added security headers to API responses to enhance security.

12. **UI Components**: Used Magento UI components for the admin grid to ensure consistency with the Magento admin interface.

## Database Structure

The module creates a custom table `vendor_order_status_log` with the following structure:

- `log_id` (primary key)
- `order_id` (order entity ID)
- `increment_id` (order increment ID)
- `old_status` (previous order status)
- `new_status` (new order status)
- `created_at` (timestamp of the status change)

## Summary of Enhancements

UI Enhancements:

- Added an admin grid to view order status logs
- Implemented UI components for consistent admin experience
- Added filtering, sorting, and pagination capabilities
- Created links to view related orders

Caching Strategy:

- Implemented a custom cache type for status logs
- Added caching for individual logs and collections
- Implemented cache invalidation when data changes
- Used proper cache tags for targeted invalidation

Rate Limiting:

- Added IP-based rate limiting for API requests
- Configured limits of 100 requests per hour
- Implemented proper error responses for rate limit exceeded
- Used caching for efficient rate limit tracking

Security Headers:

- Added Content-Type Options header to prevent MIME sniffing
- Added XSS Protection header to enable browser XSS filters
- Added Frame Options header to prevent clickjacking
- Added HSTS header to enforce HTTPS
- Added Content Security Policy to restrict resource loading
- Added Referrer Policy to control referrer information
- Added Cache Control headers to prevent sensitive data caching