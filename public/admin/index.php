<?php
require __DIR__ . '/../../src/bootstrap.php';
require __DIR__ . '/_layout.php';
start_admin_session();
(new Auth)->requireLogin();
$messages = new Messages;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    csrf_check();
    $messages->delete((int) $_POST['delete']);
    header('Location: /admin/index.php'); exit;
}
$perPage = 20;
$total = $messages->count();
$pages = max(1, (int) ceil($total / $perPage));
$page = min($pages, max(1, (int) ($_GET['page'] ?? 1)));
$rows = $messages->page($page, $perPage);
admin_header('Messages'); ?>
<h1>Messages (<?= $total ?>)</h1>
<?php foreach ($rows as $m): ?>
<div class="card">
  <strong><?= e($m['subject'] ?: '(no subject)') ?></strong>
  <div class="meta"><?= e($m['name']) ?> &lt;<a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>&gt; · <?= e($m['created_at']) ?>
  <?= $m['mail_sent'] ? '· emailed' : '· not emailed' ?><?php if ($m['page_url']): ?> · <?= e($m['page_url']) ?><?php endif; ?></div>
  <pre><?= e($m['message']) ?></pre>
  <form method="post" onsubmit="return confirm('Delete this message?')">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <button class="danger" name="delete" value="<?= (int) $m['id'] ?>">Delete</button></form>
</div>
<?php endforeach; if (!$rows): ?><p>No messages yet.</p><?php endif; ?>
<p><?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">&larr; Newer</a><?php endif; ?>
 Page <?= $page ?> of <?= $pages ?>
 <?php if ($page < $pages): ?><a href="?page=<?= $page + 1 ?>">Older &rarr;</a><?php endif; ?></p>
<?php admin_footer();
