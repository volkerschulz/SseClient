# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased] - yyyy-mm-dd

### Added

### Changed

### Fixed

## [0.9.2] - 2025-02-09
 
### Added
- Change log
- Event objects
- Option `associative` can optionally be set to `false` to be yielded an event object instead of an associative array
- Option `concatenate_data` can optionally be set to `false` to get an array instead of a string of data
- Option `always_return_last_event_id` can optionally be set to `false` to not include a previously received event id for new events without an id
- `line_delimiter` is now an option (defaults to `"/\r\n|\n|\r/"`)
- `message_delimiter` is now an option (defaults to `"/\r\n\r\n|\n\n|\r\r/"`)
 
### Changed
- Now requires PHP >= 8.1 (instead of >= 8.0)
- Default setting for `reconnect` is now `false`
- Default implicit event type is now `message`
 
### Fixed
 
## [0.9.1] - 2025-02-05
 
### Added

- Additional http request methods
- Http-client options
 
### Changed

 
### Fixed

