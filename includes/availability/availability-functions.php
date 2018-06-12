<?php
/**
 * Contains all availability checker related functions
 *
 * @package		MDJM
 * @subpackage	Availability
 * @since		1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Retrieve the default event statuses that make an employee unavailable.
 *
 * @since	1.5.6
 * @return	array
 */
function mdjm_get_availability_statuses()	{
	$statuses = mdjm_get_option( 'availability_status', 'any' );
	return apply_filters( 'mdjm_availability_statuses', $statuses );
} // mdjm_get_availability_statuses

/**
 * Retrieve the default roles to check for availability.
 *
 * @since	1.5.6
 * @return	array
 */
function mdjm_get_availability_roles()	{
	$roles = mdjm_get_option( 'availability_roles', array() );
	return apply_filters( 'mdjm_availability_roles', $roles );
} // mdjm_get_availability_roles

/**
 * Set the correct time format for the calendar
 *
 * @since	1.5.6
 * @param
 * @return
 */
function mdjm_format_calendar_time()	{
	$time_format = get_option( 'time_format' );
	
	$search = array( 'g', 'G', 'i', 'a', 'A' );
	$replace = array( 'h', 'H', 'mm', 't', 'T' );
	
	$time_format = str_replace( $search, $replace, $time_format );
			
	return apply_filters( 'mdjm_format_calendar_time', $time_format );
} // mdjm_format_calendar_time

/**
 * Retrieve all dates within the given range
 *
 * @since   1.5.6
 * @param	string	$start		The start date Y-m-d
 * @param	string	$end		The end date Y-m-d
 * @return  array   Array of all dates between two given dates
 */
function mdjm_get_all_dates_in_range( $start, $end )	{
    $start = \DateTime::createFromFormat( 'Y-m-d', $start );
    $end   = \DateTime::createFromFormat( 'Y-m-d', $end );

    $range = new \DatePeriod( $start, new \DateInterval( 'P1D' ), $end->modify( '+1 day' ) );

    return $range;
} // mdjm_get_all_dates_in_range

/**
 * Add an employee absence entry.
 *
 * @since   1.5.6
 * @param   int     $employee_id    Employee user ID
 * @param   array   $data           Array of absence data
 * @return  bool    True on success, otherwise false
 */
function mdjm_add_employee_absence( $employee_id, $data )    {
    $employee_id = absint( $employee_id );

    if ( empty( $employee_id ) || ! mdjm_is_employee( $employee_id ) )   {
        return false;
    }

    $start_time = ' 00:00:00';
    $end_time   = ' 00:00:00';
	$end_date   = '';

	if ( ! empty( $data['end'] ) )	{
		$end_date = date( 'Y-m-d', strtotime( '+1 day', $data['end'] ) . $end_time );
	} else	{
		if ( ! empty( $data['start'] ) )	{
			$end_date = date( 'Y-m-d', strtotime( '+1 day', $data['start'] ) . $end_time );
		}
	}

    $args                = array();
    $args['employee_id'] = $employee_id;
    $args['group_id']    = ! empty( $data['group_id'] )  ? $data['group_id']                         : md5( $employee_id . '_' . mdjm_generate_random_string() );
    $args['start']       = ! empty( $data['start'] )     ? $data['start'] . $start_time              : '';
    $args['end']         = $end_date;
    $args['notes']       = ! empty( $data['notes'] )     ? sanitize_textarea_field( $data['notes'] ) : '';

    $args = apply_filters( 'mdjm_add_employee_absence_args', $args, $employee_id, $data );

    do_action( 'mdjm_before_add_employee_absence', $args, $data );

    $absence_range = mdjm_get_all_dates_in_range( $args['from_date'], $args['to_date'] );

    foreach( $absence_range as $date )	{
        $args['from_date'] = $date->format( 'Y-m-d' );
        $return = MDJM()->availability_db->add( $args );

        if ( ! $return )    {
            return false;
        }
    }

    do_action( 'mdjm_add_employee_absence', $args, $data, $return );
    do_action( 'mdjm_add_employee_absence_' . $employee_id, $args, $data, $return );

    return $return;
} // mdjm_add_employee_absence

/**
 * Remove an employee absence entry.
 *
 * @since   1.5.6
 * @param   string     $group_id
 * @return  int        The number of rows deleted or false
 */
function mdjm_remove_employee_absence( $group_id )  {
    do_action( 'mdjm_before_remove_employee_absence', $group_id );

    $deleted = MDJM()->availability_db->delete( $group_id );

    do_action( 'mdjm_remove_employee_absence', $group_id, $deleted );

    return $deleted;
} // mdjm_remove_employee_absence
 

/**
 * Perform the availability lookup.
 *
 * @since	1.3
 * @param	str			$date		The requested date
 * @param	int|array	$employees	The employees to check
 * @param	str|array	$roles		The employee roles to check
 * @return	array|bool	Array of available employees or roles, or false if not available
 */
function mdjm_do_availability_check( $date, $employees = '', $roles = '', $status = '' )	{

	$check = new MDJM_Availability_Checker( $date, $employees, $roles, $status );
	
	$check->check_availability();
	
	return $check->result;
	
} // mdjm_do_availability_check

/**
 * Determine if an employee is working on the given date.
 *
 * @since	1.3
 * @param	str			$date		The date
 * @param	int			$employee	The employee ID
 * @param	str|arr		$status		The employee ID
 * @return	bool		True if the employee is working, otherwise false.
 */
function mdjm_employee_is_working( $date, $employee_id='', $status='' )	{	
	
	if ( empty( $employee_id ) && is_user_logged_in() )	{
		$employee_id = get_current_user_id();
	}
	
	if ( empty( $employee_id ) )	{
		wp_die( __( 'Ooops, an error occured.', 'mobile-dj-manager' ) );
	}
	
	if ( empty( $status ) )	{
		$status = mdjm_get_availability_statuses();
	}
	
	$event = mdjm_get_events(
		array(
			'post_status'    => $status,
			'posts_per_page' => 1,
			'meta_key'       => '_mdjm_event_date',
			'meta_value'     => date( 'Y-m-d', $date ),
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_mdjm_event_dj',
					'value'   => $employee_id,
					'compare' => '=',
					'type'    => 'NUMERIC'
				),
				array(
					'key'     => '_mdjm_event_employees',
					'value'   => sprintf( ':"%s";', $employee_id ),
					'compare' => 'LIKE'
				)
			)
		)
	);
	
	$event = apply_filters( 'mdjm_employee_is_working', $event, $date, $employee_id );
	
	if ( $event )	{
		return true;
	}
	
	return false;
} // mdjm_employee_is_working

/**
 * Determine if an employee is on vacaion the given date.
 *
 * @since	1.3
 * @param	str		$date		The date
 * @param	int		$employee	The employee
 * @return	bool	True if the employee is on vacation, otherwise false.
 */
function mdjm_employee_is_on_vacation( $date, $employee_id = '' )	{
	$employee_id = empty( $employee_id ) ? get_current_user_id() : $employee_id;
	$date        = date( 'Y-m-d', $date );

	$result      = MDJM()->availability_db->get_entries( array(
		'employee_id' => $employee_id,
		'start'       => $date,
		'number'      => 1
	) );

	$result = apply_filters( 'mdjm_employee_is_on_vacation', $result, $date, $employee_id );

	return ! empty( $result );
} // mdjm_employee_is_on_vacation

/**
 * Retrieve employee availability activity for a given date range.
 *
 * @since	1.5.6
 * @param	string	$start	Start date for which to retrieve activity
 * @param	string	$end	End date for which to retrieve activity
 * @return	array	Array of data for the calendar
 */
function mdjm_get_employee_availability_activity( $start, $end )	{
	$activity    = array();
	$description = array();
	$date_format = get_option( 'date_format' );
	$time_format = get_option( 'time_format' );
	$entries     = MDJM()->availability_db->get_entries( array(
		'start'   => date( 'Y-m-d H:i:s', $start ),
		'end'     => date( 'Y-m-d H:i:s', $end ),
		'orderby' => 'start',
		'order'   => 'ASC'
	) );

	if ( ! empty( $entries ) )	{
		foreach( $entries as $entry )	{

			$employee = mdjm_get_employee_display_name( $entry->employee_id );
			$title    = __( 'Unknown', 'mobile-dj-manager' );

			$short_date_start = date( 'Y-m-d', strtotime( $entry->start ) );
			$short_date_end   = date( 'Y-m-d', strtotime( $entry->end ) );

			if ( ! empty( $employee ) )	{
				$title = sprintf( '%s: %s', __( 'Absence', 'mobile-dj-manager' ), $employee );
			}

			if ( $short_date_end > $short_date_start )	{
				$description[] = sprintf(
					__( 'From: %s', 'mobile-dj-manager' ),
					date( $date_format, strtotime( $short_date_start ) )
				);
				$description[] = sprintf(
					__( 'Returns: %s', 'mobile-dj-manager' ),
					date( $date_format, strtotime( $short_date_end ) )
				);
			}

			if ( ! empty( $entry->notes ) )	{
				$description[] = stripslashes( $entry->notes );
			}

			$activity[] = array(
				'allDay'          => true,
				'backgroundColor' => '#f7f7f7',
				'borderColor'     => '#cccccc',
				'end'             => $entry->end,
				'id'              => $entry->id,
				'notes'           => implode( '<br>', $description ),
				'start'           => $entry->start,
				'textColor'       => '#555',
				'tipTitle'        => $title,
				'title'           => $employee
			);
		}
	}

	$activity = apply_filters( 'mdjm_employee_availability_activity', $activity, $start, $end );

	return $activity;
} // mdjm_get_employee_availability_activity

/**
 * Retrieve event activity for a given date range.
 *
 * @since	1.5.6
 * @param	string	$start	Start date for which to retrieve activity
 * @param	string	$end	End date for which to retrieve activity
 * @return	array	Array of data for the calendar
 */
function mdjm_get_event_availability_activity( $start, $end )	{
	$activity = array();
	$args     = array(
		'meta_query' => array(
			array(
				'key'     => '_mdjm_event_date',
				'value'   => array( date( 'Y-m-d', $start ), date( 'Y-m-d', $end ) ),
				'compare' => 'BETWEEN',
				'type'    => 'DATE'
			)
		)
	);

	$events = mdjm_get_events( $args );

	if ( ! empty( $events ) )	{
		foreach( $events as $_event )	{

			$popover     = 'top';
			$event       = new MDJM_Event( $_event->ID );
			$employee    = mdjm_get_employee_display_name( $event->employee_id );
			$event_id    = mdjm_get_event_contract_id( $event->ID );
			$title       = esc_attr( $event->get_type() );
			$description = array();
			$notes       = mdjm_get_calendar_event_description_text();
			$notes       = mdjm_do_content_tags( $notes, $event->ID, $event->client );
			$tip_title   = sprintf(
				'%s %s - %s',
				esc_html( mdjm_get_label_singular() ),
				$event_id,
				$title
			);			

			$day = date( 'N', $start );

			$activity[] = array(
				'allDay'          => false,
				'backgroundColor' => '#2ea2cc',
				'borderColor'     => '#0074a2',
				'end'             => $event->get_finish_date() . ' ' . $event->get_finish_time(),
				'id'              => $event->ID,
				'notes'           => $notes,
				'start'           => $event->date . ' ' . $event->get_start_time(),
				'textColor'       => '#fff',
				'tipTitle'        => $tip_title,
				'title'           => $title
			);
		}
	}

	$activity = apply_filters( 'mdjm_event_availability_activity', $activity, $start, $end );

	return $activity;
} // mdjm_get_event_availability_activity

/**
 * Retrieve the description text for the calendar popup
 *
 * @since	1.5.6
 * @return	string
 */
function mdjm_get_calendar_event_description_text()	{

	$default = sprintf( __( 'Date: %s', 'mobile-dj-manager' ), '{event_date}' ) . PHP_EOL;
	$default .= sprintf( __( 'Start: %s', 'mobile-dj-manager' ), '{start_time}' ) . PHP_EOL;
	$default .= sprintf( __( 'Finish: %s', 'mobile-dj-manager' ), '{end_time}' ) . PHP_EOL;
	$default .= sprintf( __( 'Setup: %s', 'mobile-dj-manager' ), '{dj_setup_time}' ) . PHP_EOL;
	$default .= sprintf( __( 'Cost: %s', 'mobile-dj-manager' ), '{total_cost}' ) . PHP_EOL;
	$default .= sprintf( __( 'Employees: %s', 'mobile-dj-manager' ), '{event_employees}' ) . PHP_EOL;

	$text = mdjm_get_option( 'calendar_event_description', $default );
	$text = utf8_encode( str_replace( '<br>', PHP_EOL, $text ) );

	return $text;
} // mdjm_get_calendar_event_description_text
