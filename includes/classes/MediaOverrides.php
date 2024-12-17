<?php
/**
 * Override native media behaviour.
 *
 * @package wordpress-thumbor
 */

namespace Eighteen73\Thumbor;

/**
 * Plugin singleton class.
 */
class MediaOverrides {
	/**
	 * Oh look, a singleton!
	 *
	 * @var Thumbor|null
	 */
	private static $instance = null;

	private $sizes = [];

	/**
	 * Singleton implementation
	 *
	 * @return object
	 */
	public static function instance() {

		if ( ! defined( 'THUMBOR_URL' ) || empty( THUMBOR_URL ) ) {
			return;
		}

		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			$class          = get_called_class();
			self::$instance = new $class();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Silence is golden.
	 */
	private function __construct() {}

	/**
	 * Register actions and filters, but only if basic Thumbor functions are available.
	 * The basic functions are found in ./wordpress-thumbor.php.
	 *
	 * @uses add_action, add_filter
	 * @return null
	 */
	private function setup() {

		if ( ! function_exists( 'thumbor_url' ) ) {
			return;
		}

		// Don't scale down big images
		add_filter( 'big_image_size_threshold', [ $this, 'image_threshold' ], 999, 1 );

		// Don't resize any images
		add_filter( 'intermediate_image_sizes_advanced', [ $this, 'prevent_resizing' ], 10, 5 );
	}

	/**
	 * Prevents any images from being automatically created.
	 *
	 * @param int $threshold The threshold value in pixels.
	 *
	 * @return bool|int The new “big image” threshold value.
	 */
	public static function image_threshold( $threshold ) {

		if ( ! defined( 'THUMBOR_UPLOAD_IMAGE_THRESHOLD' ) ) {
			return $threshold;
		}

		if ( THUMBOR_UPLOAD_IMAGE_THRESHOLD === false ) {
			return false;
		} elseif ( is_int( THUMBOR_UPLOAD_IMAGE_THRESHOLD ) ) {
			return THUMBOR_UPLOAD_IMAGE_THRESHOLD;
		}

		return $threshold;
	}

	/**
	 * Prevents any images from being automatically created.
	 *
	 * @param array $sizes Associative array of image sizes to be created.
	 * @param array $image_meta The image meta data: width, height, file, sizes, etc.
	 *
	 * @return array Associative array of image sizes to be created.
	 */
	public static function prevent_resizing( $sizes, $image_meta ) {
		return [];
	}
}
