<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　論文投稿ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

// ==========================================
// 画面表示処理
// ==========================================
// GETデータを格納
$r_id = (!empty($_GET['r_id'])) ? $_GET['r_id'] : '';
// DBから論文データを取得
$dbFormData = (!empty($r_id)) ? getRonbun($_SESSION['user_id'], $r_id) : '';
// 新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
// DBからカテゴリを取得
$dbCategoryData = getCategory();
debug('論文ID:'.$r_id);
debug('フォーム用DBデータ：'.print_r($dbFormData, true));
debug('カテゴリデータ：'.print_r($dbCategoryData, true));

// パラメータ改ざんチェック
// GETパラメータはあるが、改ざんされている場合、正しい論文データが取ってこれないのでマイページへ遷移する
if (!empty($r_id) && empty($dbFormData)) {
  debug('GETパラメータの論文IDが異なります。マイページへ遷移します');
  header("Location:mypage.php");
}

if (!empty($_POST)) {
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST, true));

  $title = $_POST['title'];
  $category = $_POST['category_id'];
  $abstract = $_POST['abstract'];
  $detail = $_POST['detail'];
  $image = (!empty($_FILES['image']['name'])) ? uploadImg($_FILES['image'], 'image') : '';
  $image = (empty($image) && !empty($dbFormData['image'])) ? $dbFormData['image'] : $image;

  if (empty($dbFormData)) {
    validRequired($title, 'title');
    validMaxLen($title, 'title');
    validSelect($category, 'category_id');
    validRequired($abstract, 'abstract');
    validMaxLen($abstract, 'abstract', 350);
    validRequired($detail, 'detail');
  } else {
    if ($dbFormData['title'] !== $title) {
      validRequired($title, 'title');
      validMaxLen($title, 'title');
    }
    if ($dbFormData['category_id'] !== $category) {
      validSelect($category, 'category');
    }
    if ($dbFormData['abstract'] !== $abstract) {
      validRequired($abstract, 'abstract');
      validMaxLen($abstract, 'abstract', 350);
    }
    if ($dbFormData['detail'] !== $detail) {
      validRequired($detail, 'detail');
    }
  }

  if (empty($err_msg)) {
    debug('バリデーションOKです');

    try {
      $dbh = dbConnect();
      if ($edit_flg) {
        debug('DB更新です');
        $sql = 'UPDATE ronbun SET title = :title, category_id = :category, abstract = :abstract, detail = :detail, image = :image WHERE user_id = :u_id AND id = :r_id';
        $data = array(':title' => $title, ':category' => $category, ':abstract' => $abstract, ':detail' => $detail, ':image' => $image, ':u_id' => $_SESSION['user_id'], ':r_id' => $r_id);
      } else {
        debug('新規登録です');
        $sql = 'INSERT INTO ronbun (title, category_id, abstract, detail, image, user_id, created_date) VALUES (:title, :category, :abstract, :detail, :image, :u_id, :date)';
        $data = array(':title' => $title, ':category' => $category, ':abstract' => $abstract, ':detail' => $detail, ':image' => $image, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL:'.$sql);
      debug('流し込みデータ'.print_r($data, true));
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        $_SESSION['msg_success'] = MSG01;
        debug('トップページへ遷移します');
        header("Location:index.php");
      }

    } catch (Exception $e) {
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

require('head.php');
require('header.php');
?>

<main class="container-wrapper">
  <div class="site-width">
    <div class="post-container">
      <h1 class="title"><?php echo (!$edit_flg) ? '論文を投稿する' : '論文を編集する'; ?></h1>
      <div class="form-container">
        <form class="form" action="" method="post" enctype="multipart/form-data">
          <div class="area-msg">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
          </div>
          <label class="<?php if(!empty($err_msg['title'])) echo 'err'; ?>">
            タイトル<span class="must">必須</span>
            <input type="text" name="title" value="<?php echo getFormData('title'); ?>">
          </label>
          <div class="area-msg">
            <?php if(!empty($err_msg['title'])) echo $err_msg['title']; ?>
          </div>
          <label class="<?php if(!empty($err_msg['category'])) echo 'err'; ?>">
            カテゴリー<span class="must">必須</span>
            <select class="category" name="category_id">
              <option <?php if(getFormData('category_id') == 0){ echo 'selected'; } ?>>選択してください</option>
              <?php foreach ($dbCategoryData as $key => $val) { ?>
                <option value="<?php echo $val['id'] ?>" <?php if(getFormData('category_id') == $val['id']){ echo 'selected'; } ?>>
                  <?php echo $val['name']; ?>
                </option>
              <?php } ?>
            </select>
          </label>
          <div class="area-msg">
            <?php if(!empty($err_msg['category_id'])) echo $err_msg['category_id']; ?>
          </div>
          <label class="<?php if(!empty($err_msg['abstract'])) echo 'err'; ?>">
            要約<span class="must">必須</span>
            <textarea class="abstract" id="js-count-text" name="abstract" rows="8" cols="80"><?php echo getFormData('abstract'); ?></textarea>
          </label>
          <p class="counter-text"><span class="js-count-view">0</span>/350文字</p>
          <div class="area-msg">
            <?php if(!empty($err_msg['abstract'])) echo $err_msg['abstract']; ?>
          </div>
          <label class="<?php if(!empty($err_msg['detail'])) echo 'err'; ?>">
            本文詳細<span class="must">必須</span>
            <textarea class="detail" name="detail" rows="15" cols="100"><?php echo getFormData('detail'); ?></textarea>
          </label>
          <div class="area-msg">
            <?php if(!empty($err_msg['detail'])) echo $err_msg['detail']; ?>
          </div>
          <div class="imgDrop-container">
            画像
            <div class="js-area-drop area-drop <?php if(!empty($err_msg['image'])) echo 'err'; ?>">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <input type="file" name="image" class="js-input-file input-file">
              <img src="<?php echo getFormData('image'); ?>" alt="" class="prev-img js-prev-img" style="<?php if(empty(getFormData('image'))) echo 'display:none;'; ?>">
              ドラッグ＆ドロップ
            </div>
            <div class="area-msg">
                  <?php if(!empty($err_msg['image'])) echo $err_msg['image']; ?>
            </div>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-submit" value="<?php echo (!$edit_flg) ? '投稿する' : '編集する'; ?>">
          </div>
        </form>
      </div>
      <?php require('sidebar_mypage.php'); ?>
    </div>
  </div>
</main>

<?php
require('footer.php');
?>
