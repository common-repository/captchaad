<?php

define('CAPTCHAAD_BASE_PATH', dirname(__FILE__) . '/');

require_once CAPTCHAAD_BASE_PATH . 'CaptchaAd/config.inc.php';
require_once CAPTCHAAD_BASE_PATH . 'CaptchaAd/Exception.php';
require_once CAPTCHAAD_BASE_PATH . 'CaptchaAd/Fallback.php';
require_once CAPTCHAAD_BASE_PATH . 'CaptchaAd/Http.php';
require_once CAPTCHAAD_BASE_PATH . 'recaptcha/recaptchalib.php';

class CaptchaAd {

    public function __construct() {
        $this->checkConfig();
    }

    protected function checkConfig() {
        if (!defined('CAPTCHAAD_STATICURL') || mb_strlen(CAPTCHAAD_STATICURL) == 0) {
            throw new CaptchaAd_Exception('Invalid static url.');
        }

        if (!defined('CAPTCHAAD_DELIVERYURL') || mb_strlen(CAPTCHAAD_DELIVERYURL) == 0) {
            throw new CaptchaAd_Exception('Invalid delivery url.');
        }
    }

    protected function startSession() {
        global $captchaAd_options;
        try {
            $http = new CaptchaAd_Http('startSession.php', array(
                        'api_key' => $captchaAd_options['PLAYER_ID']
                    ));

            $sessionId = $http->getResponseBody();
        } catch (CaptchaAd_Exception $e) {
            return false;
        }
        if (!empty($sessionId)) {
            return $sessionId;
        } else {
            return false;
        }
    }

    public function getHtmlBody() {

        global $captchaAd_options, $captchaAd_defaults, $captchaAd_lang;
        //$WIDTH = empty($captchaAd_options['WIDTH']) ? $captchaAd_defaults['WIDTH'] : $captchaAd_options['WIDTH'];
        //$HEIGHT = empty($captchaAd_options['HEIGHT']) ? $captchaAd_defaults['HEIGHT'] : $captchaAd_options['HEIGHT'];
        $PLAYER_ID = empty($captchaAd_options['PLAYER_ID']) ? $captchaAd_defaults['PLAYER_ID'] : $captchaAd_options['PLAYER_ID'];
        //$AUTO_PLAY = $captchaAd_options['AUTO_PLAY'] == 0 ? "false" : "true";
        $html = '';

        if ($sessionId = $this->startSession()) {
            $html.= '<input type="hidden" name="CaptchaAdSession" value="' . $sessionId . '" />';
            // CaptchaAd SWF Container
            $html.= "<script type=\"text/javascript\" src=\"" . CAPTCHAAD_STATICURL . "captchaad.js\"></script>";
            $html.= "<script type=\"text/javascript\">";
            $html.= "var CaptchaAd = {";
            //$html.= "  Stage:{width:" . $WIDTH . ",height:" . $HEIGHT . "},";
            $html.= "  PlayerId:\"".CAPTCHAAD_APIKEY."\","; 
            $html.= "  SessionId:\"" . $sessionId . "\",";
            //$html.= "  Autoplay:\"" . $AUTO_PLAY . "\",";
            $html.= "  environment:\"" . CAPTCHAAD_ENVIRONMENT . "\"";
            $html.= "};";
            $html.= "initCaptchaAd(CaptchaAd);";
            $html.= "</script>";
        }
        $html.= "<div id=\"CaptchaAdContainer_" . $PLAYER_ID . "\">";

        $html.= CaptchaAd_Fallback::getHtmlBody();

        $html.= '</div>';
        echo $html;
    }

    public function checkAnswer() {
        if (!CaptchaAd_Fallback::checkFallbackSubmitted()) {
            try {
                $post = new CaptchaAd_Http('checkResolved.php', array(
                            'session_id' => $_REQUEST['CaptchaAdSession']
                        ));
                if ($post->getResponseBody() == '1') {
                    return true;
                } else {
                    return false;
                }
            } catch (CaptchaAd_Exception $e) {
                
            }
        }
        return CaptchaAd_Fallback::checkAnswer();
    }

}
