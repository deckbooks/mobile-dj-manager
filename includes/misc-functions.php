<?php

/**
 * Contains misc functions.
 *
 * @package		MDJM
 * @subpackage	Functions
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Return all registered currencies.
 *
 * @since	1.3
 * @param
 * @return	arr		Array of MDJM registered currencies
 */
function mdjm_get_currencies()	{
	return apply_filters( 'mdjm_currencies',
				array(
					'GBP'  => __( 'Pounds Sterling (&pound;)', 'mobile-dj-manager' ),
					'USD'  => __( 'US Dollars (&#36;)', 'mobile-dj-manager' ),
					'EUR'  => __( 'Euros (&euro;)', 'mobile-dj-manager' ),
					'AUD'  => __( 'Australian Dollars (&#36;)', 'mobile-dj-manager' ),
					'BRL'  => __( 'Brazilian Real (R&#36;)', 'mobile-dj-manager' ),
					'CAD'  => __( 'Canadian Dollars (&#36;)', 'mobile-dj-manager' ),
					'CZK'  => __( 'Czech Koruna', 'mobile-dj-manager' ),
					'DKK'  => __( 'Danish Krone', 'mobile-dj-manager' ),
					'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'mobile-dj-manager' ),
					'HUF'  => __( 'Hungarian Forint', 'mobile-dj-manager' ),
					'ILS'  => __( 'Israeli Shekel (&#8362;)', 'mobile-dj-manager' ),
					'JPY'  => __( 'Japanese Yen (&yen;)', 'mobile-dj-manager' ),
					'MYR'  => __( 'Malaysian Ringgits', 'mobile-dj-manager' ),
					'MXN'  => __( 'Mexican Peso (&#36;)', 'mobile-dj-manager' ),
					'NZD'  => __( 'New Zealand Dollar (&#36;)', 'mobile-dj-manager' ),
					'NOK'  => __( 'Norwegian Krone', 'mobile-dj-manager' ),
					'PHP'  => __( 'Philippine Pesos', 'mobile-dj-manager' ),
					'PLN'  => __( 'Polish Zloty', 'mobile-dj-manager' ),
					'SGD'  => __( 'Singapore Dollar (&#36;)', 'mobile-dj-manager' ),
					'ZAR'  => __( 'South African Rand', 'mobile-dj-manager' ),
					'SEK'  => __( 'Swedish Krona', 'mobile-dj-manager' ),
					'CHF'  => __( 'Swiss Franc', 'mobile-dj-manager' ),
					'TWD'  => __( 'Taiwan New Dollars', 'mobile-dj-manager' ),
					'THB'  => __( 'Thai Baht (&#3647;)', 'mobile-dj-manager' ),
					'INR'  => __( 'Indian Rupee (&#8377;)', 'mobile-dj-manager' ),
					'TRY'  => __( 'Turkish Lira (&#8378;)', 'mobile-dj-manager' ),
					'RIAL' => __( 'Iranian Rial (&#65020;)', 'mobile-dj-manager' ),
					'RUB'  => __( 'Russian Rubles', 'mobile-dj-manager' )
				)
			);
} // mdjm_get_currencies

/**
 * Get the set currency
 *
 * @since 1.3
 * @return string The currency code
 */
function mdjm_get_currency() {
	$currency = mdjm_get_option( 'currency', 'GBP' );
	return apply_filters( 'mdjm_currency', $currency );
} // mdjm_get_currency

/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determine, the currency string is returned.
 *
 * @since 	1.3
 * @param	str		$currency	The currency string
 * @return	str		The symbol to use for the currency
 */
function mdjm_currency_symbol( $currency = '' ) {
	if ( empty( $currency ) ) {
		$currency = mdjm_get_currency();
	}

	switch ( $currency ) :
		case "GBP" :
			$symbol = '&pound;';
			break;
		case "BRL" :
			$symbol = 'R&#36;';
			break;
		case "EUR" :
			$symbol = '&euro;';
			break;
		case "USD" :
		case "AUD" :
		case "NZD" :
		case "CAD" :
		case "HKD" :
		case "MXN" :
		case "SGD" :
			$symbol = '&#36;';
			break;
		case "JPY" :
			$symbol = '&yen;';
			break;
		default :
			$symbol = $currency;
			break;
	endswitch;

	return apply_filters( 'mdjm_currency_symbol', $symbol, $currency );
} // mdjm_currency_symbol

/**
 * Get the name of a currency
 *
 * @since	1.3
 * @param	str		$code	The currency code
 * @return	str		The currency's name
 */
function mdjm_get_currency_name( $code = 'USD' ) {
	$currencies = mdjm_get_currencies();
	$name       = isset( $currencies[ $code ] ) ? $currencies[ $code ] : $code;
	
	return apply_filters( 'mdjm_currency_name', $name );
} // mdjm_get_currency_name

/**
 * Get the label used for deposits.
 *
 * @since	1.3
 * @param
 * @return	str		The label set for deposits
 */
function mdjm_get_deposit_label() {
	return mdjm_get_option( 'deposit_label', __( 'Deposit', 'mobile-dj-manager' ) );
} // mdjm_get_deposit_label

/**
 * Get the label used for balances.
 *
 * @since	1.3
 * @param
 * @return	str		The label set for balances
 */
function mdjm_get_balance_label() {
	return mdjm_get_option( 'balance_label', __( 'Balance', 'mobile-dj-manager' ) );
} // mdjm_get_balance_label

/**
 * Get the current page URL
 *
 * @since	1.3
 * @param
 * @return	str		$page_url	Current page URL
 */
function mdjm_get_current_page_url() {
	$scheme = is_ssl() ? 'https' : 'http';
	$uri    = esc_url( site_url( $_SERVER['REQUEST_URI'], $scheme ) );

	if ( is_front_page() )	{
		$uri = home_url();
	}

	$uri = apply_filters( 'mdjm_get_current_page_url', $uri );

	return $uri;
} // mdjm_get_current_page_url

/**
 * Display a Notice.
 *
 * Display a notice on the front end.
 *
 * @since	1.3
 * @param	int		$m		The notice message key.
 * @return	str		The HTML string for the notice
 */
function mdjm_display_notice( $m )	{	
	$message = mdjm_messages( $m );
	
	$notice = '<div class="mdjm-' . $message['class'] . '"><span>' . $message['title'] . ': </span>' . $message['message'] . '</div>';

	return apply_filters( 'mdjm_display_notice', $notice, $m );
} // mdjm_display_notice

/**
 * Display notice on front end.
 *
 * Check for super global $_GET['mdjm-message'] key and return message if set.
 *
 * @since	1.3
 * @param
 * @return	str		Out the relevant message to the browser.
 */
function mdjm_print_notices()	{
	if( ! isset( $_GET, $_GET['mdjm_message'] ) )	{
		return;
	}
	
	echo mdjm_display_notice( $_GET['mdjm_message'] );
} // mdjm_print_notices
add_action( 'mdjm_print_notices', 'mdjm_print_notices' );

/**
 * Messages.
 *
 * Messages that are used on the front end.
 *
 * @since	1.3
 * @param	str		$key		Array key of notice to retrieve. All by default.
 * @return	arr		Array containing message text, title and class.
 */
function mdjm_messages( $key )	{
	$messages = apply_filters(
		'mdjm_messages',
		array(
			'20'	=> array(
				'class'		=> 'success',
				'title'		=> __( 'Done', 'mobile-dj-manager' ),
				'message'	=> __( 'Playlist entry added.', 'mobile-dj-manager' )
			),
			'21'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'Unable to add playlist entry.', 'mobile-dj-manager' )
			),
			'22'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Data missing', 'mobile-dj-manager' ),
				'message'	=> __( 'Please provide at least a song and an artist for this entry.', 'mobile-dj-manager' )
			),
			'23'	=> array(
				'class'		=> 'success',
				'title'		=> __( 'Done', 'mobile-dj-manager' ),
				'message'	=> __( 'Playlist entry removed.', 'mobile-dj-manager' )
			),
			'24'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'Unable to remove playlist entry.', 'mobile-dj-manager' )
			),
			'25'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'No playlist entry selected.', 'mobile-dj-manager' )
			),
			'90'   => array(
				'class'		=> 'error',
				'title'		=> __( 'Sorry', 'mobile-dj-manager' ),
				'message'	=> __( "We seem to be missing the event details.", 'mobile-dj-manager' )
			),
			'99'   => array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'Security verification failed.', 'mobile-dj-manager' )
			)
		)
	);
	
	// Return a single message
	if( isset( $key ) && array_key_exists( $key, $messages ) )	{
		return $messages[ $key ];
	}
	
	// Return all messages
	return $messages;
} // mdjm_messages