<?php
    session_start( [ 'cookie_lifetime' => 86400, 'gc_maxlifetime' => 86400 ] );

    $session_timeout = 1800; // 30 minutes in seconds

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
        // Session expired due to inactivity
        session_unset();     // Unset all session variables
        session_destroy();   // Destroy the session
        header("Location: login.php?expired=true"); // Redirect to login page
        exit();
    }

    $_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time on every page load
?>