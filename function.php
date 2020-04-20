<?php

//ログを取る
ini_set('log_errors','on');
ini_set('error_log','php.log');

// =============================================
// デバッグ
// =============================================
// デバッグフラグ
$debug_flg = true;
function debug($str) {
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ:'.$str);
  }
}

// =============================================
// セッション準備・有効期限を伸ばす
// =============================================
session_save_path('/var/tmp/');
ini_set('session.gc_maxlifetime', 60*60*24*30);
ini_set('session.cookie_lifetime', 60*60*24*30);
session_start();
session_regenerate_id();

// =============================================
// 画面表示処理開始ログ吐き出し関数
// =============================================
function debugLogStart() {
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION, true));
  debug('現在日時タイムスタンプ：'.time());
  if (!empty($_SESSION['login_time']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ));
  }
}

// ==============================================
// 定数
// ==============================================
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', '半角英数字のみご利用いただけます');
define('MSG04', '8文字以上で入力してください');
define('MSG05', 'パスワード(再入力)が正しくありません');
define('MSG06', '255文字以内で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください');
define('MSG08', 'そのEmailアドレスはすでに使用されています');
define('MSG09', 'Emailアドレスまたはパスワードが正しくありません');
define('MSG10', '正しくありません');
define('MSG11', '半角数字のみご利用いただけます');
define('MSG12', '電話番号の形式で入力してください');
define('MSG13', '郵便番号の形式で入力してください');
define('MSG14', 'パスワードが一致しません');
define('MSG15', '新しいパスワードは古いパスワードと異なるものにしてください');
define('MSG16', '文字で入力してください');
define('MSG17', '有効期限切れです');
define('SUC01', '登録しました');
define('SUC02', 'プロフィールを更新しました');
define('SUC03', 'パスワードを変更しました');
define('SUC04', 'メールを送信しました');

// ==============================================
// エラーメッセージ格納用の配列
// ==============================================
$err_msg = array();

// ==============================================
//   バリデーション関数
// ==============================================
// 未入力チェック
function validRequired($str, $key) {
  if (empty($str)) {
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}

// email形式チェック
function validEmail($str, $key) {
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

// 最大文字数チェック
function validMaxLen($str, $key, $max = 256) {
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}

// 最小文字数チェック
function validMinLen($str, $key, $min = 8) {
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}

// 半角チェック
function validHalf($str, $key) {
  if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

// 同値チェック
function validMatch($str1, $str2, $key) {
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}

// 数字チェック
function validNumber($str, $key) {
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}

// 電話番号形式チェック
function validTel($str, $key) {
  if (!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG12;
  }
}

// 郵便番号形式チェック
function validZip($str, $key) {
  if (!preg_match("/^(([0-9]{3}-[0-9]{4})|([0-9]{7}))$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG13;
  }
}

// email重複チェック
function validDup($email) {
  global $err_msg;
  try {
    // DB接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty(array_shift($result))) {
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

// セレクトボックスチェック
function validSelect($str, $key) {
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}

// パスワードチェック
function validPass($str, $key) {
  validHalf($str, $key);
  validMaxLen($str, $key);
  validMinLen($str, $key);
}

// 固定長チェック
function validLength($str, $key, $length = 8) {
  if (mb_strlen($str) !== $length) {
    global $err_msg;
    $err_msg[$key] = $length . MSG16;
  }
}

// =============================================
// データベース
// =============================================
// DB接続関数
function dbConnect() {
  // 開発環境用
  // $dsn = 'mysql:dbname=ronbunlab;host=localhost;charset=utf8';
  // $user = 'root';
  // $password = 'root';
  // 本番環境用
  $dsn = 'mysql:dbname=renasce_ronbunlab;host=mysql1.php.xdomain.ne.jp;charset=utf8';
  $user = 'renasce_ronbun';
  $password = 'ronbunlab';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    // デフォルトフェッチモードを連想配列型式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う（一度に結果セット全てを取得し、サーバー負荷を軽減）
    // SELECTで得た結果に対してもrowCountメソッドを使う
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

// SQL実行関数
function queryPost($dbh, $sql, $data) {
  // クエリ作成
  $stmt = $dbh->prepare($sql);
  // プレースホルダーに値をセットし、SQL文を実行
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました');
    debug('失敗したSQL:'.print_r($stmt, true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリに成功しました');
  return $stmt;
}

function getRonbun($u_id, $p_id) {
  debug('論文情報を取得します');
  debug('ユーザーID：'.$u_id);
  debug('論文ID：'.$p_id);

  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM ronbun WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを1レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:'.$e->getMessage());
  }
}

function getRonbunList($category, $currentMinNum = 1, $span = 12) {
  debug('論文情報を取得します');
  try {
    $dbh = dbConnect();
    $sql = 'SELECT id FROM ronbun';
    if (!empty($category)) $sql .= ' WHERE category_id = ' . $category;
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); // 総レコード数
    $rst['total_page'] = ceil($rst['total']/$span);
    if (!$stmt) {
      return false;
    }
    $sql = 'SELECT r.id, r.title, r.abstract, r.detail, r.image, r.created_date, r.updated_date, c.name AS category FROM ronbun AS r LEFT JOIN category AS c ON r.category_id = c.id';
    if (!empty($category)) {
      $sql .= ' WHERE category_id = ' . $category . ' AND r.delete_flg = 0 AND c.delete_flg = 0 ORDER BY r.updated_date DESC';
    } else {
      $sql .= ' WHERE r.delete_flg = 0 AND c.delete_flg = 0 ORDER BY r.updated_date DESC';
    }
    $sql .= ' LIMIT ' . $span . ' OFFSET ' . $currentMinNum;
    $data = array();
    debug('SQL:'.$sql);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:'.$e->getMessage());
  }
}

function getRonbunOne($p_id) {
  debug('論文情報を取得します');
  debug('論文ID：'.$p_id);
  try {
    $dbh = dbConnect();
    $sql = 'SELECT r.id, r.title, r.abstract, r.detail, r.image, r.user_id, r.created_date, r.updated_date, c.name AS category FROM ronbun AS r LEFT JOIN category AS c ON r.category_id = c.id WHERE r.id = :p_id AND r.delete_flg = 0 AND c.delete_flg = 0';
    $data = array(':p_id' => $p_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}

function getAttentionRonbun() {
  debug('注目論文情報を取得します');
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM ronbun ORDER BY updated_date DESC LIMIT 4';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}

function getCategory() {
  debug('カテゴリ情報を取得します');
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}

function getUser($u_id) {
  debug('ユーザー情報を取得します');
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

function getMyRonbun($u_id) {
  debug('自分の論文情報を取得します');
  debug('ユーザー情報：'.$u_id);
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM ronbun WHERE user_id = :u_id AND delete_flg = 0 ORDER BY created_date DESC LIMIT 9';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

function getFavorite($u_id) {
  debug('お気に入り情報を取得します');
  debug('ユーザー情報：'.$u_id);
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorite AS f INNER JOIN ronbun AS r ON f.ronbun_id = r.id WHERE f.user_id = :u_id AND f.delete_flg = 0';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

function isFavorite($u_id, $r_id) {
  debug('お気に入り情報があるか確認します');
  debug('ユーザーID:'.$u_id);
  debug('論文ID：'.$r_id);
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorite WHERE user_id = :u_id AND ronbun_id = :r_id';
    $data = array(':u_id' => $u_id, ':r_id' => $r_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt->rowCount()) {
      debug('お気に入りに登録されています');
      return true;
    } else {
      debug('お気に入りに登録されていません');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

// =================================================
// メール送信
// =================================================
function sendMail($from, $to, $subject, $comment) {
  if (!empty($to) && !empty($subject) && !empty($comment)) {
    mb_language('Japanese');
    mb_internal_encoding('UTF-8');

    $result = mb_send_mail($to, $subject, $comment, 'From: '.$from);
    if ($result) {
      debug('メールの送信に成功しました');
    } else {
      debug('、【エラー発生】メールの送信に失敗しました');
    }
  }
}

// =================================================
// その他
// =================================================
function sanitize($str) {
  return htmlspecialchars($str, ENT_QUOTES);
}

function getFormData($str) {
  global $dbFormData;
  if (!empty($dbFormData)) {
    if (!empty($err_msg[$str])) {
      if (isset($_POST[$str])) {
        return sanitize($_POST[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    } else {
      if (isset($_POST[$str]) && $_POST[$str] !== $dbFormData[$str]) {
        return sanitize($_POST[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($_POST[$str])) {
      return sanitize($_POST[$str]);
    }
  }
}

function uploadImg($file, $key) {
  debug('画像アップロード処理開始');
  debug('FILE情報:'.print_r($file, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      switch ($file['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }

      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        throw new RuntimeException('画像形式が未対応です');
      }

      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);

      if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }

      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス:'.$path);
      return $path;
    } catch (RuntimeException $e) {
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

function showImg($path) {
  if (empty($path)) {
    return 'images/sample-img.png';
  }
  return $path;
}

/*
$currentPageNum : 現在のページ数
$totalPageNum : 総ページ数
$link : 検索用GETパラメータリンク
$pageColNum : ページネーション表示数
*/
function pagination($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5) {
  // 現在のページが総ページ数と同じ かつ 総ページ数が表示項目数以上なら左にリンク4個出す
  if ($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
    // 現在のページが、総ページの1ページ前なら左に3個、右に1個出す
  } elseif ($currentPageNum == ($totalPageNum - 1) && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum - 1;
    // 現在のページが2ページの場合、左にリンク1個、右に3個出す
  } elseif ($currentPageNum == 2 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
    // 現在のページが1ページの場合、左に何も出さず、右に4個出す
  } elseif ($currentPageNum == 1 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum;
    $maxPageNum = $currentPageNum + 4;
    // 総ページ数が表示項目数より少ない場合は総ページをループのmax、ループのminを1に設定
  } elseif ($totalPageNum < $pageColNum) {
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  } else {
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum - 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
    if ($currentPageNum !== 1) {
      echo '<li class="pagination-list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
    }
    for($i = $minPageNum; $i <= $maxPageNum; $i++) {
      echo '<li class="pagination-list-item ';
      if($currentPageNum === $i) { echo 'active'; }
      echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
    }
    if($currentPageNum !== $maxPageNum) {
      echo '<li class="pagination-list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
    }
    echo '  </ul>';
  echo '</div>';
}

// GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメーターのキー
function appendGetParam($arr_del_key = array()) {
  if (!empty($_GET)) {
    $str = '?';
    foreach ($_GET as $key => $val) {
      if (!in_array($key, $arr_del_key, true)) { // 取り除きたいパラメータじゃない場合にURLにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_strlen($str, 0, -1, "UTF-8");
    return $str;
  }
}

// セッションを1回だけ取得する
function getSessionFlash($key) {
  if (!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

// ランダムな数を作成する
function makeRandKey($length = 8) {
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; $i++) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}
