<?php
/**
 * Plugin Name:     WordPress Thumbor
 * Plugin URI:      https://github.com/eighteen73/wordpress-thumbor
 * Description:     A WordPress plugin to serve media via a Thumbor server
 * Author:          eighteen73 Web Team
 * Author URI:      https://eighteen73.co.uk
 * Update URI:      https://github.com/eighteen73/wordpress-thumbor
 * Text Domain:     wordpress-thumbor
 *
 * @package         wordpress-thumbor
 */

use Eighteen73\Thumbor\MediaOverrides;
use Eighteen73\Thumbor\ThumborImage;

spl_autoload_register(
	function ( $class_name ) {
		$path_parts = explode( '\\', $class_name );

		if ( ! empty( $path_parts ) ) {
			$package = $path_parts[0];

			unset( $path_parts[0] );

			if ( 'Eighteen73' === $package ) {
				require_once __DIR__ . '/includes/classes/' . implode( '/', $path_parts ) . '.php';
			}
		}
	}
);

MediaOverrides::instance();
ThumborImage::instance();

/**
 * Generates a Thumbor URL.
 *
 * @see https://docs.altis-dxp.com/media/dynamic-images/
 *
 * @param string       $image_url URL to the publicly accessible image you want to manipulate.
 * @param array|string $args An array of arguments, i.e. array( 'w' => '300', 'resize' => array( 123, 456 ) ), or in string form (w=123&h=456).
 * @param string|null  $scheme One of http or https.
 * @return string The raw final URL. You should run this through esc_url() before displaying it.
 */
function thumbor_url( $image_url, $args = [], $scheme = null ) {

	if ( ! defined( 'THUMBOR_URL' ) ) {
		return;
	}

	$upload_dir     = wp_upload_dir();
	$upload_baseurl = $upload_dir['baseurl'];

	if ( is_multisite() ) {
		$upload_baseurl = preg_replace( '#/sites/[\d]+#', '', $upload_baseurl );
	}

	$image_url = trim( $image_url );

	$image_file = basename( parse_url( $image_url, PHP_URL_PATH ) );
	$image_url  = str_replace( $image_file, urlencode( $image_file ), $image_url );

	if ( strpos( $image_url, $upload_baseurl ) !== 0 ) {
		return $image_url;
	}

	if ( false !== apply_filters( 'thumbor_skip_for_url', false, $image_url, $args, $scheme ) ) {
		return $image_url;
	}

	$image_url = apply_filters( 'thumbor_pre_image_url', $image_url, $args, $scheme );
	$args      = apply_filters( 'thumbor_pre_args', $args, $image_url, $scheme );

	if ( isset( $args['fit'] ) ) {
		$scale  = null;
		$width  = $args['fit'][0];
		$height = $args['fit'][1];
	} elseif ( isset( $args['resize'] ) ) {
		$scale  = 'fit-in';
		$width  = $args['resize'][0];
		$height = $args['resize'][1];
	} elseif ( isset( $args['w'] ) ) {
		$scale  = 'fit-in';
		$width  = $args['w'];
		$height = 'orig';
	} elseif ( isset( $args['h'] ) ) {
		$scale  = 'fit-in';
		$width  = 'orig';
		$height = $args['h'];
	} else {
		$scale  = 'fit-in';
		$width  = 'orig';
		$height = 'orig';
	}

	$url_parts = [
		'scale'   => $scale,
		'size'    => "{$width}x{$height}",
		'filters' => null,
		'smart'   => null,
	];

	$thumbor_url = implode( '/', array_filter( $url_parts ) ) . '/' . urlencode( $image_url );

	if ( defined( 'THUMBOR_SECRET' ) && ! empty( THUMBOR_SECRET ) ) {
		$signature   = hash_hmac( 'sha1', $thumbor_url, THUMBOR_SECRET, true );
		$thumbor_url = THUMBOR_URL . '/' . strtr( base64_encode( $signature ), '/+', '_-' ) . '/' . $thumbor_url;
	} else {
		$thumbor_url = THUMBOR_URL . '/unsafe/' . $thumbor_url;
	}

	/**
	 * Allows a final modification of the generated Thumbor URL.
	 *
	 * @param string $thumbor_url The final Thumbor image URL including query args.
	 * @param string $image_url   The image URL without query args.
	 * @param array  $args        A key value array of the query args appended to $image_url.
	 */
	return apply_filters( 'thumbor_url', $thumbor_url, $image_url, $args );
}
