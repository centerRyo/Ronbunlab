<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// 認証関数読み込み
require('auth.php');

if (!empty($_POST)) {
  debug('POST送信があります');

  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;

  // 未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');

  // Emailチェック
  validEmail($email, 'email');
  validMaxLen($email, 'email');

  // パスワードチェック
  validHalf($pass, 'pass');
  validMaxLen($pass, 'pass');
  validMinLen($pass, 'pass');

  if (empty($err_msg)) {
    debug('バリデーションOKです');

    try {
      // DB接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'SELECT password, id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      // クエリ結果取得
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      debug('クエリ結果の中身:'.print_r($result, true));

      if (!empty($result) && password_verify($pass, array_shift($result))) {
        debug('パスワードがマッチしました');

        $sesLimit = 60*60;
        $_SESSION['login_date'] = time();

        if ($pass_save) {
          debug('ログイン保持にチェックがあります');
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        } else {
          debug('ログイン保持にチェックがありません');
          $_SESSION['login_limit'] = $sesLimit;
        }

        $_SESSION['user_id'] = $result['id'];

        debug('セッション変数の中身：'.print_r($_SESSION, true));
        debug('マイページへ遷移します');
        header("Location:mypage.php");
      } else {
        debug('パスワードがアンマッチです');
        $err_msg['common'] = MSG09;
      }
    } catch (Exception $e) {
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = MSG07;
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
      <h2 class="title">ログイン</h2>
      <form class="form" action="" method="post">
        <div class="area-msg">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>
        <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
          メールアドレス
          <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email'] ?>">
        </label>
        <div class="area-msg">
          <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
        </div>
        <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
          パスワード
          <input type="password" name="pass">
        </label>
        <div class="area-msg">
          <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
        </div>
          <input type="checkbox" name="pass_save">次回から自動でログインする
        <div class="btn-container">
          <input type="submit" class="btn btn-right" value="ログイン">
        </div>
        <a class="pass_remind" href="">パスワードをお忘れの方はこちら</a>
      </form>
    </section>
  </main>

  <?php
    require('footer.php');
  ?>
