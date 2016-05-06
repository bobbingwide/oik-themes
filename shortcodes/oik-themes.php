<?php // (C) Copyright Bobbing Wide 2012, 2013


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
 */
function _oikth_download_wordpressversion( $post, $slug ) {
  $link = "http://downloads.wordpress.org/theme/$slug.zip";
  $text = __( "Download" );
  $text .= "&nbsp;";
  $text .= $slug;
  art_button( $link, $text );
} 

/**
 */
function _oikth_download_version( $version, $post, $class, $slug ) {
  if ( $version->post_type == "oik_premiumversion" ) {
    _oikth_purchase_premiumversion( $version, $post, $class );    
  } else {
    $theme_type = get_post_meta( $post->ID, "_oikth_type", true );
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
 */
function oikth_download( $atts=null ) {
  // @TODO **?** return the theme slug from the currently selected $post if it is of type "oik-themes"
  
  $theme = bw_array_get( $atts, "theme", "oik" );
  $class = bw_array_get( $atts, 'class', NULL ) . "download" ;
  oik_require( "feed/oik-themes-feed.php", "oik-themes" );
  oik_require( "admin/oik-admin.inc" );
  $slug = bw_get_slug( $theme );   
  $post = oikth_load_theme( $slug );
  if ( $post ) {
    $version = oikth_load_themeversion( $post );
    if ( $version ) { 
      _oikth_download_version( $version, $post, $class, $slug );
    } else {
      $theme_type = get_post_meta( $post->ID, "_oikth_type", true );
      if ( $theme_type == 0 ) {
        //  **?** Don't do anything yet
        // alink( null, "http://wordpress.org", "
      } elseif ( $theme_type == 1 ) {
        _oikth_download_wordpressversion( $post, $slug );
      } else {  
        p( "$theme: latest version not available for download" );
      }  
    }   
  } else {
    p( "Unknown theme: $slug " );
  }    
  return( bw_ret());
}


function oikth_download__help( $shortcode='oikth_download' ) {
  return( "Produce a download button for a theme" );
}

function oikth_download__syntax( $shortcode='oikth_download' ) {
  oik_require( "includes/oik-sc-help.inc" );
  $syntax = array( "theme" => bw_skv( "oik", "theme", "name of the theme" ) 
//                 , "text" => bw_skv( "dummy", "", "text for the button" )
//                 , "title" => bw_skv( "as text", "", "title for the tooltip" )
                 , "class" => bw_skv( "download", "", "CSS classes for the button" )
                 );
  return( $syntax ); 
}

function oikth_download__example( $shortcode='oikth_download' ) {

  oik_require( "includes/oik-sc-help.inc" );
  $text = "To create a button to download the oik2012 theme" ;
  $example = "theme=oik2012";
  bw_invoke_shortcode( $shortcode, $example, $text );
}
  

