# Changelog

## 2.0.1

- Improved Indian mobile-number normalization.
- Handles 10-digit mobile numbers, leading-zero formats, `91` country-code formats, and `+91` formatted input.
- Keeps cross-room duplicate checks for email, enrollment number, mobile number, and same-room slot conflicts.
- Compatible with requester and group-member identity checking when member details are present in the booking payload.

## 2.0.0

- Added cross-room daily fair-use checking across configured room databases.
- Added requester/member identity matching across rooms.
- Added email, enrollment, and mobile duplicate detection on the selected date.
- Added same-room same-start-slot protection.

## Notes

The core blocker version is defined in:

`core/00-bu-gd-final-duplicate-blocker.php`

When the plugin is deployed to multiple room WordPress installations, keep the same blocker version on every room unless you intentionally maintain different configurations.
