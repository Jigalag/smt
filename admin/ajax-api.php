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

add_action( "wp_ajax_saveTwitterPosts", "saveTwitterPosts" );
add_action( "wp_ajax_nopriv_saveTwitterPosts", "saveTwitterPosts" );

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
    $result = array();
    if ($categoryId && $categoryId > 0) {
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

function saveTwitterPosts() {
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
            $media_type = '';
            if (isset($saved_post->entities->media) && isset($saved_post->entities->media[0])) {
                $post_image = $saved_post->entities->media[0]->media_url;
            }
            if (isset($saved_post->extended_entities->media) && isset($saved_post->extended_entities->media[0])) {
                $media_type = $saved_post->extended_entities->media[0]->type;
            }
            $video_template = getTwitterVideo($saved_post);
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
                    'position' => 0,
                    'video_template' => $video_template,
                    'media_type' => $media_type,
                    'media_network' => 'twitter',
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

function getTwitterVideo($post_data) {

    // if (!wp_verify_nonce($_REQUEST['fts_security'], $_REQUEST['fts_time'] . 'load-more-nonce')) {.
    // exit('Sorry, You can\'t do that!');.
    // } else {.
    if ( isset( $post_data->quoted_status->entities->media[0]->type ) ) {
        $twitter_final = isset( $post_data->quoted_status->entities->media[0]->expanded_url ) ? $post_data->quoted_status->entities->media[0]->expanded_url : '';
    } else {
        $twitter_final = isset( $post_data->entities->urls[0]->expanded_url ) ? $post_data->entities->urls[0]->expanded_url : '';
    }

    // strip Vimeo URL then ouput Iframe.
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
        // strip Vimeo Staffpics URL then ouput Iframe.
        strpos( $twitter_final, 'youtube' ) > 0 && ! strpos( $twitter_final, '-youtube' ) > 0 ) {
        $pattern = '#^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';
        preg_match( $pattern, $twitter_final, $matches );
        $youtube_url_final = $matches[1];

        return '<div class="fts-fluid-videoWrapper"><iframe height="281" class="video" src="https://www.youtube.com/embed/' . $youtube_url_final . '?autoplay=0" frameborder="0" allowfullscreen></iframe></div>';
    } elseif (
        // strip Youtube URL then ouput Iframe and script.
        strpos( $twitter_final, 'youtu.be' ) > 0 ) {
        $pattern = '#^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';
        preg_match( $pattern, $twitter_final, $matches );
        $youtube_url_final = $matches[1];
        return '<div class="fts-fluid-videoWrapper"><iframe height="281" class="video" src="https://www.youtube.com/embed/' . $youtube_url_final . '?autoplay=0" frameborder="0" allowfullscreen></iframe></div>';
    } elseif (
        // strip Youtube URL then ouput Iframe and script.
        strpos( $twitter_final, 'soundcloud' ) > 0 ) {

        // Get the JSON data of song details with embed code from SoundCloud oEmbed.
        $get_values = wp_remote_get( 'https://soundcloud.com/oembed?format=js&url=' . $twitter_final . '&auto_play=false&iframe=true' );

        // Clean the Json to decode.
        $decode_iframe = substr( $get_values, 1, -2 );

        // json decode to convert it as an array.
        $json_object = json_decode( $decode_iframe );

        return '<div class="fts-fluid-videoWrapper">' . $json_object->html . '</div>';
    } else {

        // START VIDEO POST.
        // Check through the different video options availalbe. For some reson the varaints which are the atcual video urls vary at times in quality so we are going to shoot for 4 first then 2, 3 and 1.
        if ( isset( $post_data->extended_entities->media[0]->video_info->variants[4]->content_type ) && 'video/mp4' === $post_data->extended_entities->media[0]->video_info->variants[4]->content_type ) {
            $twitter_final = isset( $post_data->extended_entities->media[0]->video_info->variants[4]->url ) ? $post_data->extended_entities->media[0]->video_info->variants[4]->url : '';
        } elseif ( isset( $post_data->extended_entities->media[0]->video_info->variants[2]->content_type ) && 'video/mp4' === $post_data->extended_entities->media[0]->video_info->variants[2]->content_type ) {
            $twitter_final = isset( $post_data->extended_entities->media[0]->video_info->variants[2]->url ) ? $post_data->extended_entities->media[0]->video_info->variants[2]->url : '';
        } elseif ( isset( $post_data->extended_entities->media[0]->video_info->variants[3]->content_type ) && 'video/mp4' === $post_data->extended_entities->media[0]->video_info->variants[3]->content_type ) {
            $twitter_final = isset( $post_data->extended_entities->media[0]->video_info->variants[3]->url ) ? $post_data->extended_entities->media[0]->video_info->variants[3]->url : '';
        } elseif ( isset( $post_data->extended_entities->media[0]->video_info->variants[1]->content_type ) && 'video/mp4' === $post_data->extended_entities->media[0]->video_info->variants[1]->content_type ) {
            $twitter_final = isset( $post_data->extended_entities->media[0]->video_info->variants[1]->url ) ? $post_data->extended_entities->media[0]->video_info->variants[1]->url : '';
        }

        // The only difference in these lines is the "retweeted_status" These are twitter videos from Tweet link people post, the ones above are direct videos users post to there timeline.
        elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[4]->content_type ) && 'video/mp4' === $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[4]->content_type ) {
            $twitter_final = isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[4]->url ) ? $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[4]->url : '';
        } elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[2]->content_type ) && 'video/mp4' === $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[2]->content_type ) {
            $twitter_final = isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[2]->url ) ? $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[2]->url : '';
        } elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[3]->content_type ) && 'video/mp4' === $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[3]->content_type ) {
            $twitter_final = isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[3]->url ) ? $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[3]->url : '';
        } elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[1]->content_type ) && 'video/mp4' === $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[1]->content_type ) {
            $twitter_final = isset( $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[1]->url ) ? $post_data->retweeted_status->extended_entities->media[0]->video_info->variants[1]->url : '';
        }

        // The only difference in these lines is the "quoted_status" These are twitter videos from Tweet link people post, the ones above are direct videos users post to there timeline.
        elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[4]->content_type ) && 'video/mp4' === $post_data->quoted_status->extended_entities->media[0]->video_info->variants[4]->content_type ) {
            $twitter_final = isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[4]->url ) ? $post_data->quoted_status->extended_entities->media[0]->video_info->variants[4]->url : '';
        } elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[2]->content_type ) && 'video/mp4' === $post_data->quoted_status->extended_entities->media[0]->video_info->variants[2]->content_type ) {
            $twitter_final = isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[2]->url ) ? $post_data->quoted_status->extended_entities->media[0]->video_info->variants[2]->url : '';
        } elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[3]->content_type ) && 'video/mp4' === $post_data->quoted_status->extended_entities->media[0]->video_info->variants[3]->content_type ) {
            $twitter_final = isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[3]->url ) ? $post_data->quoted_status->extended_entities->media[0]->video_info->variants[3]->url : '';
        } elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[1]->content_type ) && 'video/mp4' === $post_data->quoted_status->extended_entities->media[0]->video_info->variants[1]->content_type ) {
            $twitter_final = isset( $post_data->quoted_status->extended_entities->media[0]->video_info->variants[1]->url ) ? $post_data->quoted_status->extended_entities->media[0]->video_info->variants[1]->url : '';
        }

        // Check to see if there is a poster image available.
        if ( isset( $post_data->extended_entities->media[0]->media_url_https ) ) {

            $twitter_final_poster = isset( $post_data->extended_entities->media[0]->media_url_https ) ? $post_data->extended_entities->media[0]->media_url_https : '';
        } elseif ( isset( $post_data->quoted_status->extended_entities->media[0]->media_url_https ) ) {

            $twitter_final_poster = isset( $post_data->quoted_status->extended_entities->media[0]->media_url_https ) ? $post_data->quoted_status->extended_entities->media[0]->media_url_https : '';
        } elseif ( isset( $post_data->retweeted_status->extended_entities->media[0]->media_url_https ) ) {

            $twitter_final_poster = isset( $post_data->retweeted_status->extended_entities->media[0]->media_url_https ) ? $post_data->retweeted_status->extended_entities->media[0]->media_url_https : '';
        }

        $fts_twitter_output = '<div class="fts-jal-fb-vid-wrap">';

        // This line is here so we can fetch the source to feed into the popup since some html 5 videos can be displayed without the need for a button.
        $fts_twitter_output .= '<a href="' . $twitter_final . '" style="display:none !important" class="fts-facebook-link-target fts-jal-fb-vid-image fts-video-type"></a>';
        $fts_twitter_output .= '<div class="fts-fluid-videoWrapper-html5">';
        $fts_twitter_output .= '<video controls poster="' . $twitter_final_poster . '" width="100%;" style="max-width:100%;">';
        $fts_twitter_output .= '<source src="' . $twitter_final . '" type="video/mp4">';
        $fts_twitter_output .= '</video>';
        $fts_twitter_output .= '</div>';

        $fts_twitter_output .= '</div>';

        // return '<div class="fts-fluid-videoWrapper"><iframe src="' . $twitter_final_video . '" class="video" height="390" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';.
        // echo $twitter_final;.
        //
        // REMOVING THIS TWITTER VID OPTION TILL WE GET SOME ANSWERS.
        //
        // https://twittercommunity.com/t/twitter-statuses-oembed-parameters-not-working/105868.
        // https://stackoverflow.com/questions/50419158/twitter-statuses-oembed-parameters-not-working.
        return $fts_twitter_output;
        // }.
        // else {.
        // exit('That is not allowed. FTS!');.
        // }.
        // } //strip Vine URL then ouput Iframe and script.
    }
    // end main else.
    // die();.
}