<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　パスワード再発行メール送信ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (!empty($_POST)) {
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST, true));

  $email = $_POST['email'];

  validRequired($email, 'email');

  if (empty($err_msg)) {
    debug('未入力チェックOKです');

    validEmail($email, 'email');
    validMaxLen($email, 'email');

    if (empty($err_msg)) {
      debug('バリデーションチェックOKです');
      try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt && array_shift($result)) {
          debug('クエリに成功し、DBにデータがありました');
          $_SESSION['msg_success'] = SUC04;

          $auth_key = makeRandKey();

          $from = 'abc.atrn@gmail.com';
          $to = $email;
          $subject = '【パスワード再発行認証】 | 論文ラボ';
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力いただけるとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8000/ronbunlab/passRemindReceive.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります。

//////////////////////////////
論文ラボ
URL: ronbunlab.com
Email: ronbunlab@gmail.com 
//////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time() + (60 * 30);

          header("Location:passRemindReceive.php");
        } else {
          debug('クエリに失敗したかDBに登録されていないメールアドレスが入力されました');
          $err_msg['common'] = MSG07;
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

  <main>
    <section class="container">
      <h2 class="title">パスワード再発行</h2>
      <form action="" class="form" method="post">
        <p>ご登録のメールアドレス宛にパスワード再発行用のURLと認証キーをお送りします</p>
        <div class="area-msg">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>
        <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
          メールアドレス
          <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
        </label>
        <div class="btn-container">
          <input type="submit" value="送信する" class="btn">
        </div>
      </form>
      <a href="login.php">&lt;ログイン画面へ戻る</a>
    </section>
  </main>