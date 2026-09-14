<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <span class="brand-mark d-inline-grid place-items-center mb-2" style="width:40px;height:40px;border-radius:12px;background:var(--lavender);color:var(--violet);font-size:1.25rem;"><i class="bi bi-key"></i></span>
                    <h1 class="h4 mb-1">Forgot Password</h1>
                    <p class="text-muted small">Enter your account email to receive a password reset link</p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> py-2 px-3 small mb-3"><?= htmlspecialchars($flash['message']) ?></div>
                <?php endif; ?>

                <?php if (!empty($demoResetLink)): ?>
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="bi bi-info-circle me-1"></i><strong>Local Demo Reset Link:</strong><br>
                        <a href="<?= htmlspecialchars($demoResetLink) ?>" class="alert-link text-break"><?= htmlspecialchars($demoResetLink) ?></a>
                    </div>
                <?php endif; ?>

                <form method="post" action="/forgot-password">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email address</label>
                        <input class="form-control" type="email" name="email" placeholder="name@veloraparlour.demo" required autofocus>
                    </div>

                    <button class="btn btn-primary w-100 py-2 mt-2" type="submit">Generate Reset Link</button>
                </form>

                <div class="text-center mt-3">
                    <a href="/login" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Back to Sign in</a>
                </div>
            </div>
        </div>
    </div>
</div>
