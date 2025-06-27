/* --- JuanUnit Complete JavaScript File (Definitive Version) --- */


// --- 1. MODAL FUNCTIONS ---
// This is the corrected function that enables centered modals.
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex'; // Use 'flex' for centering
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking on the background overlay
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
};


// --- 2. ADMIN-SIDE DYNAMIC FUNCTIONS ---

// Function to open and populate the "Edit Unit" modal
function openEditUnitModal(unitId) {
    fetch(`get_unit_data.php?id=${unitId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const unit = data.unit;
                document.getElementById('edit_unit_id').value = unit.id;
                document.getElementById('edit_unit_number').value = unit.unit_number;
                document.getElementById('edit_description').value = unit.description;
                document.getElementById('edit_monthly_rent').value = unit.monthly_rent;
                document.getElementById('edit_status').value = unit.status;
                document.getElementById('edit_existing_image').value = unit.image_path;
                
                const imagePreview = document.getElementById('edit_image_preview');
                if (unit.image_path) {
                    imagePreview.src = '../uploads/units/' + unit.image_path;
                    imagePreview.style.display = 'block';
                } else {
                    imagePreview.style.display = 'none';
                }

                openModal('editUnitModal');
            } else {
                alert('Error fetching unit data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching unit data.');
        });
}

// Function to load tenants in the "Add Payment" modal
function loadUnitDetails() {
    const unitSelect = document.getElementById('unit_id');
    const unitDetails = document.getElementById('unit-details');
    const tenantsListDiv = document.getElementById('tenants-list');
    const amountInput = document.getElementById('amount');
    const useRentBtn = document.getElementById('useUnitRentBtn');
    
    const unitId = unitSelect.value;
    
    if (unitId) {
        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        const rent = selectedOption.getAttribute('data-rent');
        
        // Show loading state and pre-fill data
        tenantsListDiv.innerHTML = '<p class="tenant-placeholder">Loading tenants...</p>';
        unitDetails.style.display = 'block';
        amountInput.value = rent;
        useRentBtn.style.display = 'inline-block';

        // Fetch tenants for this unit
        fetch(`get_unit_tenants.php?unit_id=${unitId}`)
            .then(response => response.json())
            .then(data => {
                let tenantsHtml = '';
                if (data.success && data.tenants.length > 0) {
                    data.tenants.forEach(tenant => {
                        tenantsHtml += `
                            <label class="tenant-checkbox">
                                <input type="checkbox" name="tenant_ids[]" value="${tenant.id}" checked>
                                <span>${tenant.full_name}</span>
                            </label>
                        `;
                    });
                } else {
                    tenantsHtml = '<p class="tenant-placeholder">No tenants are currently assigned to this unit.</p>';
                }
                tenantsListDiv.innerHTML = tenantsHtml;
            })
            .catch(error => {
                console.error('Error fetching tenants:', error);
                tenantsListDiv.innerHTML = '<p class="tenant-placeholder" style="color: #c62828;">An error occurred while loading tenants.</p>';
            });
    } else {
        // Hide details if no unit is selected
        unitDetails.style.display = 'none';
        useRentBtn.style.display = 'none';
        amountInput.value = '';
    }
}

// Function to use the unit's rent amount in the payment form
function useUnitRent() {
    const unitSelect = document.getElementById('unit_id');
    const amountInput = document.getElementById('amount');
    
    if (unitSelect.value) {
        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        const rent = selectedOption.getAttribute('data-rent');
        amountInput.value = rent;
    } else {
        alert('Please select a unit first.');
    }
}


// --- 3. ORIGINAL HELPER FUNCTIONS ---

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#ff6b6b';
            isValid = false;
        } else {
            input.style.borderColor = '#e1e5e9';
        }
    });

    return isValid;
}

// File upload preview
function previewFile(input, previewId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

// Confirm delete action
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Show/hide password
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Simple search for tables
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const filter = input.value.toUpperCase();
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header row
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toUpperCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        rows[i].style.display = found ? "" : "none";
    }
}

// Smooth scrolling for anchor links on the landing page
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            e.preventDefault();
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Initialize functions when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set active sidebar link based on current page
    const currentPage = window.location.pathname.split('/').pop();
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    
    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
});