<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　パスワード再発行メール送信ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (empty($_SESSION['auth_key'])) {
  header("Location:passRemindSend.php");
}

if (!empty($_POST)) {
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST, true));

  $token = $_POST['token'];

  validRequired($token, 'token');

  if (empty($err_msg)) {
    debug('未入力チェックOKです');

    validHalf($token, 'token');
    validLength($token, 'token');

    if (empty($err_msg)) {
      debug('バリデーションチェックOKです');

      if ($token !== $_SESSION['auth_key']) {
        $err_msg['token'] = MSG10;
      }

      if (time() > $_SESSION['auth_key_limit']) {
        $err_msg['token'] = MSG17;
      }

      if (empty($err_msg)) {
        debug('認証OKです');
        $pass = makeRandKey();

        try {
          $dbh = dbConnect();
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':pass' => password_hash($pass, PASSWORD_DEFAULT), ':email' => $_SESSION['auth_email']);
          $stmt = queryPost($dbh, $sql, $data);

          if ($stmt) {
            $from = 'abc.atrn@gmail.com';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行完了】論文ラボ';
            $comment = <<<EOT
本メールアドレス宛にパスワードの再発行をいたしました。
下記のURLにて再発行パスワードをご入力いただき、ログインしてください。

ログインページ：http://localhost:8000/ronbunlab/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードの変更をお願いいたします。

//////////////////////////////
論文ラボ
URL: ronbunlab.com
Email: ronbunlab@gmail.com 
//////////////////////////////
EOT;
            sendMail($from, $to, $subject, $comment);
            session_unset();
            $_SESSION['msg_success'] = SUC04;
            debug('セッション変数の中身：'.print_r($_SESSION, true));

            header("Location:login.php");
          }
        } catch (Exception $e) {
          error_log('エラー発生：'.$e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}
?>

<?php
  require('head.php');
  require('header.php');
?>

  <main>
    <section class="container">
      <form action="" method="post" class="form">
        <p>ご指定のメールアドレスにお送りした【パスワード再発行認証】メールにある「認証キー」をご入力ください</p>
        <div class="area-msg">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>
        <label class="<?php if(!empty($err_msg['token'])) echo 'err'; ?>">
          認証キー
          <input type="text" name="token" value="<?php echo getFormData('token'); ?>">
        </label>
        <div class="area-msg">
          <?php if(!empty($err_msg['token'])) echo $err_msg['token']; ?>
        </div>
        <div class="btn-container">
          <input type="submit" class="btn btn-right" value="再発行する">
        </div>
      </form>
      <a href="passRemindSend.php">&lt;パスワード再発行メールを再度送信する</a>
    </section>
  </main>

  <?php require('footer.php'); ?>