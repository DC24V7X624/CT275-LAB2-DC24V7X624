<?php

define('TITLE', 'Xem tất cả các Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$reason = null;
$quotes = [];

if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
if (!$has_access) {
    $query = 'SELECT id,quote,source,favorite FROM quotes ORDER BY date_entered DESC';

    try{
        $pdo =get_database_connection();
        $statement =$pdo->prepare($query);
        $statement->execute();
        $quotes=$statement->fetchAll();

    }catch(PDOException $e){
       $error_message = 'không thể lấy dữ liệu';
       $reason = $e->getMessage();
    }
}else{
    $error_message = 'bạn không có quyền truy cập trang này ';
}
}

?>


<?php render_page_header(); ?>

<h2>Tất cả các Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access): ?>
    <p>Trang đang được xây dựng...</p>
<?php if($has_access && empty($error_message)): ?>
  <?php if(!empty($quotes)): ?>
    <?php foreach ($quotes as $quote): ?>
        <div>
            <blockquote><?= html_escape($quote['quote']) ?></blockquote>
            <p><?= html_escape($quote['source']) ?>
               <?php if (!empty($quote['favorite'])): ?>
                <strong> | yêu thích!</strong>
                <?php endif; ?>
            </p>
            <p>
                <strong>Quản trị trích dẫn:</strong>
                <a href="edit_quote.php?id=<?= urldecode($quote['id']) ?>">Sửa</a>
                  <a href="delete_quote.php?id=<?= urldecode($quote['id']) ?>">Xoá</a>
            </p>
        </div>
        <br>
      <?php endforeach; ?>
    <?php else: ?>
        <p>Chưa có trích dẫn nào.</p>
    <?php endif; ?>
<?php endif; ?>

<?php render_page_footer(); ?>
<? endif; ?>