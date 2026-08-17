# Group Discussion (GD) Booking Control System

WordPress MU-plugin set for managing booking-control and fair-use rules across multiple Group Discussion (GD) / meeting rooms that use separate WordPress installations and Booking Calendar databases.

The main plugin prevents duplicate participation across rooms by checking the booking date, time slot, requester identity, and group-member identities before a booking is accepted.

## Main plugin

`core/00-bu-gd-final-duplicate-blocker.php`

Current version: **2.0.1**

### What it blocks

- Same room + same date + same start time slot
- Same email address on the same date across configured rooms
- Same enrollment number on the same date across configured rooms
- Same Indian mobile number on the same date across configured rooms
- A requester booking another room on the same date
- A requester appearing as a member in another booking on the same date
- A group member later becoming a requester on the same date
- A group member appearing in another group on the same date

The practical rule is simple:

> One person can participate in only one configured GD / meeting-room booking per day, whether that person is the requester or a group member.

## How it works

The blocker runs very early on WordPress `init` for POST requests that look like booking submissions.

It reads the submitted booking form and extracts:

- booking date
- start time
- email addresses
- enrollment numbers
- mobile numbers

It then checks every configured room database for non-trashed bookings on the selected date.

If a conflict is found, WordPress stops the submission and returns a user-friendly **Booking Not Allowed** page with HTTP status `409`.

If no conflict is found, the normal Booking Calendar booking process continues.

## Requirements

- WordPress
- Booking Calendar plugin using `booking` and `bookingdates` tables
- PHP with WordPress `wpdb`
- Multiple room databases accessible from the same MySQL / MariaDB server or otherwise reachable through the configured WordPress DB credentials
- The database user used by WordPress must have read access to every room database that the blocker checks
- Each room should have a stable URL slug so the plugin can identify the current room

This repository contains the custom code only. WordPress core and third-party plugins are not included.

## Installation

The blocker is designed as a WordPress **Must-Use Plugin**.

For every room WordPress installation:

1. Create the MU-plugin directory if it does not exist:

   `wp-content/mu-plugins/`

2. Copy:

   `00-bu-gd-final-duplicate-blocker.php`

   into:

   `wp-content/mu-plugins/`

3. Edit the room configuration in `bu_gd_xroom_rooms()` before deployment.

4. Verify that the configured database names and table prefixes match your installations.

5. Confirm all room WordPress sites use the expected timezone.

6. Test duplicate bookings in a staging environment before enabling it on production.

MU-plugins load automatically; there is no WordPress activation button.

## Room configuration

The room map is defined inside:

`bu_gd_xroom_rooms()`

Example:

```php
return array(
    'room1' => array(
        'label'  => 'Room 1',
        'db'     => 'room1_database',
        'prefix' => 'wp_'
    ),
    'room2' => array(
        'label'  => 'Room 2',
        'db'     => 'room2_database',
        'prefix' => 'wp_'
    ),
);
```

For each room set:

- **array key / slug**: URL slug used by that WordPress room installation
- **label**: name shown in duplicate-block messages
- **db**: WordPress database name for that room
- **prefix**: database table prefix used by Booking Calendar tables

The plugin expects these tables for every configured room:

```text
<prefix>booking
<prefix>bookingdates
```

## Database connection behavior

Cross-room connections are created with WordPress constants:

```php
new wpdb(DB_USER, DB_PASSWORD, $db_name, DB_HOST)
```

The plugin does not contain a separate database password.

This means the current WordPress database account must be allowed to read the other configured room databases.

## Identity matching

### Email

Email addresses are extracted from the complete submitted POST payload and normalized to lowercase.

### Enrollment number

Enrollment-like values are extracted with a configurable regular-expression pattern and normalized to uppercase.

If your institution uses a different enrollment format, update `bu_gd_xroom_enrollments()`.

### Mobile number

Version 2.0.1 normalizes common Indian mobile formats to the final 10-digit number. Public documentation intentionally uses masked examples rather than real-looking phone numbers:

```text
98XXXXXXXX
098XXXXXXXX
9198XXXXXXXX
+91 98XXX XXXXX
```

Only valid-looking Indian mobile numbers beginning with `6`, `7`, `8`, or `9` are retained.

For another country or numbering plan, update `bu_gd_xroom_phones()`.

## Date and time matching

The plugin accepts common date forms such as:

```text
YYYY-MM-DD
YYYY/MM/DD
DD-MM-YYYY
DD/MM/YYYY
```

It understands both 24-hour and 12-hour time ranges when extracting the submitted slot.

Same-slot blocking compares the submitted start time with the Booking Calendar `booking_date` time for the current room.

## Booking records considered active

During cross-room checks, the plugin ignores records whose Booking Calendar booking row is marked as trashed.

It currently checks non-trashed bookings regardless of approval status. If your workflow should only block against approved bookings, adapt the SQL condition to your Booking Calendar workflow before deployment.

## Group Members UI

Optional companion file:

`core/zz-gd-force-purpose-members-ui.php`

It replaces the old remarks/details area with:

- Purpose / Need
- Group Members section
- Member Name
- Member Email
- Member Enrollment No.
- Add / Remove member controls

The supplied UI supports up to **5 additional members**, giving a maximum group size of **6 including the requester**.

Because member email and enrollment values are written into the booking form data, the main duplicate blocker can include those members in the cross-room daily check.

## Other companion files

The `core/` directory also contains optional presentation and usability components used with the deployed booking form:

- `zz-gd-force-purpose-members-ui.php` — Purpose / Need and group-member fields
- `zzzz-gd-member-ui-vertical-fix.php` — layout adjustments for member fields
- `zz-gd-force-custom-ui-slots.php` — custom slot display / form behavior
- `zz-gd-force-12hour-timeslot-display.php` — 12-hour time display handling

Use only the components required by your own Booking Calendar form and theme.

## Repository structure

```text
core/
    Main reusable blocker and companion UI components

docs/
    Architecture and setup documentation

production-snapshot/
    Sanitized examples of the custom MU-plugin layout used across multiple rooms

SHA256SUMS.txt
    Checksums for the original sanitized source snapshot
```

## Recommended deployment method

Before changing any production file:

1. Back up the existing plugin.
2. Make changes in a temporary copy.
3. Run PHP syntax validation:

   `php -l filename.php`

4. Copy the tested file into `wp-content/mu-plugins/`.
5. Verify one valid booking and each duplicate-block scenario.
6. Keep the same blocker version across all configured room installations.

## Recommended test cases

At minimum test these scenarios:

1. Same room, same date, same slot twice → second attempt blocked.
2. Same email, different room, same date → blocked.
3. Same enrollment, different room, same date → blocked.
4. Same mobile, different room, same date → blocked.
5. Requester in one booking, member in another booking, same date → blocked.
6. Member in one booking, requester in another booking, same date → blocked.
7. Same member in two different groups, same date → blocked.
8. Same user on a different date → allowed if no other rule conflicts.
9. Different users on the same date in different available rooms → allowed.

## Important limitations

- The current implementation scans the complete POST payload for email, enrollment, phone, date, and time patterns. Highly customized forms may require field-specific parsing.
- Enrollment-number matching depends on the regular expression in the plugin.
- Mobile normalization is currently designed for Indian numbers.
- The blocker performs application-level checks and does not add a database unique constraint. Two truly simultaneous requests can theoretically create a race condition.
- Database and Booking Calendar schema changes may require updates to the SQL queries.
- Always test after upgrading Booking Calendar, WordPress, PHP, or changing booking form fields.

## Security and privacy

Do not commit or publish:

- `wp-config.php`
- database dumps
- SMTP passwords
- API keys
- raw booking data
- user names, emails, enrollment numbers, or phone numbers from production bookings

The plugin itself uses WordPress database constants and does not need hard-coded production credentials.

## Troubleshooting

### A duplicate is not being blocked

Check:

- room slug in `bu_gd_xroom_rooms()`
- database name
- table prefix
- database read permissions
- booking form POST field/value format
- enrollment regex
- phone-number format
- date and time format

### A valid booking is being blocked

Check whether an old non-trashed booking already contains the same email, enrollment number, or phone number for the selected date.

Also review whether your workflow needs approval-status filtering.

### Cross-room checks are skipped

The plugin continues when it cannot open a configured database or cannot find the expected Booking Calendar tables. Confirm the database mapping and MySQL permissions.

## Version

Current blocker version: **2.0.1**

Main file:

`core/00-bu-gd-final-duplicate-blocker.php`
