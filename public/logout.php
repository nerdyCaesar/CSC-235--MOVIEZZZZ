<?php
require __DIR__ . '/../app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); header('Allow: POST'); exit; }
check_csrf();
$_SESSION=[];
$p=session_get_cookie_params();
setcookie(session_name(),'',time()-3600,$p['path'],$p['domain'],$p['secure'],$p['httponly']);
session_destroy();
header('Location: index.php');
exit;
