<?php
/*
  Plugin Name: CaptchaAd
  Plugin URI: http://www.captchaad.com/
  Description: Integrates CaptchaAd to protect comment posts and registrations
  Version: 1.2
  Author: CaptchaAd GmbH
  AAuthor URI: http://www.captchaad.com/
 */

// http://codex.wordpress.org/Writing_a_Plugin
// http://codex.wordpress.org/I18n_for_WordPress_Developers
include('lib/CaptchaAd.php');
cadInit();
//register_activation_hook(__FILE__, 'cadSetDefaultOptions_');
register_deactivation_hook(__FILE__, 'cadRemoveOptions');
/**
 * Init plugin options and hooks
 * @global array $captchaAd_options
 */
function cadInit() {
    global $captchaAd_options;
    $var = new Captchaad();
    // Load Language Files
    load_plugin_textdomain('captchaAdLang', false, basename(dirname(__FILE__)) . '/languages');
    @cadSetDefaultOptions_();
    add_action('admin_menu', 'cadAddOptionsPage');
    
    $captchaAd_options = get_option('CaptchaAd');
    if ($captchaAd_options['DISPLAY_IN_COMMENT'] == 1 || $captchaAd_options['DISPLAY_IN_REGISTRATION'] == 1) {

        if ($captchaAd_options['DISPLAY_IN_COMMENT'] == 1) {
            add_filter('comment_form_defaults', 'cadField');
            add_filter('preprocess_comment', 'cadCheckComment', 1);
        }

        if ($captchaAd_options['DISPLAY_IN_REGISTRATION'] == 1) {
            add_action('register_form', array(&$var, 'getHtmlBody'));
            add_filter('registration_errors', 'cadCheckRegister', 1);
        }
    }
}

/**
 * Check the registerform
 */
function cadCheckRegister($errors) {
    $cad_register = new Captchaad();

    if (!CaptchaAd_Fallback::checkFallbackSubmitted()) {
        if (!$cad_register->checkAnswer()) {
            $errors->add('CaptchaAdWrong', _e('<strong>Error:</strong> Answer correctly to the question in the movie, please.', 'captchaAdLang'));
        }
    } else {
        if (!CaptchaAd_Fallback::checkAnswer()) {
            $errors->add('CaptchaAdWrong', _e('<strong>Error:</strong> Type in the shown signs, please.', 'captchaAdLang'));
        }
    }

    return $errors;
}

/**
 * Check the commentform
 */
function cadCheckComment($comment) {
    $cad_comment = new Captchaad();
    // Skip cad for trackback or pingback
    if ($comment['comment_type'] != '' && $comment['comment_type'] != 'comment') {
        return $comment;
    }

    if (!CaptchaAd_Fallback::checkFallbackSubmitted()) {
        if (!$cad_comment->checkAnswer()) {
            wp_die(_e('Error: Answer correctly to the question in the movie.', 'captchaAdLang'));
        }
    } else {
        if (!CaptchaAd_Fallback::checkAnswer()) {
            wp_die(_e('Error: Type in the shown signs, please.', 'captchaAdLang'));
        }
    }

    return $comment;
}


/**
 * Inject CaptchaAd after comment_note_after
 */
function cadField($comment_form_defaults) {
    $cad = new Captchaad();
    ob_start();
    $cad->getHtmlBody();
    $comment_form_defaults['comment_notes_after'] .= ob_get_contents();
    ob_end_clean();

    return $comment_form_defaults;
}

/**
 * Set the plugin default options (once)
 * @global array $captchaAd_lang
 */
function cadSetDefaultOptions_() {
    if( get_bloginfo('language')== 'de-DE') {
        $player_id = 'e609c5a7fa5ba8c0';
    }
    else if(get_bloginfo('language')== 'en-EN' || get_bloginfo('language')== 'en-GB' || get_bloginfo('language')== '') {
        $player_id = 'c2650b288b19118c';
    }
    $defaultOptions = array(
        'BASE_PATH' => dirname(__FILE__) . '/',
        'BASE_URL' => WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), '', plugin_basename(__FILE__)),
        'PLAYER_ID' => CAPTCHAAD_APIKEY,
        'RECAPTCHA_PUBLIC_KEY' => '',
        'RECAPTCHA_PRIVATE_KEY' => '',
        'DISPLAY_IN_COMMENT' => 0,
        'DISPLAY_IN_REGISTRATION' => 0,
        //'WIDTH' => 300,
        //'HEIGHT' => 250,
        //'AUTO_PLAY' => 0
    );
    //print_r($defaultOptions);
    add_option('CaptchaAd', $defaultOptions,'yes'); 
}

/**
 * Remove the CaptchaAd Options
 */
function cadRemoveOptions() {
    delete_option('CaptchaAd');
}

/**
 * Add the plugin configuration page
 */
function cadAddOptionsPage() {
    add_options_page('CaptchaAd', 'CaptchaAd', 'manage_options', __FILE__, 'cadOptionsPage_');
}

/**
 * Display admin options page for new player
 */
function cadOptionsPage_() {
    global $captchaAd_options;

    $isPost = false;
    $updatedOptions = false;
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $isPost = true;

        $newOptions = array(
            'PLAYER_ID' => $_POST['PLAYER_ID'],
            'RECAPTCHA_PUBLIC_KEY' => $_POST['RECAPTCHA_PUBLIC_KEY'],
            'RECAPTCHA_PRIVATE_KEY' => $_POST['RECAPTCHA_PRIVATE_KEY'],
            'DISPLAY_IN_COMMENT' => $_POST['DISPLAY_IN_COMMENT'],
            'DISPLAY_IN_REGISTRATION' => $_POST['DISPLAY_IN_REGISTRATION'],
            //'WIDTH' => $_POST['WIDTH'],
            //'HEIGHT' => $_POST['HEIGHT'],
            //'AUTO_PLAY' => $_POST['AUTO_PLAY']
        );

        if (cadOverwriteOptions($newOptions)) {
            $updatedOptions = true;
        }
    }
    ?>
    <style type="text/css">
        .req{color:#CC0000;}
        input.error{border-color:#CC0000;}
    </style>
    <div class="wrap">

        <?php if ($isPost) : ?>
            <?php
            if ($updatedOptions && $_POST['PLAYER_ID'] != null /*&&*/
                    /* is_numeric($_POST['WIDTH'])*/ && /*is_numeric($_POST['HEIGHT'])*/ /*&&*/
                    $_POST['RECAPTCHA_PUBLIC_KEY'] != null && $_POST['RECAPTCHA_PRIVATE_KEY'] != null /*&&*/
                    /*$_POST['WIDTH'] > 0*/ /*&& $_POST['HEIGHT'] > 0*/) :
                ?>
                <div class="updated"><?php _e('Update successful.', 'captchaAdLang'); ?></div>
            <?php else : ?>
                <div class="error"><?php _e('Sorry, something went wrong.', 'captchaAdLang'); ?></div>
            <?php endif; ?>
    <?php endif; ?>

        <div class="icon32" id="icon-options-general" style="float:left"><br></div>

        <h2 style="position:relative"><?php _e('Options &rsaquo;&nbsp;&nbsp;', 'captchaAdLang'); ?><img style="position:absolute;top:0;" src="<?php echo $captchaAd_options['BASE_URL']; ?>/img/logo.png" alt="CaptchaAd" width="165" height="45"></h2>

        <div style="clear:both"></div>

        <p><?php _e('Questions or problems with the configuration, pls contact us via e-mail, wordpress@captchaad.com', 'captchaAdLang'); ?></p>

        <form method="post" action="">

            <table class="form-table">

                <tr valign="top">
                    <th scope="row">
                        <label for="PLAYER_ID"><?php _e('API Key', 'captchaAdLang'); ?> <span class="req">*</span></label>
                    </th>
                    <td>
                        <input id="API_KEY" name="PLAYER_ID" type="text" class="regular-text <?php if (in_array('PLAYER_ID', $captchaAd_problems)) : ?>error<?php endif; ?>" value="<?php echo esc_attr($captchaAd_options['PLAYER_ID']); ?>" />
                        <br />
                        <!--
                        <span class="description">
                            <?php _e('You get this key from CaptchaAd.<br />
                                      Telefon: +49 (0) 89.237 129 21<br />
                                      Mail: <a href="mailto:wordpress@captchaad.com">wordpress@captchaad.com</a>', 'captchaAdLang'); ?>
                        </span>
                        -->
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label><?php _e('Show at', 'captchaAdLang'); ?></label>
                    </th>
                    <td>
                        <label><input id="DISPLAY_IN_COMMENT" name="DISPLAY_IN_COMMENT" type="checkbox" value="1" <?php checked('1', $captchaAd_options['DISPLAY_IN_COMMENT']); ?> /> <?php _e('Comments', 'captchaAdLang'); ?></label>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"></th>
                    <td>
                        <label><input id="DISPLAY_IN_REGISTRATION" name="DISPLAY_IN_REGISTRATION" type="checkbox" value="1" <?php checked('1', $captchaAd_options['DISPLAY_IN_REGISTRATION']); ?> /> <?php _e('Registration', 'captchaAdLang'); ?></label>
                    </td>
                </tr>

            </table>
            <!--  
            <h3><?php _e('Options', 'captchaAdLang'); ?></h3>

            <table class="form-table">

                <tr valign="top">
                    <th scope="row"><?php _e('Stage', 'captchaAdLang'); ?></th>
                    <td>
                        <label for="WIDTH"><?php _e('Width', 'captchaAdLang'); ?></label>
                        <input id="WIDTH" name="WIDTH" type="text" class="small-text <?php if (in_array('WIDTH', $captchaAd_problems)) : ?>error<?php endif; ?>" value="<?php echo esc_attr($captchaAd_options['WIDTH']); ?>" />

                        <label for="HEIGHT"><?php _e('Height', 'captchaAdLang'); ?></label>
                        <input id="HEIGHT" name="HEIGHT" type="text" class="small-text <?php if (in_array('HEIGHT', $captchaAd_problems)) : ?>error<?php endif; ?>" value="<?php echo esc_attr($captchaAd_options['HEIGHT']); ?>" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="AUTO_PLAY"><?php _e('Auto Play', 'captchaAdAutoPlay'); ?></label>
                    </th>
                    <td>
                        <select name="AUTO_PLAY" class="<?php if (in_array('AUTO_PLAY', $captchaAd_problems)) : ?>error<?php endif; ?>">
                            <option value="0">No</option>
                            <?php
                            echo '<option value="1"';
                            if ($captchaAd_options['AUTO_PLAY'])
                                echo ' selected="selected"';
                            echo '>Yes</option>';
                            ?>
                        </select>
                    </td>
                </tr>

            </table>
            -->
            <h3><?php _e('reCAPTCHA <small>(&copy; Google)</small>', 'captchaAdLang'); ?></h3>

            <p><?php _e('To ensure spam protection, CaptchaAd requires an alternative in case of disabled Flash or JavaScript. <a href="http://www.google.com/recaptcha" target="_blank">reCAPTCHA</a> will be displayed instead.', 'captchaAdLang'); ?></p>

            <table class="form-table">

                <tr valign="top">
                    <th scope="row">
                        <label for="RECAPTCHA_PUBLIC_KEY"><?php _e('Public Key', 'captchaAdLang'); ?> <span class="req">*</span></label>
                    </th>
                    <td>
                        <input id="RECAPTCHA_PUBLIC_KEY" name="RECAPTCHA_PUBLIC_KEY" type="text" class="regular-text <?php if (in_array('RECAPTCHA_PUBLIC_KEY', $captchaAd_problems)) : ?>error<?php endif; ?>" value="<?php echo esc_attr($captchaAd_options['RECAPTCHA_PUBLIC_KEY']); ?>" />
                        <br />
                        <span class="description"><?php _e('Public Key from reCAPTCHA', 'captchaAdLang'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="RECAPTCHA_PRIVATE_KEY"><?php _e('Private Key', 'captchaAdLang'); ?> <span class="req">*</span></label>
                    </th>
                    <td>
                        <input id="RECAPTCHA_PRIVATE_KEY" name="RECAPTCHA_PRIVATE_KEY" type="text" class="regular-text <?php if (in_array('RECAPTCHA_PRIVATE_KEY', $captchaAd_problems)) : ?>error<?php endif; ?>" value="<?php echo esc_attr($captchaAd_options['RECAPTCHA_PRIVATE_KEY']); ?>" />
                        <br />
                        <span class="description"><?php _e('Private Key from reCAPTCHA', 'captchaAdLang'); ?></span>
                    </td>
                </tr>

            </table>
            <p class="submit"><input type="hidden" name="action" value="update" /> <input type="submit" name="Submit" value="<?php _e('Save Changes', 'captchaAdLang') ?>" class="button-primary" /></p> 
        </form>
    </div>
    <?php
}

/**
 * Update and overwrite the global options
 * @global array $captchaAd_options
 * @global array $captchaAd_problems
 * @param array $newOptions
 * @return bool successful?
 */
function cadOverwriteOptions($newOptions) {
    global $captchaAd_options;

    $newOptions = array_merge($captchaAd_options, $newOptions);

    // to avoid problems through the input filtering functions only overwrite
    // the global options when the update was successfully
    if (update_option('CaptchaAd', $newOptions)) {
        $captchaAd_options = $newOptions;
        return true;
    }

    return false;
}
?>