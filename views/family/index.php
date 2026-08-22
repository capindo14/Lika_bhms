<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';

// Load active residents directly in the view for the dropdown selectors
$residents = \App\Models\Resident::getAll('Active');
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small mb-0">Monitor household classifications, public health indicators, and family dependencies.</p>
        </div>

        <!-- Real-time Search Box and Action Buttons -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="input-group shadow-sm border rounded">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="familySearch" class="form-control border-0" placeholder="Search by head or family number...">
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-8 d-flex justify-content-md-end gap-2">
                <button type="button" class="btn btn-primary" onclick="newFamily()">
                    <i class="bi bi-plus-lg me-1"></i> Create Family Profile
                </button>
            </div>
        </div>

        <!-- Dynamic Families Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Family No.</th>
                                <th>Household Head</th>
                                <th>Family Address</th>
                                <th class="text-center">Members</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="familyTable">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="text-muted mt-2 small">Loading household profiles...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Create/Edit Family Modal -->
<div class="modal fade" id="familyModal" tabindex="-1" aria-labelledby="familyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="familyModalLabel">
                    <i class="bi bi-house-heart-fill me-2"></i><span id="modalTitleText">Create Family Profile</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="familyForm" class="needs-validation" novalidate>
                <input type="hidden" id="familyId" name="id">
                
                <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto;">
                    <!-- Section 1: Head Details -->
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-house-fill me-2"></i>Household Head Information</h6>
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-12 col-md-6">
                            <label for="modalHeadId" class="form-label fw-semibold small">Designated Family Head <span class="text-danger">*</span></label>
                            <select class="form-select searchable-select" id="modalHeadId" name="head_resident_id" required>
                                <option value="" selected disabled>Select Resident Head</option>
                                <?php foreach ($residents as $res): ?>
                                    <option value="<?= $res['id'] ?>" data-address="<?= escape($res['address']) ?>" data-gender="<?= escape($res['gender']) ?>">
                                        <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>Cannot find the family head? 
                                <a href="<?= url('index.php?route=residents') ?>" class="text-primary text-decoration-none fw-medium">Register Resident first</a>
                            </small>
                            <div class="invalid-feedback">Please select a family head.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modalAddress" class="form-label fw-semibold small">Family Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="modalAddress" name="address" rows="2" required placeholder="Will auto-fill from selected head..."></textarea>
                            <div class="invalid-feedback">Family address is required.</div>
                        </div>
                    </div>

                    <!-- Section 2: Indicators -->
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-heart-pulse-fill me-2"></i>Public Health & Socio-Economic Indicators</h6>
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label for="modalOccupation" class="form-label fw-semibold small">Occupation (Head)</label>
                            <input type="text" class="form-control" id="modalOccupation" name="occupation" placeholder="e.g. Farmer, Office Worker">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label for="modalEducation" class="form-label fw-semibold small">Educational Attainment</label>
                            <input type="text" class="form-control" id="modalEducation" name="educational_attainment" placeholder="e.g. College Graduate">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label for="modalPlanning" class="form-label fw-semibold small">Contraceptive Method</label>
                            <input type="text" class="form-control" id="modalPlanning" name="family_planning_status" placeholder="e.g. Condom, Pills, None">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label for="modalToilet" class="form-label fw-semibold small">Toilet Facility Type</label>
                            <input type="text" class="form-control" id="modalToilet" name="toilet_type" placeholder="e.g. Flush, Pit Latrine">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label for="modalWater" class="form-label fw-semibold small">Water Source</label>
                            <input type="text" class="form-control" id="modalWater" name="water_source" placeholder="e.g. Shared tap, Deep well">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label for="modalFood" class="form-label fw-semibold small">Food Production Activity</label>
                            <input type="text" class="form-control" id="modalFood" name="food_production_activity" placeholder="e.g. Gardening, Poultry">
                        </div>
                    </div>

                    <!-- Section 3: Members -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Household Members</h6>
                        <button type="button" onclick="addMemberRow()" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add Member Row
                        </button>
                    </div>

                    <div class="table-responsive" style="overflow-x: auto; min-height: 120px;">
                        <table class="table table-hover align-middle border rounded-3" style="min-width: 500px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50%;">Resident Member</th>
                                    <th style="width: 40%;">Relationship to Head</th>
                                    <th style="width: 10%;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="modalMembersTable">
                                <!-- Populated dynamically by javascript -->
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveButton">Create Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden dropdown template for JavaScript Row generation -->
<div class="d-none">
    <select id="memberDropdownTemplate">
        <option value="" selected disabled>Select Household Member</option>
        <?php foreach ($residents as $res): ?>
            <option value="<?= $res['id'] ?>">
                <?= escape($res['last_name'] . ', ' . $res['first_name'] . ' (' . $res['resident_id'] . ')') ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

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
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php 
$pageScript = url('js/family_js.js');
require_once LAYOUT_PATH . 'footer.php'; 
?>
