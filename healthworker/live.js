document.addEventListener('DOMContentLoaded', function () {
    // Appointment live search
    const searchInputAppointment = document.querySelector('input[name="search"]');
    const appointmentTableBody = document.getElementById('appointmentTableBody');

    if (searchInputAppointment && appointmentTableBody) {
        searchInputAppointment.addEventListener('input', function () {
            const query = this.value;

            fetch(`search.php?type=appointment&search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    appointmentTableBody.innerHTML = '';

                    if (data.length === 0) {
                        appointmentTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No appointments found.</td></tr>';
                        return;
                    }

                    data.forEach(a => {
                        const row = document.createElement('tr');

                        row.innerHTML = `
                            <td>${a.appointment_id}</td>
                            <td>${a.patient_name}</td>
                            <td>${a.date}</td>
                            <td>${a.time}</td>
                            <td>${a.doctor}</td>
                            <td>${a.reason}</td>
                            <td>
                                <button class="btn btn-custom-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editModal${a.appointment_id}">Edit</button>
                                <form method="post" action="appoint_crud.php" class="d-inline" onsubmit="return confirm('Delete this appointment?');">
                                    <input type="hidden" name="delete_id" value="${a.appointment_id}">
                                    <button type="submit" name="delete" class="btn btn-custom-delete btn-sm">Delete</button>
                                </form>
                            </td>
                        `;

                        appointmentTableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error fetching appointment data:', error);
                });
        });
    }

    // Household profile live search with modals
    const searchInputHousehold = document.querySelector('input[name="search"]');
    const hhprofileTableBody = document.getElementById('hhprofileTableBody');

    if (searchInputHousehold && hhprofileTableBody) {
        searchInputHousehold.addEventListener('input', function () {
            const query = this.value;

            fetch(`search.php?type=household&search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    hhprofileTableBody.innerHTML = '';
                    document.querySelectorAll('.dynamic-household-modal').forEach(modal => modal.remove());

                    if (data.length === 0) {
                        hhprofileTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No household profiles found.</td></tr>';
                        return;
                    }

                    data.forEach(h => {
                        const row = document.createElement('tr');

                        row.innerHTML = `
                            <td>${h.household_id}</td>
                            <td>${h.head_name}</td>
                            <td>${h.purok}</td>
                            <td>${h.nic_number}</td>
                            <td>${h.created_at}</td>
                            <td>
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#descModal${h.household_id}">View</button>
                            </td>
                            <td>
                                <button class="btn btn-custom-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editHouseholdModal${h.household_id}">Edit</button>
                                <form method="post" action="hh_crud.php" class="d-inline" onsubmit="return confirm('Delete this household profile?');">
                                    <input type="hidden" name="delete_id" value="${h.household_id}">
                                    <button type="submit" name="delete" class="btn btn-custom-delete btn-sm">Delete</button>
                                </form>
                            </td>
                        `;

                        hhprofileTableBody.appendChild(row);

                        // Description Modal
                        const descModal = document.createElement('div');
                        descModal.classList.add('modal', 'fade', 'dynamic-household-modal');
                        descModal.id = `descModal${h.household_id}`;
                        descModal.tabIndex = -1;
                        descModal.setAttribute('aria-labelledby', 'descModalLabel');
                        descModal.setAttribute('aria-hidden', 'true');
                        descModal.innerHTML = `
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="descModalLabel">Household Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <h5>Head of Household: ${h.head_name}</h5>
                                        <p><strong>Purok:</strong> ${h.purok}</p>
                                        <p><strong>NIC Number:</strong> ${h.nic_number}</p>
                                        <p><strong>Number of Members:</strong> ${h.num_members}</p>
                                        <h6><strong>Medical Condition:</strong></h6>
                                        <p>${h.medical_condition || 'No conditions reported'}</p>
                                        <h6><strong>Allergies:</strong></h6>
                                        <p>${h.allergies || 'No allergies reported'}</p>
                                        <h6><strong>Emergency Contact</strong></h6>
                                        <p><strong>Name:</strong> ${h.emergency_contact_name || ''}</p>
                                        <p><strong>Contact Number:</strong> ${h.emergency_contact_number || ''}</p>
                                        <p><strong>Relationship:</strong> ${h.emergency_contact_relation || ''}</p>
                                        <p><strong>Consent Date:</strong> ${h.created_at}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        document.body.appendChild(descModal);

                        // Edit Modal
                        const editModal = document.createElement('div');
                        editModal.classList.add('modal', 'fade', 'dynamic-household-modal');
                        editModal.id = `editHouseholdModal${h.household_id}`;
                        editModal.tabIndex = -1;
                        editModal.setAttribute('aria-labelledby', `editHouseholdModalLabel${h.household_id}`);
                        editModal.setAttribute('aria-hidden', 'true');
                        editModal.innerHTML = `
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" action="hh_crud.php">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editHouseholdModalLabel${h.household_id}">Edit Household Description</h5>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="household_id" value="${h.household_id}">
                                            <div class="mb-3">
                                                <label>Head of Household</label>
                                                <input type="text" name="head_name" class="form-control" value="${h.head_name}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Address (Purok)</label>
                                                <input type="text" name="purok" class="form-control" value="${h.purok}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>NIC Number</label>
                                                <input type="text" name="nic_number" class="form-control" value="${h.nic_number}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Number of Household Members</label>
                                                <input type="number" name="num_members" class="form-control" value="${h.num_members}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Existing Medical Condition</label>
                                                <textarea name="medical_condition" class="form-control" rows="2" required>${h.medical_condition || ''}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label>Allergies</label>
                                                <textarea name="allergies" class="form-control" rows="2" required>${h.allergies || ''}</textarea>
                                            </div>
                                            <hr>
                                            <h5>Household Member Details</h5>
                                            <div id="editHouseholdMembersContainer${h.household_id}">
                                                <p>Member details loading not implemented in live search.</p>
                                            </div>
                                            <button type="button" class="btn btn-success mb-3" id="addEditMemberBtn${h.household_id}">
                                                <i class="bi bi-plus-circle"></i> Add Member
                                            </button>
                                            <div class="mb-3">
                                                <label>Emergency Contact Name</label>
                                                <input type="text" name="emergency_contact_name" class="form-control" value="${h.emergency_contact_name || ''}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Emergency Contact Number</label>
                                                <input type="text" name="emergency_contact_number" class="form-control" value="${h.emergency_contact_number || ''}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Emergency Contact Relationship</label>
                                                <input type="text" name="emergency_contact_relation" class="form-control" value="${h.emergency_contact_relation || ''}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="update" class="btn btn-custom-save">Save Changes</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        `;
                        document.body.appendChild(editModal);
                    });
                })
                .catch(error => {
                    console.error('Error fetching household data:', error);
                });
        });
    }

    // Add member for "Add Household" modal
    const addMemberBtn = document.getElementById('addMemberBtn');
    const householdMembersContainer = document.getElementById('householdMembersContainer');

    if (addMemberBtn && householdMembersContainer) {
        addMemberBtn.addEventListener('click', function () {
            const memberDiv = document.createElement('div');
            memberDiv.classList.add('household-member', 'mb-3', 'border', 'p-3', 'rounded');
            memberDiv.innerHTML = `
                <label>Member Name:</label>
                <input type="text" name="member_name[]" class="form-control mb-2" required>
                <label>Relation to Head:</label>
                <input type="text" name="relation[]" class="form-control mb-2" required>
                <label>Age:</label>
                <input type="number" name="age[]" class="form-control mb-2" required>
                <label>Sex:</label>
                <select name="sex[]" class="form-select mb-2" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <button type="button" class="btn btn-danger btn-sm remove-member-btn">Remove</button>
            `;
            householdMembersContainer.appendChild(memberDiv);

            memberDiv.querySelector('.remove-member-btn').addEventListener('click', function () {
                memberDiv.remove();
            });
        });
    }

    // Add member for "Edit Household" modals
    document.querySelectorAll('[id^="addEditMemberBtn"]').forEach(button => {
        button.addEventListener('click', function () {
            const householdId = this.id.replace('addEditMemberBtn', '');
            const container = document.getElementById(`editHouseholdMembersContainer${householdId}`);

            const memberDiv = document.createElement('div');
            memberDiv.classList.add('household-member', 'mb-3', 'border', 'p-3', 'rounded');
            memberDiv.innerHTML = `
                <label>Member Name:</label>
                <input type="text" name="member_name[]" class="form-control mb-2" required>
                <label>Relation to Head:</label>
                <input type="text" name="relation[]" class="form-control mb-2" required>
                <label>Age:</label>
                <input type="number" name="age[]" class="form-control mb-2" required>
                <label>Sex:</label>
                <select name="sex[]" class="form-select mb-2" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <button type="button" class="btn btn-danger btn-sm remove-member-btn">Remove</button>
            `;
            container.appendChild(memberDiv);

            memberDiv.querySelector('.remove-member-btn').addEventListener('click', () => {
                memberDiv.remove();
            });
        });
    });

    // Patient live search
    const searchInputPatient = document.getElementById('searchPatient');
    const patientTableBody = document.getElementById('patientTableBody');

    if (searchInputPatient && patientTableBody) {
        searchInputPatient.addEventListener('input', function () {
            const query = this.value;

            fetch(`search.php?type=patient&search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    patientTableBody.innerHTML = '';

                    if (data.length === 0) {
                        patientTableBody.innerHTML = '<tr><td colspan="9" class="text-center">No patients found.</td></tr>';
                        return;
                    }

                    data.forEach(p => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${p.patient_id}</td>
                            <td>${p.name}</td>
                            <td>${p.gender}</td>
                            <td>${p.age}</td>
                            <td>${p.address}</td>
                            <td>${p.contact}</td>
                            <td>${p.condition}</td>
                            <td>${p.doctor}</td>
                            <td>${p.date_created}</td>
                        `;
                        patientTableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error fetching patient data:', error);
                });
        });
    }
});
