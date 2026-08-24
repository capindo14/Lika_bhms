<?php
require_once LAYOUT_PATH . 'header.php';
require_once LAYOUT_PATH . 'sidebar.php';
?>

<div class="main-content-wrapper">
    <?php require_once LAYOUT_PATH . 'navbar.php'; ?>
    
    <div class="container-fluid p-3 p-md-4">
        
        <!-- Header -->
        <div class="mb-4">
            <h3 class="fw-bold mb-1"><?= escape($pageTitle) ?></h3>
            <p class="text-muted small mb-0">Manage resident profiles, family heads, and consultation histories.</p>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs border-bottom mb-4" id="statusFilterTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-secondary" onclick="setStatusFilter('Active')" id="tabActive" type="button" role="tab">
                    <i class="bi bi-person-badge-fill me-1"></i> Active Profiles
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-secondary" onclick="setStatusFilter('Pregnant')" id="tabPregnant" type="button" role="tab">
                    <i class="bi bi-person-hearts me-1"></i> Pregnant
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-secondary" onclick="setStatusFilter('Infant')" id="tabInfant" type="button" role="tab">
                    <i class="bi bi-baby me-1"></i> Infant Feeding
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-secondary" onclick="setStatusFilter('Archived')" id="tabArchived" type="button" role="tab">
                    <i class="bi bi-archive me-1"></i> Archived
                </button>
            </li>
        </ul>

        <!-- Real-time Search Box and Action Buttons -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-12 col-md-5 col-lg-4">
                <div class="input-group shadow-sm border rounded bg-white">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="residentSearch" class="form-control border-0" placeholder="Search residents...">
                    <button class="btn btn-light border-start dropdown-toggle text-secondary fw-semibold px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                        <i class="bi bi-funnel"></i> Filters
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow border-0 rounded-3 mt-1" style="width: 280px;">
                        <h6 class="dropdown-header px-0 text-dark fw-bold mb-2">Filter Directory</h6>
                        <div class="mb-3" id="genderFilterGroup">
                            <label class="form-label small text-muted fw-semibold" for="genderFilter">Gender</label>
                            <select id="genderFilter" class="form-select form-select-sm shadow-none">
                                <option value="">All Genders</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3" id="ageFilterGroup">
                            <label class="form-label small text-muted fw-semibold" for="ageFilter">Age Group</label>
                            <select id="ageFilter" class="form-select form-select-sm shadow-none">
                                <option value="">All Age Groups</option>
                                <option value="infants" id="infantAgeOption">Infants (Under 1)</option>
                                <option value="children" id="childrenAgeOption">Children (1-12)</option>
                                <option value="teens">Teens (13-19)</option>
                                <option value="adults">Adults (20-59)</option>
                                <option value="seniors">Seniors (60+)</option>
                            </select>
                        </div>
                        <div class="mb-3" id="civilStatusFilterGroup">
                            <label class="form-label small text-muted fw-semibold" for="civilStatusFilter">Civil Status</label>
                            <select id="civilStatusFilter" class="form-select form-select-sm shadow-none">
                                <option value="">All Civil Statuses</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                        <div class="mb-1" id="householdRoleFilterGroup">
                            <label class="form-label small text-muted fw-semibold" for="householdRoleFilter">Household Role</label>
                            <select id="householdRoleFilter" class="form-select form-select-sm shadow-none">
                                <option value="">All Roles</option>
                                <option value="Family Head">Family Head</option>
                                <option value="Member">Member</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3 col-lg-3"></div>
            <div class="col-12 col-md-4 col-lg-5 d-flex justify-content-md-end gap-2">
                <button type="button" id="btnRegisterResident" class="btn btn-primary" onclick="newResident()">
                    <i class="bi bi-plus-lg me-1"></i> Register Resident
                </button>
            </div>
        </div>

        <!-- Directory Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Resident ID</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Birthdate (Age)</th>
                                <th>Civil Status</th>
                                <th>Household Role</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="residentTable">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="text-muted mt-2 small">Loading resident list...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add/Edit Resident Modal -->
<div class="modal fade" id="residentModal" tabindex="-1" aria-labelledby="residentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="residentModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i><span id="modalTitleText">Register Resident</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="residentForm" class="needs-validation" novalidate>
                <input type="hidden" id="residentId" name="id">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- First Name -->
                        <div class="col-12 col-md-4 standard-details-field">
                            <label for="resFirstName" class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resFirstName" name="first_name" required placeholder="Enter first name">
                            <div class="invalid-feedback">First name is required.</div>
                        </div>

                        <!-- Middle Name -->
                        <div class="col-12 col-md-4 standard-details-field">
                            <label for="resMiddleName" class="form-label fw-semibold small">Middle Name</label>
                            <input type="text" class="form-control" id="resMiddleName" name="middle_name" placeholder="Enter middle name">
                        </div>

                        <!-- Last Name -->
                        <div class="col-12 col-md-4 standard-details-field">
                            <label for="resLastName" class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="resLastName" name="last_name" required placeholder="Enter last name">
                            <div class="invalid-feedback">Last name is required.</div>
                        </div>

                        <!-- Gender -->
                        <div class="col-12 col-md-4 standard-details-field">
                            <label for="resGender" class="form-label fw-semibold small">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="resGender" name="gender" required>
                                <option value="" selected disabled>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a gender.</div>
                        </div>

                        <!-- Birthdate -->
                        <div class="col-12 col-md-4 standard-details-field">
                            <label for="resBirthdate" class="form-label fw-semibold small">Birthdate <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                <input type="text" class="form-control birthdate-picker" id="resBirthdate" name="birthdate" required placeholder="YYYY-MM-DD">
                            </div>
                            <div class="invalid-feedback">Please enter a valid birthdate.</div>
                        </div>

                        <!-- Civil Status -->
                        <div class="col-12 col-md-4 standard-details-field">
                            <label for="resCivilStatus" class="form-label fw-semibold small">Civil Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="resCivilStatus" name="civil_status" required>
                                <option value="" selected disabled>Select Civil Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                            <div class="invalid-feedback">Please select civil status.</div>
                        </div>

                        <!-- Contact Number -->
                        <div class="col-12 col-md-6 standard-details-field">
                            <label for="resContactNumber" class="form-label fw-semibold small">Contact Number</label>
                            <input type="text" class="form-control" id="resContactNumber" name="contact_number" placeholder="e.g. 09171234567" pattern="^(09|\+639)\d{9}$">
                            <small class="text-muted d-block mt-1">11 digit starting with 09.</small>
                            <div class="invalid-feedback">Enter a valid 11-digit mobile number.</div>
                        </div>

                        <!-- Complete Address -->
                        <div class="col-12 standard-details-field">
                            <label for="resAddress" class="form-label fw-semibold small">Complete Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="resAddress" name="address" rows="2" required placeholder="Street name, zone/sitio..."></textarea>
                            <div class="invalid-feedback">Complete address is required.</div>
                        </div>

                        <!-- Pregnancy Status (Only applicable to Female) -->
                        <div class="col-12 col-md-6" id="resPregnancyContainer" style="display: none;">
                            <label for="resPregnancy" class="form-label fw-semibold small">Pregnancy Status <span class="badge bg-danger-subtle text-danger ms-1">Female</span></label>
                            <select class="form-select" id="resPregnancy" name="pregnancy_status">
                                <option value="Not Pregnant" selected>Not Pregnant</option>
                                <option value="Pregnant">Pregnant</option>
                                <option value="N/A" style="display: none;">N/A</option>
                            </select>
                        </div>

                        <!-- Child Feeding Type (Only applicable to Children < 5 yrs) -->
                        <div class="col-12 col-md-6" id="resFeedingContainer" style="display: none;">
                            <label for="resFeeding" class="form-label fw-semibold small">Child Feeding Type <span class="badge bg-info-subtle text-info ms-1">Child (< 5 yrs)</span></label>
                            <select class="form-select" id="resFeeding" name="child_feeding_type">
                                <option value="" selected disabled>Select feeding type...</option>
                                <option value="Exclusive Breastfeeding">Exclusive Breastfeeding</option>
                                <option value="Mixed Feeding">Mixed Feeding (Breastmilk + Formula)</option>
                                <option value="Formula Feeding">Formula Feeding</option>
                                <option value="Complementary Feeding">Complementary Feeding</option>
                                <option value="N/A">N/A</option>
                            </select>
                        </div>

                        <!-- Family Head Designation -->
                        <div class="col-12 standard-details-field">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light bg-opacity-50">
                                <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="resIsFamilyHead" name="is_family_head" value="1">
                                <label class="form-check-label fw-semibold small" for="resIsFamilyHead">Designate as Family Head</label>
                                <span class="d-block text-muted small mt-1">If enabled, this resident will be available as family head in profiles.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveButton">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Resident Profile Details Modal -->
<div class="modal fade" id="viewResidentModal" tabindex="-1" aria-labelledby="viewResidentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="viewResidentModalLabel">
                    <i class="bi bi-person-badge-fill me-2"></i>Resident Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Profile details -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <span class="text-muted small d-block">Full Name</span>
                        <strong id="viewResFullName" class="fs-5 text-dark"></strong>
                        <div class="mt-1" id="viewResBadges"></div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <span class="text-muted small d-block">Resident Code</span>
                        <strong id="viewResCode" class="text-primary font-monospace"></strong>
                    </div>
                    <div class="col-12"><hr class="my-1"></div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted small d-block">Age / Birthdate</span>
                        <span id="viewResAgeBirthdate" class="fw-semibold text-dark"></span>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted small d-block">Civil Status</span>
                        <span id="viewResCivilStatus" class="fw-semibold text-dark"></span>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted small d-block">Contact Number</span>
                        <span id="viewResContact" class="fw-semibold text-dark"></span>
                    </div>
                    <div class="col-6 col-md-6" id="viewResPregnancyContainer">
                        <span class="text-muted small d-block">Pregnancy Status</span>
                        <span id="viewResPregnancy" class="fw-semibold text-dark">N/A</span>
                    </div>
                    <div class="col-6 col-md-6" id="viewResFeedingContainer">
                        <span class="text-muted small d-block">Child Feeding Type</span>
                        <span id="viewResFeeding" class="fw-semibold text-dark">N/A</span>
                    </div>
                    <div class="col-12">
                        <span class="text-muted small d-block">Complete Address</span>
                        <span id="viewResAddress" class="fw-semibold text-dark"></span>
                    </div>
                </div>

                <!-- History Tabs -->
                <ul class="nav nav-tabs border-bottom mb-3" id="historyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-secondary" id="con-tab" data-bs-toggle="tab" data-bs-target="#con-pane" type="button" role="tab">
                            <i class="bi bi-clipboard2-pulse me-1"></i> Consultations (<span id="viewResConCount">0</span>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-secondary" id="imm-tab" data-bs-toggle="tab" data-bs-target="#imm-pane" type="button" role="tab">
                            <i class="bi bi-shield-plus me-1"></i> Immunizations (<span id="viewResImmCount">0</span>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="historyTabsContent">
                    <div class="tab-pane fade show active" id="con-pane" role="tabpanel">
                        <div id="viewResConList" style="max-height: 200px; overflow-y: auto;">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                    <div class="tab-pane fade" id="imm-pane" role="tabpanel">
                        <div id="viewResImmList" style="max-height: 200px; overflow-y: auto;">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php 
$pageScript = url('js/residents_js.js');
require_once LAYOUT_PATH . 'footer.php'; 
?>
