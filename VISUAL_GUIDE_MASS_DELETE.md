# Visual Guide: Mass Delete Feature

## IP Pools Page - Before Selection

```
┌─────────────────────────────────────────────────────────────────────────┐
│  IPv4 Pool Management                                     [+ Add IP Pool]│
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ [☐]  Pool Name    Network        Gateway     Total IPs  Usage  Status   │
├─────────────────────────────────────────────────────────────────────────┤
│ [☐]  Pool-1       10.0.0.0/24    10.0.0.1    254        50%    Active   │
│ [☐]  Pool-2       10.0.1.0/24    10.0.1.1    254        30%    Active   │
│ [☐]  Pool-3       10.0.2.0/24    10.0.2.1    254        80%    Active   │
└─────────────────────────────────────────────────────────────────────────┘
```

## IP Pools Page - After Selection

```
┌─────────────────────────────────────────────────────────────────────────┐
│  IPv4 Pool Management                                     [+ Add IP Pool]│
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ 2 selected  [Delete ▼]  [Apply Action]  [Clear Selection]               │
└─────────────────────────────────────────────────────────────────────────┘
         ↑ Bulk Actions Bar (appears when items selected)

┌─────────────────────────────────────────────────────────────────────────┐
│ [▣]  Pool Name    Network        Gateway     Total IPs  Usage  Status   │
│      (Indeterminate state - some selected)                              │
├─────────────────────────────────────────────────────────────────────────┤
│ [☑]  Pool-1       10.0.0.0/24    10.0.0.1    254        50%    Active   │
│ [☑]  Pool-2       10.0.1.0/24    10.0.1.1    254        30%    Active   │
│ [☐]  Pool-3       10.0.2.0/24    10.0.2.1    254        80%    Active   │
└─────────────────────────────────────────────────────────────────────────┘
```

## PPPoE Profiles Page - Select All

```
┌─────────────────────────────────────────────────────────────────────────┐
│  PPPoE Profiles                                        [+ Add Profile]   │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ 3 selected  [Delete ▼]  [Apply Action]  [Clear Selection]               │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ [☑]  Profile Name    Router      IPv4 Pool   IPv6 Pool  Users  Status   │
│      (All selected)                                                     │
├─────────────────────────────────────────────────────────────────────────┤
│ [☑]  Default         Router-1    Pool-1      -          10     Active   │
│ [☑]  Premium         Router-1    Pool-2      -          5      Active   │
│ [☑]  Business        Router-2    Pool-3      -          8      Active   │
└─────────────────────────────────────────────────────────────────────────┘
```

## Deletion Flow

### Step 1: Selection
```
User checks boxes → Bulk actions bar appears → Count updates dynamically
```

### Step 2: Action Selection
```
User selects "Delete" from dropdown → "Apply Action" button becomes active
```

### Step 3: Confirmation Dialog
```
┌─────────────────────────────────────────────────┐
│  Confirm Deletion                               │
├─────────────────────────────────────────────────┤
│                                                 │
│  Are you sure you want to delete 2 IP pool(s)? │
│  This action cannot be undone.                  │
│                                                 │
│         [Cancel]          [Confirm Delete]      │
└─────────────────────────────────────────────────┘
```

### Step 4: Password Confirmation (Middleware)
```
┌─────────────────────────────────────────────────┐
│  Confirm Password                               │
├─────────────────────────────────────────────────┤
│                                                 │
│  Please enter your password to continue:        │
│                                                 │
│  Password: [********************]               │
│                                                 │
│         [Cancel]          [Confirm]             │
└─────────────────────────────────────────────────┘
```

### Step 5: Success Message
```
┌─────────────────────────────────────────────────────────────────────────┐
│  ✓ Success: 2 IP pool(s) deleted successfully.                          │
└─────────────────────────────────────────────────────────────────────────┘
```

## Error States

### No Selection Error
```
┌─────────────────────────────────────────────────────────────────────────┐
│ 0 selected  [Delete ▼]  [Apply Action]  ⚠ Please select at least one... │
└─────────────────────────────────────────────────────────────────────────┘
              ↑ Inline error message (auto-disappears after 3 seconds)
```

### Deletion Failed Error
```
┌─────────────────────────────────────────────────────────────────────────┐
│  ✗ Error: Failed to delete IP pools. Please try again.                  │
└─────────────────────────────────────────────────────────────────────────┘
```

## Key Features Illustrated

✅ **Select All Checkbox**
   - Empty [☐] - Nothing selected
   - Indeterminate [▣] - Some selected
   - Checked [☑] - All selected

✅ **Dynamic Bulk Actions Bar**
   - Hidden by default
   - Shows when any item is selected
   - Displays count of selected items

✅ **Inline Validation**
   - Error messages appear in the bulk actions bar
   - No intrusive browser alerts
   - Auto-disappear after 3 seconds

✅ **Clear User Feedback**
   - Confirmation dialogs with item counts
   - Success/error flash messages
   - Password confirmation for security

✅ **Responsive Actions**
   - Checkboxes update in real-time
   - Indeterminate state for partial selection
   - Clear selection option available
