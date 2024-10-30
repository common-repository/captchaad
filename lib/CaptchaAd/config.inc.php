<?php if (!defined('CAPTCHAAD_BASE_PATH')) die('No direct access.');

# ------------------------------------------------------------------------
# don't change the lines behind this commentline
# ------------------------------------------------------------------------

define('CAPTCHAAD_STATICURL', 'http://static.captchaad.com/');
define('CAPTCHAAD_DELIVERYURL', 'http://delivery.captchaad.com/');
define('CAPTCHAAD_ENVIRONMENT', 'live');

//loading player id according to blog language
if(get_bloginfo('language') == 'de-DE') {
  define('CAPTCHAAD_APIKEY', 'e609c5a7fa5ba8c0');  
}
else if(get_bloginfo('language') == 'en-EN' || get_bloginfo('language') == 'en-GB' || get_bloginfo('language') == 'en-US') {
  define('CAPTCHAAD_APIKEY', 'c2650b288b19118c');    
}

