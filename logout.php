<?php
/**
 * Root Logout Redirector - Redirects traffic to the public logout handler
 */

header('Location: public/logout.php');
exit;
