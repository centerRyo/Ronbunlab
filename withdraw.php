<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　退会ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

if (!empty($_POST)) {
  debug('POST送信があります');
  try {
    $dbh = dbConnect();
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
    $sql2 = 'UPDATE ronbun SET delete_flg = 1 WHERE user_id = :u_id';
    $sql3 = 'UPDATE favorite SET delete_flg = 1 WHERE user_id = :u_id';
    $data = array(':u_id' => $_SESSION['user_id']);

    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);

    if ($stmt1 && $stmt2 && $stmt3) {
      session_destroy();
      debug('セッション変数の中身'.print_r($_SESSION, true));
      debug('トップページへ遷移します');
      header("Location:index.php");
    } else {
      error_log('クエリが失敗しました');
      $err_msg['common'] = MSG07;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
  require('head.php');
  require('header.php');
?>
  <main>
    <section class="container">
      <h2 class="title">退会</h2>
      <form action="" class="form" method="post">
        <div class="btn-container">
          <input type="submit" class="btn" value="退会する" name="submit">
        </div>
      </form>
      <a href="mypage.php">&lt;マイページへ戻る</a>
    </section>
  </main>

  <?php require('footer.php'); ?>