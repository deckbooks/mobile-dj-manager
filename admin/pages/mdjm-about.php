<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * Class Name: MDJM_About
 * Displays information regarding the plugins current release version
 *
 *
 *
 */
if( !class_exists( 'MDJM_About' ) ) : 
	class MDJM_About	{
		public function __construct()	{
			$this->enqueue();
			
			$this->page_content();
		} // __construct
		
		/**
		 * Enqueue page specific scripts and styles
		 *
		 *
		 *
		 */
		public function enqueue()	{
			wp_enqueue_script( 'youtube-subscribe' );
		}
		
		/**
		 * The header content for the page. This generally remains static
		 *
		 *
		 *
		 */
		public function page_header()	{
			?>
            <style>
			.site-title	{
				color: #FF9900;	
			}
			.site-title img	{
				display: block; max-width: 100%; max-height: 60px; height: auto; padding: 0; margin: 0 auto; -webkit-border-radius: 0; border-radius: 0;	
			}
			table { border-spacing: 0.5rem; }
			td {padding-left: 0.5rem; padding-right: 0.5rem; }
			
			</style>
            <div class="wrap">
            <a href="http://www.mydjplanner.co.uk/" target="_blank"><img style="max-height: 80px; height: auto;" src="<?php echo MDJM_PLUGIN_URL . '/admin/images/mdjm_web_header.png'; ?>" alt="<?php _e( 'MDJM Event Management', 'mobile-dj-manager' ); ?>" title="<?php _e( 'MDJM Event Management', 'mobile-dj-manager' ); ?>" /></a>
            <h1><?php printf( __( 'Welcome to MDJM Event Management version %s', 'mobile-dj-manager' ), MDJM_VERSION_NUM ); ?></h1>
            <hr>
            <?php
			mdjm_update_notice( 
				'update-nag',
				sprintf( 'We are currently working hard on new documentation for the MDJM plugin family. If you are willing to get involved and help out, please %sContact Mike%s.%s' . 
					'Additionally if you are able to assist with translating our plugins we\'d love to hear from you.',
					'<a href="http://www.mydjplanner.co.uk/contact/">',
					'</a>',
					'<br />' ),
				true );
		} // page_header
		
		/**
		 * The footer content for the page. This generally remains static
		 *
		 *
		 *
		 */
		public function page_footer()	{
			?>
            <hr>
            <h3><?php _e( 'Have you tried the MDJM Event Management extensions', 'mobile-dj-manager' ); ?>?</h3>
            <p><?php _e( 'Extensions enhance the features of the MDJM Event Management Plugin. All paid extensions are provided with a full years updates and support', 'mobile-dj-manager' ); ?>.</p>
            <table>
            <tr>
            <td><a href="<?php echo admin_url( 'plugin-install.php?tab=search&s=mdjm-to-pdf' ); ?>"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2014/11/MDJM_to_PDF_Product.jpg" alt="MDJM to PDF" title="MDJM to PDF" /></a></td>
            <td><a href="http://www.mydjplanner.co.uk/portfolio/dynamic-contact-forms/" target="_blank"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2015/09/MDJM_DCF_Product.jpg" alt="MDJM Dynamic Contact Forms" title="MDJM Dynamic Contact Forms" /></a></td>
            <td><a href="http://www.mydjplanner.co.uk/portfolio/google-calendar-sync/" target="_blank"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2015/10/MDJM_Google_Cal_Product.jpg" alt="MDJM Google Calendar Sync" title="MDJM Google Calendar Sync" /></a></td>
            </tr>
            <tr>
            <td style="text-align:center"><a href="<?php echo admin_url( 'plugin-install.php?tab=search&s=mdjm-to-pdf' ); ?>" class="button secondary">Install now</a><br>
            <strong>FREE</strong>
            </td>
            <td style="text-align:center"><a href="http://www.mydjplanner.co.uk/portfolio/dynamic-contact-forms/" target="_blank" class="button secondary">Buy now</a><br>
            <strong>&pound;35.00</strong>
            </td>
            <td style="text-align:center"><a href="http://www.mydjplanner.co.uk/portfolio/google-calendar-sync/" target="_blank" class="button secondary">Buy now</a><br>
            <strong>&pound;25.00</strong>
            </td>
            </tr>
            <tr>
            <td><a href="http://www.mydjplanner.co.uk/portfolio/payments/" target="_blank"><img src="http://www.mydjplanner.co.uk/wp-content/uploads/2015/10/MDJM_Payments_Product.jpg" alt="MDJM Google Calendar Sync" title="MDJM Google Calendar Sync" /></a></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>
            <tr>
            <td style="text-align:center"><a href="http://www.mydjplanner.co.uk/portfolio/payments/" target="_blank" class="button secondary">Buy now</a><br>
            <strong>&pound;25.00</strong>
            </td>
            <td style="text-align:center">&nbsp;</td>
            <td style="text-align:center">&nbsp;</td>
            </tr>
            </table>
            <p style="text-align:center"><a class="button-primary" href="<?php echo mdjm_get_admin_page( 'settings' ); ?>">Go to MDJM Settings</a></p>
            </div>
            <?php	
		} // page_footer
		
		/**
		 * The body for the page.
		 *
		 *
		 *
		 */
		public function page_content()	{
			$this->page_header();
			?>
            <h3 class="site-title">Version 1.2.7.5 - 22nd January 2015</h3>
            <ui>
            	<li><strong>New</strong>: Attach files from computer to email composed via communication feature</li>
                <li><strong>New</strong>: DJ / Admin access to the Client Zone is now blocked. Use the Admin area. For testing create a test client account and log in with that</li>
                <li><strong>General</strong>: List multiple attachments on communication history</li>
                <li><strong>Bug Fix</strong>: Custom event fields output if the field name contained spaces</li>
                <li><strong>Bug Fix</strong>: Venue contact name missing a space if venue is set to client address</li>
            </ui>
            <h3 class="site-title">Version 1.2.7.4 - 19th January 2015</h3>
            <ui>
                <li><strong>Bug Fix</strong>: Custom event fields did not display on the event screen if your deposit type was not set as percentage</li>
                <li><strong>Bug Fix</strong>: No MDJM data should be returned from a front end search</li>
                <li><strong>Bug Fix</strong>: Removed duplicate fields from client profile on admin profile page</li>
                <li><strong>Bug Fix</strong>: Redirecting to contact page from availability widget should pre-populate event date field if present</li>
                <li><strong>Bug Fix</strong>: Contract sign notification email to admin did not display client name. Filter content before passing to send_email method.</li>
            </ui>
            <h3 class="site-title">Version 1.2.7.3 - 26th November 2015</h3>
            <ui>
                <li>Bug Fix: Missing number_format param was causing payment gateway API to not record merchant fees</li>
                <li>General: Accomodate changes in other MDJM plugins</li>
                <li>General: Update playlist task via update_option_{$option_name} when setting changes</li>
                <li>General: get_event_types now accepts args</li>
            </ui>
            <h3 class="site-title">Version 1.2.7.2 - 25th November 2015</h3>
            <ui>
                <li><strong>Bug Fix</strong>: Availability checker ajax scripts did not work if using a Firefox web browser</li>
                <li><strong>Bug Fix</strong>: Field wrap now functions as expected for Availability Checker</li>
                <li><strong>Bug Fix</strong>: PHP Notice written to log file if WP debugging enabled when saving event that has empty fields</li>
                <li><strong>Bug Fix</strong>: Unattended event availability check now calls correct function and does not generate error</li>
                <li><strong>Bug Fix</strong>: Backwards compatibility issue with front end availability checker</li>
                <li><strong>Bug Fix</strong>: Put availability checker fields on their own line if field wrap is true</li>
                <li><strong>Bug Fix</strong>: Redirect failed after client password change</li>
                <li><strong>Bug Fix</strong>: Image now displays on about page</li>
                <li><strong>General</strong>: Ignore communication posts during custom post type save</li>
                <li><strong>General</strong>: Removed custom text playlist setting for No Active Event</li>
                <li><strong>General</strong>: Do not write to log file if no client fields are set as required</li>
                <li><strong>General</strong>: Adjust folder structure within client zone</li>
                <li><strong>New</strong>: Added submit_wrap option for availability shortcode</li>
            </ui>
            <hr />
            <h3 class="site-title">Version 1.2.7.1 - 22nd November 2015</h3>
            <ui>
            	<li><strong>New</strong>: Shortcodes added for Addons List and Availability checker></li>
            	<li><strong>New</strong>: Add your own custom fields to Client, Event, and Venue Details metaboxes within the events screen. See <a href="http://www.mydjplanner.co.uk/custom-event-fields/" target="_blank">the user guide</a></li>
                <li><strong>New</strong>:Text replacement shortcodes available for custom fields. See <a href="http://www.mydjplanner.co.uk/custom-event-fields/" target="_blank">the user guide</a></li>
                <li><strong>New</strong>: Option to use AJAX for Availability Checker to avoid page refresh</li>
            	<li><strong>New</strong>: New setting added <code>Unavailable Statuses</code> within Availability Settings so you now dictate which event status' should report as unavailable. By default we have set Enquiry, Awaiting Contract and Approved</li>
                <li><strong>New</strong>: Display name for <?php _e( MDJM_DJ ); ?> is now updated within user roles</li>
                <li><strong>New</strong>: Development hooks added to event post metaboxes</li>
                <li><strong>General</strong>: Availability checker re-write</li>
				<li><strong>General</strong>: MDJM Shortcodes button renamed to MDJM and new structure and options added</li>
                <li><strong>General</strong>: Client fields settings page is now translation ready</li>
                <li><strong>General</strong>: Updated the uninstallation procedure</li>
                <li><strong>General</strong>: Added column ordering to transactions</li>
                <li><strong>General</strong>: Added column ordering to quotes</li>
                <li><strong>General</strong>: Replace Mobile DJ Manager with MDJM in WP dashboard widgets</li>
                <li><strong>General</strong>: Change title to MDJM Event Management in MDJM dashboard</li>
                <li><strong>Bug Fix</strong>: Adjusted the order in which the deposit and balance status' are updated for events so as to ensure manual payments are captured during manual event update</li>
                <li><strong>Bug Fix</strong>: WP Dashboard MDJM Overview now has correct edit URL</li>
                <li><strong>Bug Fix</strong>: Depending on PHP notice display settings, warning may be displayed on front end when client clicks <code>Book this Event</code></li>
                <li><strong>Bug Fix</strong>: User roles should only register during install</li>
                <li><strong>Bug Fix</strong>: Ordering by event value column in event list now accurate</li>
            </ui>
            <?php
			$this->page_footer();
		} // page_content
		
	} // class MDJM_About
endif;

new MDJM_About();