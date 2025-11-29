<?php
/* Đoạn mã xử lý PHP */

define('TITLE', 'Xóa một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$success_message = null;
$quote_data = null;

/* Nếu không có quyền */
if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
}

/* Lấy ID trích dẫn */
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($has_access && $id <= 0) {
    $error_message = "ID trích dẫn không hợp lệ.";
}

/* Lấy dữ liệu trích dẫn để hiển thị thông tin */
if ($has_access && empty($error_message)) {
    try {
        $pdo = get_database_connection();
        $stmt = $pdo->prepare("SELECT * FROM quotes WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $quote_data = $stmt->fetch();

        if (!$quote_data) {
            $error_message = "Không tìm thấy trích dẫn với ID $id.";
        }
    } catch (PDOException $e) {
        $error_message = "Không thể truy vấn dữ liệu.";
    }
}

/* Nếu form xác nhận xóa được gửi */
if ($has_access && empty($error_message) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM quotes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $success_message = "Đã xóa trích dẫn thành công!";
        $quote_data = null; // Xóa dữ liệu để không hiển thị nữa
    } catch (PDOException $e) {
        $error_message = "Không thể xóa trích dẫn.";
    }
}

?>

<!-- Hiển thị giao diện -->
<?php render_page_header(); ?>

<h2>Xóa trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success">
        <?= html_escape($success_message) ?>
    </div>
    <p><a href="list_quotes.php">Quay lại danh sách trích dẫn</a></p>
<?php endif; ?>

<?php if ($has_access && !empty($quote_data) && empty($success_message)): ?>

    <p>Bạn có chắc chắn muốn xóa trích dẫn sau?</p>

    <blockquote><?= html_escape($quote_data['quote']) ?></blockquote>
    <p><strong>Nguồn:</strong> <?= html_escape($quote_data['source']) ?></p>
    <?php if ($quote_data['favorite']): ?>
        <p><strong>Yêu thích</strong></p>
    <?php endif; ?>

    <form action="delete_quote.php?id=<?= urlencode($id) ?>" method="post">
        <input type="submit" value="Xác nhận xóa">
        <a href="list_quotes.php">Hủy</a>
    </form>

<?php endif; ?>

<?php render_page_footer(); ?>