<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1">User Management</h1>
        <p class="text-muted small mb-0">Manage system login accounts, role assignments, and staff linkages.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/audit-logs" class="btn btn-outline-secondary">
            <i class="bi bi-journal-text me-1"></i> View Audit Logs
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-1"></i> Add New User
        </button>
    </div>
</div>

<!-- Filters Bar -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="get" action="/users" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, or staff..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role_id" class="form-select">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $roleFilter == $r['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                <?php if ($search !== '' || $roleFilter > 0 || $statusFilter !== ''): ?>
                    <a href="/users" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Linked Staff Profile</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php
                    $roleBadgeClass = match(strtolower($u['role_name'] ?? '')) {
                        'super admin' => 'bg-danger-subtle text-danger border border-danger-subtle',
                        'manager' => 'bg-primary-subtle text-primary border border-primary-subtle',
                        'receptionist / cashier', 'cashier' => 'bg-success-subtle text-success border border-success-subtle',
                        'staff' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                        default => 'bg-secondary-subtle text-secondary'
                    };
                    $isSelf = ((int)$u['id'] === \App\Services\AuthService::userId());
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white bg-secondary" style="width: 38px; height: 38px; font-size: 14px;">
                                    <?= htmlspecialchars(strtoupper(substr($u['name'], 0, 1))) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($u['name']) ?>
                                        <?php if ($isSelf): ?>
                                            <span class="badge bg-light text-dark border ms-1">You</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $roleBadgeClass ?> px-2 py-1 rounded-pill">
                                <?= htmlspecialchars($u['role_name']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($u['staff_id']) && !empty($u['staff_name'])): ?>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                    <i class="bi bi-person-badge me-1"></i>
                                    <?= htmlspecialchars($u['staff_name']) ?> (<?= htmlspecialchars($u['staff_code']) ?>)
                                </span>
                                <?php if (!empty($u['staff_designation'])): ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($u['staff_designation']) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted small">None (System User)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int)$u['is_active'] === 1): ?>
                                <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle me-1"></i> Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white px-2 py-1"><i class="bi bi-dash-circle me-1"></i> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?= $u['last_login_at'] ? htmlspecialchars(date('M j, Y H:i', strtotime($u['last_login_at']))) : 'Never' ?>
                        </td>
                        <td class="small text-muted">
                            <?= htmlspecialchars(date('M j, Y', strtotime($u['created_at']))) ?>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary edit-user-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal"
                                        data-id="<?= $u['id'] ?>"
                                        data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>"
                                        data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
                                        data-role="<?= $u['role_id'] ?>"
                                        data-staff="<?= $u['staff_id'] ?? '' ?>"
                                        data-active="<?= $u['is_active'] ?>">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <?php if (!$isSelf): ?>
                                    <form method="post" action="/users/toggle-status" onsubmit="return confirm('Are you sure you want to change this account status?');">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <?php if ((int)$u['is_active'] === 1): ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Deactivate Account">
                                                <i class="bi bi-person-x"></i> Deactivate
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Activate Account">
                                                <i class="bi bi-person-check"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-people display-6 d-block mb-2 text-secondary"></i>
                            No users found matching your criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="/users/create">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel"><i class="bi bi-person-plus me-1 text-primary"></i> Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Maya Patel">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="e.g. maya@veloraparlour.demo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
                        <small class="text-muted">Must be at least 8 characters long.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select" required>
                            <option value="">Select a Role</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Link to Staff Member <span class="text-muted">(Optional)</span></label>
                        <select name="staff_id" class="form-select">
                            <option value="">None (Independent Login)</option>
                            <?php foreach ($staffList as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['staff_code']) ?> - <?= htmlspecialchars($s['designation']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Associate this account with an existing staff member to track their appointments and commissions.</small>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="createIsActive" value="1" checked>
                        <label class="form-check-label fw-semibold" for="createIsActive">Account Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="/users/update">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="id" id="editUserId">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel"><i class="bi bi-pencil-square me-1 text-primary"></i> Edit User Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editUserName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reset Password</label>
                        <input type="password" name="password" id="editUserPassword" class="form-control" minlength="8" placeholder="Leave blank to keep current password">
                        <small class="text-muted">Only fill this in if you want to change the user's password (min 8 characters).</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role_id" id="editUserRole" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Link to Staff Member <span class="text-muted">(Optional)</span></label>
                        <select name="staff_id" id="editUserStaff" class="form-select">
                            <option value="">None (Independent Login)</option>
                            <?php foreach ($staffList as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['staff_code']) ?> - <?= htmlspecialchars($s['designation']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="editUserIsActive" value="1">
                        <label class="form-check-label fw-semibold" for="editUserIsActive">Account Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-user-btn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editUserId').value = this.dataset.id;
            document.getElementById('editUserName').value = this.dataset.name;
            document.getElementById('editUserEmail').value = this.dataset.email;
            document.getElementById('editUserRole').value = this.dataset.role;
            document.getElementById('editUserStaff').value = this.dataset.staff;
            document.getElementById('editUserIsActive').checked = (this.dataset.active == '1');
            document.getElementById('editUserPassword').value = '';
        });
    });
});
</script>
