[README](/README.md)

# Options

## SSE client options

**reconnect** *bool*\
*Default: true* - Whether to automatically reconnect to the SSE stream after the connection was dropped.\
\
**min_wait_for_reconnect** *int*\
*Default: 100* - Minimum time (in milliseconds) to wait before reconnecting, even if the server demands a delay lower than that (or none at all).\
\
**max_wait_for_reconnect** *int*\
*Default: 30000* - Maximum time (in milliseconds) to wait before reconnecting, even if the server demands a delay higher than that.\
\
**respect_204** *bool*\
*Default: true* - If set to true, there will be no automatic reconnects if the server responds with status code 204.\
\
**use_last_event_id** *bool*\
*Default: true* - Whether to send the `Last-Event-ID` header when (re-)connecting to the stream. Will fall back to false if no event id has been received before the (re-)connect.\
\
**always_return_last_event_id** *bool*\
*Default: true* - If set to true, any event will have the latest event id received, even if the current event has no id associated.\
\
**read_timeout** *int*\
*Default: 0* - Sets the read timeout (in seconds) after which `null` is yielded by the generator to the calling loop if no event has been received. `0` disables any read timeouts which may lead to infinite blocking if the server neither sends new events nor closes the connection. The occurrence of a read timeout will not close the connection but resume normal operation after `null` has been yielded. `abort()` can be called on the SseClient object from within the loop however to stop the stream.\
\
**associative** *bool*\
*Default: false* - If set to `true` an associative array will be yielded instead of an event object.\
\
**concatenate_data** *bool*\
*Default: true* - The standard suggests that multiple `data` messages (within a single event) must be concatenated to a single string with a single LF as the glue. By setting this option to `false`, `data` is going to be an array of data messages instead.\
\
**ignore_comments** *bool*\
*Default: false* - If set to `true` no comments (sent by the server) will be included in the object or array yielded.\


## HTTP client options

All [Guzzle request options](https://docs.guzzlephp.org/en/stable/request-options.html) may be used, with the exeption of:

- `decode_content` (Always `true`)
- `headers`=>`Accept` (Always `text/event-stream`)
- `headers`=>`Cache-Control` (Always `no-cache`)
- `read_timeout` (Set by SSE client options)
- `sink` (SSE client needs the stream)
- `stream` (Always `true`)
- `synchronous` (Not sensible for a streaming resource)
