<?php
/** 
Author: bobbingwide
Author URI: http://www.bobbingwide.com
License: GPL2

    Copyright 2012, 2013 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

function _oikth_lazy_redirect( $oik_theme_action ) {
  echo "Invalid request";
}

/**
 * Support /themes/download/?theme=theme&version=version&apikey=apikey&action=update/download&id=id 
 */
function _oikth_lazy_redirect_download( $oik_theme_action ) {
  $theme =  bw_array_get( $_REQUEST, "theme", null );
  $version = bw_array_get( $_REQUEST, "version", null );
  $apikey = bw_array_get( $_REQUEST, "apikey", null );
  $id = bw_array_get( $_REQUEST, "id", null );
  oikth_download_file( $theme, $version, $apikey, $id ); 
}

/**
 * validate the theme and version against the given post ID
 * @param post $theme_version 
 * @param string $theme - theme name - the "post_name"
 * @param string $version - theme version string e.g. 1.16.0821 
 * @return mixed $response - null if OK or WP_error 
 */
function oikth_validate_themeversion( $theme_version, $theme, $version, $apikey ) {
  $actual_version = oikth_get_latestversion( $theme_version );  
  if ( $actual_version == $version ) {
    $actual_theme = oikth_get_theme( $theme_version );
    bw_trace2( $actual_theme, "actual_theme" );   
    if ( $actual_theme && $actual_theme->post_name == $theme ) {
      $response = oikth_validate_apikey( $theme_version, $actual_theme, $apikey );
    } else {
      $response = bw_wp_error( "not-found", "Invalid theme name", $theme );
    }
  } else {
    $response = bw_wp_error( "not-found", "Invalid version" );
  }
  return( $response );
}

/** 
 * Load a record for this apikey and check that it's OK
 *
 */
function oikth_load_apikey( $apikey, $actual_theme ) {
  $result = apply_filters( "validate_apikey", $apikey, $actual_theme );
  bw_trace2( $result, "validate_apikey result" );
  return( $result );
}

/**
 * validate the apikey for this selected theme version 
 * @param post - $actual_version
 */ 
function oikth_validate_apikey( $actual_version, $actual_theme, $apikey ) {
  if ( $actual_version->post_type == "oik_themeversion" ) {
    $response = null;  // We don't need to check the API key for free themes 
  } else {
    $response = oikth_load_apikey( $apikey, $actual_theme );
  }
  return( $response );
} 

/**
 * Increment the downloads for this theme version 
 * 
 * The field incremented depends upon the &action=update/download
 * It's OK for the count to start from blank ( = 0 )
 * 
 * 
 */
function oikth_increment_downloads( $id ) {
  $action = bw_array_get( $_REQUEST, "action", "download" );
  $count = get_post_meta( $id, "_oiktv_${action}_count", true );
  $new_count = $count + 1;
  $success = update_post_meta( $id, "_oiktv_${action}_count", $new_count, $count  );
} 

/**
 * Deliver the theme version requested
*/ 
function oikth_download_file( $theme, $version, $apikey, $id ) {
  $theme_version = get_post( $id );  
  bw_trace2( $theme_version );
  /* check the $version and $theme for the post version that we have loaded */
  $response = oikth_validate_themeversion( $theme_version, $theme, $version, $apikey );
  if ( !is_wp_error( $response ) ) {
    $file = oikth_get_attachment( $theme_version );
    if ( $file ) {
      oikth_increment_downloads( $id );
      if ( $theme_version->post_type == "oik_themiumversion" ) {
        $file = oikth_create_new_file_name( $file ); 
      } else {
        $upload_dir = wp_upload_dir();
        $baseurl = $upload_dir['baseurl'];
        $file = $baseurl . "/". $file;
      }
      oikth_force_download( $file );  
      // Nothing happens after this
    } else {
      $response = bw_wp_error( "not-found", "Attachment not found" );
    }  
      
  } else { 
    //oikth_error( __FUNCTION__ );
    bw_trace2();
    //$response = bw_wp_error( "not-found", "theme version not found" );
  }
  echo serialize( $response );
} 

/**
 * Force the download of a file
 */
function oikth_force_download( $file ) {
  bw_trace2();
  $file_content = file_get_contents( $file );  
  $filename = basename( $file );
  
  header( 'Content-type: application/force-download' );  
  header( "Content-Disposition: attachment; filename=\"$filename\"" );  
  
  echo $file_content;  
  exit;
}

function _oikth_lazy_redirect_update_check( $oik_theme_action ) {
  oikth_update_check( $oik_theme_action );
}

function _oikth_lazy_redirect_info( $oik_theme_action ) {
  oikth_theme_information( $oik_theme_action );
}

/**
 * Implement lazy redirect for the selected action
 * @param string $oik_theme_action should be one of "info", "update_check" 
 */ 
function oikth_lazy_redirect( $oik_theme_action ) {
  $funcname = bw_funcname( "_oikth_lazy_redirect", $oik_theme_action );
  $funcname( $oik_theme_action );
  exit();
}

/**
 * Load the theme by $slug
 * 
 * Load the theme given the main theme's folder name
 * e.g. for the original version of oik we load the oik theme even when the theme name is oik/oik-bbpress.php
 * 
 * @param string $slug - the theme slug = folder
 * @return post|null
 * 
 */
function oikth_load_theme( $slug ) {
  oik_require( "includes/bw_posts.inc" );
  $atts = array();
  $atts['post_type'] = "oik-themes";
  // $atts['name'] = $slug;
  $atts['meta_key'] = '_oikth_slug';
  $atts['meta_value'] = $slug; 
  $atts['numberposts'] = 1; 
  $atts['exclude'] = -1;
  $posts = bw_get_posts( $atts );
  $post = bw_array_get( $posts, 0, null );
  bw_trace2( $post );
  return( $post );

}

function oikth_load_themeversion( $post ) {
  oik_require( "includes/bw_posts.inc" );
  $post_types = array( 2 => "oik_themeversion"
                     , 3 => "oik_themiumversion"
                     );
  
  $theme_type = get_post_meta( $post->ID, "_oikth_type", true );
  bw_trace2( $theme_type, "theme_type" );
  if ( $theme_type ) {
    $atts = array();
    $atts['post_type'] = bw_array_get( $post_types, $theme_type, "oik_themeversion" );
    $atts['meta_key'] = "_oiktv_theme";
    $atts['meta_value'] = $post->ID;
    $atts['numberposts'] = 1;
    $atts['orderby'] = "post_date";
    $atts['order'] = "desc";
    $posts = bw_get_posts( $atts );
    $version = bw_array_get( $posts, 0, null );
  } else {
    //gobang();
    // None - so we don't have a theme type - this is how the WordPress core is catalogued
    $version = null;
  }  
  return( $version );

}

/**
 * Return the version metadata
 */
function oikth_get_latestversion( $version ) { 
  if ( $version ) 
    $theme_version = get_post_meta( $version->ID, "_oiktv_version", true );
  else
    $theme_version = null;
  return( $theme_version );
}


/**
 * Load the theme for the given theme version or premium version node
 */

function oikth_get_theme( $version ) { 
  if ( $version ) { 
    $theme_id = get_post_meta( $version->ID, "_oiktv_theme", true );
    $theme = get_post( $theme_id );
  } else {
    $theme = null;
  }
  return( $theme );
}



/**
 * Return the value of the field depending on the field type
 */
function bw_return_field( $field_name=null, $data=null ) {
  global $bw_fields;
  $value = $data; 
  $field = bw_array_get( $bw_fields, $field_name, null );
  if ( $field ) {
    $type = bw_array_get( $field, "#field_type", null );
    if ( $type ) {
      $funcname = "bw_return_field_$type";
      if ( is_callable( $funcname ) ) {
        $value = call_user_func( $funcname, $field_name, $data, $field );
     }  
    }  
  }  
  return( $value );
}     

/**
 * Get the value of the "required_version" custom category ( was _oikpv_requires ) 
 * 
 * Note: There should only be ONE value but WordPress does cater for a list
 */
function oikth_get_requires( $version ) { 
  if ( $version ) { 
    // $requires = get_post_meta( $version->ID, "_oiktv_requires", true );
    $requires = get_the_term_list( $version->ID, "required_version", "", ",", "" );
    $requires = bw_return_field( "_oiktv_requires", $requires );
    
  } else {
    $requires = null;
  }  
  bw_trace2( $requires, "requires" ); 
  return( $requires );
}

function oikth_get_tested( $version ) { 
  if ( $version ) {
    //$tested = get_post_meta( $version->ID, "_oiktv_tested", true );
    $tested = get_the_term_list( $version->ID, "compatible_up_to", "", ",", "" );
    $tested = bw_return_field( "_oiktv_tested", $tested );
  }  
  else
    $tested = null;
  return( $tested );
}


function oikth_get_attachment( $version ) {
  oik_require( "includes/bw_posts.inc" );
  $atts = array( "post_type" => "attachment" 
               , "post_parent" => $version->ID
               , "numberposts" => 1
               , "post_mime_type" => "application/zip"
               );
  $posts = bw_get_posts( $atts );
  $attachment = bw_array_get( $posts, 0, null );
  bw_trace2( $attachment );
  if ( $attachment ) {
    $file = get_post_meta( $attachment->ID, "_wp_attached_file", true );
  } else {
    $file = null;
  }
  bw_trace2( $file );
  return( $file );
}

/** 
 * Return the package URL for this theme
 * 
 * @param post $post the theme being downloaded
 * @param post $version the post of the theme version
 * @param string $new_version the new version number
 * @param string $action the action being performed
 * @param string $apikey the (validated) apikey passed on the request
 *   
 * Even when it's a FREE theme version type we use the /themes/download URL form rather
 * than a direct download of the file from the original upload directory
 * that way we can keep track of the number of downloads and the number of updates (from previous versions)
 * 
 * Note: The "theme" name is the "post_name" not the slug. This is checked during oikth_validate_themeversion()
 */
function oikth_get_package( $post, $version, $new_version, $apikey=null, $action="update") {

  $file = oikth_get_attachment( $version );
  if ( $file ) {
    $package = home_url( "/themes/download" );
    $package = add_query_arg( array( "theme" => $post->post_name
                                   , "version" => $new_version
                                   , "id" =>  $version->ID 
                                   , "action" => $action
                                   , "apikey" => $apikey
                                   ), $package );
  } else {
    $package = null;
  } 
  bw_trace2( $package, "package" );   
  return( $package );
  
} 
 
/** 
 * @link http://lewayotte.com/2012/04/18/custom-wordpress-theme-update-repository/
 
   [action] => update-check
    [theme_name] => oik-fum/oik-fum.php
    [version] => 1.0
 */
function oikth_update_check( $oik_theme_action="update-check" ) {
  $response = new stdClass;
  $action = bw_array_get( $_REQUEST, "action", null );
  if ( $action == $oik_theme_action ) {
    $theme = bw_array_get( $_REQUEST, "theme_name", null );
    if ( $theme ) {
      //$version = bw_array_get( $_REQUEST, "version", null );
      oik_require( "admin/oik-admin.inc" );
   
      $slug = bw_get_slug( $theme );   
      $post = oikth_load_theme( $slug );
      if ( $post ) { 
        $version = oikth_load_themeversion( $post );
        if ( $version ) { 
          //$response->slug = $slug;
          $response->new_version = oikth_get_latestversion( $version );
          // $response->url = "http://qw/wpit/oik_theme/" . $slug;
          // $response->url = home_url( "/oik-themes/" . $slug );
          $response->url = get_permalink( $post );
          
          $apikey = bw_array_get( $_REQUEST, "apikey", null );
          
          $package = oikth_get_package( $post, $version, $response->new_version, $apikey );
          if ( $package ) {  
            $response->package = $package; 
          } else { 
            $response = bw_wp_error( "not-found", "Package not found" );
          }    
        } else {
          $response = bw_wp_error( "not-found", "Version not found $slug" );
        }  
      } else {
        $response = bw_wp_error( "not-found", "theme not found $slug" );  
      }
        
    }
     
  } else {
    $response = bw_wp_error( "invalid-action", "Invalid action $action" );
  }
  echo serialize( $response );      
} 

/** 
 * Get userdata for the selected user ID 
 *
 */
function bw_get_userdata( $id, $field, $default ) {
  $userdata = get_userdata( $id );
  bw_trace2( $userdata, "userdata" );
  
  $value = $userdata->data->$field;
  bw_trace2( $value );
  return( $value );
}  

/**
 * Return a link to the author's home page
 * Determine the author's display name from the post author
 * then append it to their website URL
 * 
 * Note: The value in the oik-themes admin profile is not used in this version.
 * 
 */
function bw_get_author_name( $post ) {
  // bw_trace2( $post );
  $author = $post->post_author;
  oik_require( "admin/oik-themes.php", "oik-themes" );
  $users = bw_user_list();
  $author_name = bw_array_get( $users, $author, "bobbingwide" );
  $url = bw_get_userdata( $author, "user_url", "http://www.bobbingwidewebdesign.com/about/herb/" );
  $link = retlink( null, $url, $author_name );
  return( $link );
}

function bw_get_author_profile( $post ) {
  return( "http://profiles.wordpress.org/bobbingwide" );
} 

/**
 * Return the defined FAQ page for the themes server
 */
function oikth_get_FAQ( $post ) {
  oik_require( "admin/oik-admin.inc" );
  $faq = bw_get_option( "faq", "bw_themes_server" );
  if ( $faq ) {
    $post = bw_get_post( $faq, "page" );
    e( bw_excerpt( $post ));
  } else {  
    oik_documentation();
  }
  return( bw_ret() );
}

/**
 */
function oikth_get_sections( $post, $version ) {
  $sections = array();
  $sections['description'] = bw_excerpt( $post );
  $sections['changelog' ] = $version->post_content;
  $sections['info'] = oikth_get_FAQ( $post );
  return( $sections ); 
} 


/**
 * Return the number downloaded - when we're ready to tell them! 
 
 * Note: WordPress expects this figure to be numeric. It doesn't like strings such as "n/a"
 * a blank string seems OK for when it displays theme information
 * [bw_plug name=oik table=y] just leaves a blank
 */
function oikth_get_downloaded( $post, $version ) { 
  return( "" );
} 


/** 

https://spreadsheets.google.com/pub?key=0AqP80E74YcUWdEdETXZLcXhjd2w0cHMwX2U1eDlWTHc&authkey=CK7h9toK&hl=en&single=true&gid=0&output=html


        $response->name = 'my_theme_name';  
        $response->slug = 'my_theme_slug';  
        $response->requires = '3.3';  
        $response->tested = '3.3.1';  
    $response->rating = 100.0; //just for fun, gives us a 5-star rating :)  
        $response->num_ratings = 1000000000; //just for fun, a lot of people rated it :)  
        $response->downloaded = 1000000000; //just for fun, a lot of people downloaded it :)  
        $response->last_updated = "2012-04-15";  
        $response->added = "2012-02-01";  
        $response->homepage = "http://theme.url/";  
        $response->sections = array(  
            'description' =>  'Add a description of your theme',  
            'changelog' =>  'Add a list of changes to your theme'  
        );  
        $response->download_link = 'http://theme.url/download/location'; 
        
upgrade_notice = up to 300 characters saying why the user should upgrade 
        
http://qw/wpit/themes/info?action=theme-information&request=O:8:"stdClass":2:{s:4:"slug";s:7:"bbboing";s:8:"per_page";i:24;}
                                                              O:8:"stdClass":2:{s:4:"slug";s:7:"bbboing";s:8:"per_page";i:24;}
                                                              
           [body] => O:8:"stdClass":18:{s:4:"name";s:8:"BackWPup";
           s:4:"slug";s:8:"backwpup";
           s:7:"version";s:6:"2.1.13";
           s:6:"author";s:56:"<a href="http://danielhuesken.de">Daniel H&#252;sken</a>";
           s:14:"author_profile";s:43:"http://profiles.wordpress.org/danielhuesken";
           s:12:"contributors";a:1:{s:13:"danielhuesken";s:43:"http://profiles.wordpress.org/danielhuesken";}
           s:8:"requires";s:3:"3.1";
           s:6:"tested";s:5:"3.4.1";
           s:13:"compatibility";a:1:{s:5:"3.4.1";a:4:{s:6:"2.1.11";a:3:{i:0;i:80;i:1;i:5;i:2;i:4;}s:6:"2.1.12";a:3:{i:0;i:87;i:1;i:15;i:2;i:13;}s:6:"2.1.13";a:3:{i:0;i:83;i:1;i:6;i:2;i:5;}s:5:"2.1.9";a:3:{i:0;i:100;i:1;i:1;i:2;i:1;}}}
           s:6:"rating";d:91;
           s:11:"num_ratings";i:278;
           s:10:"downloaded";i:308766;
           s:12:"last_updated";s:10:"2012-07-30";
           s:5:"added";s:10:"2009-07-05";
           s:8:"homepage";s:19:"http://backwpup.com";
           s:8:"sections";a:5:{s:11:"description";s:739:"<p>Do backups and more for your WordPress Blog.</p>

<ul>
*/

function oikth_theme_information( $oik_theme_action="info" ) {
  $body = bw_array_get( $_REQUEST, "request", null );
  if ( $body ) {
  
    bw_trace2( $body, "body", false );
    $request = unserialize( stripslashes( $body) );
    
    bw_trace2( $request, "request", false );
    $slug = bw_array_get( $request, "slug", null );
    if ( $slug ) {
    
      $response = new stdClass;
      $response->slug = $slug;
      $post = oikth_load_theme( $slug );
      if ( $post ) { 
        $version = oikth_load_themeversion( $post );
        if ( $version ) { 
          $response->name = $slug;
          $response->last_updated = $version->post_modified;
          $response->version = oikth_get_latestversion( $version );
          $response->author = bw_get_author_name( $post );
          $response->author_profile = bw_get_author_profile( $post );
          $response->requires = oikth_get_requires( $version );
          $response->tested = oikth_get_tested( $version );
          $response->homepage = get_permalink( $post->ID );
          $response->short_description = get_post_meta( $post->ID, "_oiktv_desc", true );
          $response->sections = oikth_get_sections( $post, $version );
          $response->download_url = oikth_get_package( $post, $version, $response->version );
          $response->downloaded = oikth_get_downloaded( $post, $version ); 
        } else {
          $response = bw_wp_error( "not-found", "Version not found" );  
        }
      } else {
        $response = bw_wp_error( "not-found", "theme not found" );
      }  
    } else {
      $response = bw_wp_error( "missing_slug", "Slug missing from request" );
    }
  } else {
    $response = bw_wp_error( "invalid_request", "Request missing" );  
  }
  echo serialize( $response );
}    
     
 
/* 
function oikth_dummy_info() {
  $response = new stdClass;

  $response->slug = "oik-pro";
  $response->theme_name = "oik-pro";  
  $response->new_version = "1.17i"; 
  $response->requires = "3.0.4"; 
  $response->tested = "3.4.1"; 
   
  $response->downloaded = 12126; 
  $response->rating = 100.0; //just for fun, gives us a 5-star rating :)  
  $response->num_ratings = 121; // downloaded / 100   
  $response->last_updated = bw_format_date();
     
  $response->homepage = "http://qw/wpit/oik_theme/dumbo";
  $response->sections = array( 'description' => "over 70 shortcodes"  
                             , 'changelog' => "change log " 
                             , 'FAQ' => "see the FAQ"
                             );

  echo serialize( $response );
}

*/

