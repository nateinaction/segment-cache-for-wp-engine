# Segment Cache for WP Engine
[![Build Status](https://travis-ci.org/nateinaction/segment-cache-for-wp-engine.svg?branch=master)](https://travis-ci.org/nateinaction/segment-cache-for-wp-engine) [![Maintainability](https://api.codeclimate.com/v1/badges/5bb317ca377fec035d23/maintainability)](https://codeclimate.com/github/nateinaction/segment-cache-for-wp-engine/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/5bb317ca377fec035d23/test_coverage)](https://codeclimate.com/github/nateinaction/segment-cache-for-wp-engine/test_coverage)

A WordPress plugin to enable segmented content caching via shortcode

## About

Help your site convert visitors into subscribers by serving content specific to their needs. And do it quickly by using WP Engine's built-in caching system.

## How to use

1. When you know a visitor is a specific type of user (i.e. developer, marketer, enterprise, etc.) use the `segment-cache-set` shortcode remember who they are.

    ```text
    [segment-cache-set segment-name="developer"]
    ```

2. Serve content specific to that type of user by using the `segment-cache-display` shortcode.

    ```text
    [segment-cache-display segment-name="developer"]
        This content is for developers only.
    [/segment-cache-display]
    ```

    ```text
    [segment-cache-display segment-name="marketer"]
        This content is for marketers only.
    [/segment-cache-display]
    ```

    ```text
    [segment-cache-display]
        This content is for non-segmented visitors only.
    [/segment-cache-display]
    ```

## Additional options

### `segment-cache-set`

These options mirror those found in the [Mozilla docs](https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie) for `document.cookie`.

- `segment-name`: [string] Name of segment. (required)
- `path`: [string] Path on the site where the segment will be available. (default: "/")
- `domain`: [string] Subdomain on the site where the segment will be available. (default not set)
- `max-age`: [int] Number of seconds until cookie expires. (default: 31536000 i.e. 1 year)
- `expire`: [string] Sets the date in GMT string format when segment should expire. (default not set)
- `secure`: [bool] Only set segment if connection is over HTTPS. (default: false)
- `samesite`: [string] Allow segment cookie to be read cross-site. (default: lax)

### `segment-cache-display`

- `segment-name`: [string] Name of segment. (Tip: Omitting this option will show content only to visitors who haven't had their segment set.)
- `dangerously-set-html`: [bool] Allow rendering of HTML in content. (default: false)

## Technical details

Are you a developer? You don't need a plugin to enable this feature. Start using segment cache in your themes and plugins now by following the code samples below:

1. When you know enough about your visitors, create a segment by using `setcookie()`:

    ```php
    setcookie("wpe-us", "developer");
    ```

2. You can now detect the segment via the request header `X-WPENGINE-SEGMENT` and serve dynamic content. **Note:** You need to set the `Vary` header in your response.

    ```php
    header("Vary: X-WPENGINE-SEGMENT");

    $segment_name = $_SERVER["HTTP_X_WPENGINE_SEGMENT"];
    if ($segment_name == "developer") {
        echo "Hello dev!";
    } else {
        echo "Hello world!";
    }
    ```

## Other ways to set cache segments

WP Engine designed this feature to segment based on a cookie held by the browser, but what if you want to segment based on something else like IP or user agent?

To implement this, you'll need to contact support and ask them to add the following to the `before-in-location` block of your site's NGINX config.

- Segment by IP:

    ```nginx
    set $segment_header_name "none";
    if ($remote_addr ~* (1\.1\.0\.0|1\.1\.1\.1)) {
        set $segment_header_name "MYSEGMENT";
    }
    proxy_set_header X-WPENGINE-SEGMENT $segment_header_name;
    ```

- Segment by user agent:

    ```nginx
    set $segment_header_name "none";
    if ($http_user_agent ~* (UserAgent1|UserAgent2)) {
        set $segment_header_name "MYSEGMENT";
    }
    proxy_set_header X-WPENGINE-SEGMENT $segment_header_name;
    ```

With these patterns in the NGINX config, your application will still need to pass the `Vary` header in the response.

```php
header("Vary: X-WPENGINE-SEGMENT");
```

If you're using this plugin, the `Vary` header is set for you.

## Segment by bot traffic

WP Engine already forwards bot traffic to its own caching segment. If you want your site to display data in a way only bots will see, you can implement something like the following:

```php
if ($_SERVER["HTTP_X_IS_BOT"] == "1") {
    echo "Hello bot!";
} else {
    echo "Hello world!";
}
```
