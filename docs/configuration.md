# Configuration Guide

## Room map

The core configuration is returned by `bu_gd_xroom_rooms()` in:

`core/00-bu-gd-final-duplicate-blocker.php`

Each room entry has four important values:

```php
'room-slug' => array(
    'label'  => 'Room Name',
    'db'     => 'database_name',
    'prefix' => 'wp_'
)
```

### Slug

The array key is used to identify the current room from the request URL or WordPress home URL.

Example:

```text
https://example.org/room1/
```

Use:

```php
'room1' => array(...)
```

### Label

The label appears in the duplicate-block message shown to the user.

### Database name

Set the actual WordPress database containing the Booking Calendar data for that room.

### Prefix

Set the database prefix used before `booking` and `bookingdates`.

## Database permissions

The plugin opens each configured database using:

```php
new wpdb(DB_USER, DB_PASSWORD, $db_name, DB_HOST)
```

Therefore the current WordPress database user must be able to read all configured room databases.

Do not hard-code database passwords into the plugin.

## Enrollment pattern

Enrollment IDs are extracted in `bu_gd_xroom_enrollments()`.

The supplied regular expression is intended for institution-style alphanumeric enrollment values.

If your enrollment IDs follow another structure, modify only that function and test with both valid and invalid examples.

## Phone normalization

`bu_gd_xroom_phones()` currently recognizes common Indian mobile formats and normalizes them to 10 digits.

For another country, replace the phone parser with rules that match your numbering plan.

## Date parser

`bu_gd_xroom_date()` recognizes:

- `YYYY-MM-DD`
- `YYYY/MM/DD`
- `DD-MM-YYYY`
- `DD/MM/YYYY`

If no date is found in the POST payload, it falls back to the current WordPress date.

## Slot parser

`bu_gd_xroom_slot()` recognizes common 24-hour and 12-hour ranges.

Examples:

```text
13:00 - 15:00
1:00 pm - 3:00 pm
```

Only the start time is used for same-room slot collision checking.

## Booking status behavior

The current SQL checks non-trashed bookings for the selected date.

It does not currently limit duplicate checks to approved bookings only.

If your workflow requires only approved reservations to block a user, update the SQL condition according to your Booking Calendar schema and approval process.

## Group-member fields

The optional member UI supports up to five additional members.

For each member it collects:

- name
- email
- enrollment number

The blocker uses the email and enrollment identities for duplicate checking.

## Error response

When a conflict is detected, `bu_gd_xroom_block()` displays a WordPress error page and returns HTTP status `409`.

You can customize the message text and styling in that function without changing the duplicate-detection logic.
