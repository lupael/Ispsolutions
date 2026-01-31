# Mass Delete Feature for IP Pools and PPPoE Profiles

## Overview
This implementation adds "Select All" and mass delete functionality for both IP Pools and PPPoE Profiles in the ISP solution application.

## Features Implemented

### 1. IP Pool Mass Delete (`/panel/admin/network/ipv4-pools`)
- **Select All Checkbox**: Located in the table header, allows selecting all IP pools on the current page
- **Individual Checkboxes**: Each IP pool row now has a checkbox for selection
- **Bulk Actions Bar**: Appears when one or more pools are selected, showing the count and action options
- **Mass Delete Action**: Allows deletion of multiple IP pools in a single operation
- **Confirmation Dialog**: Prompts user to confirm before deletion with the count of items to be deleted

### 2. PPPoE Profile Mass Delete (`/panel/admin/network/pppoe-profiles`)
- **Select All Checkbox**: Located in the table header, allows selecting all PPPoE profiles on the current page
- **Individual Checkboxes**: Each PPPoE profile row now has a checkbox for selection
- **Bulk Actions Bar**: Appears when one or more profiles are selected, showing the count and action options
- **Mass Delete Action**: Allows deletion of multiple PPPoE profiles in a single operation
- **Confirmation Dialog**: Prompts user to confirm before deletion with the count of items to be deleted

## Technical Implementation

### Files Modified

1. **`resources/views/panels/admin/network/ipv4-pools.blade.php`**
   - Added select-all checkbox in table header
   - Added individual checkboxes to each table row with `data-bulk-select-item` attribute
   - Integrated bulk actions bar component
   - Added JavaScript for handling bulk operations and UI updates
   - Updated empty state colspan from 7 to 8 to account for new checkbox column

2. **`resources/views/panels/admin/network/pppoe-profiles.blade.php`**
   - Added select-all checkbox in table header
   - Added individual checkboxes to each table row with `data-bulk-select-item` attribute
   - Integrated bulk actions bar component
   - Added JavaScript for handling bulk operations and UI updates
   - Updated empty state colspan from 7 to 8 to account for new checkbox column

3. **`app/Http/Controllers/Panel/AdminController.php`**
   - Added `ipv4PoolsBulkDelete()` method for bulk deletion of IP pools
   - Added `pppoeProfilesBulkDelete()` method for bulk deletion of PPPoE profiles
   - Both methods include validation and error handling

4. **`routes/web.php`**
   - Added route: `POST /panel/admin/network/ipv4-pools/bulk-delete`
   - Added route: `POST /panel/admin/network/pppoe-profiles/bulk-delete`
   - Both routes include `password.confirm` middleware for security

### Files Created

1. **`tests/Feature/BulkDeleteIpPoolsTest.php`**
   - Unit tests for IP pool bulk delete functionality
   - Tests validation and successful deletion

2. **`tests/Feature/BulkDeletePppoeProfilesTest.php`**
   - Unit tests for PPPoE profile bulk delete functionality
   - Tests validation and successful deletion

3. **`database/factories/MikrotikProfileFactory.php`**
   - Factory for creating test PPPoE profiles

4. **`app/Models/MikrotikProfile.php` (Modified)**
   - Added `HasFactory` trait to support testing

## User Interface

### Bulk Actions Bar
The bulk actions bar appears at the top of the table when items are selected and displays:
- Number of selected items
- Dropdown with available actions (currently "Delete")
- "Apply Action" button (disabled when no action is selected)
- "Clear Selection" button to deselect all items

### Select All Behavior
- Clicking the "Select All" checkbox in the header selects/deselects all items on the current page
- The checkbox shows an indeterminate state when some (but not all) items are selected
- Individual checkboxes can be toggled independently

### Delete Confirmation
When the delete action is triggered:
1. User selects one or more items using checkboxes
2. Selects "Delete" from the action dropdown
3. Clicks "Apply Action"
4. A confirmation dialog appears showing the number of items to be deleted
5. Upon confirmation, a POST request is sent to the bulk-delete endpoint
6. User is redirected back with a success message showing the count of deleted items

## Security Features

1. **CSRF Protection**: All bulk delete requests include CSRF token validation
2. **Password Confirmation**: Both bulk delete routes require password confirmation via middleware
3. **Authorization**: The controller methods ensure proper authorization and tenant isolation
4. **Validation**: Input validation ensures:
   - `ids` array is required and contains at least 1 item
   - Each ID must be an integer
   - Each ID must exist in the database

## Error Handling

- Individual deletion errors are logged but don't stop the bulk operation
- If any deletions fail, they are logged with error details
- Success message shows the count of successfully deleted items
- Failed deletions are logged to the application log for debugging

## Dependencies

The implementation uses:
- **Alpine.js**: For reactive UI components (already in use in the application)
- **Bulk Actions Bar Component**: Existing `x-bulk-actions-bar` component
- **Bulk Selection Script**: Existing `resources/js/bulk-selection.js` for checkbox management

## Routes

### IP Pools
```
POST /panel/admin/network/ipv4-pools/bulk-delete
Route Name: panel.admin.network.ipv4-pools.bulk-delete
Middleware: web, auth, password.confirm
Controller: Panel\AdminController@ipv4PoolsBulkDelete
```

### PPPoE Profiles
```
POST /panel/admin/network/pppoe-profiles/bulk-delete
Route Name: panel.admin.network.pppoe-profiles.bulk-delete
Middleware: web, auth, password.confirm
Controller: Panel\AdminController@pppoeProfilesBulkDelete
```

## Testing

Unit tests have been created for both features:
- `tests/Feature/BulkDeleteIpPoolsTest.php`
- `tests/Feature/BulkDeletePppoeProfilesTest.php`

To run the tests:
```bash
php artisan test --filter=BulkDeleteIpPoolsTest
php artisan test --filter=BulkDeletePppoeProfilesTest
```

## Browser Compatibility

The implementation uses standard JavaScript (ES6+) and is compatible with:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Other modern browsers supporting ES6

## Future Enhancements

Possible future improvements:
1. Add "Select All Pages" option for selecting items across pagination
2. Add more bulk actions (e.g., activate, deactivate, export)
3. Add undo functionality for bulk deletions
4. Add progress indicator for large bulk operations
5. Add ability to exclude specific items from selection

## Maintenance Notes

- The bulk delete functionality follows the same pattern as the existing customer bulk operations
- JavaScript code is inline in the blade templates for simplicity and CSP nonce support
- Error logging is implemented for debugging failed deletions
- The implementation maintains consistency with the existing application UI/UX patterns
