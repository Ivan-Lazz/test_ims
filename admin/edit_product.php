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
$id = $_GET['id'];
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>Update Product</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Update Product</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editProductForm" class="form-horizontal">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="control-group">
                                <label class="control-label">Select Company :</label>
                                <div class="controls">
                                    <select name="company_name" class="span11" id="companySelect" required>
                                        <option value="">Select Company</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Product Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Product Name" name="product_name" id="product_name" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Select Unit :</label>
                                <div class="controls">
                                    <select name="unit" class="span11" id="unitSelect" required>
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Packing Size :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Packing Size" name="packing_size" id="packing_size" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage">Product already exists!</span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Product updated successfully!
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Utility functions
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    const str = String(unsafe);
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function showSuccessMessage(message) {
    const successDiv = document.getElementById('success');
    successDiv.style.display = 'block';
    successDiv.querySelector('strong').nextSibling.textContent = ' ' + escapeHtml(message);
    setTimeout(() => {
        successDiv.style.display = 'none';
    }, 3000);
}

function showErrorMessage(message) {
    const errorDiv = document.getElementById('error');
    document.getElementById('errorMessage').textContent = escapeHtml(message);
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 3000);
}

// Load companies for dropdown
async function loadCompanies() {
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/company/get_companies.php');
        const data = await response.json();
        console.log('Companies API Response:', data);

        const select = document.getElementById('companySelect');
        select.innerHTML = '<option value="">Select Company</option>'; // Reset options

        if (data.status === 200 && data.data && Array.isArray(data.data)) {
            data.data.forEach(company => {
                const companyName = company.companyname ? company.companyname : '';
                const option = document.createElement('option');
                option.value = escapeHtml(companyName);
                option.textContent = escapeHtml(companyName);
                select.appendChild(option);
            });
            await loadProductData(); // Load product data after companies are loaded
        } else {
            showErrorMessage('Error loading companies: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error loading companies:', error);
        showErrorMessage('Error loading companies. Please try again.');
    }
}

// Load units for dropdown
async function loadUnits() {
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/unit/get_units.php');
        const data = await response.json();
        console.log('Units API Response:', data);

        const select = document.getElementById('unitSelect');
        select.innerHTML = '<option value="">Select Unit</option>'; // Reset options

        if (data.status === 200 && data.success && data.data && Array.isArray(data.data)) {
            data.data.forEach(unit => {
                const unitName = unit.unit ? unit.unit : '';
                const option = document.createElement('option');
                option.value = escapeHtml(unitName);
                option.textContent = escapeHtml(unitName);
                select.appendChild(option);
            });
        } else {
            showErrorMessage('Error loading units: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error loading units:', error);
        showErrorMessage('Error loading units. Please try again.');
    }
}

// Load product data
async function loadProductData() {
    try {
        const productId = <?php echo json_encode($id); ?>;
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/product/read_single_product.php?id=${productId}`);
        const data = await response.json();
        console.log('Product API Response:', data);

        if (data.success && data.status === 200 && data.data) {
            const product = data.data;
            document.getElementById('companySelect').value = product.company_name || '';
            document.getElementById('product_name').value = product.product_name || '';
            document.getElementById('unitSelect').value = product.unit || '';
            document.getElementById('packing_size').value = product.packing_size || '';
        } else {
            showErrorMessage('Error loading product: ' + (data.message || 'Product not found'));
        }
    } catch (error) {
        console.error('Error loading product data:', error);
        showErrorMessage('Error loading product data. Please try again.');
    }
}

// Form submission
document.getElementById('editProductForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const productData = {};
    formData.forEach((value, key) => productData[key] = value.trim());
    
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/product/update_product.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData)
        });
        
        const data = await response.json();
        console.log('Update Response:', data);
        
        if (data.success && data.status === 200) {
            showSuccessMessage('Product updated successfully!');
            setTimeout(() => {
                window.location.href = 'add_product.php';
            }, 1500);
        } else {
            showErrorMessage(data.message || 'Error updating product');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error updating product. Please try again.');
    }
});

// Initialize page
document.addEventListener('DOMContentLoaded', async () => {
    await Promise.all([loadCompanies(), loadUnits()]);
});
</script>

<?php include "footer.php" ?>