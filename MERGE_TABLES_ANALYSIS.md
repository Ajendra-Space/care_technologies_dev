# Tables Modified During Contact Merge Operation

## 📊 Overview

When you merge **Contact A** (secondary) into **Contact B** (master), **6 tables** store or update data.

---

## 🔢 Tables That Store Data During Merge

### Summary Table

| # | Table Name | Operation | What Happens |
|---|------------|-----------|--------------|
| 1 | `contacts` | **UPDATE** | Secondary contact status changed to 'merged' |
| 2 | `contact_additional_emails` | **INSERT** | New unique emails from secondary added |
| 3 | `contact_additional_phones` | **INSERT** | New unique phones from secondary added |
| 4 | `contact_custom_fields` | **INSERT** | New custom field values from secondary added |
| 5 | `contact_files` | **UPDATE** | File ownership transferred to master |
| 6 | `contact_merge_history` | **INSERT** | Audit trail record created |

**Total: 6 Tables**

---

## 📝 Detailed Breakdown

### 1. `contacts` Table
**Operation:** UPDATE

**What Gets Updated:**
```sql
UPDATE contacts 
SET 
    status = 'merged',
    merged_into_contact_id = [master_contact_id],
    merge_history = '[JSON snapshot]',
    updated_at = NOW()
WHERE id = [secondary_contact_id]
```

**Fields Modified:**
- `status` → Changed from `'active'` to `'merged'`
- `merged_into_contact_id` → Set to master contact ID
- `merge_history` → Stores complete JSON snapshot of both contacts
- `updated_at` → Timestamp updated

**Example:**
```
Before: Contact A (id=2)
  status = 'active'
  merged_into_contact_id = NULL
  merge_history = NULL

After: Contact A (id=2)
  status = 'merged'
  merged_into_contact_id = 1 (Contact B's ID)
  merge_history = '{"merged_at":"...","master_data":{...},"secondary_data":{...}}'
```

---

### 2. `contact_additional_emails` Table
**Operation:** INSERT (Multiple rows possible)

**What Gets Inserted:**
```sql
INSERT INTO contact_additional_emails 
(contact_id, email, created_at, updated_at)
VALUES 
([master_contact_id], '[unique_email_from_secondary]', NOW(), NOW())
```

**When Rows Are Created:**
- For each unique email in secondary contact that doesn't exist in master
- Excludes duplicate emails
- Excludes secondary's primary email if it matches master's primary email

**Example:**
```
Secondary Contact has:
  - Primary email: john@email.com
  - Additional: john.work@email.com
  - Additional: john.personal@email.com

Master Contact has:
  - Primary email: john@email.com
  - Additional: john.work@email.com

Result: 1 new row inserted
  contact_id = [master_id]
  email = 'john.personal@email.com'
```

---

### 3. `contact_additional_phones` Table
**Operation:** INSERT (Multiple rows possible)

**What Gets Inserted:**
```sql
INSERT INTO contact_additional_phones 
(contact_id, phone, created_at, updated_at)
VALUES 
([master_contact_id], '[unique_phone_from_secondary]', NOW(), NOW())
```

**When Rows Are Created:**
- For each unique phone in secondary contact that doesn't exist in master
- Excludes duplicate phones
- Excludes secondary's primary phone if it matches master's primary phone

**Example:**
```
Secondary Contact has:
  - Primary phone: +1234567890
  - Additional: +0987654321
  - Additional: +1122334455

Master Contact has:
  - Primary phone: +1234567890
  - Additional: +0987654321

Result: 1 new row inserted
  contact_id = [master_id]
  phone = '+1122334455'
```

---

### 4. `contact_custom_fields` Table
**Operation:** INSERT (Multiple rows possible)

**What Gets Inserted:**
```sql
INSERT INTO contact_custom_fields 
(contact_id, custom_field_id, field_value, created_at, updated_at)
VALUES 
([master_contact_id], [custom_field_id], '[field_value]', NOW(), NOW())
```

**When Rows Are Created:**
- Only if master contact doesn't already have that custom field
- Master's existing custom field values take precedence
- Only missing custom fields are added

**Example:**
```
Secondary Contact has:
  - Custom Field: Birthday = '1990-01-01'
  - Custom Field: Address = '123 Main St'
  - Custom Field: Company = 'ABC Corp'

Master Contact has:
  - Custom Field: Birthday = '1990-01-01'
  - Custom Field: Company = 'XYZ Corp'

Result: 1 new row inserted
  contact_id = [master_id]
  custom_field_id = [Address field ID]
  field_value = '123 Main St'
  
Note: Birthday skipped (master already has it)
      Company skipped (master already has it, master's value kept)
```

---

### 5. `contact_files` Table
**Operation:** UPDATE (Multiple rows possible)

**What Gets Updated:**
```sql
UPDATE contact_files 
SET 
    contact_id = [master_contact_id],
    updated_at = NOW()
WHERE contact_id = [secondary_contact_id]
```

**When Rows Are Updated:**
- All files belonging to secondary contact
- File ownership transferred to master contact
- Physical files remain in storage (only database reference changes)

**Example:**
```
Secondary Contact (id=2) has:
  - File 1: document.pdf
  - File 2: image.jpg

After Merge:
  - File 1: contact_id changed from 2 to 1 (master)
  - File 2: contact_id changed from 2 to 1 (master)
```

---

### 6. `contact_merge_history` Table
**Operation:** INSERT (1 row)

**What Gets Inserted:**
```sql
INSERT INTO contact_merge_history 
(master_contact_id, merged_contact_id, merge_details, merged_at, created_at, updated_at)
VALUES 
([master_contact_id], [secondary_contact_id], '[JSON_details]', NOW(), NOW(), NOW())
```

**Purpose:**
- Permanent audit trail
- Complete snapshot of both contacts before merge
- Tracks when merge occurred
- Allows reconstruction of merge operation

**Example:**
```
New Row Created:
  id = [auto_increment]
  master_contact_id = 1 (Contact B)
  merged_contact_id = 2 (Contact A)
  merge_details = '{
    "merged_at": "2024-01-15 10:30:00",
    "master_data": {
      "id": 1,
      "name": "John Doe",
      "email": "john@email.com",
      ...
    },
    "secondary_data": {
      "id": 2,
      "name": "John D.",
      "email": "john@email.com",
      ...
    }
  }'
  merged_at = '2024-01-15 10:30:00'
```

---

## 📊 Visual Representation

```
MERGE OPERATION: Contact A → Contact B

┌─────────────────────────────────────────────────────────┐
│                    DATABASE TABLES                       │
└─────────────────────────────────────────────────────────┘

1. contacts (UPDATE)
   └─ Contact A row updated
      ├─ status = 'merged'
      ├─ merged_into_contact_id = B.id
      └─ merge_history = {snapshot}

2. contact_additional_emails (INSERT)
   └─ New rows created for unique emails from A
      └─ contact_id = B.id

3. contact_additional_phones (INSERT)
   └─ New rows created for unique phones from A
      └─ contact_id = B.id

4. contact_custom_fields (INSERT)
   └─ New rows created for missing custom fields from A
      └─ contact_id = B.id

5. contact_files (UPDATE)
   └─ All files from A updated
      └─ contact_id changed from A.id to B.id

6. contact_merge_history (INSERT)
   └─ New audit record created
      ├─ master_contact_id = B.id
      ├─ merged_contact_id = A.id
      └─ merge_details = {complete snapshot}
```

---

## 🔢 Row Count Examples

### Scenario 1: Simple Merge
**Contact A has:**
- 1 additional email
- 1 additional phone
- 2 custom fields
- 1 file

**Result:**
- `contacts`: 1 UPDATE
- `contact_additional_emails`: 1 INSERT
- `contact_additional_phones`: 1 INSERT
- `contact_custom_fields`: 2 INSERTs (if master doesn't have them)
- `contact_files`: 1 UPDATE
- `contact_merge_history`: 1 INSERT

**Total: 1 UPDATE + 5 INSERTs = 6 operations**

---

### Scenario 2: Complex Merge
**Contact A has:**
- 3 additional emails
- 2 additional phones
- 5 custom fields
- 4 files

**Result:**
- `contacts`: 1 UPDATE
- `contact_additional_emails`: 3 INSERTs (if all unique)
- `contact_additional_phones`: 2 INSERTs (if all unique)
- `contact_custom_fields`: Up to 5 INSERTs (only missing ones)
- `contact_files`: 4 UPDATEs
- `contact_merge_history`: 1 INSERT

**Total: 5 UPDATEs + 11 INSERTs = 16 operations**

---

## ⚠️ Important Notes

### Tables That Are NOT Modified:
- ❌ `custom_fields` - Configuration table, not modified
- ❌ Master contact's main record in `contacts` - Only secondary is updated
- ❌ Master contact's existing emails/phones/custom fields - Only additions made

### Data Preservation:
- ✅ Secondary contact is NOT deleted (status changed to 'merged')
- ✅ All data is preserved in `merge_history`
- ✅ Complete audit trail maintained
- ✅ Can reconstruct original state from history

### Transaction Safety:
- All 6 table operations happen in **ONE database transaction**
- If ANY operation fails, ALL changes are rolled back
- Ensures data consistency

---

## 📋 Summary

**When merging Contact A into Contact B:**

| Table | Operations | Purpose |
|-------|-----------|---------|
| `contacts` | 1 UPDATE | Mark secondary as merged |
| `contact_additional_emails` | 0-N INSERTs | Add unique emails |
| `contact_additional_phones` | 0-N INSERTs | Add unique phones |
| `contact_custom_fields` | 0-N INSERTs | Add missing custom fields |
| `contact_files` | 0-N UPDATEs | Transfer file ownership |
| `contact_merge_history` | 1 INSERT | Create audit trail |

**Total Tables Modified: 6**

**Total Operations:**
- Minimum: 3 operations (1 UPDATE + 1 INSERT + 1 INSERT)
- Maximum: Depends on data in secondary contact
- Typical: 5-10 operations per merge

