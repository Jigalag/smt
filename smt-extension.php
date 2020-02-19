<?php

/*
Plugin Name: SM Trending
Version: 1.0
Author: oveprev
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'SMT_URL', plugins_url( '/', __FILE__ ) );
define( 'SMT_PATH', plugin_dir_path( __FILE__ ) );

$policy_page_id = 0;


require_once SMT_PATH.'admin/settings.php';

wp_enqueue_script('jquery');
wp_enqueue_style('smt-styles', plugins_url( '/assets/styles/styles.css', __FILE__ ), array(), '1.0.2');
wp_enqueue_script('encrypt-script', plugins_url( '/assets/js/bundle.js', __FILE__ ), array(), false, true);


function the_slug_exists($post_name)
{
    global $wpdb;
    if ($wpdb->get_row("SELECT post_name FROM wp_posts WHERE post_name = '" . $post_name . "'", 'ARRAY_A')) {
        return true;
    } else {
        return false;
    }
}

require(SMT_PATH.'admin/ajax-api.php');

