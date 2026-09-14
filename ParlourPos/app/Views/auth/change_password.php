<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h1 class="h5 mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Change Password</h1>
            </div>
            <div class="card-body p-4">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> py-2 px-3 small mb-3"><?= htmlspecialchars($flash['message']) ?></div>
                <?php endif; ?>

                <form method="post" action="/change-password">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Current Password</label>
                        <input class="form-control" type="password" name="current_password" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Password (minimum 8 characters)</label>
                        <input class="form-control" type="password" name="new_password" minlength="8" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Confirm New Password</label>
                        <input class="form-control" type="password" name="new_password_confirmation" minlength="8" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/dashboard" class="btn btn-outline-secondary btn-sm">Cancel</a>
                        <button class="btn btn-primary btn-sm px-4" type="submit">Save New Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
