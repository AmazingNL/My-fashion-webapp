<?php
$flashMessage = '';
$flashType = 'success';

$successText = trim((string) ($success ?? ''));
$errorText = trim((string) ($error ?? ''));

if ($errorText !== '') {
    $flashMessage = $errorText;
    $flashType = 'error';
} elseif ($successText !== '') {
    $flashMessage = $successText;
    $flashType = 'success';
}
?>

<?php if ($flashMessage !== ''): ?>
    <div class="notice <?= $flashType === 'error' ? 'notice--error' : 'notice--success' ?>" role="status" aria-live="polite">
        <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>