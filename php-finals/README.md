# Rental Property Management System

A comprehensive web-based rental property management system built with PHP and JSON file storage. Perfect for landlords managing multiple properties, tenants, and rent payments.

## Features

### User Authentication
- Secure landlord registration with password hashing
- Login system with PHP session management
- Protected pages requiring authentication

### Tenant Management
- Add, edit, and delete tenants
- Track contact information (name, phone, email)
- Assign tenants to properties
- Set monthly rent amounts

### Property Management
- Manage multiple properties/units
- Track property status: Occupied, Vacant, Under Maintenance
- Support for different property types: Apartment, House, Condo, Studio, Room
- Automatic status updates when tenants are assigned

### Payment Tracking
- Record monthly rent payments for each tenant
- Track payment status: Paid, Late, Unpaid
- View payment history by tenant
- Quick payment status updates
- Payment summary dashboard

### Contact Directory
- Searchable tenant directory
- Quick access to tenant contact information
- Edit contact details on the fly
- Visual contact cards

### Dashboard
- Overview of all properties and their status
- Payment status summary
- Quick action links to all features

## Getting Started

1. **First Time Setup**
   - Click on "Register here" on the login page
   - Create your landlord account with name, email, and password
   - Login with your credentials

2. **Add Properties**
   - Navigate to "Properties" from the menu
   - Click "Add New Property"
   - Enter property details (name, address, type, status)

3. **Add Tenants**
   - Navigate to "Tenants" from the menu
   - Click "Add New Tenant"
   - Fill in tenant information and assign to a vacant property
   - The property will automatically be marked as "Occupied"

4. **Track Payments**
   - Navigate to "Payments" from the menu
   - Click "Add Payment Record"
   - Select tenant, month, amount, and status
   - Update payment status anytime using the dropdown

5. **View Contacts**
   - Navigate to "Contacts" from the menu
   - Search for tenants by name, phone, or email
   - Edit contact information as needed

## Technical Details

- **Backend**: PHP 8.4
- **Storage**: JSON files (no database required)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Server**: PHP Built-in Development Server

## Data Storage

All data is stored in JSON files located in the `/data` directory:
- `users.json` - User accounts
- `tenants.json` - Tenant information
- `properties.json` - Property listings
- `payments.json` - Payment records

For detailed schema information, see [JSON_SCHEMA.md](JSON_SCHEMA.md)

## Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- Session regeneration on login to prevent fixation attacks
- Input sanitization to prevent XSS attacks
- Server-side validation for all forms
- Email and numeric validation

## Browser Support

Works on all modern browsers:
- Chrome/Edge
- Firefox
- Safari

The interface is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

## Tips

- **Property Status**: Properties are automatically marked as "Occupied" when you assign a tenant
- **Payment Tracking**: Add payment records monthly to keep track of rent payments
- **Search Contacts**: Use the search feature to quickly find tenant information
- **Dashboard**: Check the dashboard regularly for an overview of your properties and payments

## Support

For technical documentation, see:
- [JSON_SCHEMA.md](JSON_SCHEMA.md) - Database schema and relationships
- [replit.md](replit.md) - Technical architecture and preferences

## License

This project is provided as-is for rental property management purposes.
