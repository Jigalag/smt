<?php

/*
Plugin Name: SM Trending
Version: 1.0
Author: Veprev Oleksii
Description: Get trending posts from several social networks
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('SMT_URL', plugins_url( '/', __FILE__ ));
define('SMT_PATH', plugin_dir_path( __FILE__ ));

define('SMT_NUMBER_POSTS', 'smt_num_posts');
define('SMT_POST_CATEGORY_ID', 'smt_post_category_id');

define('SMT_TWITTER_TOKEN', 'smt_twitter_token');
define('SMT_TWITTER_SECRET', 'smt_twitter_secret');
define('SMT_TWITTER_CK', 'smt_twitter_ck');
define('SMT_TWITTER_CS', 'smt_twitter_cs');

require_once SMT_PATH.'admin/settings.php';

require(SMT_PATH.'admin/ajax-api.php');

