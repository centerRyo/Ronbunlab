<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　ログアウトページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

debug('ログアウトします');

session_destroy();
header("Location:login.php");
?>
