<section class="card admin-tableWrap" aria-live="polite">
    <div class="admin-tableScroll">
        <table class="admin-table" id="usersTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th class="nowrap">Role</th>
                    <th class="text-right nowrap">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php
                    $userId = (int) ($u['userId'] ?? $u['id'] ?? 0);
                    $username = (string) ($u['username'] ?? $u['name'] ?? 'User');
                    $email = (string) ($u['email'] ?? '');
                    $roleValue = strtolower((string) ($u['role'] ?? 'customer'));
                    $isAdmin = ($roleValue === 'admin');
                    $role = $isAdmin ? 'admin' : 'customer';
                    ?>
                    <tr data-user-id="<?= $userId ?>" data-search="<?= htmlspecialchars(strtolower($username . ' ' . $email), ENT_QUOTES, 'UTF-8') ?>" data-role="<?= $role ?>">
                        <td>
                            <div class="userCell">
                                <span class="userAvatar" aria-hidden="true"><?= strtoupper(substr($username, 0, 1)) ?></span>
                                <div class="userMeta">
                                    <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <div class="muted">#<?= $userId ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="muted"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="pill <?= $isAdmin ? 'pill--primary' : '' ?>">
                                <?= $isAdmin ? 'Admin' : 'Customer' ?>
                            </span>
                        </td>
                        <td class="text-right">
                            <form method="post" action="/admin/users/delete" style="display:inline; margin:0;" onsubmit="return confirm('Delete this user account? This action cannot be undone.');">
                                <?= $this->csrfField() ?>
                                <input type="hidden" name="userId" value="<?= $userId ?>">
                                <button class="btn btn--danger btn--sm" type="submit" <?= $isAdmin ? 'disabled' : '' ?>>
                                    <?= $isAdmin ? 'Protected' : 'Delete' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
