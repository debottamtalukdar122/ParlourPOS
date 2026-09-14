<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <span class="brand-mark d-inline-grid place-items-center mb-2" style="width:40px;height:40px;border-radius:12px;background:var(--lavender);color:var(--violet);font-size:1.25rem;"><i class="bi bi-shield-lock"></i></span>
                    <h1 class="h4 mb-1">Set New Password</h1>
                    <p class="text-muted small">Reset password for <strong><?= htmlspecialchars($email ?? '') ?></strong></p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> py-2 px-3 small mb-3"><?= htmlspecialchars($flash['message']) ?></div>
                <?php endif; ?>

                <form method="post" action="/reset-password">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Password (8+ characters)</label>
                        <input class="form-control" type="password" name="password" minlength="8" placeholder="••••••••" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Confirm New Password</label>
                        <input class="form-control" type="password" name="password_confirmation" minlength="8" placeholder="••••••••" required>
                    </div>

                    <button class="btn btn-primary w-100 py-2 mt-2" type="submit">Update Password</button>
                </form>

                <div class="text-center mt-3">
                    <a href="/login" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Back to Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>
