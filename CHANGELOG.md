# Changelog

All notable changes to this package will be documented in this file.

## 1.1.0 - 2026-03-09

- **Breaking:** Replaced database storage with flat-file JSON storage — no database or migration required
- Removed Eloquent model and migration in favor of `SubmissionStore` using `storage/statamic-indexnow/submissions.json`
- Compatible with flat-file Statamic sites that don't use a database

## 1.0.1 - 2026-03-09

- Improved filter bar layout with proper select dropdown styling
- Removed URL column from table to prevent overflow; URL now shown as tooltip on title hover
- Shortened column headers (Modified, Submitted) for better fit

## 1.0.0 - 2026-03-09

- Initial release
- CP Utility for browsing and submitting published entries to IndexNow
- Submission history tracking with status indicators (never/modified/submitted)
- Auto-submit on publish (configurable)
- Collection filtering and title search
- Sortable table columns
- Bulk selection actions (select unsubmitted / select modified)
- Configurable collection exclusions
- Automatic batch chunking for large submissions
- `indexnow:prune` artisan command for cleaning up old records
