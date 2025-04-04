# Vendor_CustomOrderProcessing

A Magento 2 module that provides a custom API endpoint for updating order statuses and logs status changes.

## Features

- Custom REST API endpoint for updating order statuses
- Order status change logging to a custom database table
- Automatic email notifications when orders are marked as shipped
- Proper validation of order status transitions

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

## Architectural Decisions

1. **Repository Pattern**: Used repositories for data access to follow Magento 2 best practices and ensure proper separation of concerns.

2. **Dependency Injection**: Utilized constructor injection for dependencies instead of using the ObjectManager directly.

3. **Event Observer**: Implemented an observer for the `sales_order_save_after` event to detect status changes and perform actions.

4. **API Design**: Created a clean API interface with proper validation to ensure data integrity.

5. **Database Schema**: Used declarative schema (db_schema.xml) for database table creation, which is the recommended approach in Magento 2.3+.

6. **Email Notifications**: Implemented a custom email template and used the TransportBuilder for sending emails.

7. **Error Handling**: Added proper exception handling and logging throughout the module.

8. **Status Validation**: Implemented validation logic to ensure only valid status transitions are allowed.

## Database Structure

The module creates a custom table `vendor_order_status_log` with the following structure:

- `log_id` (primary key)
- `order_id` (order entity ID)
- `increment_id` (order increment ID)
- `old_status` (previous order status)
- `new_status` (new order status)
- `created_at` (timestamp of the status change)
