<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1"><?= !empty($isStaff) ? 'My Assigned Appointments' : 'Appointments & Schedule' ?></h1>
        <p class="text-muted small mb-0">
            <?= !empty($isStaff) ? 'View your client schedule and update service status.' : 'Book new appointments and monitor parlor service schedules.' ?>
        </p>
    </div>
    <?php if (empty($isStaff)): ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#newAppointmentForm">
            <i class="bi bi-calendar-plus me-1"></i> Book New Appointment
        </button>
    <?php endif; ?>
</div>

<?php if (empty($isStaff)): ?>
<div class="collapse mb-4" id="newAppointmentForm">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-semibold"><i class="bi bi-calendar-event me-2 text-primary"></i>Book New Appointment</h5>
        </div>
        <div class="card-body">
            <form method="post" action="/appointments" class="row g-3">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Choose Customer</option>
                        <?php foreach ($customers as $x): ?>
                            <option value="<?= $x['id'] ?>"><?= htmlspecialchars($x['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
                    <select name="service_id" class="form-select" required>
                        <option value="">Choose Service</option>
                        <?php foreach ($services as $x): ?>
                            <option value="<?= $x['id'] ?>"><?= htmlspecialchars($x['name']) ?> — ₹<?= number_format((float)$x['price'], 2) ?> (<?= $x['duration_minutes'] ?>m)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Staff Member <span class="text-danger">*</span></label>
                    <select name="staff_id" class="form-select" required>
                        <option value="">Assign Staff</option>
                        <?php foreach ($staff as $x): ?>
                            <option value="<?= $x['id'] ?>"><?= htmlspecialchars($x['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Start Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="start_at" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes <span class="text-muted">(Optional)</span></label>
                    <input class="form-control" name="notes" placeholder="Special customer requests, hair type, skin allergy notes...">
                </div>
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="collapse" data-bs-target="#newAppointmentForm">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Confirm Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Customer</th>
                    <th>Staff</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th class="text-end">Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $x): ?>
                    <?php
                    $status = strtolower($x['status'] ?? 'pending');
                    $badgeClass = match($status) {
                        'confirmed' => 'bg-info text-dark',
                        'in_progress' => 'bg-warning text-dark',
                        'completed' => 'bg-success text-white',
                        'cancelled' => 'bg-danger text-white',
                        'no_show' => 'bg-secondary text-white',
                        default => 'bg-secondary text-white'
                    };
                    ?>
                    <tr>
                        <td class="fw-semibold font-monospace small">
                            <?= htmlspecialchars($x['appointment_code']) ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($x['customer_name']) ?></strong>
                            <?php if (!empty($x['customer_mobile'])): ?>
                                <small class="d-block text-muted"><?= htmlspecialchars($x['customer_mobile']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($x['staff_name'] ?? 'Unassigned') ?>
                            </span>
                        </td>
                        <td>
                            <div><?= htmlspecialchars(date('D, j M Y', strtotime($x['start_at']))) ?></div>
                            <small class="text-muted"><?= htmlspecialchars(date('h:i A', strtotime($x['start_at']))) ?> - <?= htmlspecialchars(date('h:i A', strtotime($x['end_at']))) ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $badgeClass ?> px-2 py-1">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))) ?>
                            </span>
                        </td>
                        <td class="small text-muted" style="max-width: 200px;">
                            <?= htmlspecialchars($x['notes'] ?: '—') ?>
                        </td>
                        <td class="text-end">
                            <form method="post" action="/appointments/status" class="d-inline-flex gap-1 align-items-center">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="id" value="<?= $x['id'] ?>">
                                <select name="status" class="form-select form-select-sm py-1" style="width: auto;" onchange="this.form.submit()">
                                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-calendar2-x display-6 d-block mb-2 text-secondary"></i>
                            No appointments found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
