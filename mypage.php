<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// 認証関数読み込み
require('auth.php');

require('head.php');
require('header.php');
?>

<?php
require('footer.php');
?>
