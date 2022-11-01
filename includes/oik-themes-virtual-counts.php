<?php

function oikth_templates_count( $args = null ) {
	$templates_count = '?';
	$id = bw_current_post_id();
	//$post = get_post( $id );
	$theme = get_post_meta( $id, '_oikth_slug', true );
	if ( $theme ) {
		oik_require( "includes/class-oik-themes-content.php", "oik-themes" );
		$oik_themes_content=new OIK_themes_content();
		$templates_count = $oik_themes_content->count_template_files( $theme );
	}
	return $templates_count;
}

function oikth_parts_count( $args = null ) {
	$parts_count = '?';
	$id = bw_current_post_id();
	//$post = get_post( $id );
	$theme = get_post_meta( $id, '_oikth_slug', true );
	if ( $theme ) {
		oik_require( "includes/class-oik-themes-content.php", "oik-themes" );
		$oik_themes_content=new OIK_themes_content();
		$parts_count = $oik_themes_content->count_parts_files( $theme );
	}
	return $parts_count;

}

function oikth_patterns_count( $args = null ) {

	$patterns_count = '?';
	$id = bw_current_post_id();
	//$post = get_post( $id );
	$theme = get_post_meta( $id, '_oikth_slug', true );
	if ( $theme ) {
		if ( function_exists( 'oik_patterns_loaded')) {
			oik_require( 'libs/class-oik-patterns-import.php', 'oik-patterns' );
			$oik_patterns_import=new OIK_patterns_import( $theme );
			$patterns_count     =$oik_patterns_import->count_patterns();
		}
	}
	return $patterns_count;

}

function oikth_styles_count( $args = null ) {
    $styles_count = '?';
    $id = bw_current_post_id();
    //$post = get_post( $id );
    $theme = get_post_meta( $id, '_oikth_slug', true );
    if ( $theme ) {
        oik_require( "includes/class-oik-themes-content.php", "oik-themes" );
        $oik_themes_content=new OIK_themes_content();
        oik_require("includes/class-oik-themes-json-styles.php", "oik-themes");
        $themes_json_styles = new OIK_themes_json_styles( $theme, $oik_themes_content );
        $styles_count = $themes_json_styles->count_styles();
    }
    return $styles_count;
}
