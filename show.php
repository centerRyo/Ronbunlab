<?php
// 共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('　論文詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// 論文IDのGETパラメータを取得
$p_id = (!empty($_GET['r_id'])) ? $_GET['r_id'] : '';
$viewData = getRonbunOne($p_id);

// sidebar用データ
$dbCategoryData = getCategory();
$dbAttentionRonbunData = getAttentionRonbun();
if (empty($viewData)) {
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Locataion:index.php");
}
debug('取得したDBデータ：'.print_r($viewData, true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
require('head.php');
?>
<?php
  require('header.php');
?>
  <main class="container-wrapper">
    <div class="site-width">

      <section class="article">
        <span class="category-name"><?php echo sanitize($viewData['category']); ?></span>
        <h1 class="article-title"><?php echo sanitize($viewData['title']); ?></h1>
        <div class="article-image">
          <img src="<?php echo showImg(sanitize($viewData['image'])); ?>" alt="">
        </div>

        <div class="article-abstract">
          <h2>要約</h2>
          <p><?php echo nl2br(sanitize($viewData['abstract'])); ?></p>
        </div>

        <div class="article-detail">
          <h2>本文</h2>
          <p><?php echo nl2br(sanitize($viewData['detail'])); ?></p>
        </div>
      </section>

      <?php
        require('sidebar.php');
      ?>
    </div>
  </main>
<?php
  require('footer.php');
?>
