<?php if (!defined('CAPTCHAAD_BASE_PATH')) die('No direct access.');


require_once CAPTCHAAD_BASE_PATH . 'CaptchaAd/Exception.php';
global $captchaAd_options;
class CaptchaAd_Fallback
{
	public static function checkFallbackConfig()
	{
            global $captchaAd_options;
		if (!defined('CAPTCHAAD_3RDPARTY_RECAPTCHA_PUBLIC_KEY') || mb_strlen($captchaAd_options['RECAPTCHA_PUBLIC_KEY']) == 0)
		{
			throw new CaptchaAd_Exception('Invalid reCAPTCHA public key.');
		}

		if (!defined('CAPTCHAAD_3RDPARTY_RECAPTCHA_PRIVATE_KEY') || mb_strlen($captchaAd_options['RECAPTCHA_PRIVATE_KEY']) == 0)
		{
			throw new CaptchaAd_Exception('Invalid reCAPTCHA private key.');
		}
	}

	public static function checkFallbackSubmitted()
	{
		if (isset($_REQUEST['recaptcha_challenge_field']) && isset($_REQUEST['recaptcha_response_field'])) {
			return true;
		} else {
			return false;
		}
	}

	public static function getHtmlBody()
	{
            global $captchaAd_options;
		// 3rdparty: reCAPTCHA
		$html = '<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=' . $captchaAd_options['RECAPTCHA_PUBLIC_KEY'] . '"></script>';
		$html.= '<noscript>';
		$html.= '<iframe src="http://www.google.com/recaptcha/api/noscript?k=' . $captchaAd_options['RECAPTCHA_PUBLIC_KEY'] . '" height="300" width="500" frameborder="0"></iframe><br />';
		$html.= '<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>';
		$html.= '<input type="hidden" name="recaptcha_response_field" value="manual_challenge" />';
		$html.= '</noscript>';

		return $html;
	}

	public static function checkAnswer()
	{
            global $captchaAd_options;
		if (isset($_REQUEST['recaptcha_challenge_field']) && isset($_REQUEST['recaptcha_response_field']))
		{
			$resp = recaptcha_check_answer(
				$captchaAd_options['RECAPTCHA_PRIVATE_KEY'],
				$_SERVER['REMOTE_ADDR'],
				$_REQUEST['recaptcha_challenge_field'],
				$_REQUEST['recaptcha_response_field']
			);

			$result = $resp->is_valid;
		}
		else
		{
			$result = FALSE;
		}

		return $result;
	}

}
