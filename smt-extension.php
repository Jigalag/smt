<?php

/*
Plugin Name: SM Trending
Version: 1.0
Author: oveprev
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'SMT_URL', plugins_url( '/', __FILE__ ) );
define( 'SMT_PATH', plugin_dir_path( __FILE__ ) );

require_once SMT_PATH.'admin/settings.php';

require(SMT_PATH.'admin/ajax-api.php');

