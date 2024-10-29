<?php 
/*
Plugin Name: Allow SVG
Plugin URI: https://plugins.wphelpline.com
Description: Allow SVG file upload in Wordpress Media
Version: 1.2.0
Author: WPHelpline
Author URI: https://wphelpline.com
License: GPLv2 or later
Text Domain: allow-svg
*/

// Allow SVG
add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {


	$filetype = wp_check_filetype( $filename, $mimes );



	return [

		'ext'             => $filetype['ext'],

		'type'            => $filetype['type'],

		'proper_filename' => $data['proper_filename']

	];



}, 10, 4 );



// Add svg mime type

add_filter( 'upload_mimes', 'allow_svg_cc_mime_types' );

function allow_svg_cc_mime_types( $mimes ){



	$mimes['svg'] = 'image/svg+xml';



	return $mimes;

}

// remove srcset for svg images
add_filter( 'wp_calculate_image_srcset', 'allow_svg_disable_srcset' );
function allow_svg_disable_srcset( $sources ) {

	$first_ele = reset($sources);
	
	if ( isset($first_ele) && !empty($first_ele['url']) ) {

		$extension = pathinfo(reset($sources)['url'], PATHINFO_EXTENSION);

		if ( $extension == 'svg' ) {

			$sources = array();

			return $sources;

		} else {

			return $sources;

		}

	} else {

		return $sources;

	}

}

// Sanitize SVG content
add_filter('wp_handle_upload', 'allow_svg_sanitize_svg_content');
function allow_svg_sanitize_svg_content($upload)
{
    $file_path = $upload['file'];

    // Check if the uploaded file is an SVG
    $file_info = pathinfo($file_path);
    $file_extension = strtolower($file_info['extension']);

    if ($file_extension === 'svg') {
        $svg_content = file_get_contents($file_path);

        // Sanitize SVG content
        $sanitized_svg = allow_svg_sanitize_svg($svg_content);

        // Overwrite the original SVG file with the sanitized content
        file_put_contents($file_path, $sanitized_svg);
    }

    return $upload;
}

function allow_svg_sanitize_svg($svg_content)
{
    // Use DOMDocument to parse the SVG content
    $dom = new DOMDocument;
    $dom->loadXML($svg_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // Remove potentially harmful elements
    allow_svg_remove_elements($dom);

    // Save the sanitized SVG content
    $sanitized_svg = $dom->saveXML();

    return $sanitized_svg;
}

function allow_svg_remove_elements($dom)
{
    // List of potentially harmful SVG elements to remove
    $remove_elements = array(
        'script',
        'iframe',
        'embed',
        'object',
    );

    foreach ($remove_elements as $element) {
        $elements = $dom->getElementsByTagName($element);
        foreach ($elements as $node) {
            $node->parentNode->removeChild($node);
        }
    }
}