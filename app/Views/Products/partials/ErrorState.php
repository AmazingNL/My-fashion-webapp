<?php
$errorTitle = $errorTitle ?? 'Product Not Found';
$errorMessage = $errorMessage ?? 'This piece seems to have found a new home already, or it never existed in our collection.';
$errorLink = $errorLink ?? '/products';
$errorLinkText = $errorLinkText ?? 'Explore Our Collection';
?>

<div class="error-container">
    <div class="error-content">
        <svg class="error-icon" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
            <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" />
            <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" />
        </svg>
        <h2 class="error-title"><?= htmlspecialchars((string) $errorTitle, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="error-message"><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= htmlspecialchars((string) $errorLink, ENT_QUOTES, 'UTF-8') ?>" class="btn btn--primary"><?= htmlspecialchars((string) $errorLinkText, ENT_QUOTES, 'UTF-8') ?></a>
    </div>
</div>