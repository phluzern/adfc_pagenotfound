<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Jan Bartels (j.bartels@adfc-nrw.de)
*  (c) 2013 Lorenz Ulrich <lorenz.ulrich@phz.ch>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

class user_pageNotFound {

    public function pageNotFound($param, $ref) {
    	$debug = 0;

		$host = t3lib_div::getIndpEnv('HTTP_HOST');
		$uri = t3lib_div::getIndpEnv('REQUEST_URI');
		$referer = t3lib_div::getIndpEnv('REFERER');
		$reason = $param['reasonText'];
		$cfg = $this->getDomainConfiguration($host);

		// Retrieve type of error and errorpage-url
		$accessError = (substr_count($reason, "access") > 0);  // not a clean solution to decide whether 401 or 404, but works fine.
		if ($accessError) {
			$errorpage = $cfg['errorPage401'];
			$errorpage = ($errorpage == '' ? '401' : $errorpage);
		} else {
			$errorpage = $cfg['errorPage404'];
			$errorpage = ($errorpage == '' ? '404' : $errorpage);
		}

		// Check if URL is relative
		$url_parts = parse_url($errorpage);
		if ($url_parts['host'] == '')
		{
			$url_parts['host'] = t3lib_div::getIndpEnv('HTTP_HOST');
			$errorpage = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . $errorpage;
		}

		if ($accessError) {
			/*
			** 401: access of a login-restricted page: redirect to page with fe_login-form, that redirects further to the requested page after login
			*/

			// get the PID from the URI (doesn't work with RealURL)
			$redirectUid = $this->extractPidFromUri($uri);

			// find out if redirectUid will be the first or any other parameter
			if (strpos($errorpage, '?') == FALSE) {
				$parameterSeparator = '?';
			} else {
				$parameterSeparator = '&';
			}

			// the redirect part of the new URL
			$redirect = $parameterSeparator . 'arPid=' . $redirectUid;

			header("HTTP/1.0 302 FE-Login required");
			header("Location: " . t3lib_div::locationHeaderUrl($errorpage . $redirect));

			if ($debug) {
				header("X-Reason: ".nl2br(htmlspecialchars($reason )) );
			}
			print "<html>\n<head>\n<title>302 FE-Login required</title>\n";
			print "</head>\n<body>\n";
			print "<h1>HTTP/1.0 302 FE-Login required</h1>\n";
			print "<a href=".urlencode($errorpage).">Login</a>\n";
			print "</body>\n</html>\n";
		} else {
			/*
			** 404: Page not found
			*/

			header("HTTP/1.0 404 Not Found");

			// Get 404-page. Keep User-agent, Refererer and Cookie for the request
			$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : $GLOBALS['TSFE']->defaultCharSet;

			$HeaderArr = array();
			$HeaderArr[] = 'User-agent: ' . t3lib_div::getIndpEnv('HTTP_USER_AGENT');
			$HeaderArr[] = 'Referer: ' . $referer;
			$HeaderArr[] = 'Content-Type: text/html; charset="' . $charset .'"';

			if (isset($_COOKIE)) {
				// Cookies have to be kept as the FE-user would be logged out of FE otherwise
				$cookie = array();
				foreach ($_COOKIE as $name => $value) {
					$cookie[] = "$name=$value";
				}
				$cookiestr = implode( "; ",$cookie );

				$HeaderArr[] = "Cookie: ".$cookiestr;
			}

			if ($debug) {
				header("X-Reason: ".nl2br(htmlspecialchars($reason )) );
				header("X-Location: ".$errorpage);
				header("X-URI: ".$uri );
				header("X-Cookie: ".$cookiestr );
			}
			$content = t3lib_div::getURL($errorpage, 0, $HeaderArr);

			if ($content) {
				echo $content;
			} else {
				/** @var $messageObj t3lib_message_ErrorPageMessage */
				$messageObj = t3lib_div::makeInstance('t3lib_message_ErrorPageMessage', 'Reason: ' . nl2br(htmlspecialchars($reason)), 'Error');
				$messageObj->render();
			}
		}
    }

	/**
	 * Extract a PID from an URI
	 *
	 * @param string $uri The request uri
	 * @return int $pid The TYPO3 PID extracted from the URI
	 */
	public function extractPidFromUri($uri) {

		// Parse the url into an array
		$uriParts = parse_url($uri);
		parse_str($uriParts['query'], $pathParts);

		return $pathParts['id'];

	}


	/**
	 * Returns the related configuration for domain
	 *
	 * @param string $domain Domain to fetch configuration from
	 * @return array
	 */
	public function getDomainConfiguration($domain='_DEFAULT') {
		$cfg = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['adfc_pagenotfound'];
		if (is_array($cfg) && array_key_exists($domain, $cfg)) {
			$domain_key = $domain;
		} else {
			$domain_key = '_DEFAULT';
		}
		if (is_array($cfg[$domain_key])) {
			return $cfg[$domain_key];
		} else {
			return array();
		}
	}

}

?>