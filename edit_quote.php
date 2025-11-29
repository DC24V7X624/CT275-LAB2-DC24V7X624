<?php
/* Đoạn mã xử lý PHP */

define('TITLE', 'Sửa một Trích dẫn');

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

/* Kiểm tra id */
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($has_access && $id <= 0) {
    $error_message = "ID trích dẫn không hợp lệ.";
}

/* Lấy dữ liệu trích dẫn */
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

/* Nếu form được gửi */
if ($has_access && empty($error_message) && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $quote = trim($_POST['quote'] ?? '');
    $source = trim($_POST['source'] ?? '');
    $favorite = isset($_POST['favorite']) ? 1 : 0;

    if ($quote === '' || $source === '') {
        $error_message = "Vui lòng nhập đầy đủ Trích dẫn và Nguồn.";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE quotes 
                SET quote = :quote, source = :source, favorite = :favorite 
                WHERE id = :id
            ");

            $stmt->execute([
                ':quote' => $quote,
                ':source' => $source,
                ':favorite' => $favorite,
                ':id' => $id
            ]);

            $success_message = "Cập nhật trích dẫn thành công!";
            // Cập nhật lại dữ liệu hiển thị
            $quote_data['quote'] = $quote;
            $quote_data['source'] = $source;
            $quote_data['favorite'] = $favorite;

        } catch (PDOException $e) {
            $error_message = "Không thể cập nhật trích dẫn.";
        }
    }
}

?>

<!-- Hiển thị giao diện -->
<?php render_page_header(); ?>

<h2>Sửa trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success">
        <?= html_escape($success_message) ?>
    </div>
<?php endif; ?>

<?php if ($has_access && !empty($quote_data) && empty($error_message)): ?>

    <form action="edit_quote.php?id=<?= urlencode($id) ?>" method="post">

        <p>
            <label>Trích dẫn<br>
                <textarea name="quote" rows="5" cols="40"><?= html_escape($quote_data['quote']) ?></textarea>
            </label>
        </p>

        <p>
            <label>Nguồn<br>
                <input type="text" name="source" size="40"
                       value="<?= html_escape($quote_data['source']) ?>">
            </label>
        </p>

        <p>
            <label>
                <input type="checkbox" name="favorite" value="yes"
                       <?= $quote_data['favorite'] ? 'checked' : '' ?>>
                Đây là trích dẫn yêu thích?
            </label>
        </p>

        <p>
            <input type="submit" value="Cập nhật">
        </p>

    </form>

<?php endif; ?>

<?php render_page_footer(); ?>