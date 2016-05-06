<?php
/*

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


/**
 * Define oik theme server settings
 */
function oikth_lazy_admin_menu() {
  register_setting( 'oik_themes_server', 'bw_themes_server', 'oik_plugins_validate' ); // No validation for oik-themes - uses dummy function
  add_submenu_page( 'oik_menu', 'oik server settings', "Theme server settings", 'manage_options', 'oik_theme_server', "oik_themes_options_do_page" );
}

/**
 * oik themes server settings
*/
function oik_themes_options_do_page() {
  oik_menu_header( "themes server settings", "w90pc" );
  oik_box( NULL, NULL, "Defaults", "oik_themes_server_options" );
  oik_box( null, null, "Status", "oik_themes_status" );
  oik_menu_footer();
  bw_flush();
}

/**
 * Display the oik-themes server options
 */
function oik_themes_server_options() {
  $option = 'bw_themes_server'; 
  $options = bw_form_start( $option, 'oik_themes_server' );
  bw_textfield_arr( $option, "Folder for premium themes", $options, 'zipdir', 40 );
  //bw_textfield_arr( $option, "Author", $options, 'author', 30 );
  // Do we have a field for "users" ?
  //bw_select_arr( $option, "Author name", $options, "display_name", array( "#options" =>  bw_user_list() ));
  // bw_form_field_noderef( "bw_themes_server[author_id], "", "Author", $options['author_id'], array( "#type" => "user" ));
  //bw_form_field_noderef( "bw_themes_server[faq]", "", "FAQ page", $options['faq'], array( "#type" => "page", "#optional" => true ));
  etag( "table" );   
  p( isubmit( "ok", "Update", null, "button-primary" ) );
  etag( "form" );
  bw_flush();
}

/* Summarise the downloads and updates for each theme
*/
function oik_themes_status() {
  $atts = array( "post_type" => "oik-themes" 
               , "orderby" => "meta_value"
               , "meta_key" => "_oikth_slug" 
  );
  oik_require( "includes/bw_posts.inc" );
  $posts = bw_get_posts( $atts );
  foreach ( $posts as $post ) {
    oik_themes_summarise_versions( $post ); 
  }
  oik_themes_status_report();
}

/**
 * Accumulate the figures for the theme version
 */ 
function oik_themes_accumulate_version( $post, $theme ) {
  $version = get_post_meta( $post->ID, "_oiktv_version", true );
  $downloads = get_post_meta( $post->ID, "_oiktv_download_count", true );
  $updates = get_post_meta( $post->ID, "_oiktv_update_count", true );
  //e( $downloads );
  //e( $updates );
  oik_themes_add_version( $theme, $version, $downloads, $updates );
}

/**
 * Return the result of adding $amount to $array[$index1][$index2] 
 * 
 * Example: bw_array_add2( $downloads, $themes, $versions, $download );
 * 
 */
if ( !function_exists( "bw_array_add2" ) ) { 
function bw_array_add2( &$array, $index, $index2, $amount ) {
  if ( ! isset($array[$index][$index2]) ) {
    $value = $amount;
  } else {
    $value = $array[$index][$index2] + $amount;
  }
  return( $value );  
}
}

/**
 *  
 */
function oik_themes_add_version( $theme, $version, $download, $updates ) {
  global $bw_theme_totals;
  $bw_theme_totals['Total']['downloads'] = bw_array_add2( $bw_theme_totals, "Total",  "downloads",$download ); 
  $bw_theme_totals['Total']['updates'] = bw_array_add2( $bw_theme_totals, "Total", "updates", $updates );
  $bw_theme_totals[$theme]['downloads'] = bw_array_add2( $bw_theme_totals, $theme, "downloads", $download );
  $bw_theme_totals[$theme]['updates'] = bw_array_add2( $bw_theme_totals, $theme, "updates", $updates );
}

/**
 * Summarise the versions for this theme
 */
function oik_themes_summarise_versions( $post ) {
  //bw_trace2();
  //p( $post->post_title );
  $theme = get_post_meta( $post->ID, "_oikth_slug", true );
  $version_type = get_post_meta( $post->ID, "_oikth_type", true );
  //e( $version_type );
  $versions = bw_theme_post_types();
  $post_type = bw_array_get( $versions, $version_type, null ); 
  //e( $post_type );
  $atts = array( "post_type" => $post_type 
               , "numberposts" => -1
               , "meta_key" => "_oiktv_theme" 
               , "meta_value" => $post->ID
               );
  $posts = bw_get_posts( $atts );
  if ( $posts ) {
    foreach ( $posts as $post ) {
      //p( $post->post_title .  $post->post_type );
      oik_themes_accumulate_version( $post, $theme );
    }
  }
}


function oik_themes_status_report() {
  global $bw_theme_totals;
  stag( "table", "widefat bw_themes" );
  stag( "tr" );
  th( "theme" );
  //th( "Version" );
  th( "Downloads" );
  th( "Updates" );
  th( "Totals" ); 
  etag( "tr" );

  foreach ( $bw_theme_totals as $theme => $theme_total  ) {
    stag( "tr" );
    td( $theme );
    td( $theme_total['downloads'] );
    td( $theme_total['updates'] );
    td( $theme_total['downloads'] + $theme_total['updates'] );
    etag( "tr" );
  } 
  etag( "table" );
}


