# Installation Guide

## 1. Requirements

Before installing, confirm that you have:

- WordPress on every room site
- Booking Calendar installed and configured
- access to `wp-content/mu-plugins/`
- MySQL / MariaDB access from the WordPress database account to every configured room database
- a known database name and table prefix for every room

## 2. Install the main blocker

Copy:

`core/00-bu-gd-final-duplicate-blocker.php`

into each room installation:

`wp-content/mu-plugins/00-bu-gd-final-duplicate-blocker.php`

If `mu-plugins` does not exist, create it first.

MU-plugins are loaded automatically by WordPress. No activation step is required in wp-admin.

## 3. Configure rooms

Open the blocker and edit `bu_gd_xroom_rooms()`.

Example:

```php
function bu_gd_xroom_rooms() {
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
}
```

The array key should match the room URL slug.

## 4. Check database tables

For every configured database, confirm that these tables exist:

```text
<prefix>booking
<prefix>bookingdates
```

For example, if the prefix is `wp_`:

```text
wp_booking
wp_bookingdates
```

## 5. Validate PHP syntax

Before deployment run:

```bash
php -l 00-bu-gd-final-duplicate-blocker.php
```

Expected result:

```text
No syntax errors detected
```

## 6. Optional companion UI

If you want the supplied group-member and purpose UI, copy these files into `wp-content/mu-plugins/` as needed:

- `zz-gd-force-purpose-members-ui.php`
- `zzzz-gd-member-ui-vertical-fix.php`
- `zz-gd-force-custom-ui-slots.php`
- `zz-gd-force-12hour-timeslot-display.php`

These files are optional. The main duplicate blocker can be adapted to another booking form without using the supplied UI.

## 7. Test before production use

Use a staging site if possible.

Test at least:

- valid booking with a new user
- duplicate same slot in the same room
- same email in another room on the same date
- same enrollment in another room on the same date
- same mobile in another room on the same date
- requester/member cross-match
- same person booking on a different date

## 8. Keep versions synchronized

When multiple independent WordPress installations are protected by the same rule, deploy the same blocker version to every room.

Current main blocker version: **2.0.1**
