# Plugin Architecture

The plugin is designed for environments where multiple GD / meeting rooms are managed by separate WordPress installations but should follow one common fair-use rule.

## Main components

### 1. Booking form

Each room has its own WordPress booking form, normally powered by Booking Calendar.

The form can contain requester details such as:

- email
- enrollment number
- mobile number
- booking date
- booking time slot

Optional companion UI can also add group-member name, email, and enrollment fields.

### 2. Cross-room duplicate blocker

Main file:

`core/00-bu-gd-final-duplicate-blocker.php`

The blocker loads as an MU-plugin and runs on booking POST requests.

It extracts identity and booking values from the submitted payload, then checks all configured room databases.

### 3. Room configuration map

All rooms are defined in `bu_gd_xroom_rooms()`.

Each entry contains:

- room slug
- display label
- database name
- table prefix

The current WordPress DB credentials are reused to create cross-database `wpdb` connections.

### 4. Booking Calendar tables

The blocker expects two tables in each configured database:

```text
<prefix>booking
<prefix>bookingdates
```

The tables are joined on `booking_id`.

Only non-trashed booking rows are considered during duplicate checks.

## Request flow

```text
Booking POST request
        |
        v
Detect booking submission
        |
        v
Extract date + start time
        |
        +--> extract all email identities
        +--> extract all enrollment identities
        +--> normalize Indian mobile identities
        |
        v
Loop through configured room databases
        |
        +--> expected Booking Calendar tables available?
        |       |
        |       +-- no --> skip that room
        |       +-- yes
        |
        v
Load non-trashed bookings for selected date
        |
        +--> current room + same start slot?
        +--> same email on date?
        +--> same enrollment on date?
        +--> same mobile on date?
        |
        +-- conflict --> HTTP 409 / Booking Not Allowed
        |
        +-- no conflict --> normal Booking Calendar flow continues
```

## Group-member handling

The optional member UI writes group-member email and enrollment values into the booking details payload.

Because the duplicate blocker extracts all matching identities from the submitted payload, requester and group-member identities are checked using the same cross-room rule.

This supports:

- requester → requester blocking
- requester → member blocking
- member → requester blocking
- member → member blocking

## Current matching behavior

### Email

Case-insensitive normalized email matching.

### Enrollment

Regex-based extraction followed by uppercase normalization.

### Mobile

Indian mobile formats are normalized to a 10-digit identity.

### Slot

Same-slot checking is applied to the current room by comparing the submitted start time with `bookingdates.booking_date`.

## Failure behavior

If a configured database cannot be opened or the expected Booking Calendar tables do not exist, that room is skipped rather than causing the whole WordPress request to fail.

If a duplicate is found, the plugin stops the booking with `wp_die()` and HTTP status `409`.

## Important implementation note

The duplicate protection is implemented at application level. It does not create database-level unique constraints, so extremely close simultaneous requests can theoretically create a race condition.
