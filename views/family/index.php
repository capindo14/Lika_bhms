<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1"><?= escape($pageTitle) ?></h3>
                <p class="text-muted small mb-0">Track household profiles, family units, and member relationships.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFamilyModal">
                    <i class="bi bi-plus-lg me-1"></i> Create Family Profile
                </button>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="families-table" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Family No.</th>
                                <th>Family Head</th>
                                <th>Address</th>
                                <th>Total Members</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($families as $f): 
                                // Fetch members for the modal viewer
                                $members = App\Models\Family::getMembers($f['id']);
                            ?>
                                <tr>
                                    <td class="fw-bold text-success"><?= escape($f['family_no']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= escape($f['head_name']) ?></div>
                                        <small class="text-muted"><?= escape($f['head_code']) ?></small>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-block" style="max-width: 250px;" title="<?= escape($f['address']) ?>"><?= escape($f['address']) ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-light btn-sm border fw-semibold rounded-pill px-3" onclick="viewMembers(<?= $f['id'] ?>, '<?= escape($f['family_no']) ?>', '<?= escape($f['head_name']) ?>')">
                                            <i class="bi bi-people me-1 text-primary"></i> <?= $f['total_members'] ?> Members
                                        </button>
                                        
                                        <!-- Hidden container for Modal copy -->
                                        <div id="members-data-<?= $f['id'] ?>" class="d-none">
                                            <!-- Socio-Economic & Health Summary Card -->
                                            <div class="card bg-light border-0 shadow-sm mb-4">
                                                <div class="card-body p-3">
                                                    <h6 class="fw-bold mb-3 text-success d-flex align-items-center"><i class="bi bi-heart-pulse-fill me-2"></i>Health & Socio-Economic Indicators</h6>
                                                    <div class="row g-3">
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <small class="text-muted d-block">Occupation (Head)</small>
                                                            <strong class="text-dark small"><?= escape($f['occupation'] ?: 'Not Specified') ?></strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <small class="text-muted d-block">Educational Attainment</small>
                                                            <strong class="text-dark small"><?= escape($f['educational_attainment'] ?: 'Not Specified') ?></strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <small class="text-muted d-block">Pregnancy Status</small>
                                                            <strong class="text-dark small"><?= escape($f['pregnancy_status'] ?: 'N/A') ?></strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <small class="text-muted d-block">Family Planning Method</small>
                                                            <strong class="text-dark small"><?= escape($f['family_planning_status'] ?: 'None') ?></strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <small class="text-muted d-block">Child Feeding Type</small>
                                                            <strong class="text-dark small"><?= escape($f['child_feeding_type'] ?: 'N/A') ?></strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <small class="text-muted d-block">Toilet Facility Type</small>
                                                            <strong class="text-dark small"><?= escape($f['toilet_type'] ?: 'Not Specified') ?></strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <small class="text-muted d-block">Water Source</small>
                                                            <strong class="text-dark small"><?= escape($f['water_source'] ?: 'Not Specified') ?></strong>
                                                        </div>
                                                        <div class="col-12 col-sm-6">
                                                            <small class="text-muted d-block">Food Production Activity</small>
                                                            <strong class="text-dark small"><?= escape($f['food_production_activity'] ?: 'None') ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <h6 class="fw-bold mb-3 text-success d-flex align-items-center"><i class="bi bi-people-fill me-2"></i>Household Members List</h6>
                                            <div class="table-responsive">
                                                <table class="table table-hover table-striped align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Resident ID</th>
                                                            <th>Member Name</th>
                                                            <th>Gender</th>
                                                            <th>Age</th>
                                                            <th>Relationship to Head</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($members as $m): ?>
                                                            <tr>
                                                                <td class="fw-semibold text-primary"><?= escape($m['resident_code'] ?? $m['resident_id']) ?></td>
                                                                <td class="fw-medium"><?= escape($m['last_name'] . ', ' . $m['first_name'] . ' ' . $m['middle_name']) ?></td>
                                                                <td><?= gender_badge($m['gender']) ?></td>
                                                                <td><?= escape($m['age']) ?> yrs</td>
                                                                <td>
                                                                    <span class="badge bg-<?= $m['relationship_to_head'] === 'Head' ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $m['relationship_to_head'] === 'Head' ? 'success' : 'secondary' ?> border">
                                                                        <?= escape($m['relationship_to_head']) ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group gap-1">
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-2" onclick="editFamilyProfile(<?= $f['id'] ?>)" title="Edit Family Profile">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button onclick="confirmDelete(<?= $f['id'] ?>, '<?= escape($f['family_no']) ?>')" class="btn btn-outline-danger btn-sm rounded-2" title="Delete Profile">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Create Family Profile Modal -->
<div class="modal fade" id="createFamilyModal" tabindex="-1" aria-labelledby="createFamilyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= url('index.php?route=family/store') ?>" method="POST" class="modal-content border-0 shadow needs-validation" novalidate id="modal-family-form">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="createFamilyModalLabel">
                    <i class="bi bi-house-heart-fill me-2"></i>Create New Family Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?= csrf_field() ?>

                <!-- Section 1: Household Head & Info -->
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-house-fill me-2"></i>Household Head Information</h6>
                <div class="row g-3 mb-4 pb-3 border-bottom">
                    <!-- Family Head Select -->
                    <div class="col-12 col-md-6">
                        <label for="modal_head_resident_id" class="form-label fw-semibold small">Designated Family Head <span class="text-danger">*</span></label>
                        <select class="form-select searchable-select" id="modal_head_resident_id" name="head_resident_id" required>
                            <option value="" selected disabled>Select Resident Head</option>
                            <?php if (!empty($residents)): ?>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>" data-address="<?= escape($res['address']) ?>" data-gender="<?= escape($res['gender']) ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Only active residents registered in the system are listed.</small>
                        <div class="invalid-feedback">Please select a family head.</div>
                    </div>

                    <!-- Family Address -->
                    <div class="col-12 col-md-6">
                        <label for="modal_family_address" class="form-label fw-semibold small">Family Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_family_address" name="address" rows="2" required placeholder="Will auto-fill from selected family head..."></textarea>
                        <div class="invalid-feedback">Family address is required.</div>
                    </div>
                </div>

                <!-- Section 1.5: Socio-Economic & Health Indicators -->
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-heart-pulse-fill me-2"></i>Public Health & Socio-Economic Indicators</h6>
                <div class="row g-3 mb-4 pb-3 border-bottom">
                    <!-- Occupation -->
                    <div class="col-12 col-md-4">
                        <label for="modal_occupation" class="form-label fw-semibold small">Occupation (Head)</label>
                        <input type="text" class="form-control form-control-sm" id="modal_occupation" name="occupation" placeholder="e.g. Farmer, Housewife">
                    </div>
                    <!-- Educational Attainment -->
                    <div class="col-12 col-md-4">
                        <label for="modal_educational_attainment" class="form-label fw-semibold small">Educational Attainment</label>
                        <input type="text" class="form-control form-control-sm" id="modal_educational_attainment" name="educational_attainment" placeholder="e.g. High School Graduate">
                    </div>
                    <!-- Pregnancy Status -->
                    <div class="col-12 col-md-4" id="modal_pregnancy_status_container">
                        <label for="modal_pregnancy_status" class="form-label fw-semibold small">Pregnancy Status</label>
                        <select class="form-select form-select-sm" id="modal_pregnancy_status" name="pregnancy_status">
                            <option value="N/A" selected>N/A (Not Applicable)</option>
                            <option value="Pregnant">Pregnant</option>
                            <option value="Not Pregnant">Not Pregnant</option>
                        </select>
                    </div>
                    <!-- Family Planning Status -->
                    <div class="col-12 col-md-4">
                        <label for="modal_family_planning_status" class="form-label fw-semibold small">Family Planning Method</label>
                        <input type="text" class="form-control form-control-sm" id="modal_family_planning_status" name="family_planning_status" placeholder="e.g. Pill, Condom, None">
                    </div>
                    <!-- Child Feeding Type -->
                    <div class="col-12 col-md-4" id="modal_child_feeding_type_container">
                        <label for="modal_child_feeding_type" class="form-label fw-semibold small">Child Feeding Type</label>
                        <input type="text" class="form-control form-control-sm" id="modal_child_feeding_type" name="child_feeding_type" placeholder="e.g. Breastfeeding, N/A">
                    </div>
                    <!-- Toilet Type -->
                    <div class="col-12 col-md-4">
                        <label for="modal_toilet_type" class="form-label fw-semibold small">Toilet Facility Type</label>
                        <input type="text" class="form-control form-control-sm" id="modal_toilet_type" name="toilet_type" placeholder="e.g. Water-sealed, Flush">
                    </div>
                    <!-- Water Source -->
                    <div class="col-12 col-md-6">
                        <label for="modal_water_source" class="form-label fw-semibold small">Water Source</label>
                        <input type="text" class="form-control form-control-sm" id="modal_water_source" name="water_source" placeholder="e.g. Piped water, Shared well">
                    </div>
                    <!-- Food Production Activity -->
                    <div class="col-12 col-md-6">
                        <label for="modal_food_production_activity" class="form-label fw-semibold small">Food Production Activity</label>
                        <input type="text" class="form-control form-control-sm" id="modal_food_production_activity" name="food_production_activity" placeholder="e.g. Backyard Gardening, Poultry">
                    </div>
                </div>

                <!-- Section 2: Household Members -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Household Members</h6>
                    <button type="button" id="modal-add-member-btn" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Member Row
                    </button>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-hover align-middle border rounded-3 overflow-hidden mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 55%;">Resident Member</th>
                                <th style="width: 35%;">Relationship to Head</th>
                                <th style="width: 10%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="modal-members-tbody">
                            <!-- Dynamic member rows populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Create Profile</button>
            </div>
        </form>
    </div>
</div>

<!-- Template Row for Modal Member Add (Hidden) -->
<table class="d-none">
    <tbody id="modal-member-row-template">
        <tr>
            <td>
                <select class="form-select modal-member-select" name="member_resident_id[]" required disabled>
                    <option value="" selected disabled>Select Household Member</option>
                    <?php if (!empty($residents)): ?>
                        <?php foreach ($residents as $res): ?>
                            <option value="<?= $res['id'] ?>">
                                <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="invalid-feedback">Please select a resident member.</div>
            </td>
            <td>
                <input type="text" class="form-control" name="member_relationship[]" placeholder="e.g. Spouse, Child, Parent" required disabled>
                <div class="invalid-feedback">Relationship status is required.</div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm modal-remove-row-btn" title="Remove Member">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </tbody>
</table>

<!-- Edit Family Profile Modal -->
<div class="modal fade" id="editFamilyModal" tabindex="-1" aria-labelledby="editFamilyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= url('index.php?route=family/update') ?>" method="POST" class="modal-content border-0 shadow needs-validation" novalidate id="edit-family-form">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="editFamilyModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Family Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_family_id" name="id">

                <!-- Section 1: Household Head & Info -->
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-house-fill me-2"></i>Household Head Information</h6>
                <div class="row g-3 mb-4 pb-3 border-bottom">
                    <!-- Family Head Select -->
                    <div class="col-12 col-md-6">
                        <label for="edit_modal_head_resident_id" class="form-label fw-semibold small">Designated Family Head <span class="text-danger">*</span></label>
                        <select class="form-select searchable-select" id="edit_modal_head_resident_id" name="head_resident_id" required>
                            <option value="" disabled>Select Resident Head</option>
                            <?php if (!empty($residents)): ?>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>" data-address="<?= escape($res['address']) ?>" data-gender="<?= escape($res['gender']) ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Only active residents registered in the system are listed.</small>
                        <div class="invalid-feedback">Please select a family head.</div>
                    </div>

                    <!-- Family Address -->
                    <div class="col-12 col-md-6">
                        <label for="edit_modal_family_address" class="form-label fw-semibold small">Family Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_modal_family_address" name="address" rows="2" required placeholder="Enter family address..."></textarea>
                        <div class="invalid-feedback">Family address is required.</div>
                    </div>
                </div>

                <!-- Section 1.5: Socio-Economic & Health Indicators -->
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-heart-pulse-fill me-2"></i>Public Health & Socio-Economic Indicators</h6>
                <div class="row g-3 mb-4 pb-3 border-bottom">
                    <!-- Occupation -->
                    <div class="col-12 col-md-4">
                        <label for="edit_modal_occupation" class="form-label fw-semibold small">Occupation (Head)</label>
                        <input type="text" class="form-control form-control-sm" id="edit_modal_occupation" name="occupation" placeholder="e.g. Farmer, Housewife">
                    </div>
                    <!-- Educational Attainment -->
                    <div class="col-12 col-md-4">
                        <label for="edit_modal_educational_attainment" class="form-label fw-semibold small">Educational Attainment</label>
                        <input type="text" class="form-control form-control-sm" id="edit_modal_educational_attainment" name="educational_attainment" placeholder="e.g. High School Graduate">
                    </div>
                    <!-- Pregnancy Status -->
                    <div class="col-12 col-md-4" id="edit_modal_pregnancy_status_container">
                        <label for="edit_modal_pregnancy_status" class="form-label fw-semibold small">Pregnancy Status</label>
                        <select class="form-select form-select-sm" id="edit_modal_pregnancy_status" name="pregnancy_status">
                            <option value="N/A">N/A (Not Applicable)</option>
                            <option value="Pregnant">Pregnant</option>
                            <option value="Not Pregnant">Not Pregnant</option>
                        </select>
                    </div>
                    <!-- Family Planning Status -->
                    <div class="col-12 col-md-4">
                        <label for="edit_modal_family_planning_status" class="form-label fw-semibold small">Family Planning Method</label>
                        <input type="text" class="form-control form-control-sm" id="edit_modal_family_planning_status" name="family_planning_status" placeholder="e.g. Pill, Condom, None">
                    </div>
                    <!-- Child Feeding Type -->
                    <div class="col-12 col-md-4" id="edit_modal_child_feeding_type_container">
                        <label for="edit_modal_child_feeding_type" class="form-label fw-semibold small">Child Feeding Type</label>
                        <input type="text" class="form-control form-control-sm" id="edit_modal_child_feeding_type" name="child_feeding_type" placeholder="e.g. Breastfeeding, N/A">
                    </div>
                    <!-- Toilet Type -->
                    <div class="col-12 col-md-4">
                        <label for="edit_modal_toilet_type" class="form-label fw-semibold small">Toilet Facility Type</label>
                        <input type="text" class="form-control form-control-sm" id="edit_modal_toilet_type" name="toilet_type" placeholder="e.g. Water-sealed, Flush">
                    </div>
                    <!-- Water Source -->
                    <div class="col-12 col-md-6">
                        <label for="edit_modal_water_source" class="form-label fw-semibold small">Water Source</label>
                        <input type="text" class="form-control form-control-sm" id="edit_modal_water_source" name="water_source" placeholder="e.g. Piped water, Shared well">
                    </div>
                    <!-- Food Production Activity -->
                    <div class="col-12 col-md-6">
                        <label for="edit_modal_food_production_activity" class="form-label fw-semibold small">Food Production Activity</label>
                        <input type="text" class="form-control form-control-sm" id="edit_modal_food_production_activity" name="food_production_activity" placeholder="e.g. Backyard Gardening, Poultry">
                    </div>
                </div>

                <!-- Section 2: Household Members -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Household Members</h6>
                    <button type="button" id="edit-modal-add-member-btn" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Member Row
                    </button>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-hover align-middle border rounded-3 overflow-hidden mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 55%;">Resident Member</th>
                                <th style="width: 35%;">Relationship to Head</th>
                                <th style="width: 10%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="edit-modal-members-tbody">
                            <!-- Dynamic member rows populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<!-- Template Row for Edit Modal Member Add (Hidden) -->
<table class="d-none">
    <tbody id="edit-modal-member-row-template">
        <tr>
            <td>
                <select class="form-select edit-modal-member-select" name="member_resident_id[]" required disabled>
                    <option value="" selected disabled>Select Household Member</option>
                    <?php if (!empty($residents)): ?>
                        <?php foreach ($residents as $res): ?>
                            <option value="<?= $res['id'] ?>">
                                <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="invalid-feedback">Please select a resident member.</div>
            </td>
            <td>
                <input type="text" class="form-control" name="member_relationship[]" placeholder="e.g. Spouse, Child, Parent" required disabled>
                <div class="invalid-feedback">Relationship status is required.</div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm edit-modal-remove-row-btn" title="Remove Member">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </tbody>
</table>

<!-- Bootstrap Modal for Family Members -->
<div class="modal fade" id="membersModal" tabindex="-1" aria-labelledby="membersModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-success bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="membersModalTitle">Family Members</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="membersModalBody">
                <!-- Javascript will load the table here -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-modal="hide" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once LAYOUT_PATH . 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#families-table').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: 4 }
        ]
    });

    // Auto-fill address on head selection in Create Modal
    $('#modal_head_resident_id').on('change', function() {
        const address = $(this).find(':selected').data('address');
        if (address) {
            $('#modal_family_address').val(address);
        }
        toggleGenderIndicators($(this), 'modal_');
    });

    // Auto-fill address on head selection in Edit Modal
    $('#edit_modal_head_resident_id').on('change', function() {
        const address = $(this).find(':selected').data('address');
        if (address) {
            $('#edit_modal_family_address').val(address);
        }
        toggleGenderIndicators($(this), 'edit_modal_');
    });

    // Add Member Row Click in Create Modal
    $('#modal-add-member-btn').on('click', function() {
        const clone = $('#modal-member-row-template tr').clone();
        clone.find('select, input').removeAttr('disabled');
        $('#modal-members-tbody').append(clone);
    });

    // Remove Member Row Click in Create Modal
    $(document).on('click', '.modal-remove-row-btn', function() {
        $(this).closest('tr').remove();
    });

    // Add Member Row Click in Edit Modal
    $('#edit-modal-add-member-btn').on('click', function() {
        const clone = $('#edit-modal-member-row-template tr').clone();
        clone.find('select, input').removeAttr('disabled');
        $('#edit-modal-members-tbody').append(clone);
    });

    // Remove Member Row Click in Edit Modal
    $(document).on('click', '.edit-modal-remove-row-btn', function() {
        $(this).closest('tr').remove();
    });

    // Start with 1 empty member row when create modal opens
    $('#createFamilyModal').on('show.bs.modal', function () {
        if ($('#modal-members-tbody tr').length === 0) {
            $('#modal-add-member-btn').trigger('click');
        }
        toggleGenderIndicators($('#modal_head_resident_id'), 'modal_');
    });
});

function toggleGenderIndicators(selectEl, prefix) {
    const gender = selectEl.find(':selected').data('gender');
    const pregnancyContainer = $(`#${prefix}pregnancy_status_container`);
    const feedingContainer = $(`#${prefix}child_feeding_type_container`);
    
    if (gender === 'Male') {
        pregnancyContainer.hide();
        feedingContainer.hide();
        // Reset inputs on hide
        $(`#${prefix}pregnancy_status`).val('N/A');
        $(`#${prefix}child_feeding_type`).val('');
    } else {
        pregnancyContainer.show();
        feedingContainer.show();
    }
}

function viewMembers(familyId, familyNo, headName) {
    const content = $('#members-data-' + familyId).html();
    $('#membersModalTitle').html(`<i class="bi bi-house-fill me-2"></i>Household: ${familyNo} (Head: ${headName})`);
    $('#membersModalBody').html(content);
    
    // Show Modal
    const membersModal = new bootstrap.Modal(document.getElementById('membersModal'));
    membersModal.show();
}

function editFamilyProfile(id) {
    App.showLoader();
    $.getJSON(`index.php?route=family/detail_json&id=${id}`)
        .done(function(res) {
            App.hideLoader();
            if (res.success) {
                const fam = res.data.family;
                const members = res.data.members || [];

                $('#edit_family_id').val(fam.id);
                $('#edit_modal_head_resident_id').val(fam.head_resident_id);
                $('#edit_modal_family_address').val(fam.address);
                $('#edit_modal_occupation').val(fam.occupation || '');
                $('#edit_modal_educational_attainment').val(fam.educational_attainment || '');
                $('#edit_modal_pregnancy_status').val(fam.pregnancy_status || 'N/A');
                $('#edit_modal_family_planning_status').val(fam.family_planning_status || '');
                $('#edit_modal_child_feeding_type').val(fam.child_feeding_type || '');
                $('#edit_modal_toilet_type').val(fam.toilet_type || '');
                $('#edit_modal_water_source').val(fam.water_source || '');
                $('#edit_modal_food_production_activity').val(fam.food_production_activity || '');

                // Clear members tbody
                $('#edit-modal-members-tbody').empty();

                // Populate members except head
                members.forEach(m => {
                    if (parseInt(m.resident_id) !== parseInt(fam.head_resident_id)) {
                        const clone = $('#edit-modal-member-row-template tr').clone();
                        clone.find('select, input').removeAttr('disabled');
                        clone.find('select.edit-modal-member-select').val(m.resident_id);
                        clone.find('input[name="member_relationship[]"]').val(m.relationship_to_head);
                        $('#edit-modal-members-tbody').append(clone);
                    }
                });

                // Apply gender specific indicators show/hide
                toggleGenderIndicators($('#edit_modal_head_resident_id'), 'edit_modal_');

                const modal = new bootstrap.Modal(document.getElementById('editFamilyModal'));
                modal.show();
            } else {
                Swal.fire('Error', res.message || 'Unable to fetch family profile.', 'error');
            }
        })
        .fail(function() {
            App.hideLoader();
            Swal.fire('Error', 'Communication error happened.', 'error');
        });
}

function confirmDelete(id, code) {
    Swal.fire({
        title: 'Delete Family Profile?',
        text: `Deleting family profile ${code} will disassociate all linked household members. Continue?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?route=family/delete&id=${id}`;
        }
    });
}

// Bootstrap Form Validations for Modals
(function () {
  'use strict'
  var forms = document.querySelectorAll('#modal-family-form, #edit-family-form')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        const isEdit = form.id === 'edit-family-form';
        const headVal = isEdit ? $('#edit_modal_head_resident_id').val() : $('#modal_head_resident_id').val();
        let duplicates = false;
        
        const memberSelector = isEdit ? '.edit-modal-member-select' : '.modal-member-select';
        $(memberSelector).each(function() {
            const val = $(this).val();
            if (val) {
                if (val === headVal) {
                    duplicates = true;
                    $(this).addClass('is-invalid');
                    alert("The household head cannot be added as a member in the grid.");
                } else {
                    $(this).removeClass('is-invalid');
                }
            }
        });

        if (!form.checkValidity() || duplicates) {
          event.preventDefault()
          event.stopPropagation()
        } else {
            App.showLoader();
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
