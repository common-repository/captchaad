=== CaptchaAd WordPress Plugin ===
Contributors: CaptchaAd GmbH
Donate link: http://www.captchaad.com/
Tags: antispam, captcha, comments, captchaad, registration, wpmu, video, advertising, spam protection, bots, comments, registration, security, anti spam
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 1.2

Integrates CaptchaAd anti-spam methods with WordPress including comment, registration spam protection.

== Description ==

[CaptchaAd](http://www.captchaad.com "CaptchaAd Homepage") is the first provider in the world to combine conventional CAPTCHAs (spam protection, of which around 280 million are generated worldwide every day) with high-quality video advertising. The novel aspect of the [CaptchaAds](http://www.captchaad.com "CaptchaAd Homepage") is that concrete answers to a video clip, which can be made up of the product name, slogan or other keywords, have to be typed into the Captcha field instead of a random combination of letters and numbers. This makes the advertising message much more effective than other online advertising (100% contact with the advertising medium, extremely high advertising effectiveness).

[CaptchaAd](http://www.captchaad.com "CaptchaAd Homepage") has applied for worldwide patents for this technology. 

[CaptchaAd](http://www.captchaad.com "CaptchaAd Homepage") offers an additional revenue stream for website operators and gives advertisers a completely new opportunity to carry out exceptionally efficient and measurable brand and product communications.

* High quality video
* Enhanced security
* Greater usability for users
* Better conversions
* Additional revenue stream (optional)

For more information please visit our website, [www.captchaad.com](http://www.captchaad.com "CaptchaAd Homepage")

== Installation ==

1. Upload the captchaad folder to the /wp-content/Plugins/ directory
2. Activate the Plugin through the Plugins menu in WordPress
3. Open the Plugins configuration page, which is located under Options
4. Get reCAPTCHA keys here: [www.google.com/recaptcha](http://www.google.com/recaptcha "reCAPTCHA keys")
5. Configure CaptchaAd according to your needs

== Upgrade ==

1. Disable the old version 
2. Follow the steps from Installation

== Frequently Asked Questions ==

= CaptchaAd is not displayed properly =

CaptchaAd is designed to work properly within WordPress standards. If you individualized your WordPress you might need to insert in the following Code-Snippet to your personal form-configuration.

`
<?php 
$myCustomCaptcha = new Catchaad();
$myCustomCaptcha->getHtmlBody();
To check the answer: $myCustomCaptcha->checkAnswer();
?>
`


Make sure that it is in place within the form and not just at the beginning of the code. This way you ensure that the CaptchaAd is displayed for example under the comment-box and above the submit button.

Example:

comments.php in the folder /wp-content/myTheme

= My question isn't answered here =

Please message us, wordpress@captchaad.com

== ChangeLog ==

= Version 1.2 =

- Make sure to update to latest version asap, support for older versions will end after 30.06.2013
- Make plugin compatible with latest version of CaptchaAd player
- Refactor the plugin
- Add OOP support for checking the banner answer
- Localization to DE or EN automated through general Wordpress settings
- API Key predefined

= Version 1.1 =

- Minor bug fixes
- Tracking features added

= Version 1.0 =

Initial CaptchaAd release.

== Screenshots ==

1. The CaptchaAd comment form
2. The CaptchaAd settings