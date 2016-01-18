<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_Post_Types
 * Register our custom post types, taxonomies and statuses
 *
 *
 */
if( !class_exists( 'MDJM_Post_Types' ) ) :
	class MDJM_Post_Types	{
		/**
		 * Hook in methods.
		 */
		public static function init()	{			
			add_action( 'init', array( __CLASS__, 'register_post_types' ) );
			add_action( 'init', array( __CLASS__, 'register_post_status' ) );
			add_action( 'init', array( __CLASS__, 'register_post_taxonomies' ) );		
		} // init
		
		/**
		 * Register the custom post types
		 */
		public static function register_post_types()	{
			global $mdjm_posts;
			/**
			 * Register the mdjm_communication custom post type for communications
			 */
			if( !post_type_exists( MDJM_COMM_POSTS ) )	{
				register_post_type( MDJM_COMM_POSTS,
					 array(
						'labels'	=> array(
							'name'               => _x( 'Email History', 'post type general name', 'mobile-dj-manager' ),
							'singular_name'      => _x( 'Email History', 'post type singular name', 'mobile-dj-manager' ),
							'menu_name'          => _x( 'Email History', 'admin menu', 'mobile-dj-manager' ),
							'name_admin_bar'     => _x( 'Email History', 'add new on admin bar', 'mobile-dj-manager' ),
							'add_new'            => __( 'Add Communication', 'mobile-dj-manager' ),
							'add_new_item'       => __( 'Add New Communication', 'mobile-dj-manager' ),
							'new_item'           => __( 'New Communication', 'mobile-dj-manager' ),
							'edit_item'          => __( 'Review Email', 'mobile-dj-manager' ),
							'view_item'          => __( 'View Email', 'mobile-dj-manager' ),
							'all_items'          => __( 'All Emails', 'mobile-dj-manager' ),
							'search_items'       => __( 'Search Emails', 'mobile-dj-manager' ),
							'not_found'          => __( 'No Emails found.', 'mobile-dj-manager' ),
							'not_found_in_trash' => __( 'No Emails found in Trash.', 'mobile-dj-manager' ) ),
						'description'			=> __( 'Communication used by the Mobile DJ Manager for WordPress plugin', 'mobile-dj-manager' ),
						'public'			 	 => false,
						'exclude_from_search'	=> true,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_COMM_POSTS,
						'show_in_admin_bar'	  => false,
						'rewrite' 			    => array( 'slug' => 'mdjm-communications' ),
						'query_var'		 	  => true,
						'capability_type'    	=> 'post',
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'register_meta_box_cb'   => array( $mdjm_posts, 'define_metabox' ) ) );
			} // Communications
			
			/**
			 * Register the contract custom post type for contract templates
			 */
			if( !post_type_exists( MDJM_CONTRACT_POSTS ) )	{
				register_post_type( MDJM_CONTRACT_POSTS,
					array(
						'labels'	=> array(
							'name'               => _x( 'Contract Templates', 'post type general name', 'mobile-dj-manager' ),
							'singular_name'      => _x( 'Contract Template', 'post type singular name', 'mobile-dj-manager' ),
							'menu_name'          => _x( 'Contract Templates', 'admin menu', 'mobile-dj-manager' ),
							'name_admin_bar'     => _x( 'Contract Template', 'add new on admin bar', 'mobile-dj-manager' ),
							'add_new'            => __( 'Add Contract Template', 'mobile-dj-manager' ),
							'add_new_item'       => __( 'Add New Contract Template', 'mobile-dj-manager' ),
							'new_item'           => __( 'New Contract Template', 'mobile-dj-manager' ),
							'edit_item'          => __( 'Edit Contract Template', 'mobile-dj-manager' ),
							'view_item'          => __( 'View Contract Template', 'mobile-dj-manager' ),
							'all_items'          => __( 'All Contract Templates', 'mobile-dj-manager' ),
							'search_items'       => __( 'Search Contract Templates', 'mobile-dj-manager' ),
							'not_found'          => __( 'No contract templates found.', 'mobile-dj-manager' ),
							'not_found_in_trash' => __( 'No contract templates found in Trash.', 'mobile-dj-manager' ) ),
						'description'			=> __( 'Contracts used by the MDJM plugin', 'mobile-dj-manager' ),
						'public'			 	 => false,
						'exclude_from_search'	=> true,
						'show_ui'				=> true,
						'show_in_menu'	   	   => 'edit.php?post_type=' . MDJM_CONTRACT_POSTS,
						'query_var'		  	  => true,
						'rewrite'            	=> array( 'slug' => 'contract' ),
						'capability_type'    	=> 'post',
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'      	  => 5,
						'supports'           	   => array( 'title', 'editor', 'revisions' ),
						'menu_icon'		  	  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'register_meta_box_cb'   => array( $mdjm_posts, 'define_metabox' ) ) );
			}
			
			/**
			 * Register the mdjm-signed-contract custom post type for signed contracts
			 */
			if( !post_type_exists( MDJM_SIGNED_CONTRACT_POSTS ) )	{
				register_post_type( MDJM_SIGNED_CONTRACT_POSTS,
				array(
					'labels'	=> array(
						'name'               => _x( 'Signed Contracts', 'post type general name', 'mobile-dj-manager' ),
						'singular_name'      => _x( 'Signed Contract', 'post type singular name', 'mobile-dj-manager' ),
						'menu_name'          => _x( 'Signed Contracts', 'admin menu', 'mobile-dj-manager' ),
						'name_admin_bar'     => _x( 'Signed Contract', 'add new on admin bar', 'mobile-dj-manager' ),
						'add_new'            => __( 'Add Signed Contract', 'mobile-dj-manager' ),
						'add_new_item'       => __( 'Add New Signed Contract', 'mobile-dj-manager' ),
						'new_item'           => __( 'New Signed Contract', 'mobile-dj-manager' ),
						'edit_item'          => __( 'Edit Signed Contract', 'mobile-dj-manager' ),
						'view_item'          => __( 'View Signed Contract', 'mobile-dj-manager' ),
						'all_items'          => __( 'All Signed Contracts', 'mobile-dj-manager' ),
						'search_items'       => __( 'Search Signed Contracts', 'mobile-dj-manager' ),
						'not_found'          => __( 'No signed contracts found.', 'mobile-dj-manager' ),
						'not_found_in_trash' => __( 'No signed contracts found in Trash.', 'mobile-dj-manager' ) ),
					'description'			=>  __( 'Signed Contracts used by the MDJM plugin', 'mobile-dj-manager' ),
					'public'			 	 => false,
					'exclude_from_search'	=> true,
					'publicly_queryable' 	 => true,
					'show_ui'				=> false,
					'show_in_menu'	   	   => false,
					'query_var'		  	  => true,
					'rewrite'            	=> array( 'slug' => 'mdjm-signed-contract' ),
					'capability_type'    	=> array( 'mdjm_signed_contract', 'mdjm_signed_contracts' ),
					'map_meta_cap'		   => true,
					'has_archive'        	=> true,
					'hierarchical'       	   => false,
					'menu_position'      	  => 5,
					'supports'           	   => array( 'title' ),
					'menu_icon'		  	  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
					'register_meta_box_cb'   => array( $mdjm_posts, 'define_metabox' ) ) );
			}
			
			/**
			 * Register the mdjm-custom-fields custom post type for custom event fields
			 */
			if( !post_type_exists( MDJM_CUSTOM_FIELD_POSTS ) )	{
				register_post_type( MDJM_CUSTOM_FIELD_POSTS,
					array(
						'labels'	=> array(
							'name'               => _x( 'Custom Event Fields', 'post type general name', 'mobile-dj-manager' ),
							'singular_name'      => _x( 'Custom Event Field', 'post type singular name', 'mobile-dj-manager' ),
							'menu_name'          => _x( 'Custom Event Fields', 'admin menu', 'mobile-dj-manager' ),
							'add_new'            => _x( 'Add Custom Event Field', 'add new on admin bar', 'mobile-dj-manager' ),
							'add_new_item'       => __( 'Add New Custom Event Field' ),
							'edit'               => __( 'Edit Custom Event Field' ),
							'edit_item'          => __( 'Edit Custom Event Field' ),
							'new_item'           => __( 'New Hosted Plugin' ),
							'view'               => __( 'View Custom Event Field' ),
							'view_item'          => __( 'View Custom Event Field' ),
							'search_items'       => __( 'Search Custom Event Field' ),
							'not_found'          => __( 'No Custom Event Fields found' ),
							'not_found_in_trash' => __( 'No Custom Event Fields found in trash' ) ),
						'description'         	=> __( 'This is where you can add Custom Event Fields for use in the event screen.', 'mobile-dj-manager' ),
						'public'			 	 => false,
						'exclude_from_search'	=> true,
						'show_ui'             	=> false,
						'publicly_queryable'  	 => false,
						'exclude_from_search' 	=> false,
						'hierarchical'           => false,
						'rewrite' 			 	=> array( 'slug' => 'mdjm-custom-fields' ),
						'query_var'		   	  => true,
						'supports'               => array( 'title' ),
						'has_archive'         	=> false,
						'show_in_menu'		   => false,
						'capability_type'    	=> 'post',
						'show_in_nav_menus'   	  => false ) );
			} // Custom fields
						
			/**
			 * Register the email_template custom post type for email templates
			 */
			if( !post_type_exists( MDJM_EMAIL_POSTS ) )	{
				register_post_type( MDJM_EMAIL_POSTS,
					array(
						'labels'	=> array(
							'name'               => _x( 'Email Templates', 'post type general name', 'mobile-dj-manager' ),
							'singular_name'      => _x( 'Email Template', 'post type singular name', 'mobile-dj-manager' ),
							'menu_name'          => _x( 'Email Templates', 'admin menu', 'mobile-dj-manager' ),
							'name_admin_bar'     => _x( 'Email Template', 'add new on admin bar', 'mobile-dj-manager' ),
							'add_new'            => __( 'Add Template', 'mobile-dj-manager' ),
							'add_new_item'       => __( 'Add New Template', 'mobile-dj-manager' ),
							'new_item'           => __( 'New Template', 'mobile-dj-manager' ),
							'edit_item'          => __( 'Edit Template', 'mobile-dj-manager' ),
							'view_item'          => __( 'View Template', 'mobile-dj-manager' ),
							'all_items'          => __( 'All Templates', 'mobile-dj-manager' ),
							'search_items'       => __( 'Search Templates', 'mobile-dj-manager' ),
							'not_found'          => __( 'No templates found.', 'mobile-dj-manager' ),
							'not_found_in_trash' => __( 'No templates found in Trash.', 'mobile-dj-manager' ) ),
						'description'			=> __( 'Email Templates for the Mobile DJ Manager plugin', 'mobile-dj-manager' ),
						'public'			 	 => false,
						'exclude_from_search'	=> true,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_EMAIL_POSTS,
						'show_in_admin_bar'	  => true,
						'query_var'		 	  => true,
						'rewrite'            	=> array( 'slug' => 'email-template' ),
						'capability_type'    	=> 'post',
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title', 'editor', 'revisions' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'register_meta_box_cb'   => array( $mdjm_posts, 'define_metabox' ) ) );
			}
			
			/**
			 * Register the mdjm-event custom post type for events
			 */
			if( !post_type_exists( MDJM_EVENT_POSTS ) )	{
				register_post_type( MDJM_EVENT_POSTS,
					array(
						'labels'	=> array(
							'name'               => _x( 'Events', 'post type general name', 'mobile-dj-manager' ),
							'singular_name'      => _x( 'Event', 'post type singular name', 'mobile-dj-manager' ),
							'menu_name'          => _x( 'Events', 'admin menu', 'mobile-dj-manager' ),
							'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'mobile-dj-manager' ),
							'add_new'            => __( 'Create Event', 'mobile-dj-manager' ),
							'add_new_item'       => __( 'Create New Event', 'mobile-dj-manager' ),
							'new_item'           => __( 'New Event', 'mobile-dj-manager' ),
							'edit_item'          => __( 'Edit Event', 'mobile-dj-manager' ),
							'view_item'          => __( 'View Event', 'mobile-dj-manager' ),
							'all_items'          => __( 'All Events', 'mobile-dj-manager' ),
							'search_items'       => __( 'Search Events', 'mobile-dj-manager' ),
							'not_found'          => __( 'No events found.', 'mobile-dj-manager' ),
							'not_found_in_trash' => __( 'No events found in Trash.', 'mobile-dj-manager' ) ),
						'description'			=> __( 'Mobile DJ Manager Events', 'mobile-dj-manager' ),
						'public'			 	 => false,
						'exclude_from_search'	=> true,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_EVENT_POSTS,
						'show_in_admin_bar'	  => true,
						'query_var'		 	  => true,
						'rewrite'            	=> array( 'slug' => 'mdjm-event' ),
						'capability_type'    	=> array( 'mdjm_manage_event', 'mdjm_manage_events' ),
						'map_meta_cap'		   => true,
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'taxonomies'			 => array( MDJM_EVENT_POSTS ),
						'register_meta_box_cb'   => array( $mdjm_posts, 'define_metabox' ) ) );
			}
			
			/**
			 * Register the mdjm-quotes custom post type for online quotations
			 */
			if( !post_type_exists( MDJM_QUOTE_POSTS ) )	{
				register_post_type( MDJM_QUOTE_POSTS,
					array(
						'labels'	=> array(
							'name'               => _x( 'Quotes', 'post type general name', 'mobile-dj-manager' ),
							'singular_name'      => _x( 'Quote', 'post type singular name', 'mobile-dj-manager' ),
							'menu_name'          => _x( 'Quotes', 'admin menu', 'mobile-dj-manager' ),
							'name_admin_bar'     => _x( 'Quote', 'add new on admin bar', 'mobile-dj-manager' ),
							'add_new'            => __( 'Create Quote', 'mobile-dj-manager' ),
							'add_new_item'       => __( 'Create New Quote', 'mobile-dj-manager' ),
							'new_item'           => __( 'New Quote', 'mobile-dj-manager' ),
							'edit_item'          => __( 'Edit Quote', 'mobile-dj-manager' ),
							'view_item'          => __( 'View Quote', 'mobile-dj-manager' ),
							'all_items'          => __( 'All Quotes', 'mobile-dj-manager' ),
							'search_items'       => __( 'Search Quotes', 'mobile-dj-manager' ),
							'not_found'          => __( 'No quotes found.', 'mobile-dj-manager' ),
							'not_found_in_trash' => __( 'No quotes found in Trash.', 'mobile-dj-manager' ) ),
						'description'			=> __( 'Mobile DJ Manager Quotes', 'mobile-dj-manager' ),
						'public'			 	 => false,
						'exclude_from_search'	=> true,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'	   	   => false,
						'query_var'		  	  => true,
						'rewrite'            	=> array( 'slug' => 'mdjm-quotes' ),
						'capability_type'    	=> array( 'mdjm_manage_quote', 'mdjm_manage_quotes' ),
						'map_meta_cap'		   => true,
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'      	  => 5,
						'supports'           	   => array( 'title' ),
						'menu_icon'		  	  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'register_meta_box_cb'   => array( $mdjm_posts, 'define_metabox' ) ) );
			}
			
			/**
			 * Register the mdjm-transaction custom post type for event transactions
			 */
			if( !post_type_exists( MDJM_TRANS_POSTS ) )	{
				register_post_type( MDJM_TRANS_POSTS,
					array(
						'labels'	=> array(
							'name'               => _x( 'Transactions', 'post type general name', 'mobile-dj-manager' ),
							'singular_name'      => _x( 'Transaction', 'post type singular name', 'mobile-dj-manager' ),
							'menu_name'          => _x( 'Transactions', 'admin menu', 'mobile-dj-manager' ),
							'name_admin_bar'     => _x( 'Transaction', 'add new on admin bar', 'mobile-dj-manager' ),
							'add_new'            => __( 'Add Transaction', 'mobile-dj-manager' ),
							'add_new_item'       => __( 'Add New Transaction', 'mobile-dj-manager' ),
							'new_item'           => __( 'New Transaction', 'mobile-dj-manager' ),
							'edit_item'          => __( 'Edit Transaction', 'mobile-dj-manager' ),
							'view_item'          => __( 'View Transaction', 'mobile-dj-manager' ),
							'all_items'          => __( 'All Transactions', 'mobile-dj-manager' ),
							'search_items'       => __( 'Search Transactions', 'mobile-dj-manager' ),
							'not_found'          => __( 'No Transactions found.', 'mobile-dj-manager' ),
							'not_found_in_trash' => __( 'No Transactions found in Trash.' ) ),
						'description'			=> __( 'Transactions for the Mobile DJ Manager plugin', 'mobile-dj-manager' ),
						'public'			 	 => false,
						'exclude_from_search'	=> false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_TRANS_POSTS,
						'show_in_admin_bar'	  => true,
						'rewrite' 			  	=> array( 'slug' => 'mdjm-transaction'),
						'query_var'		 	  => true,
						'capability_type'    	=> array( 'mdjm_manage_transaction', 'mdjm_manage_transactions' ),
						'map_meta_cap'		   => true,
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'taxonomies'			 => array( MDJM_TRANS_POSTS ),
						'register_meta_box_cb'   => array( $mdjm_posts, 'define_metabox' ) ) );
			}
			
			/**
			 * Register the mdjm-venue custom post type for venues
			 */
			if( !post_type_exists( MDJM_VENUE_POSTS ) )	{
				register_post_type( MDJM_VENUE_POSTS,
					array(
						'labels'	=> array(
							'name'               => _x( 'Venues', 'post type general name', 'mobile-dj-manager' ),
							'singular_name'      => _x( 'Venue', 'post type singular name', 'mobile-dj-manager' ),
							'menu_name'          => _x( 'Venues', 'admin menu', 'mobile-dj-manager' ),
							'name_admin_bar'     => _x( 'Venue', 'add new on admin bar', 'mobile-dj-manager' ),
							'add_new'            => __( 'Add Venue', 'mobile-dj-manager' ),
							'add_new_item'       => __( 'Add New Venue', 'mobile-dj-manager' ),
							'new_item'           => __( 'New Venue', 'mobile-dj-manager' ),
							'edit_item'          => __( 'Edit Venue', 'mobile-dj-manager' ),
							'view_item'          => __( 'View Venue', 'mobile-dj-manager' ),
							'all_items'          => __( 'All Venues', 'mobile-dj-manager' ),
							'search_items'       => __( 'Search Venues', 'mobile-dj-manager' ),
							'not_found'          => __( 'No Venues found.', 'mobile-dj-manager' ),
							'not_found_in_trash' => __( 'No Venues found in Trash.', 'mobile-dj-manager' ) ),
						'description'			=> __( 'Venues stored for the Mobile DJ Manager plugin', 'mobile-dj-manager' ),
						'public'			 	 => false,
						'exclude_from_search'	=> false,
						'publicly_queryable' 	 => true,
						'show_ui'				=> true,
						'show_in_menu'		   => 'edit.php?post_type=' . MDJM_VENUE_POSTS,
						'show_in_admin_bar'	  => true,
						'rewrite' 			  	=> array( 'slug' => 'mdjm-venue'),
						'query_var'		 	  => true,
						'capability_type'    	=> array( 'mdjm_manage_venue', 'mdjm_manage_venues' ),
						'map_meta_cap'		   => true,
						'has_archive'        	=> true,
						'hierarchical'       	   => false,
						'menu_position'     	  => 5,
						'supports'			   => array( 'title' ),
						'menu_icon'			  => plugins_url( 'mobile-dj-manager/admin/images/mdjm-icon-20x20.jpg' ),
						'taxonomies'			 => array( MDJM_VENUE_POSTS ),
						'register_meta_box_cb'   => array( $mdjm_posts, 'define_metabox' ) ) );
			}
			
		} // register_post_types
		
		/**
		 * Register the custom post statuses
		 */
		public static function register_post_status()	{
			/**
			 * Communication Post Statuses
			 */
			register_post_status( 
				'ready to send',
				array(
					'label'                     => __( 'Ready to Send', 'mobile-dj-manager' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Ready to Send <span class="count">(%s)</span>', 'Ready to Send <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
			
			register_post_status( 
				'sent',
				array(
					'label'                     => __( 'Sent', 'mobile-dj-manager' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Sent <span class="count">(%s)</span>', 'Sent <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
			
			register_post_status(
				'opened',
				array(
					'label'                     => __( 'Opened', 'mobile-dj-manager' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Opened <span class="count">(%s)</span>', 'Opened <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
			
			register_post_status(
				'failed',
				array(
					'label'                     => __( 'Failed', 'mobile-dj-manager' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
			/**
			 * Event Post Statuses
			 */
			register_post_status( 
				'mdjm-unattended',
				array(
					'label'                     => __( 'Unattended Enquiry', 'mobile-dj-manager' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Unattended Enquiry <span class="count">(%s)</span>', 'Unattended Enquiries <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );

			register_post_status(
				'mdjm-enquiry',
				array(
					'label'                     => __( 'Enquiry', 'mobile-dj-manager' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Enquiry <span class="count">(%s)</span>', 'Enquiries <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
							
				register_post_status(
					'mdjm-approved',
					array(
						'label'                     => __( 'Approved', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Approved <span class="count">(%s)</span>', 'Approved <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
				
				register_post_status(
					'mdjm-contract',
					array(
						'label'                     => __( 'Awaiting Contract', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Awaiting Contract <span class="count">(%s)</span>', 'Awaiting Contracts <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );

				register_post_status(
					'mdjm-completed',
					array(
						'label'                     => __( 'Completed', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
						
				register_post_status(
					'mdjm-cancelled',
					array(
						'label'                     => __( 'Cancelled', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
				
				register_post_status(
					'mdjm-rejected',
					array(
						'label'                     => __( 'Rejected Enquiry', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Rejected Enquiry <span class="count">(%s)</span>', 'Rejected Enquiries <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
				
				register_post_status(
					'mdjm-failed',
					array(
						'label'                     => __( 'Failed Enquiry', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Failed Enquiry <span class="count">(%s)</span>', 'Failed Enquiries <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
				
				/**
				 * Online Quote Post Statuses
				 */		
				register_post_status(
					'mdjm-quote-generated',
					array(
						'label'                     => __( 'Generated', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Generated Quote <span class="count">(%s)</span>', 'Generated Quotes <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
				
				register_post_status(
					'mdjm-quote-viewed',
					array(
						'label'                     => __( 'Viewed', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Viewed Quote <span class="count">(%s)</span>', 'Viewed Quotes <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
				
				/**
				 * Transaction Post Statuses
				 */		
				register_post_status(
					'mdjm-income',
					array(
						'label'                     => __( 'Income', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Received Payment <span class="count">(%s)</span>', 'Received Payments <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
				
				register_post_status(
					'mdjm-expenditure',
					array(
						'label'                     => __( 'Expenditure', 'mobile-dj-manager' ),
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( 'Ougoing Payment <span class="count">(%s)</span>', 'Ougoing Payments <span class="count">(%s)</span>', 'mobile-dj-manager' ) ) );
		} // register_post_status
		
		/**
		 * Register the custom taxonomies for our custom posts
		 */
		public static function register_post_taxonomies()	{
			/**
			 * Event Type Taxonomies
			 */
			if( !get_taxonomy( 'event-types' ) )	{
				register_taxonomy(
					'event-types',
					MDJM_EVENT_POSTS,
					array(
						'hierarchical'      	   => true,
						'labels'            	 => array(
							'name'              		   => _x( 'Event Type', 'taxonomy general name', 'mobile-dj-manager' ),
							'singular_name'     		  => _x( 'Event Type', 'taxonomy singular name', 'mobile-dj-manager' ),
							'search_items'      		   => __( 'Search Event Types', 'mobile-dj-manager' ),
							'all_items'         		  => __( 'All Event Types', 'mobile-dj-manager' ),
							'edit_item'        		  => __( 'Edit Event Type', 'mobile-dj-manager' ),
							'update_item'       			=> __( 'Update Event Type', 'mobile-dj-manager' ),
							'add_new_item'      		   => __( 'Add New Event Type', 'mobile-dj-manager' ),
							'new_item_name'     		  => __( 'New Event Type', 'mobile-dj-manager' ),
							'menu_name'         		  => __( 'Event Types', 'mobile-dj-manager' ),
							'separate_items_with_commas' => NULL,
							'choose_from_most_used'	  => __( 'Choose from the most popular Event Types', 'mobile-dj-manager' ),
							'not_found'				  => __( 'No event types found', 'mobile-dj-manager' ) ),
						'show_ui'           		=> true,
						'show_admin_column' 	  => false,
						'query_var'         	  => true,
						'rewrite'           		=> array( 'slug' => 'event-types' ),
						'update_count_callback'  => '_update_generic_term_count',) );
			}

			/**
			 * Transaction Type Taxonomies
			 */
			if( !get_taxonomy( 'transaction-types' ) )	{
				register_taxonomy(
					'transaction-types',
					MDJM_TRANS_POSTS,
					array(
						'hierarchical'      	   => true,
						'labels'            	 => array(
							'name'              		   => _x( 'Transaction Type', 'taxonomy general name', 'mobile-dj-manager' ),
							'singular_name'     		  => _x( 'Transaction Type', 'taxonomy singular name', 'mobile-dj-manager' ),
							'search_items'      		   => __( 'Search Transaction Types', 'mobile-dj-manager' ),
							'all_items'         		  => __( 'All Transaction Types', 'mobile-dj-manager' ),
							'edit_item'        		  => __( 'Edit Transaction Type', 'mobile-dj-manager' ),
							'update_item'       			=> __( 'Update Transaction Type', 'mobile-dj-manager' ),
							'add_new_item'      		   => __( 'Add New Transaction Type', 'mobile-dj-manager' ),
							'new_item_name'     		  => __( 'New Transaction Type', 'mobile-dj-manager' ),
							'menu_name'         		  => __( 'Transaction Types', 'mobile-dj-manager' ),
							'separate_items_with_commas' => NULL,
							'choose_from_most_used'	  => __( 'Choose from the most popular Transaction Types', 'mobile-dj-manager' ),
							'not_found'				  => __( 'No transaction types found', 'mobile-dj-manager' ) ),
						'show_ui'           		=> true,
						'show_admin_column' 	  => false,
						'query_var'         	  => true,
						'rewrite'           		=> array( 'slug' => 'transaction-types' ),
						'update_count_callback'      => '_update_generic_term_count') );
			}
			
			/**
			 * Venue Details Taxonomies
			 */
			if( !get_taxonomy( 'venue-details' ) )	{
				register_taxonomy(
					'venue-details',
					MDJM_VENUE_POSTS,
					array(
						'hierarchical'      => true,
						'labels'            => array(
							'name'              		   => _x( 'Venue Details', 'taxonomy general name', 'mobile-dj-manager' ),
							'singular_name'     		  => _x( 'Venue Detail', 'taxonomy singular name', 'mobile-dj-manager' ),
							'search_items'      		   => __( 'Search Venue Details', 'mobile-dj-manager' ),
							'all_items'         		  => __( 'All Venue Details', 'mobile-dj-manager' ),
							'edit_item'        		  => __( 'Edit Venue Detail', 'mobile-dj-manager' ),
							'update_item'       			=> __( 'Update Venue Detail', 'mobile-dj-manager' ),
							'add_new_item'      		   => __( 'Add New Venue Detail', 'mobile-dj-manager' ),
							'new_item_name'     		  => __( 'New Venue Detail', 'mobile-dj-manager' ),
							'menu_name'         		  => __( 'Venue Details', 'mobile-dj-manager' ),
							'separate_items_with_commas' => NULL,
							'choose_from_most_used'	  => __( 'Choose from the most popular Venue Details', 'mobile-dj-manager' ),
							'not_found'				  => __( 'No details found', 'mobile-dj-manager' ) ),
						'show_ui'           => true,
						'show_admin_column' => true,
						'query_var'         => true,
						'rewrite'           => array( 'slug' => 'venue-details' ) ) );
			}
		} // register_post_taxonomies
		
	} // class MDJM_Post_Types
endif;

	MDJM_Post_Types::init();