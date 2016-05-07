<?php // (C) Copyright Bobbing Wide 2015, 2016
	 
/**
 * Determine the tabs to display
 *
 * The tabs depend on the theme type, which is currently a simple field
 * 
 * Type | Means                           | Tabs to display
 * ---- | -----                           | -----------------------------
 * 0    | "None"													| ? Reserved for WordPress core
 * 1    | "WordPress theme"								| Omit: FAQ, Changelog, Screenshots, Documentation
 * 2    | "FREE oik theme"							  |	All
 * 3    | "Premium oik theme"							| All
 * 4    | "Other premium theme"						| Omit: FAQ, Changelog, Screenshots, Documentation
 * 5    | "Bespoke theme"									| All
 * 6    | "WordPress and FREE theme"		  | All
 
 
 *
 * @param object $post the post object
 * @return array keyed by tab of valid tabs for this theme type
 *
 */	 
function oikth_additional_content_tabs( $post ) {
  $tabs = array( "description" => "Description"
               //, "faq" => "FAQ"
               //, "screenshots" => "Screenshots"
               , "changelog" => "Changelog"
               , "shortcodes" => "Shortcodes"
               , "apiref" => "API Ref"
               , "documentation" => "Documentation"
               );
	// "apiref" or what?
								 
							 
	$theme_type = get_post_meta( $post->ID,  "_oikth_type", true );
	switch ( $theme_type ) {
		case 0:
		case 1:
		case 4:
      unset( $tabs['documentation'] );
			unset( $tabs['faq'] );
			unset( $tabs['screenshots'] );	
			unset( $tabs['changelog'] );
			break;
	}	
	$tabs = oikth_oikth_additional_content_tabs( $tabs, $post );					 
	return( $tabs );
}

/**
 * Decide which tabs to display based on website information
 * A2Z - displays APIs Classes Files Hooks
 * oik-plugins - displays apiref
 *
 * We should use an option field 
 *  
 */ 
function oikth_oikth_additional_content_tabs( $tabs, $post ) {
	$use_apiref_shortcode = bw_get_option( "apiref", "bw_themes_server" );
	if ( $use_apiref_shortcode ) {

	} else {
		unset( $tabs['apiref'] );
		$tabs['apis'] = "APIs";
		$tabs['classes'] = "Classes";
		$tabs['files'] = "Files";
		$tabs['hooks'] = "Hooks";
	}
	return( $tabs );
	
}

/**
 * Add the sections links for the theme
 *
 * Here we use a style similar to wordpress.org
 *
 * We omit these at present:
 *    
               //, "installation" => "Installation"
               //, "Other notes" => "Other notes"
               //, "Stats" => "Stats"
               //, "Support" => "Support"
               //, "Reviews" => "Reviews"
               //, "Developers" => "Developers"
 *
 * We may display these for WP-a2z
 * 
 * [apiref] DIY shortcode breaks down into
 * <h3>APIs</h3> [apis] <h3>Classes</h3> [classes] <h3>Files</h3> [files] <h3>Hooks</h3> [hooks]
 */
function oikth_additional_content_links( $post, $current_tab ) {
	$tabs = oikth_additional_content_tabs( $post ); 
	$valid = bw_array_get( $tabs, $current_tab, false );
	if ( !$valid ) { 
		return( $valid );
	}						 
	$url = get_permalink( $post->ID );
	wp_enqueue_style( "oik-themesCSS", oik_url( "css/oik-themes.css", "oik-themes" ) );
  bw_push();
  sdiv( "theme-info" );
  sul( null, "sections" );
  foreach ( $tabs as $tab => $label ) {
    $class = "section-$tab" ;
    $target_url = add_query_arg( "oik-tab", $tab, $url );
    if ( $tab === $current_tab ) {
      stag( "li", "current" );
    } else {
      stag( "li" );
    }
    alink( $class, $target_url, $label ); 
    etag( "li" );
  }
  eul();
  ediv();
  sediv( "clear" );
  sdiv( "theme-body" );
  $ret = bw_ret(); 
  bw_pop();
  return( $ret );
}

/**
 * Handle varying requests for additional content
 *
 * Default to displaying the description if "oik-tab" is not set
 *  
 *
 */
function oikth_additional_content( $post, $slug=null ) {
  $oik_tab = bw_array_get( $_REQUEST, "oik-tab", "description" ); 
  $additional_content = oikth_additional_content_links( $post, $oik_tab );
  if ( $oik_tab ) {
    $tabs = array( "description" => "oikth_display_description"
                 , "faq" => "oikth_display_faq"
                 , "screenshots" => "oikth_display_screenshots"
                 , "changelog" => "oikth_tabulate_themeversion" 
                 , "shortcodes" => "oikth_display_shortcodes" 
                 , "apiref" => "oikth_display_apiref"
                 , "documentation" => "oikth_display_documentation" 
                 );
    $oik_tab_function = bw_array_get( $tabs, $oik_tab, "oikth_display_unknown" );
    if ( $oik_tab_function ) {
      if ( is_callable( $oik_tab_function ) ) {
        $additional_content .= $oik_tab_function( $post, $slug );
      } else {
        $additional_content .= "Missing: $oik_tab_function";
      }
    }  
  }
  $additional_content .= "</div>";
  return( $additional_content );
}


/**
 * Automatically add the table of version information for a FREE or themium oik theme
 * 
 *  [bw_table post_type="oik_themeversion" fields="title,excerpt,_oiktv_version" meta_key="_oiktv_theme" meta_value=89 orderby=date order=DESC]
 */
function oikth_tabulate_themeversion( $post ) {
  $version_type = get_post_meta( $post->ID, "_oikth_type", true );
  $versions = array( null, null, "oik_themeversion", "oik_themiumversion" );
  $post_type = bw_array_get( $versions, $version_type, null ); 
  if ( $post_type ) {
    $additional_content = "[bw_table";
    $additional_content .= kv( "post_type", $post_type );
    
    $additional_content .= kv( "fields", "title,excerpt,_oiktv_version" );
    $additional_content .= kv( "meta_key", "_oiktv_theme" );
    $additional_content .= kv( "meta_value", $post->ID );
    $additional_content .= kv( "orderby", "date" );
    $additional_content .= kv( "order", "DESC" );
    $additional_content .= "]";
  } else {
    $additional_content = null;
  }     
  return( $additional_content ); 
}

/**
 * Display output for a potentially unknown tab
 *
 * If there's a shortcode for it then we'll use that
 */
function oikth_display_unknown( $post, $slug ) {
	$oik_tab = bw_array_get( $_REQUEST, "oik-tab", "description" ); 
	if ( shortcode_exists( $oik_tab ) ) {
		$ret = "[$oik_tab]" ;
  } else {
		$oik_tab = esc_html( $oik_tab );
		$ret = "Invalid request: $oik_tab. Shortcode is not registered";
		bw_trace2( $ret, "ret", true, BW_TRACE_ERROR );
	}
	return( $ret );
	

}

/**
 * Display the description of the theme 
 *
 * @param object $post - the post object
 * @return string - the post content - shortcode will be expanded later
 */
function oikth_display_description( $post ) {
  return( $post->post_content );
}

/**
 * Display the FAQ's for the theme
 */
function oikth_display_faq( $post ) {
  $id = $post->ID;
  return( "[bw_accordion post_type=oik-faq meta_key=_plugin_ref meta_value=$id format=TEM]" );
} 
 
/**
 * Display the screenshots for the theme
 *
 * This uses the nivo shortcode. 
 * We should probably test if it's available.
 * If not then we need to do what?
 * 
 */
function oikth_display_screenshots( $post, $slug ) {
  $additional_content = "[nivo post_type=screenshot:$slug caption=n link=n]";
  return( $additional_content ); 
}

/**
 * Display the shortcodes for the theme
 * 
 * Uses the [codes] shortcode which determines the theme automatically
 *
 */
function oikth_display_shortcodes( $post, $slug ) {
  $additional_content = "[codes posts_per_page=.]";
  return( $additional_content ); 
}

/**
 * Display the API reference for the theme
 * 
 * Uses the [apiref] shortcode which determines the theme automatically
 *
 */
function oikth_display_apiref( $post, $slug ) {
  $additional_content = "[apiref]";
  return( $additional_content ); 
}

/**
 * Display the documentation for the theme
 *
 * Only use _oik_doc_home if 
 * - the field is defined for the post type
 * - the value is non null
 * - it's a valid page
 * 
 * Otherwise - make it up using the bw_related shortcode
 * 
 * This is how it used to be when displayed in the sidebar widget area
 * `
 * Pages
 * [bw_related post_type=page meta_key=_plugin_ref posts_per_page=5 ] - temporarily disabled 2015/03/15
 * 
 * [clear]
 * 
 * Posts
 * [bw_related post_type=post meta_key=_plugin_ref posts_per_page=5 ] - temporarily disabled 2015/03/15
 * `
 */
function oikth_display_documentation( $post, $slug ) {
	$field_names = bw_get_field_names( $post->ID );
	//bw_trace2( $field_names, "field_names" );
	if ( bw_array_get( bw_assoc( $field_names) , "_oik_doc_home", false ) ) {
		$post_id = get_post_meta( $post->ID, "_oik_doc_home", true );
		if ( $post_id ) {
			oik_require( "includes/bw_posts.inc" );
			$post = bw_get_post( $post_id, "page" );
			if ( !$post ) {
				bw_trace2( $post_id, "Invalid ID for _oik_doc_home" );
				$post_id = null;
			}
		}
	} else {
		$post_id =  null;
	}
	bw_trace2( $post_id, "post_id for _oik_doc_home", false );
  if ( $post_id ) {
		
    $additional_content = "[bw_tree post_type=page post_parent=$post_id posts_per_page=.]";
  } else {
    $additional_content = "[bw_related post_type='page,post' meta_key=_plugin_ref posts_per_page=. orderby=title order=asc ]";
  }    
  return( $additional_content ); 
}
  
