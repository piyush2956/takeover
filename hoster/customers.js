document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('isLoggedIn')) {
        window.location.href = 'index.html';
        return;
    }
    loadCustomers();
    loadCities();
});

async function loadCustomers() {
    try {
        const response = await fetch('get_customers.php');
        console.log('Response status:', response.status); // Debug log

        const result = await response.json();
        console.log('Response data:', result); // Debug log

        if (!result.success) {
            throw new Error(result.message || 'Unknown error occurred');
        }

        const customers = result.data;
        const tableBody = document.getElementById('customersTableBody');

        if (!Array.isArray(customers)) {
            throw new Error('Invalid data format received from server');
        }

        if (customers.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No customers found</td></tr>';
            return;
        }

        // Simplify table rendering first for testing
        tableBody.innerHTML = customers.map(customer => `
            <tr>
                <td>#${customer.id || ''}</td>
                <td>${customer.first_name || ''}</td>
                <td>${customer.last_name || ''}</td>
                <td>${customer.email || '-'}</td>
                <td>${customer.phone || ''}</td>
                <td>${customer.city || '-'}</td>
                <td>-</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="editCustomer(${customer.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${customer.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error details:', error); // Detailed error logging
        alert('Failed to load customers: ' + error.message);
    }
}

function getStatusColor(status) {
    const colors = {
        'Pending': 'warning',
        'Processing': 'info',
        'Shipped': 'primary',
        'Delivered': 'success',
        'Cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}

async function loadCities() {
    try {
        const response = await fetch('get_cities.php');
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        const cityFilter = document.getElementById('cityFilter');
        const cities = result.data;

        cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            cityFilter.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading cities:', error);
    }
}

async function saveCustomer(event) {
    event.preventDefault();

    try {
        const form = document.getElementById('customerForm');
        const customerId = form.dataset.customerId;

        // Collect form data
        const formData = {
            id: customerId,
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            state: document.getElementById('state').value
        };

        // Send update request
        const response = await fetch('update_customer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        // Close modal and refresh table
        bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();
        await loadCustomers(); // Reload the customers table

        // Show success message
        showNotification('Customer updated successfully', 'success');
    } catch (error) {
        console.error('Error saving customer:', error);
        showNotification('Failed to update customer: ' + error.message, 'error');
    }
}

async function editCustomer(id) {
    try {
        const response = await fetch(`get_customer.php?id=${id}`);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        const customer = result.data;

        // Populate the modal form with customer data
        const form = document.getElementById('customerForm');
        form.dataset.customerId = customer.id; // Store ID for reference

        // Populate all form fields
        Object.keys(customer).forEach(key => {
            const input = document.getElementById(key);
            if (input) {
                input.value = customer[key];
            }
        });

        // Show modal
        document.getElementById('modalTitle').textContent = 'Edit Customer';
        const modal = new bootstrap.Modal(document.getElementById('customerModal'));
        modal.show();
    } catch (error) {
        console.error('Error loading customer:', error);
        alert('Failed to load customer details');
    }
}

async function deleteCustomer(id) {
    if (!confirm('Are you sure you want to delete this customer?')) {
        return;
    }

    try {
        const response = await fetch('delete_customer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        loadCustomers();
        alert('Customer deleted successfully');
    } catch (error) {
        console.error('Error deleting customer:', error);
        alert('Failed to delete customer');
    }
}

function logout() {
    sessionStorage.clear();
    window.location.href = 'index.html';
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    if (!sessionStorage.getItem('isLoggedIn')) {
        window.location.href = 'index.html';
        return;
    }
    loadCustomers();
    loadCities();

    // Add form submit handler
    document.getElementById('customerForm').addEventListener('submit', saveCustomer);

    // Add save button click handler
    document.getElementById('saveCustomerBtn').addEventListener('click', () => {
        document.getElementById('customerForm').requestSubmit();
    });

    // Reset form when opening modal for new customer
    document.querySelector('[data-bs-target="#customerModal"]').addEventListener('click', function() {
        document.getElementById('customerForm').reset();
        document.getElementById('customerId').value = '';
        document.getElementById('modalTitle').textContent = 'Add Customer';
    });

    // Handle form submission
    document.getElementById('customerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('Form submitted'); // Debug log

        const formData = {
            firstName: document.getElementById('firstName').value.trim(),
            lastName: document.getElementById('lastName').value.trim(),
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            address: document.getElementById('address').value.trim(),
            city: document.getElementById('city').value.trim(),
            state: document.getElementById('state').value.trim()
        };

        console.log('Form data:', formData); // Debug log

        try {
            const response = await fetch('add_customer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            console.log('Response status:', response.status); // Debug log
            const result = await response.json();
            console.log('Response data:', result); // Debug log

            if (result.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('customerModal'));
                modal.hide();
                await loadCustomers();
                showNotification('Customer added successfully', 'success');
            } else {
                throw new Error(result.message || 'Failed to save customer');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification(error.message, 'danger');
        }
    });

    // Handle save button click
    document.getElementById('saveCustomerBtn').addEventListener('click', function() {
        console.log('Save button clicked'); // Debug log
        const form = document.getElementById('customerForm');
        if (form.checkValidity()) {
            form.requestSubmit();
        } else {
            form.reportValidity();
        }
    });
});

document.getElementById('searchCustomer').addEventListener('input', function(e) {
    // Implement search functionality here
});
document.getElementById('cityFilter').addEventListener('change', function(e) {
    // Implement city filter functionality here
});