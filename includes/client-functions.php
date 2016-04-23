<?php
/**
 * Contains all client related functions
 *
 * @package		MDJM
 * @subpackage	Users/Clients
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
	
/**
 * Retrieve a list of all clients
 *
 * @param	str|arr	$roles		Optional: The roles for which we want to retrieve the clients from.
 *			int		$employee	Optional: Only display clients of the given employee
 *			str		$orderby	Optional: The field by which to order. Default display_name
 *			str		$order		Optional: ASC (default) | Desc
 *
 * @return	$arr	$employees	or false if no employees for the specified roles
 */
function mdjm_get_clients( $roles='', $employee='', $orderby='', $order='' )	{
	$defaults = array(
		'roles'		=> array( 'client', 'inactive_client' ),
		'employee'	 => false,
		'orderby'	  => 'display_name',
		'order'		=> 'ASC'
	);
	
	$roles = empty( $roles ) ? $defaults['roles'] : $roles;
	$employee = empty( $employee ) ? $defaults['employee'] : $employee;
	$orderby = empty( $orderby ) ? $defaults['orderby'] : $orderby;
	$order = empty( $order ) ? $defaults['order'] : $order;
	
	// We'll work with an array of roles
	if( !empty( $roles ) && !is_array( $roles ) )
		$roles = array( $roles );
	
	$all_clients = get_users( 
		array(
			'role__in'	 => $roles,
			'orderby'	  => $orderby,
			'order'		=> $order
		)
	);
	
	// If we are only quering an employee's client, we need to filter	
	if( !empty( $employee ) )	{
		foreach( $all_clients as $client )	{
			if( !MDJM()->users->is_employee_client( $client->ID, $employee ) )
				continue;
				
			$clients[] = $client;	
		}
		// No clients for employee
		if( empty( $clients ) )
			return false;
			
		$all_clients = $clients;
	}
	
	$clients = $all_clients; 
				
	return $clients;
} // mdjm_get_clients

/**
 * Retrieve the client ID from the event
 *
 * @since	1.3
 * @param	int		$event_id	The event ID.
 * @return	$arr	$employees	or false if no employees for the specified roles
 */
function mdjm_get_client_id( $event_id )	{
	return mdjm_get_event_client_id( $event_id );
} // mdjm_get_client_id

/**
 * Adds a new client.
 *
 * We assume that $data is passed from the $_POST super global but $user_data can be passed.
 *
 * @since	1.3
 * @param	arr			$user_data	Array of client data. See $defaults.
 * @return	int|bool	$user_id	User ID of the new client or false on failure.
 */
function mdjm_add_client( $user_data = array() )	{
	
	if( ! mdjm_employee_can( 'list_all_clients' ) )	{
		return false;
	}
	
	$defaults = array(
		'first_name' => ! empty( $_POST['client_firstname'] )	? ucwords( $_POST['client_firstname'] )	: '',
		'last_name'  => ! empty( $_POST['client_lastname'] )		? ucwords( $_POST['client_lastname'] )	: '',
		'user_email' => ! empty( $_POST['client_email'] )		? $_POST['client_email']				: '',
		'user_pass'  => wp_generate_password( mdjm_get_option( 'pass_length' ) ),
		'role'       => 'client'
	);
	
	$defaults['display_name'] = $defaults['first_name'] . ' ' . $defaults['last_name'];
	$defaults['nickname']     = $defaults['display_name'];
	$defaults['user_login']   = is_email( $defaults['user_email'] );
	
	$args = wp_parse_args( $user_data, $defaults );
	
	/**
	 * Allow filtering of the user data
	 *
	 * @since	1.3
	 * @param	arr		$args	Array of user data
	 */
	$args = apply_filters( 'mdjm_add_client_user_data', $args );
	
	/**
	 * Fire the `mdjm_pre_create_client` action.
	 *
	 * @since	1.3
	 * @param	arr		$args	Array of user data
	 */
	do_action( 'mdjm_pre_add_client' );
	
	// Create the user
	$user_id = wp_insert_user( $args );
	
	if ( is_wp_error( $user_id ) )	{
		
		if( MDJM_DEBUG == true )	{
			MDJM()->debug->log_it( 'Error creating user: ' . $user_id->get_error_message(), true );
		}
		
		return false;
	}

	$user_meta = array(
		'show_admin_bar_front'	=> false,
		'marketing'				=> 'Y',
		'phone1'				=> isset( $args['client_phone'] ) ? $args['client_phone'] : ''
	);
	
	/**
	 * Allow filtering of the client meta data
	 *
	 * @since	1.3
	 * @param	arr		$user_meta	Array of client meta data
	 */
	$user_meta = apply_filters( 'mdjm_add_client_meta_data', $user_meta );

	foreach( $user_meta as $key => $value )	{
		update_user_meta( $user_id, $key, $value );
	}
	
	/**
	 * Fire the `mdjm_post_create_client` action.
	 *
	 * @since	1.3
	 * @param	int		$user_id	ID of the new client
	 */
	do_action( 'mdjm_post_create_client', $user_id );
	
	MDJM()->debug->log_it( sprintf( 'Client created with ID: %d', $user_id ) );
	
	return $user_id;
	
} // mdjm_add_client

/**
 * Retrieve all of this clients events.
 *
 * @param	int		$client_id	Optional: The WP userID of the client. Default to current user.
 *			str|arr	$status		Optional: Status of events that should be returned. Default any.
 *			str		$orderby	Optional: The field by which to order. Default event date.
 *			str		$order		Optional: DESC (default) | ASC
 *
 * @return	mixed	$events		WP_Post objects or false.
 */
function mdjm_get_client_events( $client_id='', $status='any', $orderby='event_date', $order='ASC' )	{
	$args = apply_filters( 'mdjm_get_client_events_args',
		array(
			'post_type'        => 'mdjm-event',
			'post_status'      => $status,
			'posts_per_page'   => -1,
			'meta_key'         => '_mdjm_' . $orderby,
			'orderby'          => 'meta_value_num',
			'order'            => $order,
			'meta_query'       => array(
				array(
					'key'      => '_mdjm_event_client',
					'value'    => !empty( $client_id ) ? $client_id : get_current_user_id(),
					'compare'  => 'IN',
				),
			)
		)
	);
	
	$events = get_posts( $args );
	
	return $events;
} // mdjm_get_client_events

/**
 * Check whether the user is a client.
 *
 * @since	1.3
 * @param	int		$client_id	The ID of the user to check.
 * @return	bool	True if user has the client role, or false.
 */
function mdjm_user_is_client( $client_id )	{
	if( mdjm_get_client_events( $client_id ) )	{
		return true;
	}
	
	return false;
} // mdjm_user_is_client

/**
 * Retrieve a clients first name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The first name of the client.
 */
function mdjm_get_client_firstname( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->first_name ) )	{
		$first_name = $client->first_name;
	} else	{
		$first_name = __( 'First name not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_client_firstname', $first_name, $user_id );
} // mdjm_get_client_firstname

/**
 * Retrieve a clients last name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The last name of the client.
 */
function mdjm_get_client_lastname( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->last_name ) )	{
		$last_name = $client->last_name;
	} else	{
		$last_name = __( 'Last name not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_client_lastname', $last_name, $user_id );
} // mdjm_get_client_lastname

/**
 * Retrieve a clients display name.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The display name of the client.
 */
function mdjm_get_client_display_name( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->display_name ) )	{
		$display_name = $client->display_name;
	} else	{
		$display_name = __( 'Display name not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_client_display_name', $display_name, $user_id );
} // mdjm_get_client_display_name

/**
 * Retrieve a clients email address.
 *
 * @since	1.3
 * @param	int		$user_id	The ID of the user to check.
 * @return	str		The first name of the client.
 */
function mdjm_get_client_email( $user_id )	{
	if( empty( $user_id ) )	{
		return false;
	}
	
	$client = get_userdata( $user_id );
	
	if( $client && ! empty( $client->user_email ) )	{
		$email = $client->user_email;
	} else	{
		$email = __( 'Email address not set', 'mobile-dj-manager' );
	}
	
	return apply_filters( 'mdjm_get_client_email', $email, $user_id );
} // mdjm_get_client_email