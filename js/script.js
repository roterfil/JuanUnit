/* --- JuanUnit Complete JavaScript File (Definitive Version) --- */


// --- 1. MODAL FUNCTIONS ---
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
};

function openProofModal(filePath) {
    const proofContent = document.getElementById('proofContent');
    if (!proofContent) {
        console.error('Proof content container not found!');
        return;
    }

    const isPdf = filePath.toLowerCase().endsWith('.pdf');

    if (isPdf) {
        proofContent.innerHTML = `<embed src="${filePath}" type="application/pdf" width="100%" height="600px" />`;
    } else {
        proofContent.innerHTML = `<img src="${filePath}" style="max-width: 100%; height: auto; display: block; margin: 0 auto;" alt="Proof of Payment">`;
    }

    openModal('proofModal');
}


// --- 2. ADMIN-SIDE DYNAMIC FUNCTIONS ---

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

function loadUnitDetails() {
    const unitSelect = document.getElementById('unit_id');
    const unitDetails = document.getElementById('unit-details');
    const unitRent = document.getElementById('unit-rent');
    const tenantCount = document.getElementById('tenant-count');
    const tenantsList = document.getElementById('tenants-list');
    const amountInput = document.getElementById('amount');
    const useRentBtn = document.getElementById('useUnitRentBtn');

    if (!unitSelect || !unitDetails || !unitRent || !tenantCount || !tenantsList || !amountInput) {
        return;
    }

    const unitId = unitSelect.value;
    
    if (unitId) {
        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        const rent = selectedOption.getAttribute('data-rent');
        
        unitRent.textContent = '₱' + parseFloat(rent).toLocaleString('en-US', {minimumFractionDigits: 2});
        tenantsList.innerHTML = '<p style="color: #666;">Loading tenants...</p>';
        unitDetails.style.display = 'block';
        amountInput.value = rent;
        if (useRentBtn) useRentBtn.style.display = 'inline-block';
        
        fetch(`get_unit_tenants.php?unit_id=${unitId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success === false && data.reason === 'auth') {
                    alert('Your session has expired. Please log in again.');
                    window.location.href = '../login.php';
                    return;
                }

                if (data.success) {
                    tenantCount.textContent = data.tenants.length + ' tenant(s)';
                    let tenantsHtml = '';
                    if (data.tenants.length > 0) {
                        data.tenants.forEach(tenant => {
                            tenantsHtml += `
                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; cursor: pointer;">
                                    <input type="checkbox" name="tenant_ids[]" value="${tenant.id}" checked>
                                    <span>${tenant.full_name}</span>
                                    <small style="color: #666;">(${tenant.email})</small>
                                </label>
                            `;
                        });
                    } else {
                        tenantsHtml = '<p style="color: #666; margin: 0;">No tenants assigned to this unit</p>';
                    }
                    tenantsList.innerHTML = tenantsHtml;
                } else {
                    tenantsList.innerHTML = `<p style="color: #c62828;">Error: ${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching tenants:', error);
                tenantsList.innerHTML = '<p style="color: #ff6b6b;">Failed to load tenants. See console for details.</p>';
            });
    } else {
        unitDetails.style.display = 'none';
        if (useRentBtn) useRentBtn.style.display = 'none';
    }
}

function useUnitRent() {
    const unitSelect = document.getElementById('unit_id');
    const amountInput = document.getElementById('amount');
    
    if (unitSelect && unitSelect.value) {
        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        const rent = selectedOption.getAttribute('data-rent');
        if(amountInput) amountInput.value = rent;
    } else {
        alert('Please select a unit first.');
    }
}


// --- 3. HELPER FUNCTIONS ---

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

function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const filter = input.value.toUpperCase();
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
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

document.addEventListener('DOMContentLoaded', function() {
    // Set active sidebar link
    const currentPage = window.location.pathname.split('/').pop();
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    
    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });

    // Highlight item from notification link
    if (window.location.hash) {
        const hash = window.location.hash;
        try {
            const targetElement = document.querySelector(hash);
            if (targetElement) {
                targetElement.classList.add('highlight-item');
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } catch (e) {
            console.warn("Could not find element for hash:", hash);
        }
    }
});