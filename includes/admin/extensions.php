<?php
/**
 * Admin Extensions
 *
 * @package     MDJM
 * @subpackage  Admin/Extensions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Display the addons page.
 *
 * @since	1.4.7
 * @return	void
 */
function mdjm_extensions_page()	{
	setlocale( LC_MONETARY, get_locale() );
	$extensions_url = 'https://mdjm.co.uk/addons/';
	$extensions     = mdjm_get_extensions();
	$tags           = '<a><em><strong><blockquote><ul><ol><li><p>';
	$length         = 55;

	$slug_corrections = array(
		'ratings-and-satisfaction' => 'ratings-satisfaction',
		'easy-digital-downloads'   => 'edd'
	);

	?>
	<div class="wrap about-wrap mdjm-about-wrapp">
		<h1>
			<?php _e( 'Extensions for MDJM Event Management', 'mobile-dj-manager' ); ?>
		</h1>
		<div>
        	<p><a href="https://mdjm.com/extensions/" class="button-primary" target="_blank"><?php _e( 'Browse All Extensions', 'mobile-dj-manager' ); ?></a></p>
			<p><?php _e( 'These extensions <em><strong>add even more functionality</strong></em> to your MDJM Event Management solution.', 'mobile-dj-manager' ); ?></p>
            <p><?php printf( __( '<em><strong>Remember</strong></em> to <a href="%s" target="_blank">sign up to our newsletter</a> and receive a 15%s discount off your next purchase from our <a href="%s" target="_blank">plugin store</a>.', 'mobile-dj-manager' ), 'https://mobile-dj-manager.com/#newsletter-signup', '%', $extensions_url ); ?></p>
		</div>

		<div class="mdjm-extension-wrapper grid3">
			<?php foreach ( $extensions as $key => $extension ) :

				$slug  = $extension->info->slug;
				$link  = 'https://mobile-dj-manager.com/downloads/' . $slug .'/';
				$price = false;

				if ( array_key_exists( $slug, $slug_corrections ) )	{
					$slug = $slug_corrections[ $slug ];
				}

				if ( isset( $extension->pricing->amount ) ) {
					$price = '&pound;' . number_format( $extension->pricing->amount, 2 );
				} else {
					if ( isset( $extension->pricing->singlesite ) ) {
						$price = '&pound;' . number_format( $extension->pricing->singlesite, 2 );
					}
				}

				if ( ! empty( $extension->info->excerpt ) ) {
					$the_excerpt = $extension->info->excerpt;
				}

				$the_excerpt   = strip_shortcodes( strip_tags( stripslashes( $the_excerpt ), $tags ) );
				$the_excerpt   = preg_split( '/\b/', $the_excerpt, $length * 2+1 );
				$excerpt_waste = array_pop( $the_excerpt );
				$the_excerpt   = implode( $the_excerpt ); ?>

                <article class="col">
                    <div class="mdjm-extension-item">
                        <div class="mdjm-extension-item-img">
                            <a href="<?php echo $link; ?>" target="_blank"><img src="<?php echo $extension->info->thumbnail; ?>" /></a>
                        </div>
                        <div class="mdjm-extension-item-desc">
                            <p class="mdjm-extension-item-heading"><?php echo $extension->info->title; ?></p>
                            <div class="mdjm-extension-item-excerpt">
                            	<p><?php echo $the_excerpt; ?></p>
                            </div>
                            <div class="mdjm-extension-buy-now">
                                <?php if ( ! is_plugin_active( 'mdjm-' . $slug . '/' . 'mdjm-' . $slug . '.php' ) ) : ?>
                                    <a href="<?php echo $link; ?>" class="button-primary" target="_blank"><?php printf( __( 'Buy Now from %s', 'mobile-dj-manager' ), $price ); ?></a>
                                <?php else : ?>
                                    <p class="button-primary"><?php _e( 'Already Installed', 'mobile-dj-manager' ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
			<?php endforeach; ?>
		</div>
	</div>
	<?php

} // mdjm_extensions_page

/**
 * Retrieve the published extensions from mobile-dj-manager.com and store within transient.
 *
 * @since	1.0.3
 * @return	void
 */
function mdjm_get_extensions()	{
	$extensions = get_transient( '_mdjm_extensions_feed' );

	if ( false === $extensions || doing_action( 'mdjm_daily_scheduled_events' ) )	{
		$route    = esc_url( 'https://mdjm.co.uk/edd-api/products/' );
		$number   = 20;
		$endpoint = add_query_arg( array( 'number' => $number ), $route );
		$response = wp_remote_get( $endpoint );

		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body    = wp_remote_retrieve_body( $response );
			$content = json_decode( $body );
	
			if ( is_object( $content ) && isset( $content->products ) ) {
				set_transient( '_mdjm_extensions_feed', $content->products, DAY_IN_SECONDS / 2 ); // Store for 12 hours
				$extensions = $content->products;
			}
		}
	}

	return $extensions;
} // mdjm_get_extensions
add_action( 'mdjm_daily_scheduled_events', 'mdjm_get_extensions' );
