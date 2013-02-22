Use this in localconf.php:

$TYPO3_CONF_VARS['EXTCONF']['adfc_pagenotfound'] = array(
    '_DEFAULT' => array(
        'errorPage404' => 'http://www.tld.dev/index.php?id=257', # insert the SpeakingURL path segment of the error page here
        'errorPage401' => 'http://www.tld.dev/index.php?id=256', # insert the SpeakingURL path segment of the error page here with login-form
    ),
    'otherdomain.tld' => array(
        'errorPage404' => 'http://otherdomain.tld/index.php?id=301', # insert the SpeakingURL path segment of the error page here
        'errorPage401' => 'http://otherdomain.tld/index.php?id=301', # insert the SpeakingURL path segment of the error page here with login-form
    ),
);