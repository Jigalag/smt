<?php
/**
 * Created by PhpStorm.
 * User: oveprev
 * Date: 2019-11-11
 * Time: 16:03
 */

function sm_trending() {
    require_once 'templates/smt-admin-page.php';
}

function smt_menu() {
    add_menu_page("SM Trending", "SM Trending", "sm_manage_options", "sm_trending", "sm_trending", "");
}
add_action("admin_menu", "smt_menu");


$admin = get_role( 'administrator' );
if ( ! empty( $admin ) ) {
    $admin->add_cap( 'sm_manage_options' );
}
$editor = get_role( 'editor' );
if ( ! empty( $editor ) ) {
    $editor->add_cap( 'sm_manage_options' );
}