<?php
/*
 * Plugin Name:   Simple Comments Captcha
 * Plugin URI:    http://www.github.com/aramg/wordpress-captcha
 * Description:   Adds a captcha/verification image to the comment form, without creating files on disk.
 * Version:       0.1
 * Author:        aramg
 * Author URI:    http://www.dev47apps.com/
 *
 * License:      GNU General Public License, v2 (or newer)
 * License URI:  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

// Passphrase, or salt. Change this to something random.
define(PASSPHRASE, 'uHh4fr6epKoL2RU5ErkJggg');

function show_captcha() {
    //if ( is_user_logged_in() ) { return; }
    $captcha_word   = generate_random_word();
    $captcha_image  = generate_image($captcha_word);
    $captcha_hash   = generate_hash($captcha_word);
    ?>
    <img src="data:image/png;base64,<?php echo base64_encode($captcha_image); ?>" alt="captcha" /><br />
    <input type="hidden" name="comment_captcha_hash" value="<?php echo $captcha_hash; ?>" />
    <input type="text" name="comment_captcha_code" class="textfield" value="" size="24" />
    <label for="comment_captcha_code" class="small">Type the code shown above (required)</label>
    <?php 
}
add_action( 'comment_form_after_fields' , 'show_captcha' );

/*
// Method 1: Accept and Mark as spam
function check_captcha_1( $approved, $comment_data  ) {
    if (!isset($_POST['comment_captcha_hash'], $_POST['comment_captcha_code']) ||
            0 != strcmp($_POST['comment_captcha_hash'], generate_hash(str_replace(' ', '', $_POST['comment_captcha_code']))))
    {
        $approved = 'spam';
    }
    return $approved;
}
add_filter( 'pre_comment_approved', 'check_captcha_1', 99, 2 );
// */

// /*
// Method 2: Show error message
function check_captcha_2() {
    if (!isset($_POST['comment_captcha_hash'], $_POST['comment_captcha_code']) 
        || 0 != strcmp($_POST['comment_captcha_hash'], generate_hash(str_replace(' ', '', $_POST['comment_captcha_code']))))
    {
        wp_die('Sorry, invalid text: <b>' .$_POST['comment_captcha_code']. '</b>. Please try again.');
    }
}
add_action('pre_comment_on_post', 'check_captcha_2');
// */

function generate_random_word() {
 $chars = 'abdefghimrt@#%*2345678';
 $word = '';
 $maxIndex = strlen( $chars ) - 1;
 for ( $i = 0; $i < 4; $i++ ) {
  $word .= $chars[mt_rand(0, $maxIndex)];
 }
 return $word;
}

function generate_hash( $word ) {
    $hash = md5(PASSPHRASE . $word);
    //$hash = substr(sha1(PASSPHRASE . $word . strlen(PASSPHRASE)), 5, 32);
    return $hash;
}

function generate_image( $word ) {
    if (!($im = imagecreatetruecolor( 72, 24 ))) {
        return '';
    }
    $fonts = array('/GenAI102.TTF', '/GenAR102.TTF', '/GenI102.TTF', '/GenR102.TTF');

    $bg = imagecolorallocate( $im, 255, 255, 255);
    $fg = imagecolorallocate( $im, 100, 100, 100);

    imagefill( $im, 0, 0, $bg );
    $x = 6 + mt_rand( -2, 2 );

    for ( $i = 0; $i < strlen( $word ); $i++ ) {
        $font = dirname( __FILE__ ) . $fonts[array_rand( $fonts )];
        imagettftext( $im, 14/*font_size*/, mt_rand( -2, 2 ), $x, 18 + mt_rand( -2, 2 ), $fg, $font, $word[$i] );
        $x += 15;
    }
    ob_start();
    imagepng( $im );
    $theImage = ob_get_contents();
    ob_end_clean();
    imagedestroy( $im );
    return $theImage;
}
