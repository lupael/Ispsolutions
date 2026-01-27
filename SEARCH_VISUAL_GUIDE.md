# Visual Guide: Global Search Feature

## Feature Location

### 1. Sidebar Search Input
```
┌─────────────────────────────────┐
│  ISP Solution              [X]  │ ← Logo
├─────────────────────────────────┤
│  🔍 Search customers, invoices  │ ← NEW SEARCH INPUT
├─────────────────────────────────┤
│  🏠 Dashboard                   │
│  📦 Packages                    │
│  👥 Customers                   │
│  📡 Network Devices             │
│  ...                            │
└─────────────────────────────────┘
```

**Location**: Right below the logo in the sidebar
**Accessible to**: All authenticated users
**Action**: Submits to `/panel/search` on Enter key

## Search Results Page

### Layout
```
┌─────────────────────────────────────────────────────────────┐
│  Search Results                                             │
│  Results for: "john"                                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  [🔍 Search input box                          ] [Search]  │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  👥 Customers (3)                                           │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Name    Email         Username  Package  Status    │   │
│  │ John D  john@ex.com   johnd     Premium  🟢 Active │   │
│  │ Johnny  johnny@...    johnny    Basic    🟢 Active │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  📄 Invoices (2)                                            │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Invoice#  Customer   Package   Amount   Status     │   │
│  │ INV-001   John Doe   Premium   $99.00   🟢 Paid    │   │
│  │ INV-005   Johnny A   Basic     $49.00   🟡 Pending │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

### Empty State
```
┌─────────────────────────────────────────────────────────────┐
│  Search Results                                             │
│  Results for: "xyz"                                         │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│                      🔍                                     │
│              No Results Found                              │
│     Try adjusting your search terms or                     │
│     search for different criteria.                         │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## Search Functionality

### What You Can Search For

#### Customer Search
- ✅ Customer Name (e.g., "John Doe")
- ✅ Email Address (e.g., "john@example.com")
- ✅ Username (e.g., "johnd")

#### Invoice Search
- ✅ Invoice Number (e.g., "INV-001")
- ✅ Customer Name (from invoice)
- ✅ Customer Email (from invoice)

### Search Behavior
- **Case Insensitive**: Searches "john" and "JOHN" give same results
- **Partial Match**: Searching "john" finds "Johnny", "Johnson", etc.
- **Multiple Fields**: Single query searches all relevant fields
- **Safe**: Special characters are escaped to prevent SQL injection

## Customer Results Display

### Columns Shown
```
┌───────────────────────────────────────────────────────────────────┐
│ Name      │ Email        │ Username │ Package │ Status │
├───────────────────────────────────────────────────────────────────┤
│ John Doe  │ john@ex.com  │ johnd    │ Premium │ Active │
│ Jane S.   │ jane@ex.com  │ janes    │ Basic   │ Active │
│ Bob W.    │ bob@ex.com   │ bobw     │ No Pkg  │ Inactive│
└───────────────────────────────────────────────────────────────────┘
```

### Additional Info (for Developer only)
```
┌────────────────────────────────────────────────────────────────────┐
│ Name     │ Email      │ ... │ Status  │ Tenant        │ Actions   │
├────────────────────────────────────────────────────────────────────┤
│ John Doe │ john@...   │ ... │ Active  │ ISP Corp      │ 👁️ View    │
│ Jane S.  │ jane@...   │ ... │ Active  │ NetService Co │ 👁️ View    │
└────────────────────────────────────────────────────────────────────┘
```

## Invoice Results Display

### Columns Shown
```
┌─────────────────────────────────────────────────────────────────┐
│ Invoice # │ Customer  │ Email           │ Amount  │ Due Date │ Status │
├─────────────────────────────────────────────────────────────────┤
│ INV-001   │ John Doe  │ john@ex.com     │ $99.00  │ 2024-01  │ 🟢 Paid │
├─────────────────────────────────────────────────────────────────┤
│ INV-002   │ Jane S.   │ jane@ex.com     │ $149.00 │ 2024-02  │🟡Pending│
└─────────────────────────────────────────────────────────────────┘
```

### Status Colors
- 🟢 **Paid**: Green badge
- 🟡 **Pending**: Yellow badge
- 🔴 **Overdue**: Red badge
- ⚪ **Cancelled**: Gray badge
- 🔵 **Draft**: Blue badge

## Permission-Based Results

### Example: Admin User (Level 20)
**Sees**: All customers and invoices in their tenant only
```
Search Results for: "customer"
👥 Customers (15) ← All customers in admin's tenant
📄 Invoices (45)  ← All invoices in admin's tenant
```

### Example: Operator User (Level 30)
**Sees**: Only customers and invoices they created
```
Search Results for: "customer"
👥 Customers (5)  ← Only customers created by this operator
📄 Invoices (12)  ← Only invoices created by this operator
```

### Example: Customer User (Level 100)
**Sees**: Only their own invoices, no customer search
```
Search Results for: "INV"
👥 Customers (0)  ← No access to customer search
📄 Invoices (3)   ← Only their own invoices
```

## View Links Behavior

### Customer View Links
- **Developer**: ✅ View available
- **Super Admin/Admin/Manager/Accountant**: ✅ View available (tenant-scoped)
- **Operator/Sub-Operator/Staff**: ❌ N/A (no view route)
- **Customer**: ❌ N/A (no access)

### Invoice View Links
- **Developer/Super Admin/Admin/Manager/Accountant**: ✅ View invoice details
- **Customer**: ✅ View own invoice only
- **Operator/Sub-Operator/Staff**: ❌ N/A (no view route)

## UI Components

### Search Input (Sidebar)
```html
<form action="/panel/search" method="GET">
    <input 
        type="text" 
        name="query" 
        placeholder="Search customers, invoices..."
        class="search-input"
    />
</form>
```

### Status Badges
- **Active Customer**: Green badge with "Active" text
- **Inactive Customer**: Red badge with "Inactive" text
- **Invoice Statuses**: Color-coded badges (see above)

### Empty States
- When no results found for query
- Shows helpful message and search icon
- Suggests trying different search terms

## Key Features Summary

✅ **Sidebar Integration**: Search input always visible  
✅ **Comprehensive Search**: Multiple fields searched simultaneously  
✅ **Permission-Based**: Results filtered by user role  
✅ **Secure**: SQL injection protected  
✅ **Fast**: Optimized queries, limited results  
✅ **User-Friendly**: Clear results display, status badges  
✅ **Responsive**: Works on all screen sizes  
✅ **Well-Documented**: Complete documentation available  

## Testing the Feature

### Test Scenarios
1. **Search by Name**: Enter "John" → Should find all Johns
2. **Search by Email**: Enter "john@" → Should find matching emails
3. **Search by Username**: Enter "johnd" → Should find matching usernames
4. **Search by Invoice**: Enter "INV" → Should find all invoices
5. **Partial Match**: Enter "Jo" → Should find John, Joe, Johnson, etc.
6. **No Results**: Enter "xyz123" → Should show empty state
7. **Permission Test**: Different roles see different results

### Expected Behavior
- Results appear within seconds
- Correct permission filtering applied
- View links work for authorized users
- No errors or SQL injection vulnerabilities
