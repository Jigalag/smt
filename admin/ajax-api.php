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

add_action( "wp_ajax_saveFacebookSettings", "saveFacebookSettings" );
add_action( "wp_ajax_nopriv_saveFacebookSettings", "saveFacebookSettings" );

add_action( "wp_ajax_saveTwitterPosts", "saveTwitterPosts" );
add_action( "wp_ajax_nopriv_saveTwitterPosts", "saveTwitterPosts" );

add_action( "wp_ajax_saveFacebookPosts", "saveFacebookPosts" );
add_action( "wp_ajax_nopriv_saveFacebookPosts", "saveFacebookPosts" );

add_action( "wp_ajax_removePost", "removePost" );
add_action( "wp_ajax_nopriv_removePost", "removePost" );

add_action( "wp_ajax_updatePosition", "updatePosition" );
add_action( "wp_ajax_nopriv_updatePosition", "updatePosition" );

add_action( "wp_ajax_getTwitterFeeds", "getTwitterFeeds" );
add_action( "wp_ajax_nopriv_getTwitterFeeds", "getTwitterFeeds" );

add_action( "wp_ajax_publishPosts", "publishPosts" );
add_action( "wp_ajax_nopriv_publishPosts", "publishPosts" );



function getSMTSettings() {
    $number_posts = intval(get_option(SMT_NUMBER_POSTS));
    $category_id = intval(get_option(SMT_POST_CATEGORY_ID));
    $draft_category_id = intval(get_option(SMT_DRAFT_POST_CATEGORY_ID));
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
    $facebook_token = get_option(SMT_FACEBOOK_TOKEN);
    $facebook = array(
        'token' => $facebook_token
    );
    header('Content-Type: application/json');
    $result = array(
        'general' => array(
            'numberPosts' => $number_posts,
            'categoryId' => $category_id,
            'draftCategoryId' => $draft_category_id
        ),
        'facebook' => $facebook,
        'twitter' => $twitter,
    );
    echo json_encode($result);
    exit;
}

function getSavedPosts() {
    $draftCategoryId = get_option(SMT_DRAFT_POST_CATEGORY_ID);
    $categoryId = get_option(SMT_POST_CATEGORY_ID);
    if ((!$categoryId || $categoryId == 0) && (!$draftCategoryId || $draftCategoryId == 0)) {
        echo json_encode(array(
            'data' => array(),
            'error' => true,
            'errorText' => 'Please provide category id and draft category id'
        ));
        return false;
    }
    $result = array();
    if ($categoryId && $categoryId > 0) {
        $posts = get_posts(array(
            'post_status' => array('publish'),
            'post_type'		=> 'post',
            'cat' => $draftCategoryId,
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
            $video_template = get_field('video_template', $postId);
            $media_type = get_field('media_type', $postId);
            $post_original_date = get_field('post_original_date', $postId);
            $media_network = get_field('media_network', $postId);
            $post->image = $image;
            $post->originalId = $originalId;
            $post->position = $position;
            $post->video_template = $video_template;
            $post->media_type = $media_type;
            $post->media_network = $media_network;
            $post->post_original_date = $post_original_date;
        }
        $result = array(
            'data' => $posts
        );
    } else {
        $result = array(
            'data' => array()
        );
    }
    echo json_encode($result);
    exit;
}

function publishPosts() {
    $draftCategoryId = get_option(SMT_DRAFT_POST_CATEGORY_ID);
    $categoryId = get_option(SMT_POST_CATEGORY_ID);
    if ((!$categoryId || $categoryId == 0) && (!$draftCategoryId || $draftCategoryId == 0)) {
        echo json_encode(array(
            'data' => array(),
            'error' => true,
            'errorText' => 'Please provide category id and draft category id'
        ));
        return false;
    }
    $posts = get_posts(array(
        'post_status' => array('publish'),
        'post_type'		=> 'post',
        'cat' => $categoryId,
        'numberposts' => -1,
    ));
    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
    }
    $draft_posts = get_posts(array(
        'post_status' => array('publish'),
        'post_type'		=> 'post',
        'cat' => $draftCategoryId,
        'numberposts' => -1,
    ));
    foreach ($draft_posts as $draft_post) {
        $postId = $draft_post->ID;
        $image = get_field('image', $postId);
        $originalId = get_field('original_id', $postId);
        $position = get_field('position', $postId);
        $video_template = get_field('video_template', $postId);
        $media_type = get_field('media_type', $postId);
        $post_original_date = get_field('post_original_date', $postId);
        $media_network = get_field('media_network', $postId);
        $permalink = get_field('media_link', $postId);
        $content = $draft_post->post_content;
        $title = $draft_post->post_title;
        $post_object = array(
            'post_content' => $content,
            'post_status' => 'publish',
            'post_title' => $title,
            'post_type' => 'post',
            'post_category' => array($categoryId),
            'meta_input'  => array(
                'original_id' => $originalId,
                'image' => $image,
                'position' => $position,
                'video_template' => $video_template,
                'media_type' => $media_type,
                'media_network' => $media_network,
                'media_link' => $permalink,
                'post_original_date' => $post_original_date,
            ),
        );
        $post_ids[] = wp_insert_post($post_object);
    }
    $result = array(
        'data' => 'Posts successfully published'
    );
    echo json_encode($result);
    exit;
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

function twitter_hashtags($post_data) {

    $text = isset( $post_data->retweeted_status->full_text ) ? $post_data->retweeted_status->full_text : $post_data->full_text;

    // Message. Convert links to real links.
    $pattern   = array( '/http:(\S)+/', '/https:(\S)+/', '/@+(\w+)/u', '/#+(\w+)/u' );
    $replace   = array( ' <a href="${0}" target="_blank" rel="nofollow">${0}</a>', ' <a href="${0}" target="_blank" rel="nofollow">${0}</a>', ' <a href="https://twitter.com/$1" target="_blank" rel="nofollow">@$1</a>', ' <a href="https://twitter.com/hashtag/$1?src=hash" target="_blank" rel="nofollow">#$1</a>' );
    $full_text = preg_replace( $pattern, $replace, $text );

    return nl2br( $full_text );
}

function saveImage($image_url) {
    $image_name       = 'smt-image_'.time().'.jpg';
    $upload_dir       = wp_upload_dir();
    $image_data       = file_get_contents($image_url);
    $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name);
    $filename         = basename($unique_file_name);

    if(wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    // Create the image  file on the server
    file_put_contents($file, $image_data);

    // Check image file type
    $wp_filetype = wp_check_filetype($filename, null);

    // Set attachment data
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name($filename),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file );

    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

    wp_update_attachment_metadata( $attach_id, $attach_data );

    return wp_get_attachment_image_src($attach_id, 'full')[0];
}

function saveTwitterPosts() {
    $draftCategoryId = get_option(SMT_DRAFT_POST_CATEGORY_ID);
    $categoryId = get_option(SMT_POST_CATEGORY_ID);
    if ((!$categoryId || $categoryId == 0) && (!$draftCategoryId || $draftCategoryId == 0)) {
        echo json_encode(array(
            'data' => array(),
            'error' => true,
            'errorText' => 'Please provide category id and draft category id'
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
            $media_type = '';
            if (isset($saved_post->entities->media) && isset($saved_post->entities->media[0])) {
                $current_image = $saved_post->entities->media[0]->media_url;
                $post_image = saveImage($current_image);
            }
            if (isset($saved_post->extended_entities->media) && isset($saved_post->extended_entities->media[0])) {
                $media_type = $saved_post->extended_entities->media[0]->type;
            }
            $video_template = getTwitterVideo($saved_post);
            $content = twitter_hashtags($saved_post);
            $post_object = array(
                'post_content' => $content,
                'post_status' => 'publish',
                'post_title' => mb_strimwidth($saved_post->full_text, 0, 20, '...'),
                'post_type' => 'post',
                'post_category' => array($draftCategoryId),
                'meta_input'  => array(
                    'original_id' => $saved_post->id_str,
                    'image' => $post_image,
                    'position' => 1,
                    'video_template' => $video_template,
                    'media_type' => $media_type,
                    'media_network' => 'twitter',
                    'media_link' => $saved_post->permalink_url,
                    'post_original_date' => $saved_post->created_at,
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


function facebook_hashtags($post_data) {

    $text = isset( $post_data->retweeted_status->full_text ) ? $post_data->retweeted_status->full_text : $post_data->full_text;

    // Message. Convert links to real links.
    $pattern   = array( '/http:(\S)+/', '/https:(\S)+/', '/@+(\w+)/u', '/#+(\w+)/u' );
    $replace   = array( ' <a href="${0}" target="_blank" rel="nofollow">${0}</a>', ' <a href="${0}" target="_blank" rel="nofollow">${0}</a>', ' <a href="https://www.facebook.com/hashtag/$1" target="_blank" rel="nofollow">@$1</a>', ' <a href="https://www.facebook.com/hashtag/$1" target="_blank" rel="nofollow">#$1</a>' );
    $full_text = preg_replace( $pattern, $replace, $text );

    return nl2br( $full_text );
}

function saveFacebookPosts() {
    $draftCategoryId = get_option(SMT_DRAFT_POST_CATEGORY_ID);
    $categoryId = get_option(SMT_POST_CATEGORY_ID);
    if ((!$categoryId || $categoryId == 0) && (!$draftCategoryId || $draftCategoryId == 0)) {
        echo json_encode(array(
            'data' => array(),
            'error' => true,
            'errorText' => 'Please provide category id and draft category id'
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
            $media_type = '';
            if (isset($saved_post->entities->media) && isset($saved_post->entities->media[0])) {
                $current_image = $saved_post->entities->media[0]->media_url;
                $post_image = saveImage($current_image);
            }
            if (isset($saved_post->extended_entities->media) && isset($saved_post->extended_entities->media[0])) {
                $media_type = $saved_post->extended_entities->media[0]->type;
            }
            if ($media_type == 'video') {
                $video_template = getFacebookVideo($saved_post);
            } else {
                $video_template = '';
            }
            $content = facebook_hashtags($saved_post);
            $post_object = array(
                'post_content' => $content,
                'post_status' => 'publish',
                'post_title' => mb_strimwidth($saved_post->full_text, 0, 20, '...'),
                'post_type' => 'post',
                'post_category' => array($draftCategoryId),
                'meta_input'  => array(
                    'original_id' => $saved_post->id_str,
                    'image' => $post_image,
                    'position' => 1,
                    'video_template' => $video_template,
                    'media_type' => $media_type,
                    'media_network' => 'facebook',
                    'media_link' => $saved_post->permalink_url,
                    'post_original_date' => $saved_post->created_at,
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
        $draftCategoryId = $_POST['draftCategoryId'];
        update_option(SMT_NUMBER_POSTS, $numberPosts);
        update_option(SMT_POST_CATEGORY_ID, $categoryId);
        update_option(SMT_DRAFT_POST_CATEGORY_ID, $draftCategoryId);
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

function saveFacebookSettings() {
    if ( !empty( trim( file_get_contents("php://input" ) ) ) ) {
        $post = trim(file_get_contents("php://input"));
        $_POST = ( array ) json_decode( $post );
        $token = $_POST['token'];
        update_option(SMT_FACEBOOK_TOKEN, $token);
    }
}

function getFacebookVideo($post_data) {
    $poster = $post_data->entities->media[0]->media_url;
    $source = $post_data->extended_entities->media[0]->source;
    $video_output = '<div class="fts-jal-fb-vid-wrap">';

    // This line is here so we can fetch the source to feed into the popup since some html 5 videos can be displayed without the need for a button.
    $video_output .= '<a href="' . $poster . '" style="display:none !important" class="fts-facebook-link-target fts-jal-fb-vid-image fts-video-type"></a>';
    $video_output .= '<div class="fts-fluid-videoWrapper-html5">';
    $video_output .= '<video controls poster="' . $poster . '" width="100%;" style="max-width:100%;">';
    $video_output .= '<source src="' . $source . '" type="video/mp4">';
    $video_output .= '</video>';
    $video_output .= '</div>';

    $video_output .= '</div>';

    return $video_output;
}

function getTwitterVideo($post_data) {

    if ( isset( $post_data->quoted_status->entities->media[0]->type ) ) {
        $twitter_final = isset( $post_data->quoted_status->entities->media[0]->expanded_url ) ? $post_data->quoted_status->entities->media[0]->expanded_url : '';
    } else {
        $twitter_final = isset( $post_data->entities->urls[0]->expanded_url ) ? $post_data->entities->urls[0]->expanded_url : '';
    }

    if ( strpos( $twitter_final, 'vimeo' ) > 0 ) {
        if ( strpos( $twitter_final, 'staffpicks' ) > 0 ) {
            $parsed_url      = $twitter_final;
            $parsed_url      = parse_url( $parsed_url );
            $vimeo_url_final = preg_replace( '/\D/', '', $parsed_url['path'] );
        } else {
            $vimeo_url_final = (int) substr( parse_url( $twitter_final, PHP_URL_PATH ), 1 );
        }
        return '<div class="fts-fluid-videoWrapper"><iframe src="https://player.vimeo.com/video/' . $vimeo_url_final . '?autoplay=0" class="video" height="390" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';
    } elseif (

        strpos( $twitter_final, 'youtube' ) > 0 && ! strpos( $twitter_final, '-youtube' ) > 0 ) {
        $pattern = '#^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';
        preg_match( $pattern, $twitter_final, $matches );
        $youtube_url_final = $matches[1];

        return '<div class="fts-fluid-videoWrapper"><iframe height="281" class="video" src="https://www.youtube.com/embed/' . $youtube_url_final . '?autoplay=0" frameborder="0" allowfullscreen></iframe></div>';
    } elseif (
        strpos( $twitter_final, 'youtu.be' ) > 0 ) {
        $pattern = '#^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';
        preg_match( $pattern, $twitter_final, $matches );
        $youtube_url_final = $matches[1];
        return '<div class="fts-fluid-videoWrapper"><iframe height="281" class="video" src="https://www.youtube.com/embed/' . $youtube_url_final . '?autoplay=0" frameborder="0" allowfullscreen></iframe></div>';
    } elseif (

        strpos( $twitter_final, 'soundcloud' ) > 0 ) {

        $get_values = wp_remote_get( 'https://soundcloud.com/oembed?format=js&url=' . $twitter_final . '&auto_play=false&iframe=true' );

        $decode_iframe = substr( $get_values, 1, -2 );

        $json_object = json_decode( $decode_iframe );

        return '<div class="fts-fluid-videoWrapper">' . $json_object->html . '</div>';
    } else {

        if ( isset( $post_data->extended_entities->media[0]->video_info->variants[4]->content_type ) && 'video/mp4' === $post_data->extended_entities->media[0]->video_info->variants[4]->content_type ) {
            $twitter_final = isset( $post_data->extended_entities->media[0]->video_info->variants[4]->url ) ? $post_data->extended_entities->media[0]->video_info->variants[4]->url : '';
        } elseif ( isset( $post_data->extended_entities->media[0]->video_info->variants[2]->content_type ) && 'video/mp4' === $post_data->extended_entities->media[0]->video_info->variants[2]->content_type ) {
            $twitter_final = isset( $post_data->extended_entities->media[0]->video_info->variants[2]->url ) ? $post_data->extended_entities->media[0]->video_info->variants[2]->url : '';
        } elseif ( isset( $post_data->extended_entities->media[0]->video_info->variants[3]->content_type ) && 'video/mp4' === $post_data->extended_entities->media[0]->video_info->variants[3]->content_type ) {
            $twitter_final = isset( $post_data->extended_entities->media[0]->video_info->variants[3]->url ) ? $post_data->extended_entities->media[0]->video_info->variants[3]->url : '';
        } elseif ( isset( $post_data->extended_entities->media[0]->video_info->variants[1]->content_type ) && 'video/mp4' === $post_data->extended_entities->media[0]->video_info->variants[1]->content_type ) {
            $twitter_final = isset( $post_data->extended_entities->media[0]->video_info->variants[1]->url ) ? $post_data->extended_entities->media[0]->video_info->variants[1]->url : '';
        }

        elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[4]->content_type ) && 'video/mp4' === $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[4]->content_type ) {
            $twitter_final = isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[4]->url ) ? $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[4]->url : '';
        } elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[2]->content_type ) && 'video/mp4' === $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[2]->content_type ) {
            $twitter_final = isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[2]->url ) ? $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[2]->url : '';
        } elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[3]->content_type ) && 'video/mp4' === $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[3]->content_type ) {
            $twitter_final = isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[3]->url ) ? $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[3]->url : '';
        } elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[1]->content_type ) && 'video/mp4' === $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[1]->content_type ) {
            $twitter_final = isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[1]->url ) ? $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[1]->url : '';
        }

        elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[4]->content_type ) && 'video/mp4' === $post_data->quoted_status->extended_entities->media[0]->video_info->variants[4]->content_type ) {
            $twitter_final = isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[4]->url ) ? $post_data->quoted_status->extended_entities->media[0]->video_info->variants[4]->url : '';
        } elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[2]->content_type ) && 'video/mp4' === $post_data->quoted_status->extended_entities->media[0]->video_info->variants[2]->content_type ) {
            $twitter_final = isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[2]->url ) ? $post_data->quoted_status->extended_entities->media[0]->video_info->variants[2]->url : '';
        } elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[3]->content_type ) && 'video/mp4' === $post_data->quoted_status->extended_entities->media[0]->video_info->variants[3]->content_type ) {
            $twitter_final = isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[3]->url ) ? $post_data->quoted_status->extended_entities->media[0]->video_info->variants[3]->url : '';
        } elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[1]->content_type ) && 'video/mp4' === $post_data->quoted_status->extended_entities->media[0]->video_info->variants[1]->content_type ) {
            $twitter_final = isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[1]->url ) ? $post_data->quoted_status->extended_entities->media[0]->video_info->variants[1]->url : '';
        }

        if ( isset( $post_data->extended_entities->media[0]->media_url_https ) ) {

            $twitter_final_poster = isset( $post_data->extended_entities->media[0]->media_url_https ) ? $post_data->extended_entities->media[0]->media_url_https : '';
        } elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->media_url_https ) ) {

            $twitter_final_poster = isset( $post_data->quoted_status->extended_entities->media[0]->media_url_https ) ? $post_data->quoted_status->extended_entities->media[0]->media_url_https : '';
        } elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->media_url_https ) ) {

            $twitter_final_poster = isset( $post_data->retweeted_status->extended_entities->media[0]->media_url_https ) ? $post_data->retweeted_status->extended_entities->media[0]->media_url_https : '';
        }

        $fts_twitter_output = '<div class="fts-jal-fb-vid-wrap">';

        $fts_twitter_output .= '<a href="' . $twitter_final . '" style="display:none !important" class="fts-facebook-link-target fts-jal-fb-vid-image fts-video-type"></a>';
        $fts_twitter_output .= '<div class="fts-fluid-videoWrapper-html5">';
        $fts_twitter_output .= '<video controls poster="' . $twitter_final_poster . '" width="100%;" style="max-width:100%;">';
        $fts_twitter_output .= '<source src="' . $twitter_final . '" type="video/mp4">';
        $fts_twitter_output .= '</video>';
        $fts_twitter_output .= '</div>';

        $fts_twitter_output .= '</div>';
        return $fts_twitter_output;
    }
}
