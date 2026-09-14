<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($data['isStaff'])): ?>
    <!-- Staff-Specific Dashboard -->
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="page-kicker">Staff Portal</div>
            <h1 class="h3 mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user']['name']) ?></h1>
            <p class="text-muted small mb-0 mt-1">
                <?= htmlspecialchars($data['staff']['designation'] ?? 'Beautician & Stylist') ?> 
                &middot; <span class="badge bg-light text-dark border"><?= htmlspecialchars($data['staff']['staff_code'] ?? 'STAFF') ?></span>
            </p>
        </div>
        <a class="btn btn-primary" href="/appointments">
            <i class="bi bi-calendar3 me-1"></i> View Full Schedule
        </a>
    </div>

    <!-- Staff Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small mb-2"><i class="bi bi-calendar-check me-1 text-primary"></i> Today's Appointments</div>
                    <div class="metric-value fs-2 fw-bold text-primary"><?= (int)$data['todayAppointments'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small mb-2"><i class="bi bi-calendar-event me-1 text-info"></i> Total Assigned</div>
                    <div class="metric-value fs-2 fw-bold text-dark"><?= (int)$data['totalAppointments'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small mb-2"><i class="bi bi-check2-circle me-1 text-success"></i> Completed Services</div>
                    <div class="metric-value fs-2 fw-bold text-success"><?= (int)$data['completedAppointments'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small mb-2"><i class="bi bi-cash-coin me-1 text-warning"></i> Commissions Earned</div>
                    <div class="metric-value fs-2 fw-bold text-warning-emphasis">₹<?= number_format((float)$data['totalCommissions'], 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Appointments Schedule -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold"><i class="bi bi-clock-history me-2 text-primary"></i>My Upcoming Appointments</h5>
            <a href="/appointments" class="small text-decoration-none">Manage all &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Client Name</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th class="text-end">Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['upcomingAppointments'] as $apt): ?>
                        <?php
                        $status = strtolower($apt['status'] ?? 'pending');
                        $badgeClass = match($status) {
                            'confirmed' => 'bg-info text-dark',
                            'in_progress' => 'bg-warning text-dark',
                            'completed' => 'bg-success text-white',
                            'cancelled' => 'bg-danger text-white',
                            default => 'bg-secondary text-white'
                        };
                        ?>
                        <tr>
                            <td class="fw-semibold font-monospace small"><?= htmlspecialchars($apt['appointment_code']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($apt['customer_name']) ?></strong>
                                <?php if (!empty($apt['customer_mobile'])): ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($apt['customer_mobile']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= htmlspecialchars(date('D, j M Y', strtotime($apt['start_at']))) ?></div>
                                <small class="text-muted"><?= htmlspecialchars(date('h:i A', strtotime($apt['start_at']))) ?> - <?= htmlspecialchars(date('h:i A', strtotime($apt['end_at']))) ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?> px-2 py-1">
                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <form method="post" action="/appointments/status" class="d-inline-flex gap-1 align-items-center">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="id" value="<?= $apt['id'] ?>">
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
                    <?php if (empty($data['upcomingAppointments'])): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-check display-6 d-block mb-2 text-secondary"></i>
                                No upcoming appointments scheduled today.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>
    <!-- Manager & Admin & Cashier Dashboard -->
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="page-kicker">Command Center</div>
            <h1 class="h3 mb-0">Good day, <?= htmlspecialchars($_SESSION['user']['name']) ?></h1>
        </div>
        <?php if (in_array('pos.use', $_SESSION['user']['permissions'] ?? [], true)): ?>
            <a class="btn btn-primary" href="/pos"><i class="bi bi-cart-plus me-1"></i>Create a sale</a>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ([
            'Today’s sales' => '₹' . number_format($data['sales'], 2),
            'Bills today' => $data['bills'],
            'Today’s appointments' => $data['appointments'],
            'Today’s expenses' => '₹' . number_format($data['expenses'], 2)
        ] as $label => $value): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="card metric-card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="text-muted small mb-2"><?= $label ?></div>
                        <div class="metric-value fs-2 fw-bold"><?= $value ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Today at a glance</h2>
                        <a href="/appointments" class="small">View appointments</a>
                    </div>
                    <div class="row text-center g-3">
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-primary"><?= $data['customers'] ?></div>
                            <div class="small text-muted">Active customers</div>
                        </div>
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-warning"><?= $data['pending'] ?></div>
                            <div class="small text-muted">Pending bookings</div>
                        </div>
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-success"><?= $data['appointments'] ?></div>
                            <div class="small text-muted">Appointments today</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-semibold py-3">Low-stock alerts</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Available</th>
                                <th>Min.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['lowStock'] as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td class="text-danger fw-bold"><?= $p['current_stock'] ?></td>
                                    <td><?= $p['minimum_stock'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$data['lowStock']): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Everything is stocked up.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-semibold">Recent invoices</span>
                    <?php if (in_array('pos.use', $_SESSION['user']['permissions'] ?? [], true)): ?>
                        <a class="small" href="/pos">Create sale</a>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['recentInvoices'] as $x): ?>
                                <tr>
                                    <td><a class="fw-semibold text-decoration-none" href="/invoices/<?= $x['id'] ?>"><?= htmlspecialchars($x['invoice_number']) ?></a></td>
                                    <td><?= htmlspecialchars($x['customer_name']) ?></td>
                                    <td><?= htmlspecialchars(date('d M, H:i', strtotime($x['created_at']))) ?></td>
                                    <td class="text-end fw-semibold">₹<?= number_format((float)$x['grand_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$data['recentInvoices']): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Your completed sales will appear here.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-semibold">Upcoming appointments</span>
                    <a class="small" href="/appointments">Manage</a>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($data['upcomingAppointments'] as $x): ?>
                        <div class="list-group-item px-3 py-3">
                            <div class="d-flex justify-content-between gap-2">
                                <strong><?= htmlspecialchars($x['customer_name']) ?></strong>
                                <span class="badge text-bg-light border"><?= htmlspecialchars(str_replace('_', ' ', $x['status'])) ?></span>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-clock me-1"></i><?= htmlspecialchars(date('d M · h:i A', strtotime($x['start_at']))) ?><?= $x['staff_name'] ? ' · ' . htmlspecialchars($x['staff_name']) : '' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$data['upcomingAppointments']): ?>
                        <div class="text-center text-muted py-4">No upcoming appointments.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
