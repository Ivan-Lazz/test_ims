<?php

session_start();
if(!isset($_SESSION['admin'])) {
    ?>
    <script type="text/javascript">
        window.location = "index.php";
    </script>
    <?php
}
include "../user/connection.php";
include "header.php";
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>Add Purchase</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Add New Purchase</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="purchaseForm" class="form-horizontal">
                            <div class="control-group">
                                <label class="control-label">Select Company:</label>
                                <div class="controls">
                                    <select name="company_name" class="span11" id="company_name" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Select Product Name:</label>
                                <div class="controls">
                                    <select name="product_name" class="span11" id="product_name" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Select Unit:</label>
                                <div class="controls">
                                    <select name="unit" class="span11" id="unit" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Select Packing Size:</label>
                                <div class="controls">
                                    <select name="packing_size" class="span11" id="packing_size" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Enter Quantity:</label>
                                <div class="controls">
                                    <input type="number" class="span11" value="0" name="qty" min="0" required />
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Enter Price:</label>
                                <div class="controls">
                                    <input type="number" class="span11" value="0" name="price" min="0" required />
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Select Party Name:</label>
                                <div class="controls">
                                    <select name="party_name" class="span11" id="party_name" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Select Purchase Type:</label>
                                <div class="controls">
                                    <select name="purchase_type" class="span11">
                                        <option value="Cash">Cash</option>
                                        <option value="Debit">Debit</option>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Enter Expiry Date:</label>
                                <div class="controls">
                                    <input type="date" class="span11" name="expiry_date" required />
                                </div>
                            </div>

                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage"></span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Purchase added successfully!
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Purchase Now</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial data load
    loadInitialData();
    
    // Event listeners for cascading dropdowns
    document.getElementById('company_name').addEventListener('change', function() {
        if (this.value) {
            loadProducts(this.value);
            resetDependentDropdowns(['product_name', 'unit', 'packing_size']);
        }
    });

    document.getElementById('product_name').addEventListener('change', function() {
        if (this.value) {
            const company_name = document.getElementById('company_name').value;
            loadUnits(this.value, company_name);
            resetDependentDropdowns(['unit', 'packing_size']);
        }
    });

    document.getElementById('unit').addEventListener('change', function() {
        if (this.value) {
            const company_name = document.getElementById('company_name').value;
            const product_name = document.getElementById('product_name').value;
            loadPackingSizes(this.value, product_name, company_name);
        }
    });

    // Form submission handler
    document.getElementById('purchaseForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const purchaseData = {};
        formData.forEach((value, key) => {
            purchaseData[key] = ['qty', 'price'].includes(key) ? 
                parseFloat(value) || 0 : value.trim();
        });

        try {
            const response = await fetch('http://localhost/imsfin/IMS_API/api/sales/create_purchase.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(purchaseData)
            });

            const data = await response.json();

            if (data.success && data.status === 201) {
                showSuccess();
                this.reset();
                resetAllDropdowns();
                loadInitialData(); // Reload initial data after successful submission
            } else {
                throw new Error(data.error || data.message || "Failed to create purchase");
            }
        } catch (error) {
            showError(error.message);
        }
    });
});

// Load initial dropdown data
async function loadInitialData() {
    try {
        // Load companies
        const companyResponse = await fetch('http://localhost/imsfin/IMS_API/api/company/get_companies.php');
        const companiesData = await companyResponse.json();
        
        if (companiesData.status === 200 && companiesData.data) {
            populateDropdown('company_name', companiesData.data, 'companyname');
        } else {
            console.warn('No companies data available');
        }

        // Load parties
        const partyResponse = await fetch('http://localhost/imsfin/IMS_API/api/party/get_parties.php');
        const partiesData = await partyResponse.json();
        
        if (partiesData.status === 200 && partiesData.data) {
            populateDropdown('party_name', partiesData.data, 'businessname');
        } else {
            console.warn('No parties data available');
        }
    } catch (error) {
        console.error('Error loading initial data:', error);
        showError('Failed to load initial data');
    }
}

// Load products for selected company
async function loadProducts(company_name) {
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/product/get_products_by_company.php?company_name=${encodeURIComponent(company_name)}`);
        const data = await response.json();
        
        if (data.status === 200 && data.success && data.data) {
            populateDropdown('product_name', data.data, 'product_name');
        } else {
            console.warn('No products found for this company');
            resetDependentDropdowns(['product_name', 'unit', 'packing_size']);
        }
    } catch (error) {
        console.error('Error loading products:', error);
        showError('Failed to load products');
    }
}

// Load units for selected product
async function loadUnits(product_name, company_name) {
    try {
        const response = await fetch(
            `http://localhost/imsfin/IMS_API/api/unit/get_units_by_product.php?product_name=${encodeURIComponent(product_name)}&company_name=${encodeURIComponent(company_name)}`
        );
        const data = await response.json();
        
        if (data.status === 200 && data.success && data.data) {
            populateDropdown('unit', data.data, 'unit');
        } else {
            console.warn('No units found for this product');
            resetDependentDropdowns(['unit', 'packing_size']);
        }
    } catch (error) {
        console.error('Error loading units:', error);
        showError('Failed to load units');
    }
}

// Load packing sizes
async function loadPackingSizes(unit, product_name, company_name) {
    try {
        const response = await fetch(
            `http://localhost/imsfin/IMS_API/api/product/get_packing_sizes.php?unit=${encodeURIComponent(unit)}&product_name=${encodeURIComponent(product_name)}&company_name=${encodeURIComponent(company_name)}`
        );
        const data = await response.json();
        
        if (data.status === 200 && data.success && data.data) {
            populateDropdown('packing_size', data.data, 'packing_size');
        } else {
            console.warn('No packing sizes found');
            resetDependentDropdowns(['packing_size']);
        }
    } catch (error) {
        console.error('Error loading packing sizes:', error);
        showError('Failed to load packing sizes');
    }
}

// Helper function to populate dropdowns
function populateDropdown(elementId, data, valueKey) {
    const select = document.getElementById(elementId);
    select.innerHTML = '<option value="">Select</option>';
    
    if (Array.isArray(data)) {
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = item[valueKey];
            select.appendChild(option);
        });
    }
}

// Reset dependent dropdowns
function resetDependentDropdowns(dropdownIds) {
    dropdownIds.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = '<option value="">Select</option>';
        }
    });
}

// Reset all dropdowns
function resetAllDropdowns() {
    ['company_name', 'product_name', 'unit', 'packing_size', 'party_name'].forEach(id => {
        const element = document.getElementById(id);
        if (element && id !== 'company_name' && id !== 'party_name') {
            element.innerHTML = '<option value="">Select</option>';
        }
    });
}

// Show success message
function showSuccess() {
    const success = document.getElementById('success');
    success.style.display = 'block';
    setTimeout(() => {
        success.style.display = 'none';
    }, 3000);
}

// Show error message
function showError(message) {
    const error = document.getElementById('error');
    const errorMessage = document.getElementById('errorMessage');
    errorMessage.textContent = message;
    error.style.display = 'block';
    setTimeout(() => {
        error.style.display = 'none';
    }, 5000);
}
</script>

<?php include "footer.php" ?>