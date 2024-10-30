<?php if (!defined('CAPTCHAAD_BASE_PATH')) die('No direct access.');

class CaptchaAd_Http
{
	protected $responseBody;

	public function __construct($path, $data = array(), $port = 80, $timeout = 5)
	{
		$host =  str_replace('http://', '', CAPTCHAAD_DELIVERYURL);
		$host =  str_replace('/', '', $host);

		if (($fp = fsockopen($host, $port, $errno, $errstr, $timeout)) === FALSE)
		{
			throw new CaptchaAd_Exception('Could not open socket.');
		}

		$data = http_build_query($data, '', '&');

		$request = "GET /". $path ."?". $data ." HTTP/1.1\r\n";
		$request.= "Host: ". $host ."\r\n";
		$request.= "User-Agent: CaptchaAd/PHP\r\n";
		$request.= "Connection: Close\r\n\r\n";

		if ((fwrite($fp, $request)) === FALSE)
		{
			throw new CaptchaAd_Exception('Could not write content to stream.');
		}

		stream_set_timeout($fp, $timeout);

		$response = '';

		$start = NULL;
		while ($this->testEOF($fp, $start) === FALSE && (microtime(TRUE) - $start) < $timeout)
		{
			if (($response.= fgets($fp, 1024)) === FALSE)
			{
				break;
			}
		}

		$response = explode("\r\n\r\n", $response, 2);

		$this->responseBody = $response[1];

		$responseMetaData = stream_get_meta_data($fp);


		if (mb_strlen($this->responseBody) == 0 ||
			$responseMetaData['timed_out'] === TRUE ||
			$responseMetaData['eof'] === FALSE ||
			stripos($response[0], '200 OK') === FALSE
			)
		{
			throw new CaptchaAd_Exception('Could not get content from stream.');
		}

		if (fclose($fp) === FALSE)
		{
			throw new CaptchaAd_Exception('Could not close the socket connection.');
		}
	}

	protected function testEOF($fp, &$start = NULL)
	{
		$start = microtime(TRUE);

		return feof($fp);
	}

	public function getResponseBody()
	{
		return $this->responseBody;
	}

}
