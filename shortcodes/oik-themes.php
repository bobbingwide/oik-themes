<?php // (C) Copyright Bobbing Wide 2012-2017

/**
 * Create a link to purchase a premium version
 *
 */
function _oikth_purchase_premiumversion( $version, $post, $class ) {
  $link_id = get_post_meta( $post->ID, "_oikth_prod", true );
  if ( $link_id ) {
    $link = get_permalink( $link_id );
    $text = "Purchase " . $version->post_title ;
    $title = "Purchase " . $version->post_title ;  
    //art_button( $link, $text, $title, $class );
    alink( $class, $link, $text, $title );
  } else {
    p( "Sorry: Product not available for download for: " . $version->post_name );
  }
}

/** 
 * Create a link to download the FREE version
 */
function _oikth_download_freeversion( $version, $post, $class ) {
  $new_version = oikth_get_latestversion( $version );
  $link = oikth_get_package( $post, $version, $new_version, null, "download" );
  if ( $link ) {
    $text = __( "Download" ); 
    $text .= "&nbsp;";
    $text .= $post->post_name; 
    $text .= "&nbsp;";
    $text .= retstag( "span", "version" );
    $text .= __("version" );
    $text .= "&nbsp;";
    $text .= $new_version ;
    $text .= retetag( "span" );
    $title = $text; 
    alink( $class, $link, $text, $title );
    //or 
    //art_button( $link, $text, $title, $class );
  } else {
    p( "Sorry: No download file available for: " . $version->post_name );
  }   
}

/**
 * Create a link to download the WordPress theme
 * Note: For the download to actually work this now requires the theme version, which we may not have.
 * So currently we'll have to just create a link to the theme's home page.
 * Using type: None in the mean time. 
 */
function _oikth_download_wordpressversion( $post, $slug ) {
  //$link = "http://downloads.wordpress.org/theme/$slug.zip";
  //$link = "http://wordpress.org/themes/download/$slug.zip";
  $link = "http://wordpress.org/themes/$slug";
  $text = __( "Download" );
  $text .= "&nbsp;";
  $text .= $slug;
  art_button( $link, $text );
} 

/**
 * Create a link to download the latest theme version
 * 
 */
function _oikth_download_version( $version, $post, $class, $slug ) {
  if ( $version->post_type == "oik_premiumversion" ) {
    _oikth_purchase_premiumversion( $version, $post, $class );    
  } else {
    $theme_type = get_post_meta( $post->ID, "_oikth_type", true );
		bw_trace2( $theme_type, "theme_type" );
    switch ( $theme_type ) {
      case 0:
        // No theme type specified - do not create a download link
        break;
      case 1:
        _oikth_download_wordpressversion( $post, $slug );
        break;
        
      case 2:
        _oikth_download_freeversion( $version, $post, $class );      
        break;
        
      case 6: 
        _oikth_download_wordpressversion( $post, $slug );
        br();
        _oikth_download_freeversion( $version, $post, $class );  
        break;    
        
      default:
        // Do nothing for premium or other versions
    }    
  }
}

/**
 * Provide a download button for the zip attachment to this content or a selected theme ( theme="oik-fum" )
 *
 * 
 * For a premium theme the download should be of the form
 *   themes/download/?theme=oik-blogger-redirect&version=1.1.0802&id=51&action=download&apikey=herb
 
 * For a FREE theme the download should be of the form
 *   themes/download/?theme=oik-fum&version=1.1.0802&id=51&action=download&apikey=
 
 * @param array $atts - array of shortcode parameters
 *   theme=  default: oik
 *   class=   default: download
 * 
  // @TODO **?** return the theme slug from the currently selected $post if it is of type "oik-themes"
 */
function oikth_download( $atts=null ) {
  $theme = bw_array_get( $atts, "theme", "oik" );
  $class = bw_array_get( $atts, 'class', NULL ) . "download" ;
  oik_require( "feed/oik-themes-feed.php", "oik-themes" );
  oik_require( "admin/oik-admin.php" );
	
	if ( $theme == '.' ) {
		oik_require( "includes/bw_posts.inc" );
		$post_type = bw_global_post_type();
		if ( $post_type == "oik-themes" ) {
			$post_id = bw_current_post_id();
			$slug = get_post_meta( $post_id, "_oikth_slug", true );
			//bw_trace2( $slug, "slug" );
	//	}  elseif ( $post_type == "oik_themeversion" ) {
		//	$plugin_version = bw_current_post_id();
	//		$plugin_id = get_post_meta( $plugin_version, "_oikpv_plugin", true );
	//		$slug = get_post_meta( $plugin_id, "_oikp_slug", true );
	//		
	//	} elseif ( $post_type == "oik_themiumversion" ) {
	//		$plugin_version = bw_current_post_id();
//			$plugin_id = get_post_meta( $plugin_version, "_oikpv_plugin", true );
//			$slug = get_post_meta( $plugin_id, "_oikp_slug", true );
		} else {
			bw_trace2( "not an oik theme", null, true, BW_TRACE_WARNING );
		}
		
	} else { 
		$slug = bw_get_slug( $theme );
	}
	//bw_trace2( $slug, "slug" );
  $post = oikth_load_theme( $slug );
  if ( $post ) {
    $version = oikth_load_themeversion( $post );
    if ( $version ) { 
      _oikth_download_version( $version, $post, $class, $slug );
    } else {
      $theme_type = get_post_meta( $post->ID, "_oikth_type", true );
			switch ( $theme_type ) {
				case '1':
					_oikth_download_wordpressversion( $post, $slug );
					break;
					
				case '0':
				case '4':
					break;
				default:
					p( "$slug: latest version not available for download" );
			}
    }   
  } else {
    p( "Unknown theme: $slug " );
  }    
  return( bw_ret());
}


function oikth_download__help( $shortcode='oikth_download' ) {
  return( __( "Produce a download button for a theme", "oik-themes" ) );
}

function oikth_download__syntax( $shortcode='oikth_download' ) {
  oik_require( "includes/oik-sc-help.php" );
  $syntax = array( "theme" => bw_skv( "oik", "theme", "name of the theme" ) 
//                 , "text" => bw_skv( "dummy", "", "text for the button" )
//                 , "title" => bw_skv( "as text", "", "title for the tooltip" )
                 , "class" => bw_skv( "download", "", "CSS classes for the button" )
                 );
  return( $syntax ); 
}

function oikth_download__example( $shortcode='oikth_download' ) {

  oik_require( "includes/oik-sc-help.php" );
  $text = "To create a button to download the oik2012 theme" ;
  $example = "theme=oik2012";
  bw_invoke_shortcode( $shortcode, $example, $text );
}
  

