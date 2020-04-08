<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　パスワード変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// 認証関数読み込み
require('auth.php');

$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData, true));

if (!empty($_POST)) {
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST, true));

  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if (empty($err_msg)) {
    debug('未入力チェックOKです');

    validPass($pass_old, 'pass_old');
    validPass($pass_new, 'pass_new');

    if (!password_verify($pass_old, $userData['password'])) {
      $err_msg['pass_old'] = MSG14;
    }

    if ($pass_old === $pass_new) {
      $err_msg['pass_new'] = MSG15;
    }

    validMatch($pass_new, $pass_new_re, 'pass_new');

    if (empty($err_msg)) {
      debug('バリデーションOKです');
      try {
        $dbh = dbConnect();
        $sql = 'UPDATE users SET password = :pass WHERE id = :u_id';
        $data = array(':pass' => password_hash($pass_new, PASSWORD_DEFAULT), ':u_id' => $userData['id']);
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
          $_SESSION['msg_success'] = SUC03;

          $username = ($userData['username']) ? $userData['username'] : '名無し';
          $from = 'abc.atrn@gmail.com';
          $to = $userData['email'];
          $subject = 'パスワード変更通知 | 論文ラボ';
          $comment = <<<EOT
{$username}さん
パスワードが変更されました。

//////////////////////////////
論文ラボ
URL: ronbunlab.com
Email: ronbunlab@gmail.com 
//////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);
          header("Location:mypage.php");
        }
      } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }  
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
  require('head.php');
  require('header.php');
?>
  <main class="container-wrapper">
    <div class="site-width">
      <div class="post-container">
        <h1 class="title">パスワード変更</h1>
        <div class="form-container">
          <form action="" method="post" class="form" enctype="multipart/form-data">
            <div class="area-msg">
              <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
            </div>
            <label>
              古いパスワード
              <input type="password" name="pass_old">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['pass_old'])) echo $err_msg['pass_old']; ?>
            </div>
            <label>
              新しいパスワード
              <input type="password" name="pass_new">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['pass_new'])) echo $err_msg['pass_new']; ?>
            </div>
            <label>
              新しいパスワード(再入力)
              <input type="password" name="pass_new_re">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['pass_new_re'])) echo $err_msg['pass_new_re']; ?>
            </div>
            <div class="btn-container">
              <input type="submit" value="変更する" class="btn">
            </div>
          </form>
        </div>
        <?php require('sidebar_mypage.php'); ?>
      </div>
    </div>
  </main>

  <?php require('footer.php'); ?>