<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/auth.php';

$auth = Auth::getInstance();
echo "<h1>Auth Debugger</h1>";
echo "Session ID: " . session_id() . "<br>";
echo "Cookie: " . print_r($_COOKIE, true) . "<br>";

echo "<h2>Status</h2>";
echo "isLoggedIn(): " . ($auth->isLoggedIn() ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "<br>";
echo "isAdmin(): " . ($auth->isAdmin() ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "<br>";

echo "<h2>Comparison</h2>";
echo "Current IP: " . $_SERVER['REMOTE_ADDR'] . "<br>";
echo "Session IP: " . ($_SESSION['ip_address'] ?? 'UNSET') . "<br>";
echo "Match? " . (($_SESSION['ip_address'] ?? '') === $_SERVER['REMOTE_ADDR'] ? 'Yes' : 'No') . "<br><br>";

echo "Current MA: " . $_SERVER['HTTP_USER_AGENT'] . "<br>";
echo "Session MA: " . ($_SESSION['user_agent'] ?? 'UNSET') . "<br>";
echo "Match? " . (($_SESSION['user_agent'] ?? '') === $_SERVER['HTTP_USER_AGENT'] ? 'Yes' : 'No') . "<br>";

echo "<h2>Session Data</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
