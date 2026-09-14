<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1">Staff Directory</h1>
        <p class="text-muted small mb-0">Manage parlour team members, stylist profiles, commission rates, and official documents.</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addStaffModal">
        <i class="bi bi-person-plus-fill"></i>
        <span>Add Staff Member</span>
    </button>
</div>

<!-- Search & Filter Bar -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="get" action="/staff" class="row g-2 align-items-center">
            <div class="col-md-7 col-lg-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, code, designation, mobile or email..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3 col-lg-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Staff</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive Staff</option>
                </select>
            </div>
            <div class="col-md-2 col-lg-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                <?php if ($search !== '' || $statusFilter !== ''): ?>
                    <a href="/staff" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Staff Cards Grid -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
    <?php foreach ($staffList as $s): ?>
        <?php
        $isActive = ((int)$s['is_active'] === 1);
        $docs = $documentsByStaff[(int)$s['id']] ?? [];
        $photo = !empty($s['profile_photo_path']) ? $s['profile_photo_path'] : null;
        ?>
        <div class="col">
            <div class="card h-100 shadow-sm border-0 staff-card position-relative" style="transition: transform 0.15s ease, box-shadow 0.15s ease;">
                <!-- Card Header with Avatar and Basic Info -->
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative">
                                <?php if ($photo): ?>
                                    <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($s['name']) ?>" class="rounded-circle shadow-sm object-fit-cover" style="width: 76px; height: 76px; border: 3px solid #f3e8ff;">
                                <?php else: ?>
                                    <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center text-white fw-bold" style="width: 76px; height: 76px; font-size: 26px; background: linear-gradient(135deg, #7c3aed, #a855f7); border: 3px solid #f3e8ff;">
                                        <?= htmlspecialchars(strtoupper(substr($s['name'], 0, 1))) ?>
                                    </div>
                                <?php endif; ?>
                                <span class="position-absolute bottom-0 end-0 p-1 border border-2 border-white rounded-circle <?= $isActive ? 'bg-success' : 'bg-secondary' ?>" style="width: 14px; height: 14px;" title="<?= $isActive ? 'Active' : 'Inactive' ?>"></span>
                            </div>
                            <div>
                                <h5 class="card-title mb-1 fw-bold text-dark"><?= htmlspecialchars($s['name']) ?></h5>
                                <div class="text-primary small fw-semibold mb-1"><?= htmlspecialchars($s['designation'] ?: 'Stylist & Specialist') ?></div>
                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                                    <?= htmlspecialchars($s['staff_code']) ?>
                                </span>
                            </div>
                        </div>
                        <span class="badge <?= $isActive ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?> rounded-pill px-2 py-1">
                            <?= $isActive ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>

                    <!-- Contact & Financial Information -->
                    <div class="py-2 border-top border-bottom my-2 small">
                        <div class="d-flex align-items-center justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-telephone me-2 text-primary"></i>Mobile:</span>
                            <?php if (!empty($s['mobile'])): ?>
                                <a href="tel:<?= htmlspecialchars($s['mobile']) ?>" class="text-decoration-none fw-medium text-dark"><?= htmlspecialchars($s['mobile']) ?></a>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($s['email'])): ?>
                            <div class="d-flex align-items-center justify-content-between py-1">
                                <span class="text-muted"><i class="bi bi-envelope me-2 text-primary"></i>Email:</span>
                                <span class="text-truncate text-dark" style="max-width: 170px;" title="<?= htmlspecialchars($s['email']) ?>"><?= htmlspecialchars($s['email']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex align-items-center justify-content-between py-1">
                            <span class="text-muted"><i class="bi bi-percent me-2 text-warning-emphasis"></i>Commission:</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-semibold">
                                <?= $s['commission_type'] === 'percentage' ? number_format((float)$s['commission_value'], 1) . '% of service' : '₹' . number_format((float)$s['commission_value'], 2) . ' fixed' ?>
                            </span>
                        </div>
                        <?php if ($s['salary'] !== null && (float)$s['salary'] > 0): ?>
                            <div class="d-flex align-items-center justify-content-between py-1">
                                <span class="text-muted"><i class="bi bi-wallet2 me-2 text-success"></i>Base Salary:</span>
                                <span class="fw-semibold text-dark">₹<?= number_format((float)$s['salary'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Metrics & Linkages -->
                    <div class="d-flex flex-wrap gap-2 mb-3 mt-1">
                        <span class="badge bg-light text-dark border py-1 px-2" title="Total Appointments Assigned">
                            <i class="bi bi-calendar3 me-1 text-info"></i><?= (int)$s['appointments_count'] ?> Appointments
                        </span>
                        <span class="badge bg-light text-dark border py-1 px-2" title="Lifetime Commissions Earned">
                            <i class="bi bi-cash-coin me-1 text-success"></i>₹<?= number_format((float)$s['total_commissions'], 2) ?> Earned
                        </span>
                        <?php if ($s['user_id']): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2" title="Linked to login user: <?= htmlspecialchars($s['user_name']) ?>">
                                <i class="bi bi-person-check-fill me-1"></i>Login: <?= htmlspecialchars($s['user_name']) ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-muted border py-1 px-2" title="No user login assigned to this staff member">
                                <i class="bi bi-person-x me-1"></i>No Login User
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Document indicator -->
                    <div class="mb-3 mt-auto">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 d-flex align-items-center justify-content-between py-1 px-3" data-bs-toggle="modal" data-bs-target="#docsModal<?= $s['id'] ?>">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                <span>Attached Documents</span>
                            </span>
                            <span class="badge bg-secondary rounded-pill"><?= count($docs) ?></span>
                        </button>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#editStaffModal<?= $s['id'] ?>">
                            <i class="bi bi-pencil-square"></i>
                            <span>Edit Profile</span>
                        </button>

                        <form method="post" action="/staff/toggle-status" class="m-0">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-sm <?= $isActive ? 'btn-outline-warning' : 'btn-outline-success' ?>" title="<?= $isActive ? 'Deactivate Staff' : 'Activate Staff' ?>">
                                <i class="bi <?= $isActive ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Staff Modal for ID <?= $s['id'] ?> -->
        <div class="modal fade" id="editStaffModal<?= $s['id'] ?>" tabindex="-1" aria-labelledby="editStaffModalLabel<?= $s['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="/staff/update" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editStaffModalLabel<?= $s['id'] ?>">
                                <i class="bi bi-pencil-square text-primary me-2"></i>Edit Staff Profile &mdash; <?= htmlspecialchars($s['name']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Photo Upload Area -->
                            <div class="card bg-light border-0 mb-4 p-3">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <?php if ($photo): ?>
                                            <img src="<?= htmlspecialchars($photo) ?>" alt="Current Avatar" class="rounded-circle object-fit-cover shadow-sm" style="width: 72px; height: 72px; border: 2px solid #fff;">
                                        <?php else: ?>
                                            <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center text-white fw-bold" style="width: 72px; height: 72px; font-size: 24px; background: linear-gradient(135deg, #7c3aed, #a855f7);">
                                                <?= htmlspecialchars(strtoupper(substr($s['name'], 0, 1))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col">
                                        <label class="form-label fw-semibold mb-1">Change Profile Photo</label>
                                        <input type="file" name="profile_photo" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                                        <small class="text-muted">Select an image file (JPG, PNG, or WEBP, max 5MB). Replaces existing photo.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($s['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Designation / Role <span class="text-danger">*</span></label>
                                    <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($s['designation'] ?? '') ?>" required placeholder="e.g. Senior Stylist, Therapist">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mobile Number</label>
                                    <input type="tel" name="mobile" class="form-control" value="<?= htmlspecialchars($s['mobile'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($s['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select</option>
                                        <option value="female" <?= ($s['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="male" <?= ($s['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="other" <?= ($s['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($s['dob'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Joining Date</label>
                                    <input type="date" name="joining_date" class="form-control" value="<?= htmlspecialchars($s['joining_date'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Commission Type</label>
                                    <select name="commission_type" class="form-select">
                                        <option value="percentage" <?= ($s['commission_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                        <option value="fixed" <?= ($s['commission_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed (₹)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Commission Value</label>
                                    <input type="number" step="0.01" name="commission_value" class="form-control" value="<?= htmlspecialchars((string)($s['commission_value'] ?? '0.00')) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Base Monthly Salary (₹)</label>
                                    <input type="number" step="0.01" name="salary" class="form-control" value="<?= htmlspecialchars((string)($s['salary'] ?? '')) ?>" placeholder="Optional">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address / Notes</label>
                                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($s['address'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="editActive<?= $s['id'] ?>" value="1" <?= $isActive ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="editActive<?= $s['id'] ?>">Staff Member Active & Available for Bookings</label>
                                    </div>
                                </div>
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

        <!-- Documents Modal for ID <?= $s['id'] ?> -->
        <div class="modal fade" id="docsModal<?= $s['id'] ?>" tabindex="-1" aria-labelledby="docsModalLabel<?= $s['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="docsModalLabel<?= $s['id'] ?>">
                            <i class="bi bi-file-earmark-text text-primary me-2"></i>Staff Documents &mdash; <?= htmlspecialchars($s['name']) ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Attached Documents List -->
                        <h6 class="fw-bold mb-3">Attached Documents</h6>
                        <?php if (empty($docs)): ?>
                            <div class="alert alert-light border text-center py-4 text-muted mb-4">
                                <i class="bi bi-folder2-open display-6 d-block mb-2 text-secondary"></i>
                                No documents attached for this staff member yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive mb-4">
                                <table class="table table-hover align-middle border mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Original Filename</th>
                                            <th>Size</th>
                                            <th>Uploaded</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($docs as $doc): ?>
                                            <?php
                                            $docTypeBadge = match($doc['document_type'] ?? 'other') {
                                                'id_proof' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                                'qualification' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                                'joining' => 'bg-success-subtle text-success border border-success-subtle',
                                                default => 'bg-secondary-subtle text-secondary'
                                            };
                                            $docTypeLabel = match($doc['document_type'] ?? 'other') {
                                                'id_proof' => 'ID Proof',
                                                'qualification' => 'Certificate',
                                                'joining' => 'Joining / Contract',
                                                default => 'Other'
                                            };
                                            $fileSizeKb = round(((int)$doc['file_size']) / 1024, 1);
                                            ?>
                                            <tr>
                                                <td class="fw-semibold"><?= htmlspecialchars($doc['title']) ?></td>
                                                <td><span class="badge <?= $docTypeBadge ?>"><?= $docTypeLabel ?></span></td>
                                                <td class="small text-muted font-monospace"><?= htmlspecialchars($doc['original_name']) ?></td>
                                                <td class="small text-muted"><?= $fileSizeKb ?> KB</td>
                                                <td class="small text-muted"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="/staff/documents/<?= $doc['id'] ?>/download" class="btn btn-outline-primary" target="_blank" title="Download / View Document">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                        <form method="post" action="/staff/documents/delete" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this document?');">
                                                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                                                            <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                                            <button type="submit" class="btn btn-outline-danger" title="Delete Document">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <!-- Upload New Document Form -->
                        <div class="card bg-light border-0 p-3">
                            <h6 class="fw-bold mb-3"><i class="bi bi-cloud-upload text-primary me-2"></i>Upload New Staff Document</h6>
                            <form method="post" action="/staff/documents/upload" enctype="multipart/form-data" class="row g-3">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="staff_id" value="<?= $s['id'] ?>">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold">Document Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Aadhaar Card, Cosmetology Diploma" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Type</label>
                                    <select name="document_type" class="form-select form-select-sm">
                                        <option value="id_proof">ID Proof</option>
                                        <option value="qualification">Qualification / Certificate</option>
                                        <option value="joining">Joining Letter / Agreement</option>
                                        <option value="other">Other Document</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">File (PDF, JPG, PNG, DOC) <span class="text-danger">*</span></label>
                                    <input type="file" name="document" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-upload me-1"></i> Upload Document
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($staffList)): ?>
        <div class="col-12">
            <div class="card shadow-sm border-0 py-5 text-center text-muted">
                <i class="bi bi-people display-4 text-secondary mb-3"></i>
                <h5 class="fw-semibold">No Staff Members Found</h5>
                <p class="small text-muted mb-3">There are no staff records matching your search or filters.</p>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                        <i class="bi bi-person-plus-fill me-1"></i> Add Your First Staff Member
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="/staff" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStaffModalLabel">
                        <i class="bi bi-person-plus-fill text-primary me-2"></i>Add New Staff Member
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Photo Upload Area -->
                    <div class="card bg-light border-0 mb-4 p-3">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div id="addPhotoPreview" class="rounded-circle shadow-sm d-flex align-items-center justify-content-center text-white fw-bold" style="width: 76px; height: 76px; font-size: 26px; background: linear-gradient(135deg, #7c3aed, #a855f7); border: 2px solid #fff;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label fw-semibold mb-1">Profile Photo / Staff Avatar</label>
                                <input type="file" name="profile_photo" id="addProfilePhotoInput" class="form-control" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">Select photo from your computer (JPG, PNG, or WEBP, max 5MB).</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Shalini Roy">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Designation / Role <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control" required placeholder="e.g. Senior Hair Stylist, Makeup Artist">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mobile Number</label>
                            <input type="tel" name="mobile" class="form-control" placeholder="e.g. 9876543210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. shalini@veloraparlour.demo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select Gender</option>
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" name="dob" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Commission Type</label>
                            <select name="commission_type" class="form-select">
                                <option value="percentage" selected>Percentage (%)</option>
                                <option value="fixed">Fixed (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Commission Rate</label>
                            <input type="number" step="0.01" name="commission_value" class="form-control" value="10.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Monthly Salary (₹)</label>
                            <input type="number" step="0.01" name="salary" class="form-control" placeholder="Optional">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address / Notes</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Full residential address or qualification details..."></textarea>
                        </div>

                        <!-- Optional Document Attachment during Creation -->
                        <div class="col-12">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="fw-bold mb-2 small text-uppercase text-muted"><i class="bi bi-paperclip me-1"></i> Attach Initial Document (Optional)</h6>
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <input type="text" name="initial_doc_title" class="form-control form-control-sm" placeholder="Document title (e.g. ID Proof)">
                                    </div>
                                    <div class="col-md-3">
                                        <select name="initial_doc_type" class="form-select form-select-sm">
                                            <option value="id_proof">ID Proof</option>
                                            <option value="qualification">Qualification Certificate</option>
                                            <option value="joining">Joining Agreement</option>
                                            <option value="other">Other Document</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="file" name="initial_document" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="addStaffActive" value="1" checked>
                                <label class="form-check-label fw-semibold" for="addStaffActive">Staff Member is Active and Available</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Staff Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const photoInput = document.getElementById('addProfilePhotoInput');
    const photoPreview = document.getElementById('addPhotoPreview');
    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    photoPreview.innerHTML = '<img src="' + e.target.result + '" class="rounded-circle object-fit-cover shadow-sm" style="width: 76px; height: 76px; border: 2px solid #fff;">';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
