<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <span class="brand-mark d-inline-grid place-items-center mb-2" style="width:40px;height:40px;border-radius:12px;background:var(--lavender);color:var(--violet);font-size:1.25rem;"><i class="bi bi-stars"></i></span>
                    <h1 class="h4 mb-1">Sign in to Velora</h1>
                    <p class="text-muted small">Parlour POS Billing &amp; Management System</p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> py-2 px-3 small mb-3"><?= htmlspecialchars($flash['message']) ?></div>
                <?php endif; ?>

                <form method="post" action="/login">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email address</label>
                        <input class="form-control" type="email" name="email" placeholder="name@veloraparlour.demo" required autofocus>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label small fw-semibold mb-0">Password</label>
                            <a href="/forgot-password" class="small text-decoration-none">Forgot password?</a>
                        </div>
                        <input class="form-control mt-1" type="password" name="password" placeholder="••••••••" required>
                    </div>

                    <button class="btn btn-primary w-100 py-2 mt-2" type="submit">Sign in</button>
                </form>

                <?php if (!empty($canSetup)): ?>
                    <p class="small text-center mt-3 mb-0 text-muted">First run? <a href="/setup" class="fw-semibold">Create the administrator account.</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
