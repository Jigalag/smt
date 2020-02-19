<?php
/**
 * Created by PhpStorm.
 * User: oveprev
 * Date: 2020-01-09
 * Time: 13:27
 */

add_action( "wp_ajax_getPolicyPage", "get_policy_page" );
add_action( "wp_ajax_nopriv_getPolicyPage", "get_policy_page" );


function get_policy_page() {

    $url = get_page_link($_GET['pageId']);

    header('Content-Type: application/json');
    $result = array(
        'pageUrl' => $url,
    );
    echo json_encode($result);
    exit;
}