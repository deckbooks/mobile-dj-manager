<?php

/**
 * Add an option field to set the default event type when adding a new type.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_add_event_type_fields( $tag )	{
	?>
    <div class="form-field term-group">
        <label for="event_type_default"><?php printf( __( 'Set as Default %s type?', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label>
        <input type="checkbox" name="event_type_default" id="event_type_default" value="<?php echo $tag->term_id; ?>" />
    </div>
    <?php
	
} // mdjm_add_event_category_fields
add_action( 'event-types_add_form_fields', 'mdjm_add_event_type_fields' );

/**
 * Add an option field to set the default event type when editing a type.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_edit_event_type_fields( $tag )	{
	
	?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="event_type_default"><?php printf( __( 'Set as Default %s type?', 'mobile-dj-manager' ), mdjm_get_label_singular() ); ?></label></th>
        <td><input type="checkbox" id="event_type_default" name="event_type_default" value="<?php echo $tag->term_id; ?>" <?php checked( mdjm_get_option( 'event_type_default' ), $tag->term_id ); ?>></td>
    </tr>
    <?php
	
} // mdjm_edit_event_category_fields
add_action( 'event-types_edit_form_fields', 'mdjm_edit_event_type_fields' );

/**
 * Fires when an event type is created or edited.
 *
 * Check whether the set as default option is set and update options.
 *
 * @since	1.3
 * @param	int		$term_id	The term ID
 * @param	int		$tt_id		The term taxonomy ID
 * @return	str
 */
function mdjm_save_event_type( $term_id, $tt_id )	{
	
    if( ! empty( $_POST['event_type_default'] ) )	{
	
		mdjm_update_option( 'event_type_default', $term_id );
	
    } else	{
		
		if( mdjm_get_option( 'event_type_default' ) == $term_id )	{
			
			mdjm_delete_option( 'event_type_default' );
			
		}
		
	}
	
} // mdjm_save_playlist_category
add_action( 'create_event-types', 'mdjm_save_event_type', 10, 2 );
add_action( 'edited_event-types', 'mdjm_save_event_type', 10, 2 );

/**
 * Add an option field to set the default category when adding a new category.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_add_playlist_category_fields( $tag )	{
	?>
    <div class="form-field term-group">
        <label for="playlist_default_cat"><?php _e( 'Set as default Category?', 'mobile-dj-manager' ); ?></label>
        <input type="checkbox" name="playlist_default_cat" id="playlist_default_cat" value="<?php echo $tag->term_id; ?>" />
    </div>
    <?php
	
} // mdjm_add_default_playlist_category
add_action( 'playlist-category_add_form_fields', 'mdjm_add_playlist_category_fields' );

/**
 * Add an option field to set the default category when editing a new category.
 *
 * @since	1.3
 * @param	obj		$tag	The tag object
 * @return	str
 */
function mdjm_edit_playlist_category_fields( $tag )	{
	
	?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="playlist_default_cat"><?php _e( 'Set as Default Category?', 'mobile-dj-manager' ); ?></label></th>
        <td><input type="checkbox" id="playlist_default_cat" name="playlist_default_cat" value="<?php echo $tag->term_id; ?>" <?php checked( mdjm_get_option( 'playlist_default_cat' ), $tag->term_id ); ?>></td>
    </tr>
    <?php
	
} // mdjm_add_default_playlist_category
add_action( 'playlist-category_edit_form_fields', 'mdjm_edit_playlist_category_fields' );

/**
 * Fires when a playlist category is created or edited.
 *
 * Check whether the set as default option is set and update options.
 *
 * @since	1.3
 * @param	int		$term_id	The term ID
 * @param	int		$tt_id		The term taxonomy ID
 * @return	str
 */
function mdjm_save_playlist_category( $term_id, $tt_id )	{
	
    if( ! empty( $_POST['playlist_default_cat'] ) )	{
	
		mdjm_update_option( 'playlist_default_cat', $term_id );
	
    } else	{
		
		if( mdjm_get_option( 'playlist_default_cat' ) == $term_id )	{
			
			mdjm_delete_option( 'playlist_default_cat' );
			
		}
		
	}
	
} // mdjm_save_playlist_category
add_action( 'create_playlist-category', 'mdjm_save_playlist_category', 10, 2 );
add_action( 'edited_playlist-category', 'mdjm_save_playlist_category', 10, 2 );

/**
 * Adds the Default column to the playlist category terms list.
 *
 * @since	1.3
 * @param	arr		$columns	The table columns
 * @return	arr		$columns	The table columns
 */
function mdjm_add_playlist_category_default_column( $columns )	{
    $columns['default'] = 'Default?';
    
	return $columns;
} // mdjm_add_playlist_category_default_column
add_filter( 'manage_edit-playlist-category_columns', 'mdjm_add_playlist_category_default_column' );

/**
 * Adds the content to the Default column within the playlist category terms list.
 *
 * @since	1.3
 * @param	str		$content		The cell content
 * @param	str		$column_name	The column name
 * @param	int		$term_id		The term ID
 * @return	str		$content		The table columns
 */
function mdjm_add_playlist_category_default_column_content( $content, $column_name, $term_id )	{
	
	$term = get_term( $term_id, 'playlist-category' );
    
	switch ( $column_name ) {
        case 'default':
            if( mdjm_get_option( 'playlist_default_cat' ) == $term_id )	{
				$content = __( 'Yes', 'mobile-dj-manager' );
			} else	{
				$content = __( 'No', 'mobile-dj-manager' );
			}
            break;
        
		default:
            break;
    }
	
	return $content;
}
add_filter( 'manage_playlist-category_custom_column', 'mdjm_add_playlist_category_default_column_content', 10, 3 );