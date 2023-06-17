# Thumbor for WordPress

**This plugin is in early development and may yet undergo changes that affect its functionality. Use it at your own risk until we publish a versioned release.**

This plugin modifies Wordpress image URLs so they can be served from [Thumbor](https://www.thumbor.org/). 

A well configured Thumbor server can deliver highly optimised images to improve website load times, and take a large of your web server by removing the need for WordPress to generate image derivatives for itself.

## Prerequisites

You need access to a Thumbor service to use this plugin.

Note this typically only works on remote WordPress websites because Thumbor needs web access to the uploaded images. With some environment configuration you may be able to make it work in a development environment but that setup is outside the scope of this plugin.

## Installation

Install and activate the plugin as normal then add the following configuration to your website.

```php
define( 'THUMBOR_URL', 'https://media.example.com' );
define( 'THUMBOR_SECRET', 'your_thumbor_secret' );
```

If you Thumbor server runs in unsafe mode (which is _highly_ discouraged) you may set `THUMBOR_SECRET` to `null`.

### Deactivating/pausing the plugin

The plugin automatically deactivates itself when `THUMBOR_URL` is not set. So in your development environment you can remove the above configuration to make WordPress return to its default behaviour.

## Credits

This plugin is heavily based on code that was forked from the [Tachyon plugin](https://github.com/humanmade/tachyon-plugin) by Human Made. All due credit to the authors of that plugin.
