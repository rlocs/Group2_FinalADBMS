document.addEventListener('DOMContentLoaded', function() {
    // View Medication Details
    const viewButtons = document.querySelectorAll('.view-medication');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const medicationId = this.getAttribute('data-id');
            const modalBody = document.getElementById('viewMedicationBody');
            
            // Show loading message
            modalBody.innerHTML = '<p class="text-center">Loading details for medication ID: ' + medicationId + '...</p>';
            
            // Fetch medication details
            fetch('medications.php?action=get_medication_details&medication_id=' + medicationId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    const medication = data.medication;
                    const batches = data.batches;
                    
                    // Create HTML for medication details
                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${medication.name}</p>
                                <p><strong>Generic Name:</strong> ${medication.generic_name}</p>
                                <p><strong>Category:</strong> ${medication.category}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Unit:</strong> ${medication.unit}</p>
                                <p><strong>Description:</strong> ${medication.description || 'N/A'}</p>
                            </div>
                        </div>
                    `;
                    
                    // Add inventory batches table if there are any
                    if (batches && batches.length > 0) {
                        html += `
                            <h5 class="mt-4">Inventory Batches</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Batch #</th>
                                            <th>Quantity</th>
                                            <th>Expiry Date</th>
                                            <th>Supplier</th>
                                            <th>Date Received</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        batches.forEach(batch => {
                            html += `
                                <tr>
                                    <td>${batch.batch_number || 'N/A'}</td>
                                    <td>${batch.quantity}</td>
                                    <td>${batch.expiry_date}</td>
                                    <td>${batch.supplier || 'N/A'}</td>
                                    <td>${batch.date_received || 'N/A'}</td>
                                </tr>
                            `;
                        });
                        
                        html += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    } else {
                        html += '<p class="mt-3">No inventory batches available for this medication.</p>';
                    }
                    
                    // Update modal content
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching medication details:', error);
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            Error loading medication details: ${error.message}. Please try again.
                        </div>
                    `;
                });
        });
    });
    
    // Dispense Medication
    const dispenseButtons = document.querySelectorAll('.dispense-medication');
    const dispenseForm = document.getElementById('dispenseForm');
    const quantityInput = document.getElementById('dispense_quantity');
    const batchSelect = document.getElementById('dispense_inventory_id');
    const quantityFeedback = document.getElementById('quantity-feedback');

    // Store batch quantities for validation
    let batchQuantities = {};

    dispenseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const medicationId = this.getAttribute('data-id');
            
            // Reset form and validation
            dispenseForm.reset();
            quantityInput.classList.remove('is-invalid');
            
            // Set the medication ID in the form
            document.getElementById('dispense_medication_id').value = medicationId;
            
            // Fetch available inventory batches
            fetch(`medications.php?action=get_inventory_batches&medication_id=${medicationId}`)
                .then(response => response.json())
                .then(batches => {
                    batchSelect.innerHTML = '<option value="">-- Select a batch --</option>';
                    
                    // Reset batch quantities
                    batchQuantities = {};
                    
                    if (batches.length === 0) {
                        batchSelect.innerHTML += '<option value="" disabled>No available batches</option>';
                    } else {
                        batches.forEach(batch => {
                            // Store the quantity for this batch
                            batchQuantities[batch.inventory_id] = parseInt(batch.quantity);
                            
                            batchSelect.innerHTML += `
                                <option value="${batch.inventory_id}">
                                    Batch: ${batch.batch_number} | 
                                    Qty: ${batch.quantity} | 
                                    Expires: ${batch.expiry_date}
                                </option>
                            `;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching inventory batches:', error);
                    batchSelect.innerHTML = '<option value="">Error loading batches</option>';
                });
        });
    });

    // Validate quantity when batch is selected
    batchSelect.addEventListener('change', function() {
        // Reset validation
        quantityInput.classList.remove('is-invalid');
        quantityInput.value = '';
        
        // Set max attribute based on selected batch
        const selectedBatchId = this.value;
        if (selectedBatchId && batchQuantities[selectedBatchId]) {
            quantityInput.setAttribute('max', batchQuantities[selectedBatchId]);
            quantityInput.placeholder = `Max: ${batchQuantities[selectedBatchId]}`;
        }
    });

    // Validate quantity when it changes
    quantityInput.addEventListener('input', function() {
        validateQuantity();
    });

    // Form submission validation
    dispenseForm.addEventListener('submit', function(event) {
        if (!validateQuantity()) {
            event.preventDefault();
        }
    });

    // Quantity validation function
    function validateQuantity() {
        const selectedBatchId = batchSelect.value;
        const enteredQuantity = parseInt(quantityInput.value);
        
        if (!selectedBatchId || !batchQuantities[selectedBatchId]) {
            return false;
        }
        
        const maxQuantity = batchQuantities[selectedBatchId];
        
        if (isNaN(enteredQuantity) || enteredQuantity <= 0) {
            quantityInput.classList.add('is-invalid');
            quantityFeedback.textContent = 'Please enter a valid quantity.';
            return false;
        } else if (enteredQuantity > maxQuantity) {
            quantityInput.classList.add('is-invalid');
            quantityFeedback.textContent = `Quantity cannot exceed available stock (${maxQuantity}).`;
            return false;
        } else {
            quantityInput.classList.remove('is-invalid');
            return true;
        }
    }

    // Delete Medication
    const deleteButtons = document.querySelectorAll('.delete-medication');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const medicationId = this.getAttribute('data-id');
            const medicationName = this.getAttribute('data-name');
            
            // Set values in the delete modal
            document.getElementById('delete-medication-name').textContent = medicationName;
            document.getElementById('delete-medication-id').value = medicationId;
            document.getElementById('force-delete-medication-id').value = medicationId;
        });
    });
});







