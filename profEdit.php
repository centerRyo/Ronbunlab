<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　プロフィール編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// 認証関数読み込み
require('auth.php');

$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.$_SESSION['user_id']);

if (!empty($_POST)) {
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST, true));
  debug('FILE情報：'.print_r($_FILE, true));

  $username = $_POST['username'];
  $age = $_POST['age'];
  $tel = $_POST['tel'];
  $zip = (!empty($_POST['zip'])) ? $_POST['zip'] : 0;
  $address = $_POST['address'];
  $email = $_POST['email'];
  $pic = (!empty($_FILE['pic']['name'])) ? uploadImg($_FILE['pic'], 'pic') : '';
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

  if ($dbFormData['username'] !== $username) {
    validMaxLen($username, 'username');
  }

  if ($dbFormData['age'] !== $age) {
    validNumber($age, 'age');
  }

  if ($dbFormData['tel'] !== $tel) {
    validTel($tel, 'tel');
  }

  if ($dbFormData['zip'] !== $zip) {
    validZip($zip, 'zip');
  }

  if ($dbFormData['address'] !== $address) {
    validMaxLen($address, 'address');
  }

  if ($dbFormData['email'] !== $email) {
    validRequired($email, 'email');
    validMaxLen($email, 'email');
    validEmail($email, 'email');
    if (empty($err_msg['email'])) {
      validDup($email);
    }
  }

  if (empty($err_msg)) {
    debug('バリデーションOKです');
    try {
      $dbh = dbConnect();
      $sql = 'UPDATE users SET username = :u_name, age = :age, tel = :tel, zip = :zip, address = :address, pic = :pic, email = :email WHERE id = :u_id';
      $data = array(':u_name' => $username, ':age' => $age, ':tel' => $tel, ':zip' => $zip, ':address' => $address, ':pic' => $pic, ':email' => $email, ':u_id' => $dbFormData['id']);
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        $_SESSION['msg_success'] = SUC02;
        debug('マイページへ遷移します');
        header("Location:mypage.php");
      }
    } catch (Exception $e) {
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
?>

<?php
  require('head.php');
  require('header.php');
?>
  <main class="container-wrapper">
    <div class="site-width">
      <div class="post-container">
        <h1 class="title">プロフィール編集</h1>
        <div class="form-container">
          <form class="form" action="" method="post" enctype="multipart/form-data">
            <div class="area-msg">
              <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
              名前
              <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['username'])) echo $err_msg['username']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
              年齢
              <input type="number" name="age" value="<?php echo getFormData('age'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['age'])) echo $err_msg['age']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['tel'])) echo 'err'; ?>">
              電話番号<span class="additional">※ハイフンなしでご入力ください</span>
              <input type="text" name="tel" value="<?php echo getFormData('tel'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['tel'])) echo $err_msg['tel']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['zip'])) echo 'err'; ?>">
              郵便番号<span class="additional">※ハイフンなしでご入力ください</span>
              <input type="text" name="zip" value="<?php echo getFormData('zip'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['zip'])) echo $err_msg['zip']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['address'])) echo 'err'; ?>">
              住所
              <input type="text" name="address" value="<?php echo getFormData('address'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['address'])) echo $err_msg['address']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              メールアドレス
              <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
            </div>
            <div class="imgDrop-container">
              プロフィール画像
              <div class="js-area-drop area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic" class="js-input-file input-file">
                <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img js-prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
                ドラッグ＆ドロップ
              </div>
              <div class="area-msg">
                <?php if(!empty($err_msg['pic'])) echo $err_msg['pic']; ?>
              </div>
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