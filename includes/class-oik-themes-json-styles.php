<?php

/**
 * @copyright (C) Bobbing Wide 2022
 * @package oik-themes
 */

class OIK_themes_json_styles
{

    private $oik_themes_content = null;
    private $slug = null;

    /**
     * The theme's primary theme.json file. Referenced when the style doesn't contain the requested field.
     * Assume to be the first theme loaded into $variations.
     * @var null
     */
    private $theme_json = null;

    /**
     * Array of variations including the default theme.
     * @var
     */
    private $variations;


    function __construct( $slug, $oik_themes_content ) {
        $this->slug = $slug;
        $this->oik_themes_content = $oik_themes_content;
    }

    function load_variations() {
        //$styles = WP_Theme_JSON_Resolver::get_style_variations();
        $style_files = $this->get_all_styles( $this->slug );
        $this->variations = [];
        foreach ( $style_files as $style_file ) {
            $this->variations[] = $this->fetch_style_variation( $style_file );
        }
        $this->theme_json = $this->variations[0];
    }

    function display_styles() {

        $additional_content = "Theme styles";
        $this->load_variations();

        $additional_content .= '<ol>';
        foreach ( $this->variations as $variation ) {
            //print_r( $variation);
            //$fancy_stuff =  $this->get_contentSize ( $variation );
            $palettes = $this->get_field( $variation, 'settings.color.palette');
            $additional_content .= '<li>';
            $additional_content .= '<div ';
            $additional_content .= $this->getForegroundBackground( $variation );

            $additional_content .= '>';
            $additional_content .= $this->get_title( $variation );

            $additional_content .= $this->get_contentSize ( $variation );
            $additional_content .= $this->get_contentSize( $variation, 'wide:', 'settings.layout.wideSize');
            $additional_content .= $this->getColorPalette( $variation );
            $additional_content .= '</div>';
            $additional_content .= '</li>';

        }
        $additional_content .= '</ol>';
        //print_r( $variations );

        //print_r( $style_files );
        return $additional_content;
    }

    function get_all_styles( $slug ) {
        $theme_dir = get_theme_root();
        $theme_dir .= '/';
        $theme_dir .= $slug;
        $dirs = [ '.' /*, 'styles' */ ];
        $masks = [ '*.json' ];
        $files = [];
        foreach ( $dirs as $dir ) {
            $files1 = $this->oik_themes_content->get_subdir_file_list( $theme_dir . '/' . $dir, $masks );
            $files = array_merge( $files, $files1 );
        }
        return $files;

    }

    /**
     * Fetches the style variation.
     *
     * Note: Rather than using WordPress core functions to merge styles we're going to do it ourselves
     *
     * When this
     * @param $path
     * @return array
     */
    function fetch_style_variation( $path ) {
        $variation = wp_json_file_decode( $path, array( 'associative' => true ) );
        //if ( is_array( $decoded_file )) {
        //    $variation  = ( new WP_Theme_JSON( $decoded_file ) )->get_raw_data();
        if ( empty( $variation['title'] ) ) {
                $variation['title'] = basename( $path, '.json' );
        }
        //bw_trace2( $variation, "variation" );
        return $variation;
    }

    function get_title( $variation ) {
        $title = '<div ';
        $title .= $this->getH1Style( $variation );
        $title .= '>';
        $title .= $variation['title'];
        $title .= '</div>';
        return $title;

    }


    function getH1Style( $variation ) {
        $style = 'style="';
        $style .= 'color:';
        $color = $this->get_style_field( $variation, 'styles.elements.h1.color.text' );
        $color = $this->replace_cssvars( $variation, $color );
        $style .= $color;
        $style .= '" ';
        return $style;
    }

    function get_contentSize( $variation, $label="width: ", $key='settings.layout.contentSize' ) {
        $content_size = "<div ";
        $content_size .= $this->getH1Style( $variation );
        $content_size .= '>';
        $content_size .= $label;
        $value = $this->get_style_field( $variation, $key);
        //$content_size .= $variation['settings']['layout']['contentSize'];
        $content_size .= $value;
        $content_size .= '</div>';
        return $content_size;
    }

    function getForegroundBackground( $variation ) {
        $style = 'style="display:grid; ';
        $foreground = $this->get_style_field( $variation, 'styles.color.text' );
        //echo "F:$foreground:F";
        $background = $this->get_style_field( $variation, 'styles.color.background');
        $foreground = $this->replace_cssvars( $variation, $foreground );
        $background = $this->replace_cssvars( $variation, $background );
        $style .= "color:$foreground; ";
        $style .= "background-color:$background; ";
        //$style .= "justify-content: space-between; ";
        $style .= "gap:10px; ";
        $style .= 'max-width:';
        $style .= $this->get_style_field( $variation, 'settings.layout.contentSize');
        $style .= '"';
        return $style;
    }


    function getColorPalette( $variation ) {
        //print_r( $variation );
        $palettes = $this->get_field( $variation, 'settings.color.palette');
        //print_r( $palettes );
        $colors = '';
        $colors .= '<div style="display:flex; height:1em;">';
        foreach ( $palettes as $palette ) {
            $colors .= '<div style=" width:1em; border-radius:50%; border: 1px solid grey; ';
            $colors .= 'background:';
            $colors .= $palette['color'];
            $colors .= '"';
            $colors .= "title=\"${palette['name']}\"";
            $colors .= '/>&nbsp;';
            //$colors .= $palette['name'];
            $colors .= '</div>';
        }
        $colors .= '</div>';

        return $colors;
    }

    /**
     * Obtains a field from the variation or theme.json.
     *
     * @param $variation
     * @param $key
     * @param $default
     * @return mixed|null
     */
    function get_style_field( $variation, $key, $default=null ) {
        $value = $this->get_field( $variation, $key, null );
        if ( null === $value ) {
            $value = $this->get_field( $this->theme_json, $key, $default );
        }
        return $value;

    }

    function get_field( $variation, $key, $default=null ) {
        $keys = explode( '.',  $key);
        //print_r( $keys );
        $value = $variation;
        foreach ( $keys as $key ) {
            $value = bw_array_get( $value, $key, null  );
            if ( null === $value ) {
                break;
            }
        }
        //echo $value;
        return $value;

    }

    function replace_cssvars( $variation, $cssvar ) {
        //print_r( $cssvar );
        //print_r( $variation );

        $palettes = $this->get_field( $variation, 'settings.color.palette');
        //print_r( $palettes );
        foreach ( $palettes as $palette ) {
            //print_r( $palette );
            $preset_name = $this->get_preset_name( $palette );
            $preset_color = $this->get_preset_color( $palette );
            if ( $preset_name === $cssvar ) {
                $cssvar = $preset_color;
                //echo "$preset_name $preset_color";
            }
            //$cssvar = str_replace( $preset_name, $preset_color, $cssvar );

        }

        return $cssvar;
    }

    function get_preset_name( $palette ) {
        $preset_name = 'var(--wp--preset--color--';
        $preset_name .= $palette['slug'];
        $preset_name .= ')';
        return $preset_name;
    }

    function get_preset_color( $palette ) {
        return $palette['color'];
    }


}