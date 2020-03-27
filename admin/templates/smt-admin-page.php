<?php
/**
 * Created by PhpStorm.
 * User: oveprev
 * Date: 2019-11-11
 * Time: 16:23
 */

?>
<script>
    window.ajaxURL = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
</script>
<style>
    .fts-jal-fb-vid-wrap, .fts-fluid-videoWrapper-html5 {
        width: 100%;
        height: 100%;
    }
    video {
        height: 100%;
        width: 100%;
        object-fit: cover;
    }
    video:focus {
        outline: none;
    }
    *:focus {
        outline: none;
    }
</style>

<div id="root"></div>

<script src="<?php echo SMT_URL.'/assets/js/bundle.js'; ?>"></script>
