# Segment Cache for WP Engine
## A WordPress plugin to enable segmented content caching via shortcode

### About
Help your site convert visitors into subscribers by serving content specific to their needs. And do it quickly by using WP Engine's built-in caching system.

### How to use

1. When you know a visitor is a specific type of user (i.e. developer, marketer, enterprise, etc.) use the `segment-cache-set` shortcode remember who they are.

    ```
    [segment-cache-set segment-on="developer"]
    ```

2. Serve content specific to that type of user by using the `segment-cache-display` shortcode.

    ```
    [segment-cache-display segment-on="developer"]
        This content is for developers only.
    [/segment-cache-display]
    ```
    
    ```
    [segment-cache-display segment-on="marketer"]
        This content is for marketers only.
    [/segment-cache-display]
    ```
    
    ```
    [segment-cache-display]
        This content is for non-segmented visitors only.
    [/segment-cache-display]
    ```

### Additional options

#### `segment-cache-set`

These options mirror those found in the [official PHP docs](http://php.net/manual/en/function.setcookie.php) for `setcookie`.

- `segment-on`: [string] The name of segment. (required)
- `expire`: [int] The number of seconds until cookie expires. (default: 31536000 i.e. 1 year)
- `path`: [string] The path on the site where the segment will be available. (default: "/")
- `domain`: [string] The subdomain on the site where the segment will be available. (default: "")
- `secure`: [bool] Only set segment if connection is over HTTPS. (default: false)
- `httponly`: [bool] Only set segment if connection is via HTTP protocol. (default: false)

#### `segment-cache-display`

- `segment-on`: [string] The name of segment. (Tip: Omitting this option will show content only to visitors who haven't had their segment set.)
- `dangerously-set-html`: [bool] Allow rendering of HTML in content. (default: false)

### Technical details

Are you a developer? You don't need a plugin to enable this feature. Start using segment cache in your themes and plugins now by following the code samples below:

1. When you know enough about your visitors, create a segment by using `setcookie()`:

    ```
    setcookie("wpe-us", "developer");
    ```

2. You can now detect the segment via the request header `X-WPENGINE-SEGMENT` and serve dynamic content. **Note:** You need to set the `Vary` header in your response.

    ```
    header("Vary: X-WPENGINE-SEGMENT");
    
    $header_name = $_SERVER["HTTP_X_WPENGINE_SEGMENT"];
    if ($header_name == "developer") {
        echo "Hello dev!";
    } else {
        echo "Hello world!";
    }
    ```

### Other ways to set cache segments

WP Engine designed this feature to segment based on a cookie held by the browser, but what if you want to segment based on something else like IP or user agent?

To implement this, you'll need to contact support and ask them to add the following to the `before-in-location` block of your site's NGINX config.

- Segment by IP:

    ```
    set $segment_header_name "none";
    if ($remote_addr ~* (1\.1\.0\.0|1\.1\.1\.1)) {
        set $segment_header_name "MYSEGMENT";
    }
    proxy_set_header X-WPENGINE-SEGMENT $segment_header_name;
    ```
- Segment by user agent:

    ```
    set $segment_header_name "none";
    if ($http_user_agent ~* (UserAgent1|UserAgent2)) {
        set $segment_header_name "MYSEGMENT";
    }
    proxy_set_header X-WPENGINE-SEGMENT $segment_header_name;
    ```

With these patterns in the NGINX config, your application will still need to pass the Vary header in the response.

```
header("Vary: X-WPENGINE-SEGMENT");
```
