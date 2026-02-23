<?php
session_start(); // Required to access session data

// See EVERYTHING in the session (formatted)
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "Client IP Address: " . htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES, 'UTF-8');
?>