# Troubleshooting

## Duplicate booking is not blocked

Check these items in order:

1. The room slug matches the actual room URL.
2. The configured database name is correct.
3. The table prefix is correct.
4. The WordPress DB user can read the other room databases.
5. The booking tables exist.
6. The booking request contains a recognizable date and time.
7. Email / enrollment / phone values are present in the POST payload.
8. Your enrollment format matches the regex used by `bu_gd_xroom_enrollments()`.
9. Your mobile format matches `bu_gd_xroom_phones()`.

## Valid booking is blocked

The plugin checks any non-trashed booking on the selected date.

Look for an earlier booking containing the same:

- email
- enrollment number
- mobile number

Also remember that group-member email and enrollment values are part of the daily identity check.

If your site keeps rejected or unapproved bookings as non-trashed records, decide whether the SQL should also filter by booking approval/status.

## Current room slot is not detected correctly

The current room is identified from the request URI or WordPress home URL using the configured room slug.

Verify that the slug in `bu_gd_xroom_rooms()` exactly matches the room path.

## Cross-room check silently skips a room

The plugin is intentionally fail-soft for unavailable databases and missing tables.

A room can be skipped when:

- the database connection fails
- the expected booking table does not exist
- the expected bookingdates table does not exist

Check database permissions and mapping.

## Phone duplicate is not detected

The supplied parser is designed for Indian mobile numbers.

Supported examples include:

```text
9876543210
09876543210
91 9876543210
+91 98765 43210
```

The normalized number must begin with 6, 7, 8, or 9.

## Enrollment duplicate is not detected

Enrollment extraction is regex-based. If your institution uses a different format, update `bu_gd_xroom_enrollments()` and test against real format examples without committing user data to the repository.

## Booking form changed after a plugin/theme update

The blocker extracts identities from the complete POST request. The companion UI files also depend on frontend form structure and DOM labels.

After any Booking Calendar, WordPress, PHP, or theme upgrade, retest:

- booking submission
- date extraction
- slot extraction
- member UI
- duplicate blocks

## PHP error after editing

Run syntax validation before deploying:

```bash
php -l 00-bu-gd-final-duplicate-blocker.php
```

Do not replace a working production file until the edited copy passes syntax validation.

## Race condition

The plugin performs an application-level read/check before Booking Calendar writes the new booking.

Two requests submitted at almost the exact same time can theoretically both pass before either booking is stored. If strict transactional uniqueness is required, add an appropriate database-level or centralized locking design for your environment.
