<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

# Install error-handler
$TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = 'USER_FUNCTION:'.'EXT:adfc_pagenotfound/class.adfc_pagenotfound.php:user_pageNotFound->pageNotFound';

?>