<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1">Audit Trail & Security Logs</h1>
        <p class="text-muted small mb-0">Track user authentications, credential events, data updates, and administrative actions.</p>
    </div>
    <div>
        <a href="/users" class="btn btn-outline-primary">
            <i class="bi bi-people me-1"></i> Back to User Management
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="get" action="/audit-logs" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-filter"></i></span>
                    <select name="module" class="form-select" onchange="this.form.submit()">
                        <option value="">All Modules (All System Events)</option>
                        <?php foreach ($modules as $mod): ?>
                            <option value="<?= htmlspecialchars($mod) ?>" <?= $moduleFilter === $mod ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($mod)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 d-flex justify-content-end gap-2">
                <?php if ($moduleFilter !== ''): ?>
                    <a href="/audit-logs" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i> Reset Filter</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-secondary"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 font-monospace" style="font-size: 0.875rem;">
            <thead class="table-light font-sans-serif" style="font-family: inherit;">
                <tr>
                    <th style="width: 170px;">Timestamp</th>
                    <th>User / Actor</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>Record ID</th>
                    <th>IP Address</th>
                    <th>Details / Changes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <?php
                    $action = strtolower($log['action'] ?? '');
                    $actionBadge = match(true) {
                        str_contains($action, 'failed') || str_contains($action, 'error') => 'bg-danger text-white',
                        str_contains($action, 'login') || str_contains($action, 'auth') => 'bg-success text-white',
                        str_contains($action, 'logout') => 'bg-secondary text-white',
                        str_contains($action, 'created') => 'bg-primary text-white',
                        str_contains($action, 'updated') || str_contains($action, 'status') || str_contains($action, 'password') => 'bg-info text-dark',
                        str_contains($action, 'deleted') => 'bg-warning text-dark',
                        default => 'bg-light text-dark border'
                    };
                    $changes = json_decode($log['changes_json'] ?? '{}', true);
                    ?>
                    <tr>
                        <td class="text-nowrap text-muted">
                            <?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))) ?>
                        </td>
                        <td>
                            <?php if (!empty($log['user_name'])): ?>
                                <div><strong><?= htmlspecialchars($log['user_name']) ?></strong></div>
                                <small class="text-muted"><?= htmlspecialchars($log['user_role'] ?? '') ?></small>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Anonymous / System</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $actionBadge ?> px-2 py-1">
                                <?= htmlspecialchars(strtoupper($log['action'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?= htmlspecialchars($log['module_name']) ?>
                            </span>
                        </td>
                        <td>
                            <?= $log['record_id'] ? '#' . (int)$log['record_id'] : '-' ?>
                        </td>
                        <td class="text-muted">
                            <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?>
                        </td>
                        <td>
                            <?php if (!empty($changes)): ?>
                                <pre class="mb-0 text-break p-1 bg-light rounded" style="max-width: 400px; max-height: 120px; overflow-y: auto; font-size: 11px; white-space: pre-wrap;"><?= htmlspecialchars(json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-journal-x display-6 d-block mb-2 text-secondary"></i>
                            No audit logs recorded yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
