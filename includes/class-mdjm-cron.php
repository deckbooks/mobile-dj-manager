<?php
/*
 * MDJM Cron Class
 * 10/03/2015
 * @since 1.1.2
 * The MDJM cron class
 */
	
/* -- Build the MDJM_Cron class -- */
class MDJM_Cron	{		
	/*
	 * __construct
	 * 
	 *
	 *
	 */
	public function __construct()	{
		$this->schedules = get_option( 'mdjm_schedules' );

		add_filter( 'cron_schedules', array( $this, 'add_schedules'   ) );
		add_action( 'mdjm_hourly_schedule', array( &$this, 'execute_cron' ) ); // Run the MDJM scheduler
	} // __construct

	/**
	 * Creates custom cron schedules within WP.
	 *
	 * @since	1.3.8.6
	 * @return	void
	 */
	function add_schedules( $schedules = array() )	{
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'mobile-dj-manager' )
		);

		return $schedules;
	} // add_schedules
			
	/*
	 * Determine is a task is active and due to be executed
	 *
	 * @param	arr		$task		The array of the task to be queried
	 *
	 */
	public function task_ready( $task )	{			
		if( empty( $task ) )	{			
			return false;
		}
		
		// Check for active task
		if( $task['active'] != 'Y' && $task['active'] != true )	{
				MDJM()->debug->log_it( 'The task ' . $task['name'] . ' is not active' );
			return false;
		}
		
		// Check if scheduled to run
		if( !isset( $task['nextrun'] ) || $task['nextrun'] <= time() || $task['nextrun'] == 'Today'
					|| $task['nextrun'] == 'Next Week' || $task['nextrun'] == 'Next Month'
					|| $task['nextrun'] == 'Next Year' )	{
					
			return true;
		}
		else	{
			MDJM()->debug->log_it( 'SKIPPING CRON TASK: ' . $task['name'] . '. Next due ' . date( 'd/m/Y H:i:s', $task['nextrun'] ), true );	
		}
					
		return false;	
		
	} // task_ready

	/*
	 * Execute the schedules tasks which are due to be run
	 *
	 *
	 *
	 */
	public function execute_cron()	{
		require_once( MDJM_PLUGIN_DIR . '/includes/class-mdjm-task-runner.php' );
		$tasks = get_option( 'mdjm_schedules' );

		if ( $tasks )	{
			foreach( $tasks as $slug => $task )	{
				new MDJM_Task_Runner( $slug );
			}
		}
	} // execute_cron
	
	/*
	 * Mark events as completed if the event date has passed
	 *
	 *
	 *
	 */
	public function complete_event()	{
		global $mdjm, $mdjm_settings;

		MDJM()->debug->log_it( '*** Starting the Complete Events task ***', true );

		$cron_start = microtime( true );

		$events = mdjm_get_events( array(
			'post_status' => 'mdjm-approved',
			'meta_key'    => '_mdjm_event_date',
			'orderby'     => 'meta_value',
			'order'       => 'ASC',
			'meta_query'  => array(
				'key'     => '_mdjm_event_date',
				'value'   => date( 'Y-m-d' ),
				'type'    => 'date',
				'compare' => '<'
			)
		) );

		$notify = array();
		$x = 0;

		if ( count( $events ) > 0 )	{ // Enquiries to process
			MDJM()->debug->log_it( count( $events ) . ' ' . _n( 'event', 'events', count( $events ) ) . ' to mark as completed' );
			
			remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
			
			/* -- Loop through the enquiries and update as completed -- */	
			foreach( $events as $event )	{
				$cronned = get_post_meta( $event->ID, '_mdjm_event_tasks', true );

				if ( ! empty( $cronned ) && $cronned != '' )	{
					$cron_update = json_decode( $cronned, true );
				}

				if ( ! empty( $cron_update ) && array_key_exists( 'complete-events', $cron_update ) )	{ // Task has already run for this event
					MDJM()->debug->log_it( 'This task has already run for this event (' . $event->ID . ')' );
					continue;
				}
					
				if ( empty( $cron_update ) || ! is_array( $cron_update ) )	{
					$cron_update = array();
				}

				$cron_update[ $this->schedules['complete-events']['slug'] ] = time();

				wp_update_post( array( 'ID' => $event->ID, 'post_status' => 'mdjm-completed' ) );
				
				update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
				update_post_meta( $event->ID, '_mdjm_event_tasks', json_encode( $cron_update ) );
				
				if ( mdjm_get_option( 'employee_auto_pay_complete' ) )	{
					mdjm_pay_event_employees( $event->ID );
				}
				
				/* -- Update Journal -- */
				if ( mdjm_get_option( 'journaling' ) )	{
					MDJM()->debug->log_it( '	-- Adding journal entry' );

					mdjm_add_journal(
						array(
							'user_id'         => 1,
							'event_id'        => $event->ID,
							'comment_content' => 'Event marked as completed via Scheduled Task <br /><br />' . time()
						),
						array(
							'type'       => 'update-event',
							'visibility' => '1',
						)
					);
				} // End if( MDJM_JOURNAL == true )
				else	{
					MDJM()->debug->log_it( '	-- Journalling is disabled' );	
				}
				
				$notify_dj    = isset( $this->schedules['complete-events']['options']['notify_dj'] ) ? $this->schedules['complete-events']['options']['notify_dj'] : '';
				$notify_admin = isset( $this->schedules['complete-events']['options']['notify_admin'] ) ? $this->schedules['complete-events']['options']['notify_admin'] : '';
				
				$client = get_post_meta( $event->ID, '_mdjm_event_client', true );
				$dj     = get_post_meta( $event->ID, '_mdjm_event_dj', true );

				$event_date   = get_post_meta( $event->ID, '_mdjm_event_date', true );
				$event_dj     = ! empty( $dj )     ? get_userdata( $dj )     : __( 'DJ not found', 'mobile-dj-manager' );
				$event_client = ! empty( $client ) ? get_userdata( $client ) : __( 'Client not found', 'mobile-dj-manager' );

				$venue_post_id = get_post_meta( $event->ID, '_mdjm_event_venue_id', true );

				$event_venue = MDJM()->events->mdjm_get_venue_details( $venue_post_id, $event->ID );	

				// Prepare admin notification email data array
				if ( ! empty( $notify_admin ) && $notify_admin == 'Y' )	{
					MDJM()->debug->log_it( '	-- Admin notifications are enabled' );

					if ( ! isset( $notify['admin'] ) || ! is_array( $notify['admin'] ) )	{
						$notify['admin'] = array();
					}

					$notify['admin'][ $event->ID ] = array(
						'id'     => $event->ID,
						'client' => $event_client->display_name,
						'venue'  => ! empty( $event_venue['name'] ) ? $event_venue['name'] : __( 'No Venue Set', 'mobile-dj-manager' ),
						'djinfo' => $event_dj,
						'date'   => ! empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : __( 'Date not found', 'mobile-dj-manager' )
					);
				} // End if( !empty( $notify_admin ) && $notify_admin == 'Y' )

				// Prepare DJ notification email data array
				if ( ! empty( $notify_dj ) && $notify_dj == 'Y' )	{
					MDJM()->debug->log_it( '	-- DJ notifications are enabled' );

					if ( ! isset( $notify['dj'] ) || !is_array( $notify['dj'] ) )	{
						$notify['dj'] = array();
					}

					$notify['dj'][ $dj ] = array();
					$notify['dj'][ $dj ][ $event->ID ] = array(
						'id'     => $event->ID,
						'client' => $event_client->display_name,
						'venue'  => ! empty( $event_venue['name'] ) ? $event_venue['name'] : __( 'No Venue Set', 'mobile-dj-manager' ),
						'djinfo' => $event_dj,
						'date'   => ! empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : __( 'Date not found', 'mobile-dj-manager' )
					);
						
				} // End if( !empty( $notify_dj ) && $notify_dj == 'Y' )
				
				$x++;
				
			} // End foreach
			$cron_end = microtime( true );
							
			// Prepare the Admin notification email
			if ( ! empty( $notify_admin ) && $notify_admin == 'Y' )	{
				$notify_email_args = array(
					'data'     => $notify['admin'],
					'taskinfo' => $this->schedules['complete-events'],
					'start'    => $cron_start,
					'end'      => $cron_end,
					'total'    => $x,
				); // $notify_email_args
									
				$mdjm->send_email( array(
					'content'  => $this->notification_content( $notify_email_args ),
					'to'       => $mdjm_settings['email']['system_email'],
					'subject'  => sanitize_text_field( $this->schedules['complete-events']['options']['email_subject'] ),
					'journal'  => false,
					'html'     => false,
					'cc_admin' => false,
					'cc_dj'    => false,
					'filter'   => false,
					'log_comm' => false,
				) );
			} // if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{

			// Prepare the DJ notification email
			if ( ! empty( $notify_dj ) && $notify_dj == 'Y' )	{
				foreach( $notify['dj'] as $notify_dj )	{
					foreach( $notify_dj as $dj )	{
						$notify_email_args = array(
							'data'     => $notify_dj,
							'taskinfo' => $this->schedules['complete-events'],
							'start'    => $cron_start,
							'end'      => $cron_end,
							'total'    => $x,
						); // $notify_email_args

						$mdjm->send_email( array(
							'content'  => $this->notification_content( $notify_email_args ),
							'to'       => $dj->ID,
							'subject'  => sanitize_text_field( $this->schedules['complete-events']['options']['email_subject'] ),
							'journal'  => false,
							'html'     => false,
							'cc_admin' => false,
							'cc_dj'    => false,
							'filter'   => false,
							'log_comm' => false,
						) );
					} // foreach( $notify_dj as $dj )
				} // foreach( $notify['dj'] as $notify_dj )
			} // if( !empty( $notify_dj ) && $notify_dj == 'Y' )
			
			add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
		} // if( count( $events ) > 0 )

		else	{
			MDJM()->debug->log_it( 'No events to mark as complete' );	
		}

		// Prepare next run time
		$this->update_nextrun( 'complete-events' );

		MDJM()->debug->log_it( '*** Completed the Complete Events task ***', true );

	} // complete_event
	
	/*
	 * Fail event enquiries that have been outstanding longer than the specified time
	 *
	 *
	 *
	 */
	public function fail_enquiry()	{
		global $mdjm, $mdjm_settings;
		
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( '*** Starting the Fail Enquiry task ***', true );
		
		$cron_start = microtime(true);
		
		$expired = date( 'Y-m-d', strtotime( "-" . $this->schedules['fail-enquiry']['options']['age'] ) );
		
		// Retrieve expired enquiries
		$enquiries = mdjm_get_events( array(
			'post_status' => 'mdjm-enquiry',
			'date_query'  => array(
				'before'	=> $expired
			)
		) );
		
		$notify = array();
		$x = 0;
		
		if( count( $enquiries ) > 0 )	{ // Enquiries to process
			MDJM()->debug->log_it( count( $enquiries ) . ' ' . _n( 'enquiry', 'enquiries', count( $enquiries ) ) . ' to expire' );
			
			remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
			
			/* -- Loop through the enquiries and update as failed -- */	
			foreach( $enquiries as $enquiry )	{
				$cronned = get_post_meta( $event->ID, '_mdjm_event_tasks', true );
				if( !empty( $cronned ) && $cronned != '' )
					$cron_update = json_decode( $cronned, true );
				
				if( array_key_exists( 'request-deposit', $cron_update ) ) // Task has already run for this event
					continue;
					
				if( !is_array( $cron_update ) ) $cron_update = array();
				
				$cron_update[$this->schedules['fail-enquiry']['slug']] = time();
				
				mdjm_update_event_status(
					$enquiry->ID,
					'mdjm-failed',
					$enquiry->post_status,
					array(
						'meta' => array(
							'_mdjm_event_tasks' => json_encode( $cron_update )
						)
					)
				);
				
				/* -- Update Journal -- */
				MDJM()->debug->log_it( '	-- Adding journal entry' );
						
				mdjm_add_journal(
					array(
						'user_id'         => 1,
						'event_id'        => $enquiry->ID,
						'comment_content' => 'Enquiry marked as lost via Scheduled Task <br /><br />' . time()
					),
					array(
						'type' 		  => 'update-event',
						'visibility'	=> '1',
					)
				);
				
				$notify_dj = isset( $this->schedules['fail-enquiry']['options']['notify_dj'] ) ? $this->schedules['fail-enquiry']['options']['notify_dj'] : '';
				$notify_admin = isset( $this->schedules['fail-enquiry']['options']['notify_admin'] ) ? $this->schedules['fail-enquiry']['options']['notify_admin'] : '';
				
				$client = get_post_meta( $enquiry->ID, '_mdjm_event_client', true );
				$dj = get_post_meta( $enquiry->ID, '_mdjm_event_dj', true );
				$event_date = get_post_meta( $enquiry->ID, '_mdjm_event_date', true );
				
				$event_dj = !empty( $dj ) ? get_userdata( $dj ) : 'DJ not found';
				$event_client = !empty( $client ) ? get_userdata( $client ) : 'Client not found';
				
				$venue_post_id = get_post_meta( $enquiry->ID, '_mdjm_event_venue_id', true );
				
				$event_venue = MDJM()->events->mdjm_get_venue_details( $venue_post_id, $enquiry->ID );	
			
				/* Prepare admin notification email data array */
				if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
					MDJM()->debug->log_it( '	-- Admin notifications are enabled' );
						
					if( !isset( $notify['admin'] ) || !is_array( $notify['admin'] ) ) $notify['admin'] = array();
					
					$notify['admin'][$enquiry->ID] = array(
															'id'		=> $enquiry->ID,
															'client'	=> $event_client->display_name,
															'venue'	 => !empty( $event_venue['name'] ) ? 
																$event_venue['name'] : 'No Venue Set',
															'djinfo'	=> $event_dj,
															'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
															);
				} // End if( !empty( $notify_admin ) && $notify_admin == 'Y' )
				
				/* Prepare DJ notification email data array */
				if( !empty( $notify_dj ) && $notify_dj == 'Y' )	{
					MDJM()->debug->log_it( '	-- DJ notifications are enabled' );
						
					if( !isset( $notify['dj'] ) || !is_array( $notify['dj'] ) ) $notify['dj'] = array();
					$notify['dj'][$dj] = array();
					$notify['dj'][$dj][$enquiry->ID] = array(
															'id'		=> $enquiry->id,
															'client'	=> $event_client->display_name,
															'venue'	 => !empty( $event_venue['name'] ) ? 
																$event_venue['name'] : 'No Venue Set',
															'djinfo'	=> $event_dj,
															'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
															);
						
				} // End if( !empty( $notify_dj ) && $notify_dj == 'Y' )
				
				$x++;
			} // End foreach
			
			$cron_end = microtime(true);
							
			/* -- Prepare the Admin notification email -- */
			if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
				$notify_email_args = array(
										'data'		=> $notify['admin'],
										'taskinfo'	=> $this->schedules['fail-enquiry'],
										'start'	   => $cron_start,
										'end'		 => $cron_end,
										'total'	   => $x,
									); // $notify_email_args
				$content = $this->notification_content( $notify_email_args );
									
				$mdjm->send_email( array(
										'content'	=> $content,
										'to'		 => $mdjm_settings['email']['system_email'],
										'subject'	=> sanitize_text_field( $this->schedules['fail-enquiry']['options']['email_subject'] ),
										'journal'	=> false,
										'html'	   => false,
										'cc_admin'   => false,
										'cc_dj'	  => false,
										'filter'   => false,
										'log_comm'   => false,
										) );
			}// if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
			
			/* -- Prepare the DJ notification email -- */
			if( !empty( $notify_dj ) && $notify_dj == 'Y' )	{
				foreach( $notify['dj'] as $notify_dj )	{
					foreach( $notify_dj as $dj )	{
						$notify_email_args = array(
												'data'		=> $notify_dj,
												'taskinfo'	=> $this->schedules['fail-enquiry'],
												'start'	   => $cron_start,
												'end'		 => $cron_end,
												'total'	   => $x,
											); // $notify_email_args
						$content = $this->notification_content( $notify_email_args );
						
						$mdjm->send_email( array(
												'content'	=> $content,
												'to'		 => $dj->ID,
												'subject'	=> sanitize_text_field( $this->schedules['fail-enquiry']['options']['email_subject'] ),
												'journal'	=> false,
												'html'	   => false,
												'cc_admin'   => false,
												'cc_dj'	  => false,
												'filter'   => false,
												'log_comm'   => false,
												) );
					} // foreach( $notify_dj as $dj )
				} // foreach( $notify['dj'] as $notify_dj )
			} // if( !empty( $notify_dj ) && $notify_dj == 'Y' )
			
			add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
		} // if( count( $enquiries ) > 0 )
		
		else	{
			MDJM()->debug->log_it( 'No enquiries to process as failed' );	
		}
					
		// Prepare next run time
		$this->update_nextrun( 'fail-enquiry' );
		
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( '*** Completed the Fail Enquiry task ***', true );
		
	} // fail_enquiry
	
	/*
	 * Request deposits from clients whose events are within the specified timeframe
	 * and where the deposit is still outstanding using defined email template
	 *
	 *
	 */
	public function request_deposit()	{
		global $mdjm, $mdjm_settings;
		
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( '*** Starting the Request Deposit task ***', true );
		
		$cron_start = microtime(true);
					
		$args = array(
					'posts_per_page'	=> -1,
					'post_type'		 => 'mdjm-event',
					'post_status'	   => 'mdjm-approved',
					'meta_query'		=> array(
											'relation'	=> 'AND',
												array(
													'key'		=> '_mdjm_event_deposit_status',
													'compare'	=> '==',
													'value'	  => 'Due',
												),
												array(
													'key'		=> '_mdjm_event_deposit',
													'value'	  => '0.00',
													'compare'	=> '>',
												),
												array(
													'key'		=> '_mdjm_event_tasks',
													'value'	  => 'request-deposit',
													'compare'	=> 'NOT IN',
												),
											),
					);
		
		// Retrieve events for which deposit is due
		$events = get_posts( $args );
		
		$notify = array();
		$x = 0;
		
		if( count( $events ) > 0 )	{ // Events to process
			if( MDJM_DEBUG == true )
				MDJM()->debug->log_it( count( $events ) . ' ' . _n( 'event', 'events', count( $events ) ) . ' where the deposit needs to be requested' );
			
			remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
			
			/* -- Loop through the enquiries and update as completed -- */	
			foreach( $events as $event )	{
				$cronned = get_post_meta( $event->ID, '_mdjm_event_tasks', true );
				if( !empty( $cronned ) && $cronned != '' )
					$cron_update = json_decode( $cronned, true );
				
				if( array_key_exists( 'request-deposit', $cron_update ) ) // Task has already run for this event
					continue;
					
				if( !is_array( $cron_update ) ) $cron_update = array();
				
				$cron_update[$this->schedules['request-deposit']['slug']] = time();
				
				wp_update_post( array( 'ID' => $event->ID, 'post_modified' => date( 'Y-m-d H:i:s' ) ) );
				
				update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
				update_post_meta( $event->ID, '_mdjm_event_tasks', json_encode( $cron_update ) );
				
				/* -- Update Journal -- */
				if( MDJM_JOURNAL == true )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( '	-- Adding journal entry' );
							
					mdjm_add_journal(
						array(
							'user_id'         => 1,
							'event_id'        => $event->ID,
							'comment_content' => mdjm_get_deposit_label() . ' request Scheduled Task executed<br /><br />' . time()
						),
						array(
							'type' 		  => 'added-note',
							'visibility'	=> '0',
						)
					);
				} // End if( MDJM_JOURNAL == true )
				else	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( '	-- Journalling is disabled' );	
				}
				
				$notify_dj = isset( $this->schedules['request-deposit']['options']['notify_dj'] ) ? $this->schedules['request-deposit']['options']['notify_dj'] : '';
				$notify_admin = isset( $this->schedules['request-deposit']['options']['notify_admin'] ) ? $this->schedules['request-deposit']['options']['notify_admin'] : '';
				
				$client = get_post_meta( $event->ID, '_mdjm_event_client', true );
				$dj = get_post_meta( $event->ID, '_mdjm_event_dj', true );
				$event_date = get_post_meta( $event->ID, '_mdjm_event_date', true );
				
				$event_dj = !empty( $dj ) ? get_userdata( $dj ) : 'DJ not found';
				$event_client = !empty( $client ) ? get_userdata( $client ) : 'Client not found';
				$event_deposit = get_post_meta( $event->ID, '_mdjm_event_deposit', true );
				$event_cost = get_post_meta( $event->ID, '_mdjm_event_cost', true );
				
				$venue_post_id = get_post_meta( $event->ID, '_mdjm_event_venue_id', true );
				
				$event_venue = MDJM()->events->mdjm_get_venue_details( $venue_post_id, $event->ID );	
			
				$contact_client = ( isset( $this->schedules['request-deposit']['options']['email_client'] ) 
					&& $this->schedules['request-deposit']['options']['email_client'] == 'Y'  ? true : false );
					
				$email_template = ( isset( $this->schedules['request-deposit']['options']['email_template'] ) && 
					is_string( get_post_status( $this->schedules['request-deposit']['options']['email_template'] ) ) ? 
					$this->schedules['request-deposit']['options']['email_template'] : false );
				
				/* -- Client Deposit Request Email -- */
				if( !empty( $contact_client ) && !empty( $email_template ) )	{ // Email the client
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Task ' . $this->schedules['request-deposit']['name'] . ' is configured to notify client that deposit is due' );
						
					$request = $mdjm->send_email( array( 
								'content'	=> $email_template,
								'to'		 => $event_client->ID,
								'from'	   => $mdjm_settings['templates']['enquiry_from'] == 'dj' ? $event_dj->ID : 0,
								'journal'	=> 'email-client',
								'event_id'   => $event->ID,
								'html'	   => true,
								'cc_dj'	  => isset( $mdjm_settings['email']['bcc_dj_to_client'] ) ? true : false,
								'cc_admin'   => isset( $mdjm_settings['email']['bcc_admin_to_client'] ) ? true : false,
								'source'	 => __( 'Request Deposit Scheduled Task' ),
							) );
					if( $request )	{
						if( MDJM_DEBUG == true )
							 MDJM()->debug->log_it( '	-- Deposit request sent to ' . $event_client->display_name . '. ' . $request . ' ID ' );
					}
					else	{
						if( MDJM_DEBUG == true )
							 MDJM()->debug->log_it( '	ERROR: Deposit request was not sent' );
					}
				}
				else	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( 'Task ' . $this->schedules['request-deposit']['name'] . ' is not configured to notify client' );	
				}
			
				/* Prepare admin notification email data array */
				if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( '	-- Admin notifications are enabled' );
						
					if( !isset( $notify['admin'] ) || !is_array( $notify['admin'] ) ) $notify['admin'] = array();
					
					$notify['admin'][$event->ID] = array(
															'id'		=> $event->ID,
															'client'	=> $event_client->display_name,
															'deposit'   => !empty( $event_deposit ) ? 
																			$event_deposit : '0',
															'cost'   => !empty( $event_cost ) ? 
																			$event_cost : '0',
															'venue'	 => !empty( $event_venue['name'] ) ? 
																$event_venue['name'] : 'No Venue Set',
															'djinfo'	=> $event_dj,
															'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
															);
				} // End if( !empty( $notify_admin ) && $notify_admin == 'Y' )
				
				/* Prepare DJ notification email data array */
				if( !empty( $notify_dj ) && $notify_dj == 'Y' && mdjm_employee_can( 'edit_txns' ) )	{
					if( MDJM_DEBUG == true )
						MDJM()->debug->log_it( '	-- DJ notifications are enabled' );
						
					if( !isset( $notify['dj'] ) || !is_array( $notify['dj'] ) ) $notify['dj'] = array();
					$notify['dj'][$dj] = array();
					$notify['dj'][$dj][$event->ID] = array(
															'id'		=> $event->id,
															'client'	=> $event_client->display_name,
															'deposit'   => !empty( $event_deposit ) ? 
																			$event_deposit : '0',
															'cost'   => !empty( $event_cost ) ? 
																			$event_cost : '0',
															'venue'	 => !empty( $event_venue['name'] ) ? 
																$event_venue['name'] : 'No Venue Set',
															'djinfo'	=> $event_dj,
															'date'	  => !empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : 'Date not found',
															);
						
				} // End if( !empty( $notify_dj ) && $notify_dj == 'Y' )
				
				$x++;
				
			} // End foreach
			$cron_end = microtime(true);
							
			/* -- Prepare the Admin notification email -- */
			if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
				$notify_email_args = array(
										'data'		=> $notify['admin'],
										'taskinfo'	=> $this->schedules['request-deposit'],
										'start'	   => $cron_start,
										'end'		 => $cron_end,
										'total'	   => $x,
									); // $notify_email_args
									
				$mdjm->send_email( array(
										'content'	=> $this->notification_content( $notify_email_args ),
										'to'		 => $mdjm_settings['email']['system_email'],
										'subject'	=> mdjm_get_deposit_label() . ' Request Scheduled Task Completed - ' . MDJM_APP,
										'journal'	=> false,
										'html'	   => false,
										'cc_admin'   => false,
										'cc_dj'	  => false,
										'filter'	 => false,
										'log_comm'   => false,
										) );
			}// if( !empty( $notify_admin ) && $notify_admin == 'Y' )	{
			
			/* -- Prepare the DJ notification email -- */
			if( !empty( $notify_dj ) && $notify_dj == 'Y' && mdjm_employee_can( 'edit_txns' ) )	{
				foreach( $notify['dj'] as $notify_dj )	{
					foreach( $notify_dj as $dj )	{
						$notify_email_args = array(
												'data'		=> $notify_dj,
												'taskinfo'	=> $this->schedules['request-deposit'],
												'start'	   => $cron_start,
												'end'		 => $cron_end,
												'total'	   => $x,
											); // $notify_email_args
																		
						$mdjm->send_email( array(
												'content'	=> $this->notification_content( $notify_email_args ),
												'to'		 => $dj->ID,
												'subject'	=> mdjm_get_deposit_label() . ' Request Scheduled Task Completed - ' . MDJM_APP,
												'journal'	=> false,
												'html'	   => false,
												'cc_admin'   => false,
												'cc_dj'	  => false,
												'filter'   => false,
												'log_comm'   => false,
												) );
					} // foreach( $notify_dj as $dj )
				} // foreach( $notify['dj'] as $notify_dj )
			} // if( !empty( $notify_dj ) && $notify_dj == 'Y' )
			
			add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
		} // if( count( $events ) > 0 )
		else	{
			if( MDJM_DEBUG == true )
				MDJM()->debug->log_it( 'No deposits are due' );	
		}
					
		// Prepare next run time
		$this->update_nextrun( 'request-deposit' );
		
		if( MDJM_DEBUG == true )
			MDJM()->debug->log_it( '*** Completed the Request Deposit task ***', true );
		
	} // request_deposit
	
	/*
	 * Request balance payment from clients whose events are within the specified timeframe
	 * using defined email template
	 *
	 *
	 */
	public function balance_reminder()	{
		global $mdjm;
		
		MDJM()->debug->log_it( '*** Starting the Request Balance task ***', true );
		
		$cron_start = microtime( true );
		$notify     = array();
		$x          = 0;
		$options    = $this->schedules['balance-reminder']['options'];
		
		// Calculate the time period for which the task should run
		$due_date = date( 'Y-m-d', strtotime( "-" . $options['age'] ) );

		$events = mdjm_get_events( array(
			'post_status' => 'mdjm-approved',
			'meta_query'  => array(
				'relation' => 'AND',
					array(
						'key'     => '_mdjm_event_date',
						'compare' => '>=',
						'value'   => $due_date,
						'type'    => 'date'
					),
					array(
						'key'     => '_mdjm_event_balance_status',
						'value'   => 'Due'
					),
					array(
						'key'     => '_mdjm_event_cost',
						'value'   => '0.00',
						'compare' => '>'
					),
					array(
						'key'     => '_mdjm_event_tasks',
						'value'   => 'balance-reminder',
						'compare' => 'NOT IN'
					)
				)
		) );

		if ( count( $events ) > 0 )	{ // Events to process
			MDJM()->debug->log_it( count( $events ) . ' ' . _n( 'event', 'events', count( $events ) ) . ' where the balance is due' );

			remove_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );

			// Loop through the enquiries and update as completed
			foreach( $events as $_event )	{
				$event = new MDJM_Event( $_event->ID );

				if ( ! $event )	{
					return;
				}

				$tasks = $event->get_tasks();
				
				if ( ! empty( $tasks ) && array_key_exists( 'balance-reminder', $tasks ) )	{
					continue;
				}

				wp_update_post( array( 'ID' => $event->ID, 'post_modified' => date( 'Y-m-d H:i:s' ) ) );

				update_post_meta( $event->ID, '_mdjm_event_last_updated_by', 0 );
				$event->complete_task( 'balance-reminder' );

				// Update Journal
				mdjm_add_journal(
					array(
						'user_id'         => 1,
						'event_id'        => $event->ID,
						'comment_content' => mdjm_get_balance_label() . ' Reminder Scheduled Task executed<br /><br />' . time()
					),
					array(
						'type'       => 'added-note',
						'visibility' => '0',
					) );

				$notify_dj    = isset( $options['notify_dj'] ) ? $options['notify_dj'] : '';
				$notify_admin = isset( $options['notify_dj'] ) ? $options['notify_dj'] : '';

				$client     = $event->client;
				$dj         = $event->employee_id;
				$event_date = $event->date;

				$event_dj      = ! empty( $dj )     ? get_userdata( $dj )     : __( 'DJ not found', 'mobile-dj-manager' );
				$event_client  = ! empty( $client ) ? get_userdata( $client ) : __( 'Client not found', 'mobile-dj-manager' );
				$event_deposit = $event->deposit;
				$event_cost    = $event->price;

				$venue_post_id = $event->get_venue_id();

				$contact_client = ( isset( $options['email_client'] ) && $options['email_client'] == 'Y'  ? true : false );

				if ( isset( $options['email_template'] ) && is_string( get_post_status( $options['email_template'] ) ) )	{
					$email_template = $options['email_template'];
				} else	{
					$email_template = false;
				}
					
				// Client Balance Request Email
				if ( $contact_client && $email_template )	{
					MDJM()->debug->log_it( 'Task ' . $this->schedules['balance-reminder']['name'] . ' is configured to notify client that balance is due' );

					$from_email = mdjm_get_option( 'system_email' );
					$from_name  = mdjm_get_option( 'company_name' );

					if ( 'dj' == $options['email_from'] )	{
						$from_email = $event_dj->user_email;
						$from_name  = $event_dj->display_name;
					}

					$email_args = array(
						'to_email'       => $event_client->user_email,
						'from_name'      => $from_name,
						'from_email'     => $from_email,
						'event_id'       => $event->ID,
						'client_id'      => $event->client,
						'subject'        => $options['email_subject'],
						'message'        => mdjm_get_email_template_content( $email_template ),
						'track'          => true,
						'source'         => __( 'Request ' . mdjm_get_balance_label() . ' Scheduled Task' )
					);

					if ( mdjm_send_email_content( $email_args ) )	{
						 MDJM()->debug->log_it( '	-- Balance reminder sent to ' . $event_client->display_name . '. ' . $request . ' ID ' );
					} else	{
						 MDJM()->debug->log_it( '	ERROR: Balance reminder was not sent' );
					}
				} else	{
					MDJM()->debug->log_it( 'Task ' . $this->schedules['balance-reminder']['name'] . ' is not configured to notify client' );	
				}
			
				// Prepare admin notification email data array
				if ( ! empty( $notify_admin ) && $notify_admin == 'Y' )	{
					MDJM()->debug->log_it( '	-- Admin notifications are enabled' );
						
					if ( ! isset( $notify['admin'] ) || ! is_array( $notify['admin'] ) )	{
						$notify['admin'] = array();
					}

					$notify['admin'][ $event->ID ] = array(
						'id'      => $event->ID,
						'client'  => $event_client->display_name,
						'deposit' => ! empty( $event_deposit ) ? $event_deposit : '0',
						'cost'    => ! empty( $event_cost ) ? $event_cost : '0',
						'venue'   => ! empty( $event_venue['name'] ) ? $event_venue['name'] : __( 'No Venue Set', 'mobile-dj-manager' ),
						'djinfo'  => $event_dj,
						'date'    => ! empty( $event_date ) ? date( "d M Y", strtotime( $event_date ) ) : __( 'Date not found', 'mobile-dj-manager' )
					);
				}

				// Prepare DJ notification email data array
				if ( ! empty( $notify_dj ) && $notify_dj == 'Y' && mdjm_employee_can( 'edit_txns' ) )	{
					MDJM()->debug->log_it( '	-- DJ notifications are enabled' );
						
					if ( ! isset( $notify['dj'] ) ||  !is_array( $notify['dj'] ) )	{
						$notify['dj'] = array();
					}

					$notify['dj'][ $dj ] = array();
					$notify['dj'][ $dj ][ $event->ID ] = array(
						'id'      => $event->id,
						'client'  => $event_client->display_name,
						'deposit' => ! empty( $event_deposit ) ? $event_deposit : '0',
						'cost'    => ! empty( $event_cost )    ? $event_cost : '0',
						'venue'   => ! empty( $event_venue['name'] ) ? $event_venue['name'] : __( 'No Venue Set', 'mobile-dj-manager' ),
						'djinfo'  => $event_dj,
						'date'    => ! empty( $event_date )    ? date( "d M Y", strtotime( $event_date ) ) : __( 'Date not found', 'mobile-dj-manager' )
					);

				}

				$x++;

			}
			$cron_end = microtime( true );

			// Prepare the Admin notification email
			if ( ! empty( $notify_admin ) && $notify_admin == 'Y' )	{
				$notify_email_args = array(
					'data'     => $notify['admin'],
					'taskinfo' => $this->schedules['balance-reminder'],
					'start'    => $cron_start,
					'end'      => $cron_end,
					'total'    => $x
				);

				$mdjm->send_email( array(
					'content'  => $this->notification_content( $notify_email_args ),
					'to'       => mdjm_get_option( 'system_email' ),
					'subject'  => 'Balance Reminder Scheduled Task Completed - ' . MDJM_APP,
					'journal'  => false,
					'html'     => false,
					'cc_admin' => false,
					'cc_dj'    => false,
					'filter'   => false,
					'log_comm' => false
				) );
			}

			// Prepare the DJ notification email
			if ( ! empty( $notify_dj ) && $notify_dj == 'Y' && mdjm_employee_can( 'edit_txns' ) )	{

				foreach( $notify['dj'] as $notify_dj )	{

					foreach( $notify_dj as $dj )	{

						$notify_email_args = array(
							'data'		=> $notify_dj,
							'taskinfo'	=> $this->schedules['balance-reminder'],
							'start'	   => $cron_start,
							'end'		 => $cron_end,
							'total'	   => $x,
						); // $notify_email_args

						$mdjm->send_email( array(
							'content'	=> $this->notification_content( $notify_email_args ),
							'to'		 => $dj->ID,
							'subject'	=> 'Balance Reminder Scheduled Task Completed - ' . MDJM_APP,
							'journal'	=> false,
							'html'	   => false,
							'cc_admin'   => false,
							'cc_dj'	  => false,
							'filter'   => false,
							'log_comm'   => false,
						) );

					}

				}
			}

			add_action( 'save_post_mdjm-event', 'mdjm_save_event_post', 10, 3 );
		} else	{
			MDJM()->debug->log_it( 'No balances are due' );
		}

		// Prepare next run time
		$this->update_nextrun( 'balance-reminder' );

		MDJM()->debug->log_it( '*** Completed the Balance Reminder task ***', true );

	} // balance_reminder
	
	/**
	 * Update the cron task following execution setting next run time.
	 *
	 * @since	0.7
	 * @param	str		$task	The slug of the task to update
	 * @return	void
	 */
	public function update_nextrun( $task )	{
		
		$mdjm_schedules = get_option( 'mdjm_schedules' );
		
		$mdjm_schedules[ $task ]['lastran'] = time();
		$time = current_time( 'timestamp' );
		
		if( isset( $mdjm_schedules[ $task ]['frequency'] ) && $mdjm_schedules[ $task ]['frequency'] == 'Hourly')
			$mdjm_schedules[ $task ]['nextrun'] = strtotime( "+1 hour", $time );
		
		elseif( isset( $mdjm_schedules[ $task ]['frequency'] ) && $mdjm_schedules[ $task ]['frequency'] == 'Daily')
			$mdjm_schedules[ $task ]['nextrun'] = strtotime( "+1 day", $time );
			
		elseif( isset( $mdjm_schedules[ $task ]['frequency'] ) && $mdjm_schedules[ $task ]['frequency'] == 'Twice Daily')
			$mdjm_schedules[ $task ]['nextrun'] = strtotime( "+12 hours", $time );
		
		elseif( isset( $mdjm_schedules[ $task ]['frequency'] ) && $mdjm_schedules[ $task ]['frequency'] == 'Weekly')
			$mdjm_schedules[ $task ]['nextrun'] = strtotime( "+1 week", $time );
		
		elseif( isset( $mdjm_schedules[ $task ]['frequency'] ) && $mdjm_schedules[ $task ]['frequency'] == 'Monthly')
			$mdjm_schedules[ $task ]['nextrun'] = strtotime( "+1 month", $time );
		
		elseif( isset( $mdjm_schedules[ $task ]['frequency'] ) && $mdjm_schedules[ $task ]['frequency'] == 'Yearly')
			$mdjm_schedules[ $task ]['nextrun'] = strtotime( "+1 year", $time );
		
		else /* It should not run again */
			$mdjm_schedules[ $task ]['nextrun'] = 'N/A';
		
		$mdjm_schedules[ $task ]['totalruns'] = $mdjm_schedules[ $task ]['totalruns'] + 1;

		update_option( 'mdjm_schedules', $mdjm_schedules );
		
	} // update_nextrun
	
	/*
	 * Build the notification email content
	 *
	 * @param	arr		$task		The current task array
	 * @return	str		$content	The content of the email
	 */
	public function notification_content( $task )	{
		global $mdjm, $mdjm_settings;
					
		if( empty( $task ) )	{
			if( MDJM_DEBUG == true )
				MDJM()->debug->log_it( 'ERROR: No task was parsed ' . __METHOD__ );
		}
		else	{
			if( MDJM_DEBUG == true )
				MDJM()->debug->log_it( 'Creating notification content for ' . $task['taskinfo']['name'] );	
		}
		
		/* -- Start the email content -- */
		$content = 'The ' . $task['taskinfo']['name'] . ' scheduled task from ' . MDJM_COMPANY . ' has completed. ' . "\r\n" . 
				"\r\n" . 
				'Task Start time: ' . date( 'H:i:s l, jS F Y', $task['start'] ) . "\r\n" . 
				"\r\n";
		/* Build the email content relating to the current task */
		switch( $task['taskinfo']['slug'] )	{
			case 'complete-events': // Notification content for Complete Events task
				$content .= $task['total'] . ' event(s) have been marked as completed...' . "\r\n" . 
					"\r\n" . 
					'----------------------------------------' . 
					'----------------------------------------' . "\r\n";
				/* -- List each event -- */
				foreach ( $task['data'] as $eventinfo )	{
					$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
						'Date: ' . $eventinfo['date'] . "\r\n" . 
						'Venue: ' . $eventinfo['venue'] . "\r\n" . 
						'Client: ' . $eventinfo['client'] . "\r\n" . 
						'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
						'Link: ' . get_edit_post_link( $eventinfo['id'], '' ) . "\r\n" . 
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
				} // End Foreach
			break;
			
			case 'fail-enquiry':
				$content .= $task['total'] . ' enquiry(s) have been marked as lost...' . "\r\n" . 
					"\r\n" . 
					'----------------------------------------' . 
					'----------------------------------------' . "\r\n";
				foreach ( $task['data'] as $eventinfo )	{
					$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
						'Date: ' . $eventinfo['date'] . "\r\n" . 
						'Client: ' . $eventinfo['client'] . "\r\n" . 
						'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
						'Link: ' . get_edit_post_link( $eventinfo['id'], '' ) . "\r\n" .
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
				} // End Foreach
			break;
			
			case 'request-deposit':
				$content .= $task['total'] . ' deposit requests ' . 
					( !empty( $task['taskinfo']['options']['email_client'] ) && $task['taskinfo']['options']['email_client'] == 'Y' ? 
					' have been sent' : ' need to be requested' ) . "\r\n" . 
					"\r\n" . 
					'----------------------------------------' . 
					'----------------------------------------' . "\r\n";
				foreach ( $task['data'] as $eventinfo )	{
					$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
						'Date: ' . $eventinfo['date'] . "\r\n" . 
						'Client: ' . $eventinfo['client'] . "\r\n" . 
						'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
						mdjm_get_deposit_label() . ': ' . mdjm_currency_filter( mdjm_sanitize_amount( $eventinfo['deposit'] ) ) . "\r\n" . 
						'Link: ' . get_edit_post_link( $eventinfo['id'], '' ) . "\r\n" .
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
				} // End Foreach
			break;
			
			case 'balance-reminder':
				$content .= $task['total'] . ' balance requests have been sent' . "\r\n" . 
					"\r\n" . 
					'----------------------------------------' . 
					'----------------------------------------' . "\r\n";
				foreach ( $task['data'] as $eventinfo )	{
					$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
						'Date: ' . $eventinfo['date'] . "\r\n" . 
						'Client: ' . $eventinfo['client'] . "\r\n" . 
						'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
						mdjm_get_balance_label() . ' Due: ' . mdjm_currency_filter( mdjm_sanitize_amount( $eventinfo['cost'] - $eventinfo['deposit'] ) ) . "\r\n" . 
						'Link: ' . get_edit_post_link( $eventinfo['id'], '' ) . "\r\n" .
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
				} // End Foreach
			break;
			
			case 'client-feedback':
				$content .= $task['total'] . ' client feedback requests have been sent' . "\r\n" . 
					"\r\n" . 
					'----------------------------------------' . 
					'----------------------------------------' . "\r\n";
				foreach ( $task['data'] as $eventinfo )	{
					$content .= 'Event ID: ' . $eventinfo['id'] . "\r\n" . 
						'Date: ' . $eventinfo['date'] . "\r\n" . 
						'Client: ' . $eventinfo['client'] . "\r\n" . 
						'DJ: ' . $eventinfo['djinfo']->display_name . "\r\n" . 
						'----------------------------------------' . 
						'----------------------------------------' . "\r\n";
				}
			break;
		} // Switch
		/* -- Complete the email content -- */
		$content .= 'Task End time: ' . date( 'H:i:s l, jS F Y', $task['end'] ) . "\r\n" . 
					"\r\n" . 
					'This email was generated by the MDJM Event Management for WordPress plugin - http://mdjm.co.uk';
		
		/* -- Return the content -- */
		return $content;
	} // notification_content

	/*
	 * Setup the tasks
	 *
	 * @since	1.3
	 * @param
	 * @return	void
	 */
	public function create_tasks()	{
		global $mdjm_options;
		
		$time = current_time( 'timestamp' );
		
		if( isset( $mdjm_options['upload_playlists'] ) )	{
			$playlist_nextrun = strtotime( '+1 day', $time );
		} else	{
			$playlist_nextrun = 'N/A';
		}
		
		$mdjm_schedules = array(
			'complete-events'    => array(
				'slug'           => 'complete-events',
				'name'           => 'Complete Events',
				'active'         => true,
				'desc'           => sprintf( __( 'Mark %s as completed once the %s date has passed', 'mobile-dj-manager' ), mdjm_get_label_plural( true ), mdjm_get_label_singular( true ) ),
				'frequency'      => 'Daily',
				'nextrun'        => $time,
				'lastran'        => 'Never',
				'options'        => array(
					'email_client'   => false,
					'email_template' => '0',
					'email_subject'  => sprintf( __( 'Task Complete %s has finished', 'mobile-dj-manager' ), mdjm_get_label_plural() ),
					'email_from'     => 'admin',
					'run_when'       => 'after_event',
					'age'            => '1 HOUR',
					'notify_admin'   => true,
					'notify_dj'      => false,
				),
				'function'       => 'complete_event',
				'totalruns'      => '0',
				'default'        => true,
				'last_result'    => false
			),			
			'request-deposit'    => array(
				'slug'           => 'request-deposit',
				'name'           => 'Request Deposit',
				'active'         => false,
				'desc'           => sprintf( __( 'Send reminder email to client requesting deposit payment if %s status is Approved and deposit has not been received', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'frequency'      => 'Daily',
				'nextrun'        => 'N/A',
				'lastran'        => 'Never',
				'options'        => array(
					'email_client'   => true,
					'email_template' => '0',
					'email_subject'  => __( 'Request Deposit Task Complete', 'mobile-dj-manager' ),
					'email_from'	 => 'admin',
					'run_when'	   => 'after_approval',
					'age'			=> '3 DAY',
					'notify_admin'   => true,
					'notify_dj' 	  => false,
				),
				'function'       => 'request_deposit',
				'totalruns'      => '0',
				'default'        => true,
				'last_result'    => false
			),			
			'balance-reminder'    => array(
				'slug'            => 'balance-reminder',
				'name'            => __( 'Balance Reminder', 'mobile-dj-manager' ),
				'active'          => false,
				'desc'            => sprintf( __( 'Send email to client requesting they pay remaining balance for %s', 'mobile-dj-manager' ), mdjm_get_label_singular( true ) ),
				'frequency'       => 'Daily',
				'nextrun'         => 'N/A',
				'lastran'         => 'Never',
				'options'         => array(
					'email_client'   => true,
					'email_template' => '0',
					'email_subject'  => __( 'Balance Reminder Task Complete', 'mobile-dj-manager' ),
					'email_from'     => 'admin',
					'run_when'       => 'before_event',
					'age'            => '2 WEEK',
					'notify_admin'   => true,
					'notify_dj'      => false,
				),
				'function'        => 'balance_reminder',
				'totalruns'       => '0',
				'default'         => true,
				'last_result'     => false
			), 
			'fail-enquiry'         => array(
				'slug'             => 'fail-enquiry',
				'name'             => __( 'Fail Enquiry', 'mobile-dj-manager' ),
				'active'           => false,
				'desc'             => __( 'Automatically fail enquiries that have not been updated within the specified amount of time', 'mobile-dj-manager' ),
				'frequency'        => 'Daily',
				'nextrun'          => 'N/A',
				'lastran'          => 'Never',
				'options'          => array(
					'email_client'   => false,
					'email_template' => '0',
					'email_subject'  => __( 'Fail Enquiry Task Complete', 'mobile-dj-manager' ),
					'email_from'	 => 'admin',
					'run_when'	   => 'event_created',
					'age'			=> '2 WEEK',
					'notify_admin'   => true,
					'notify_dj' 	  => false,
				),
				'function'           => 'fail_enquiry',
				'totalruns'          => '0',
				'default'            => true,
				'last_result'        => false
			),		
			'upload-playlists'      => array(
				'slug'              => 'upload-playlists',
				'name'              => __( 'Upload Playlists', 'mobile-dj-manager' ),
				'active'            => true,
				'desc'              => __( 'Transmit playlist information back to the MDJM servers to help build an information library. This option is updated the MDJM Settings pages.', 'mobile-dj-manager' ),
				'frequency'         => 'Twice Daily',
				'nextrun'           => $playlist_nextrun,
				'lastran'           => 'Never',
				'options'           => array(
					'email_client'    => false,
					'email_template'  => '0',
					'email_subject'   => '0',
					'email_from'      => '0',
					'run_when'        => 'after_event',
					'age'             => '1 HOUR',
					'notify_admin'    => false,
					'notify_dj'       => false,
				),
				'function'            => 'submit_playlist',
				'totalruns'           => '0',
				'default'             => true,
				'last_result'         => false
			)
		);
		
		update_option( 'mdjm_schedules', $mdjm_schedules );
	} // create_tasks

} // class