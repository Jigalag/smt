<?php
/**
 * Created by PhpStorm.
 * User: oveprev
 * Date: 2020-01-09
 * Time: 13:27
 */

add_action( "wp_ajax_getSMTSettings", "getSMTSettings" );
add_action( "wp_ajax_nopriv_getSMTSettings", "getSMTSettings" );

add_action( "wp_ajax_getSavedPosts", "getSavedPosts" );
add_action( "wp_ajax_nopriv_getSavedPosts", "getSavedPosts" );

add_action( "wp_ajax_saveGeneralSettings", "saveGeneralSettings" );
add_action( "wp_ajax_nopriv_saveGeneralSettings", "saveGeneralSettings" );

add_action( "wp_ajax_saveTwitterSettings", "saveTwitterSettings" );
add_action( "wp_ajax_nopriv_saveTwitterSettings", "saveTwitterSettings" );

add_action( "wp_ajax_savePosts", "savePosts" );
add_action( "wp_ajax_nopriv_savePosts", "savePosts" );

add_action( "wp_ajax_removePost", "removePost" );
add_action( "wp_ajax_nopriv_removePost", "removePost" );

add_action( "wp_ajax_updatePosition", "updatePosition" );
add_action( "wp_ajax_nopriv_updatePosition", "updatePosition" );

add_action( "wp_ajax_getTwitterFeeds", "getTwitterFeeds" );
add_action( "wp_ajax_nopriv_getTwitterFeeds", "getTwitterFeeds" );



function getSMTSettings() {
    $number_posts = intval(get_option(SMT_NUMBER_POSTS));
    $category_id = intval(get_option(SMT_POST_CATEGORY_ID));
    $token = get_option(SMT_TWITTER_TOKEN);
    $secret = get_option(SMT_TWITTER_SECRET);
    $ck = get_option(SMT_TWITTER_CK);
    $cs = get_option(SMT_TWITTER_CS);
    $twitter = array(
        'token' => $token ? $token : '',
        'token_secret' => $secret ? $secret : '',
        'consumer_key' => $ck ? $ck : '',
        'consumer_secret' => $cs ? $cs : '',
    );

    header('Content-Type: application/json');
    $result = array(
        'general' => array(
            'numberPosts' => $number_posts,
            'categoryId' => $category_id
        ),
        'twitter' => $twitter,
    );
    echo json_encode($result);
    exit;
}

function getSavedPosts() {
    $categoryId = get_option(SMT_POST_CATEGORY_ID);
    if (!$categoryId || $categoryId === 0) {
        echo json_encode(array(
            'data' => array(),
            'error' => true,
            'errorText' => 'Please provide category id'
        ));
        return false;
    }
    $posts = get_posts(array(
        'post_status' => array('publish'),
        'post_type'		=> 'post',
        'cat' => $categoryId,
        'meta_key' => 'position',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'numberposts' => -1,
    ));
    foreach ($posts as $post) {
        $postId = $post->ID;
        $image = get_field('image', $postId);
        $originalId = get_field('original_id', $postId);
        $position = get_field('position', $postId);
        $post->image = $image;
        $post->originalId = $originalId;
        $post->position = $position;
    }
    $result = array(
        'data' => $posts
    );
    echo json_encode($result);
    exit;
}

function fix_hash_tags($post_data) {

    $text = isset( $post_data->retweeted_status->full_text ) ? $post_data->retweeted_status->full_text : $post_data->full_text;

    // Message. Convert links to real links.
    $pattern   = array( '/http:(\S)+/', '/https:(\S)+/', '/@+(\w+)/u', '/#+(\w+)/u' );
    $replace   = array( ' <a href="${0}" target="_blank" rel="nofollow">${0}</a>', ' <a href="${0}" target="_blank" rel="nofollow">${0}</a>', ' <a href="https://twitter.com/$1" target="_blank" rel="nofollow">@$1</a>', ' <a href="https://twitter.com/hashtag/$1?src=hash" target="_blank" rel="nofollow">#$1</a>' );
    $full_text = preg_replace( $pattern, $replace, $text );

    return nl2br( $full_text );
}

function updatePosition() {
    if (!empty(trim(file_get_contents("php://input")))) {
        $post = trim(file_get_contents("php://input"));
        $_POST = (array)json_decode($post);
        $post_id = $_POST['postId'];
        $position = $_POST['position'];
        update_post_meta($post_id, 'position', $position);
    }
    $result = array(
        'data' => array('Field updated')
    );
    echo json_encode($result);
    exit;
}

function removePost() {
    if (!empty(trim(file_get_contents("php://input")))) {
        $post = trim(file_get_contents("php://input"));
        $_POST = (array)json_decode($post);
        $post_id = $_POST['postId'];
        wp_delete_post($post_id);
    }
    $result = array(
        'data' => array('Field updated')
    );
    echo json_encode($result);
    exit;
}

function savePosts() {
    $categoryId = get_option(SMT_POST_CATEGORY_ID);
    if (!$categoryId || $categoryId === 0) {
        echo json_encode(array(
            'data' => array(),
            'error' => true,
            'errorText' => 'Please provide category id'
        ));
        return false;
    }
    $post_ids = [];
    if (!empty(trim(file_get_contents("php://input")))) {
        $post = trim(file_get_contents("php://input"));
        $_POST = (array)json_decode($post);
        // TODO: check if exist
        foreach ($_POST as $saved_post) {
            $post_image = '';
            if (isset($saved_post->entities->media) && isset($saved_post->entities->media[0])) {
                $post_image = $saved_post->entities->media[0]->media_url;
            }
            $content = fix_hash_tags($saved_post);
            $post_object = array(
                'post_content' => $content,
                'post_status' => 'publish',
                'post_title' => mb_strimwidth($saved_post->full_text, 0, 20, '...'),
                'post_type' => 'post',
                'post_category' => array($categoryId),
                'meta_input'  => array(
                    'original_id' => $saved_post->id_str,
                    'image' => $post_image,
                    'position' => 0
                ),
            );
            $post_ids[] = wp_insert_post($post_object);
        }
    }
    $result = array(
        'data' => $post_ids
    );
    echo json_encode($result);
    exit;
}

function build_signature($consumerSecret, $tokenSecret, $params) {
    $base = "GET" . "&" . rawurlencode("https://api.twitter.com/1.1/statuses/user_timeline.json")
        . "&" . rawurlencode($params);
    $key = rawurlencode($consumerSecret) . '&' . rawurlencode($tokenSecret);

    $signature = base64_encode(hash_hmac('sha1', $base, $key, true));

    return $signature;
}
function getNonce($length = 5){
    $result = '';
    $nonce_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $cLength = strlen($nonce_chars);
    for ($i = 0; $i < $length; $i++) {
        $rnum = rand(0, $cLength - 1);
        $result .= substr($nonce_chars, $rnum, 1);
    }

    return $result;
}

function getTwitterFeeds() {
    $token = get_option(SMT_TWITTER_TOKEN);
    $ck = get_option(SMT_TWITTER_CK);
    $token_secret = get_option(SMT_TWITTER_SECRET);
    $cs = get_option(SMT_TWITTER_CS);
    if (!$token || !$ck) {
        echo json_encode(array(
            'data' => array(),
            'error' => true,
            'errorText' => 'Please provide Twitter auth data'
        ));
        exit;
    }
    $timestamp = time();
    $nonce = getNonce();
    $params = 'count=100&oauth_consumer_key='.$ck.'&oauth_nonce='.$nonce.'&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$timestamp.'&oauth_token='.$token.'&oauth_version=1.0&tweet_mode=extended';
    $signature = build_signature($cs, $token_secret, $params);
    $authorization = 'OAuth oauth_consumer_key="'.$ck.'",oauth_token="'.$token.'",oauth_signature_method="HMAC-SHA1",oauth_timestamp="'.$timestamp.'",oauth_nonce="'.$nonce.'",oauth_version="1.0",oauth_signature="'.rawurlencode($signature).'"';
    $custom_headers = array(
        'Authorization' => $authorization
    );
    $response = wp_remote_request('https://api.twitter.com/1.1/statuses/user_timeline.json?tweet_mode=extended&count=100', array(
        'method' => 'GET',
        'headers' => $custom_headers
    ));
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if ($body['errors']) {
        echo json_encode(array(
            'data' => array(),
            'error' => true,
            'errors' => $body['errors']
        ));
        exit;
    }
    $result = array(
        'data' => $body
    );
    echo json_encode($result);
    exit;
}

function saveGeneralSettings() {
    if (!empty(trim( file_get_contents("php://input")))) {
        $post = trim(file_get_contents("php://input"));
        $_POST = ( array ) json_decode( $post );
        $numberPosts = $_POST['numberPosts'];
        $categoryId = $_POST['categoryId'];
        update_option(SMT_NUMBER_POSTS, $numberPosts);
        update_option(SMT_POST_CATEGORY_ID, $categoryId);
    }
}

function saveTwitterSettings() {
    if ( !empty( trim( file_get_contents("php://input" ) ) ) ) {
        $post = trim(file_get_contents("php://input"));
        $_POST = ( array ) json_decode( $post );
        $token = $_POST['token'];
        $secret = $_POST['secret'];
        $ck = $_POST['consumerKey'];
        $cs = $_POST['consumerSecret'];
        update_option(SMT_TWITTER_TOKEN, $token);
        update_option(SMT_TWITTER_SECRET, $secret);
        update_option(SMT_TWITTER_CK, $ck);
        update_option(SMT_TWITTER_CS, $cs);
    }
}