<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　Ajax　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (isset($_POST['ronbunId']) && isset($_SESSION['user_id'])) {
  debug('POST送信があります');
  $r_id = $_POST['ronbunId'];
  debug('論文ID：'.$r_id);

  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorite WHERE user_id = :u_id AND ronbun_id = :r_id';
    $data = array(':u_id' => $_SESSION['user_id'], ':r_id' => $r_id);
    $stmt = queryPost($dbh, $sql, $data);
    $resultCount = $stmt->rowCount();
    debug('クエリ結果：'.$resultCount);

    if (!empty($resultCount)) {
      $sql = 'DELETE FROM favorite WHERE user_id = :u_id AND ronbun_id = :r_id';
      $data = array(':u_id' => $_SESSION['user_id'], ':r_id' => $r_id);
      $stmt = queryPost($dbh, $sql, $data);
    } else {
      $sql = 'INSERT INTO favorite (user_id, ronbun_id, created_date) VALUES (:u_id, :r_id, :date)';
      $data = array(':u_id' => $_SESSION['user_id'], ':r_id' => $r_id, ':date' => date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh, $sql, $data);
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}