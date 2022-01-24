<?php 
/**
Plugin Name: oik themes server
Depends: oik base plugin, oik fields, oik-plugins
Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-themes
Description: oik themes server for themium and free(mium) oik themes
Version: 1.4.3
Author: bobbingwide
Author URI: https://www.oik-plugins.com/author/bobbingwide
Text Domain: oik-themes
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2013-2022 Bobbing Wide (email : herb@bobbingwide.com )

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

oikth_plugin_loaded();

/**
 * Function to invoke when plugin file loaded 
 */                                
function oikth_plugin_loaded() {
  add_action( "init", "oikth_theme_rewrite" );
  add_action( 'oik_fields_loaded', 'oikth_init' );
  add_action( "admin_notices", "oikth_activation" );
}

/** 
 * Implement "init" action for oik themes server
 * 
 * Implement the oik equivalent of WordPress.org responding to 
 *  http://api.wordpress.org/themes/update-check/1.0/
 *  http://api.wordpress.org/themes/info/1.0/
 */
function oikth_theme_rewrite() {
  add_rewrite_tag( "%oik-theme%", '([^/]+)' );
  add_permastruct( 'oik-theme', 'themes/%oik-theme%' );
  add_action( "template_redirect", "oikth_template_redirect" ); 
  add_filter( "wp_handle_upload", "oikth_handle_upload", 10, 2 );
}

/**
 * @TODO TBC
 */   
function oikth_inspect_request_uri() {
  $request_uri = $_SERVER['REQUEST_URI'];
  bw_trace2(  $request_uri, "request_uri" );
  if ( substr( $request_uri, -1 ) == "?" ) {
     bw_trace2( "Shall we iframe it?" );
     $template = locate_template( 'oik-themes-iframe.php' );
     if ( '' != $template  ) {
       echo "$template";
       require $template;
       exit();
     } else {
       echo "No template for iframe" ;
     }    
  } else {
     bw_trace2( "Looks like a normal request" );  
  }
}

/**
 * Handle the themes/%oik-theme% request
 */
function oikth_template_redirect() {
  $oik_theme = get_query_var( "oik-theme" );
  //bw_trace2( $oik_theme, "oik-theme", false );
  if ( $oik_theme ) {
    oik_require( "feed/oik-themes-feed.php", "oik-themes" );
    oikth_lazy_redirect( $oik_theme ); 
  } 
  // oikth_inspect_request_uri();
}

/**
 * Implement the "oik_fields_loaded" action for oik themes server
 */
function oikth_init( ) {
  oik_register_oik_count_fields();
  oik_register_oik_theme();
  oik_register_oik_themeversion();
  oik_register_oik_themiumversion();
  
  add_action( 'add_meta_boxes', 'oikth_header_meta_box' );
  bw_add_shortcode( "oikth_download", "oikth_download", oik_path("shortcodes/oik-themes.php", "oik-themes"), false );
  // add_action( 'the_post', "oikth_the_post", 10, 1 );
  
  // As a temporary workaround to a problem where oikth_the_content thinks we've gone recursive
  // stop jetpack from messing with opengraph tags
  // or stop wp_trim_excerpt() from being called when processing the 'get_the_excerpt' filter
  
  remove_action( 'wp_head', 'jetpack_og_tags' );
  //remove_action( 'get_the_excerpt', 'wp_trim_excerpt'  );
  add_action( 'the_content', "oikth_the_content", 1, 1 );
  add_action( "oik_admin_menu", "oikth_admin_menu" );
}

/**
 * Return an array of theme types
 *
 * This is used as a select field. The alternative is to use a custom category
 * 
 * @return array - values for different theme types
 */
function bw_theme_types() {
  $theme_types = array( 0 => "None"
                       , 1 => "WordPress theme"
                       , 2 => "FREE oik theme"
                       , 3 => "Premium oik theme"
                       , 4 => "Other premium theme"
                       , 5 => "Bespoke"
                       , 6 => "WordPress and FREE theme"
					 , 7 => "Other theme"
      , 8 => "Full Site Editing (FSE) WordPress theme"
	  , 9 => "Full Site Editing (FSE) theme"
                       );
  return( $theme_types );                      
}

/**
 * Return an array of theme version types associated with different theme types
 *
 * @return array - post_types for the theme version
 */
function bw_theme_post_types() {
  $post_types = array( null
                     , "oik_themeversion"
                     , "oik_themeversion"
                     , "oik_themiumversion"
                     , "oik_themiumversion"
                     , "oik_themeversion"
                     , "oik_themeversion"
										 , null
                     );
  return( $post_types );
}                   

/**
 * Register the oik-themes custom post type
 *
 * - The description is the content field
 * - The title should contain the theme slug and the theme description
 * - The featured image is for the theme banner
 * - You can also manually create excerpts
 */
function oik_register_oik_theme() {
  $post_type = 'oik-themes';
  $post_type_args = array();
  $post_type_args['label'] = __( 'Themes', "oik" );
  $post_type_args['description'] = __( 'WordPress theme', "oik" );
  $post_type_args['supports'] = array( 'title', 'editor', 'thumbnail', 'excerpt' );
  $post_type_args['has_archive'] = true;
  $post_type_args['menu_icon'] = 'dashicons-admin-appearance';
	
	$post_type_args['show_in_rest'] = true;
	$post_type_args['rest_base'] = 'oik-themes';
	$post_type_args['rest_controller_class'] = 'WP_REST_Posts_Controller';
  bw_register_post_type( $post_type, $post_type_args );


  bw_register_field( "_oikth_type", "select", "Theme type", array( '#options' => bw_theme_types() ) ); 
  bw_register_field( "_oikth_slug", "text", "Theme name" ); 
  bw_register_field( "_oikth_desc", "text", "Description" );
  bw_register_field( "_oikth_demo", "URL", "Live demonstration" ); 
  bw_register_field( "_oikth_template", "noderef", "Template", array( '#type' => 'oik-themes', '#optional' => true ) ); // Parent theme name



	/** Currently we support two different systems for delivering themium themes: WooCommerce and Easy Digital Downloads
   * The Purchasable product should be completed for each themium oik theme (and Other themium theme? )
   */
  $purchasable_product_type = array();
  $purchasable_product_type[] = "download"; 
  $purchasable_product_type[] = "product"; 
  bw_register_field( "_oikth_prod", "noderef", "Purchasable product", array( '#type' => $purchasable_product_type, '#optional' => true, '#theme_null' => false ) ); 
	  
	bw_register_field_for_object_type( "_component_version", $post_type );
  bw_register_field_for_object_type( "_oikth_type", $post_type );
  bw_register_field_for_object_type( "_oikth_slug", $post_type );
  //bw_register_field_for_object_type( "_oikth_name", $post_type );
  bw_register_field_for_object_type( "_oikth_desc", $post_type );
  bw_register_field_for_object_type( "_oikth_prod", $post_type );
  bw_register_field_for_object_type( "_oikth_demo", $post_type );
  bw_register_field_for_object_type( "_oikth_template", $post_type );
	bw_register_field_for_object_type( "_oikth_templates_count", $post_type );
	bw_register_field_for_object_type( "_oikth_parts_count", $post_type );
	bw_register_field_for_object_type( "_oikth_patterns_count", $post_type );
	
  bw_register_field_for_object_type( "_oikp_git", $post_type );
  oikth_columns_and_titles( $post_type );
}

/** 
 * Return a candidate function name from the given string
 * 
 * Converts spaces and hyphens to underscores 
 * and converts to lowercase - which is not actually necessary for PHP code but can help in legibility
 *
 */
if ( !function_exists( "bw_function_namify" ) ) {
function bw_function_namify( $name ) {
  $name = trim( $name );
  $name = str_replace( ' ', '_', $name );
  $name = str_replace( '-', '_', $name );
  $name = strtolower( $name );
  return( bw_trace2( $name ) ); 
}
} 

/** 
 * Add filters for the $post_type
 * @param string $post_type - the Custom Post type name
 */ 
function oikth_columns_and_titles( $post_type ) {
  $post_type_namify = bw_function_namify( $post_type );
  add_filter( "manage_edit-${post_type}_columns", "${post_type_namify}_columns", 10, 2 );
  add_action( "manage_${post_type}_posts_custom_column", "bw_custom_column_admin", 10, 2 );
  add_filter( "oik_table_fields_${post_type}", "${post_type_namify}_fields", 10, 2 );
  //add_filter( "oik_table_titles_${post_type}", "${post_type_namify}_titles", 10, 3 ); 
}

/**
 * Return the columns to be displayed in the All post_type display admin page
 */
function oik_themes_columns( $columns, $arg2=null ) {
  $columns['_oikth_type'] = __("Type"); 
  $columns['_oikth_slug'] = __("Slug" );
  $columns['_oikth_desc'] = __("Description" );
  $columns['_oikth_prod'] = __("Product" );
  //bw_trace2();
  return( $columns ); 
}

/**
 * Return the fields to be displayed in a table
 */ 
function oik_themes_fields( $fields, $arg2 ) {
  $fields['title'] = 'title';
  $fields['excerpt'] = 'excerpt';
  $fields['_oikth_type'] = '_oikth_type';
  $fields['_oikth_slug'] = '_oikth_slug';
  $fields['_oikth_desc'] = '_oikth_desc' ;
  // $fields['_oikth_prod'] = '_oikth_prod' ;
  return( $fields );
}

/**
 * Return the column titles
 * 
 * Titles are remarkably similar to columns for the admin pages
 * We remove the Product column since it's not working properly - it's an optional field!
 *
 * @param array $titles
 * @param string $arg2
 * @param 
 * @return array Updated titles 
 */
function oik_themes_titles( $titles, $arg2=null, $fields=null ) {
  $titles = oik_themes_columns( $titles, $arg2 );
  unset( $titles['_oikth_prod'] );
  return( $titles );
}

/** 
 * Add custom header support as required 
 */
function oikth_header_meta_box() {
  if ( function_exists( "bw_oik_header_box2" ) ) {
    add_meta_box( 'bw_oik_header2', 'Custom header image', 'bw_oik_header_box2', "oik-theme" );
    //add_meta_box( 'bw_oik_header2', 'Custom header image', 'bw_oik_header_box2', "oik-themium" );
  }
}

/**
 * Create the oik_themeversion custom post type
 *
 * Any zip file that is attached to this post type should automatically be stored
 * in a safe location so that it can only be downloaded by a controlled request
 * Protected (themium) files will require a valid API key
 * 
 */
function oik_register_oik_themeversion() {
  $post_type = 'oik_themeversion';
  $post_type_args = array();
  $post_type_args['label'] = __( 'oik theme versions', 'oik-themes' );
  $post_type_args['description'] = __( 'oik theme version', 'oik-themes' );
  $post_type_args['taxonomies'] = array( 'required_version', 'compatible_up_to' );
  $post_type_args['has_archive'] = true;
  $post_type_args['menu_icon'] = 'dashicons-shield';
	$post_type_args['show_in_rest'] = true;
  bw_register_post_type( $post_type, $post_type_args );
  oik_register_oik_themeversion_fields( $post_type );
}

/**
 * Register the fields for the oik_themeversion and oik_themiumversion CPTs
 *  
 * The title should contain the name and version e.g. oik2012 v0.1
 */
function oik_register_oik_themeversion_fields( $post_type ) {
  
  bw_register_field( "_oiktv_theme", "noderef", "theme", array( '#type' => 'oik-themes') );   
  bw_register_field( "_oiktv_version", "text", "Version", array( '#hint' => "(omit the v)" ) ); 
  
  /*
  $wp_versions = array( 0 => "3.0.4"
                      , 1 => "3.4.1"
                      , 2 => "3.4.2"
                      , 3 => "3.5" 
                      , 4 => "3.5.1"
                      , 5 => "3.6-alpha-23386" // 8 Feb 2013"
                      );
  */
  //bw_register_field( "_oiktv_requires", "select", "Requires", array( '#options' => $wp_versions, '#theme' => false ) ); 
  //bw_register_field( "_oiktv_tested", "select", "Tested", array( '#options' => $wp_versions, '#theme' => false ) ); 
  // bw_register_field( "_oiktv_upgrade", "textarea", "Upgrade notice" ); 
  bw_register_field( "_oiktv_download_count", "numeric", "Download count", array( '#theme' => false ) );
  bw_register_field( "_oiktv_update_count", "numeric", "Update count", array( '#theme' => false ) );
  
  bw_register_field_for_object_type( "_oiktv_version", $post_type );
  bw_register_field_for_object_type( "_oiktv_theme", $post_type );
  //bw_register_field_for_object_type( "_oiktv_requires", $post_type );
  //bw_register_field_for_object_type( "_oiktv_tested", $post_type );
  // bw_register_field_for_object_type( "_oiktv_upgrade", $post_type );
  bw_register_field_for_object_type( "_oiktv_download_count", $post_type );
  bw_register_field_for_object_type( "_oiktv_update_count", $post_type );
  
  oikth_columns_and_titles( $post_type );
  
}

/**
 * Return the columns to be displayed
 */
function oik_themeversion_columns( $columns, $args=null ) {
  $columns['_oiktv_version'] = __("Version"); 
  $columns['_oiktv_theme'] = __("Theme" );
  $columns['_oiktv_download_count'] = __("Downloads" );
  $columns['_oiktv_update_count'] = __("Updates" );
  return( $columns ); 
}

/**
 * Return the fields to be displayed in a table
 */ 
function oik_themeversion_fields( $fields, $arg2 ) {
  $fields['title'] = 'title';
  $fields['excerpt'] = 'excerpt';
  $fields['_oiktv_version'] = '_oiktv_version' ;
  return( $fields );
}

/**
 * Create titles for oik_themeversion
 * 
 * Titles are remarkably similar to columns for the admin pages
 * Except when you don't want them by default
 */
function oik_themeversion_titles( $titles, $arg2, $fields=null ) {
  $titles['_oiktv_version'] = __("Version"); 
  //return( oik_themeversion_columns( $titles, $arg2 ) );
  return( $titles ); 
}

/**
 * Create the oik_themiumversion custom post type
 * 
 * Any zip file that is attached to this post type should automatically be stored
 * in a safe location so that it can only be downloaded by a controlled request
 * Protected (themium) files will require a valid API key
 */
function oik_register_oik_themiumversion() {
  $post_type = 'oik_themiumversion';
  $post_type_args = array();
  $post_type_args['label'] = __( 'oik themium versions', 'oik-themes' );
  $post_type_args['description'] = __( 'oik themium theme version', 'oik-themes' );
  $post_type_args['taxonomies'] = array( 'required_version', 'compatible_up_to' );
  $post_type_args['has_archive'] = true;
  $post_type_args['menu_icon'] = 'dashicons-shield-alt';
	$post_type_args['show_in_rest'] = true;
  bw_register_post_type( $post_type, $post_type_args );
  oik_register_oik_themeversion_fields( $post_type ); 
}


function oik_themiumversion_columns( $columns, $args=null ) {
  return( oik_themeversion_columns( $columns, $args) );
}

/**
 * Return the fields to be displayed in a table
 */ 
function oik_themiumversion_fields( $fields, $arg2 ) {
  $fields['title'] = 'title';
  $fields['excerpt'] = 'excerpt';
  $fields['_oiktv_version'] = '_oiktv_version' ;
  return( $fields );
}

/**
 * Titles are remarkably similar to columns for the admin pages
 */
function oik_themiumversion_titles( $titles, $arg2, $fields=null ) {
  return( oik_themeversion_titles( $titles, $arg2 ) );
}

/** 
 * Add the "oik_theme" feed... don't know why! Herb 2012/07/27
 */
function oikth_theme_add_feed() {
 $hook = add_feed( 'oik_theme', "oikth_theme_feed");
 bw_trace2( $hook );
 
}

function oikth_theme_feed() {
  oik_require( "feed/oik-themes-feed.php", "oik-themes" );
  oik_lazy_oikth_theme_feed();
}

/**  
 * Return true if the current post is of the selected $post_type
 * 
 * @param string $test_post_type - post type to check for
 * @return bool - true if the current post IS of this type, false in all other cases
 */
function oikth_check_post_type( $test_post_type="oik_themiumversion" ) {
  //bw_trace2( $pagenow, "pagenow" );
  $post_id = bw_array_get( $_REQUEST, "post_id", null );
  if ( $post_id ) { 
    $post_type = get_post_type( $post_id );
    $result = $post_type == $test_post_type ;
  } else {
    $result = false; 
  }  
  return( $result );
}

/**
 * Build the full external directory
 *
 * For non Windows servers (e.g. Linux) we need to find the "home" directory and build $external_dir from there
 * @param string - required external directory name with leading and trailing slashes
 * @return string - external directory with "home" directory prepended
 * e.g
 * If [DOCUMENT_ROOT] => /home/t10scom/public_html
 * and $dir parameter is '/zipdir/'
 * then external_directory will become "/home/t10scom/zipdir/"
 */
function oikth_build_external_dir( $dir ) {
  $external_dir = dirname( $_SERVER['DOCUMENT_ROOT'] );
  $external_dir .=  $dir;
  return( $external_dir );
}

/**
 * Create a new file name
 *
 * Can we alter the filter in wp_handle_upload to control where the file gets stored and the 
 * download URL for it?
 *
 * custom backgrounds and custom headers are created using wp_file_upload then wp_insert_attachment
 *
 * Note: by renaming the .zip file then it's no longer accessible from the uploads directory
 * BUT we still have links to it and to all intents and purposes it still exists.
 * So now we can intercept calls to 
 * download?theme=fred&version=1.18 and access the file from the renamed directory.
 *
 */
function oikth_create_new_file_name( $old_file ) {
  //global $pagenow;
  $file = basename( $old_file, ".zip" );
  list( $theme, $version ) = explode( ".", $file, 2);
  if ( $theme && $version ) {
     $zipdir = bw_get_option( "zipdir", "bw_themes_server" );
     if ( PHP_OS == "WINNT" ) {
       $new_file = "C:\\${zipdir}\\";
     } else {
       $new_file = oikth_build_external_dir( "/${zipdir}/" );
     }   
     $new_file .= $theme;
     $new_file .= ".";
     $new_file .= $version;
     $new_file .= ".zip";
   } else {
     $new_file = null;
   }  
  return( $new_file );
}


/**
 * Implement 'wp_handle_upload' filter 
 *
 * In [bw_plug name="easy-digital-downloads"] the files are uploaded to an 'edd' directory in the uploads folder
 * if the post type of the current post is "download" and the current page ( $pagenow ) is  'async-upload.php' or 'media-upload.php'
 *
 * Here we check for a zip file ( with theme name and version number) being uploaded for post_type "oik_themiumversion"
 *
 * If so the file is renamed ( moved ) to a secret target directory
 * if not then we don't do anything
 * In either case the attachment is recorded as if the file has been stored in the uploads
 *
 * `
    C:\apache\htdocs\wordpress\wp-includes\theme.php(142:0) 2012-07-23T21:46:22+00:00 8485 cf! apply_filters(14338) 3 Array
    (
        [0] => wp_handle_upload
        [1] => Array
            (
                [file] => C:\apache\htdocs\wordpress/wp-content/uploads/2012/07/blogger-301-redirect.1.9.51.zip
                [url] => http://qw/wordpress/wp-content/uploads/2012/07/blogger-301-redirect.1.9.51.zip
                [type] => application/zip
            )

        [2] => upload
    ) 
 * `		
 * 
 * @param array $file array containing file, url and type
 * @param string action. e.g. 'upload' 
 * @returns array $file - unchanged
 * 
 */
function oikth_handle_upload( $file, $action ) {
  bw_trace2();
  $type = bw_array_get( $file, "type", null );
  if ( $type == "application/zip" ) {
     $rename = oikth_check_post_type( "oik_themiumversion" );
     if ( $rename ) {  
       $old_file = bw_array_get( $file, 'file', null );
       $new_file = oikth_create_new_file_name( $old_file );
       if ( $new_file ) {
         $renamed = rename( $old_file, $new_file );
       }
     }    
  }     
  return( $file );
}

/**
 * Add some content before 'the_content' filtering
 * 
 * @param post $post
 * @param string $content - the current content
 * @return string additional content
 */
function oikth_the_post_oik_themes( $post, $content ) {
	do_action( "oik_add_shortcodes" );
	$additional_content = null;
  
	$slug = get_post_meta( $post->ID, "_oikth_slug", true );
	
	if ( !is_single() && false === strpos( $post->post_content, "[oikth_download" ) ) {
  
		$additional_content .= "[clear][oikth_download theme='$slug' text='" ;
		$additional_content .= __( "Download", "oik-themes" );
		$additional_content .= " ";
		$additional_content .= $post->post_title;
		$additional_content .= "']";
  }

  if ( is_single() ) {
    oik_require( "includes/class-oik-themes-content.php", "oik-themes" );
		$oik_themes_content = new OIK_themes_content();
		$content = $oik_themes_content->additional_content( $post, $slug );
  } else {
    $content .= $additional_content;
  }  
  return( $content );
} 

/**
 * Add some content before 'the_content' filtering completes for oik_themeversion
 *
 * @param object $post
 * @param string $content
 */
function oikth_the_post_oik_themeversion( $post, $content ) {
  if ( false === strpos( $post->post_content, "[bw_field" ) ) {
    $additional_content = "[bw_fields]";
  } else {
    $additional_content = null;
  }     
  return( $additional_content ); 
}

/**
 * Autogenerate additional content for selected post_types
 *
 * @param string $content
 * @return string update content
 */
function oikth_the_content( $content ) {
	global $post;
	//bw_trace2( $post, "global post" );
	if ( $post ) {
		switch ( $post->post_type ) {
			case "oik-themes": 
				$content = oikth_the_post_oik_themes( $post, $content );
				break;
						
			case "oik_themeversion": 
			case "oik_themiumversion":
				$content .= oikth_the_post_oik_themeversion( $post, $content ); 
				break;  
		}
	}  
	return( $content );
}

/**
 * Implement "oik_admin_menu" for oik-themes 
 */
function oikth_admin_menu() {
	oik_require( "admin/oik-themes.php", "oik-themes" );
	oikth_lazy_admin_menu();
}

/**
 * Dependency checking for oik-themes
 
 * Version | Depends
 * ------- | -------------
 * v0.7    | oik 2.4, oik-fields 1.39 and oik-plugins
 * v1.0.0  | oik v2.6-alpha.0722, oik-fields 1.40 and oik-plugins 1.15.1
 * v1.1.0  | oik 3.0.0, oik-fields 1.40, oik-plugins 1.15.4
 * v1.3.0  | oik 3.2.1, oik-fields 1.50.0, oik-plugins 1.16.0
 */ 
function oikth_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    add_action( "after_plugin_row_oik-themes/oik-themes.php", "oikth_activation" );   
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) { 
      require_once( "admin/oik-activation.php" );
    }
  }  
  $depends = "oik-plugins:1.16.0,oik-fields:1.50.0,oik:3.2.1";
  oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Registers the virtual fields for counts.
 *
 * Note: Plugins can deliver patterns as well. eg core.
 */
function oik_register_oik_count_fields() {
	$templates_args = array( "#callback" => "oikth_templates_count"
	, "#parms" => null
	, "#plugin" => 'oik-themes'
	, "#file" => "includes/oik-themes-virtual-counts.php"
	, "#form" => false
	, "#hint" => "virtual field"
	//, '#theme_null' => false // set this to false when it's not needed in Information
	);
	bw_register_field( '_oikth_templates_count', 'virtual', 'Templates delivered', $templates_args);
	
	$parts_args = array( "#callback" => "oikth_parts_count"
	, "#parms" => null
	, "#plugin" => 'oik-themes'
	, "#file" => "includes/oik-themes-virtual-counts.php"
	, "#form" => false
	, "#hint" => "virtual field"
		//, '#theme_null' => false // set this to false when it's not needed in Information
	);
	bw_register_field( '_oikth_parts_count', 'virtual', 'Template parts delivered', $parts_args );

	$patterns_args = array( "#callback" => "oikth_patterns_count"
	, "#parms" => null
	, "#plugin" => 'oik-themes'
	, "#file" => "includes/oik-themes-virtual-counts.php"
	, "#form" => false
	, "#hint" => "virtual field"
		//, '#theme_null' => false // set this to false when it's not needed in Information
	);
	bw_register_field( '_oikth_patterns_count', 'virtual', 'Patterns delivered', $patterns_args );

}