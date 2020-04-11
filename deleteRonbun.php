<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　論文削除ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

$r_id = (!empty($_GET['r_id'])) ? $_GET['r_id'] : '';
debug('論文ID：'.$r_id);

debug('論文を削除します');
try {
  $dbh = dbConnect();
  $sql = 'UPDATE ronbun SET delete_flg = 1 WHERE id = :id';
  $data = array(':id' => $r_id);
  $stmt = queryPost($dbh, $sql, $data);

  if ($stmt) {
    header("Location:mypage.php");
  } else {
    debug('論文の削除に失敗しました');
  }
} catch (Exception $e) {
  error_log('エラー発生：'.$e->getMessage());
  $err_msg['common'] = MSG07;
}
?>