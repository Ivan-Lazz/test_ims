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
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>Add Unit</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <!-- Add Unit Form Widget -->
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Add New Unit</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="addUnitForm" class="form-horizontal">
                            <div class="control-group">
                                <label class="control-label">Unit Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Unit Name" name="unitname" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage">Unit already exists!</span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Unit added successfully!
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Units List Widget -->
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-th"></i></span>
                        <h5>Units List</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <!-- Loading Spinner -->
                        <div id="loadingSpinner" class="loading-spinner" style="display: none;">
                            <i class="icon-spinner icon-spin"></i> Loading...
                        </div>
                        
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Unit Name</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody id="unitTableBody"></tbody>
                        </table>

                        <!-- Pagination Container -->
                        <div id="paginationContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.loading-spinner {
    text-align: center;
    padding: 20px;
    background-color: #f9f9f9;
    border-bottom: 1px solid #ddd;
}

.icon-spin {
    animation: spin 1s infinite linear;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.pagination-container {
    margin-top: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background-color: #f9f9f9;
    border-top: 1px solid #ddd;
}

.dataTables_info {
    color: #666;
    padding: 8px 0;
}

.dataTables_paginate {
    text-align: right;
}

.dataTables_paginate .btn {
    margin: 0 2px;
    padding: 4px 10px;
    border: 1px solid #ddd;
}

.dataTables_paginate .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f5f5f5;
}

.page-numbers {
    margin: 0 10px;
    display: inline-block;
}

.page-numbers .btn {
    min-width: 35px;
}

.btn-info {
    color: #ffffff;
    background-color: #49afcd;
    border-color: #2f96b4;
}

.text-center { text-align: center; }
.text-error { color: #b94a48; }

.alert {
    margin: 10px;
}
</style>

<script type="text/javascript">
// Global variables
let currentPage = 1;
const recordsPerPage = 10;

// Load units with pagination
async function loadUnits() {
    const spinner = document.getElementById('loadingSpinner');
    spinner.style.display = 'block';
    
    try {
        const url = `http://localhost/imsfin/IMS_API/api/unit/read_units.php?page=${currentPage}&per_page=${recordsPerPage}`;
        console.log('Fetching from URL:', url);
        
        const response = await fetch(url);
        const data = await response.json();
        console.log('Received data:', data);
        
        const tbody = document.getElementById('unitTableBody');
        if (!tbody) {
            console.error('Table body element not found!');
            return;
        }
        
        tbody.innerHTML = '';
        
        if (data.status === 200 && data.success && data.data && Array.isArray(data.data.records)) {
            const units = data.data.records;
            
            if (units.length > 0) {
                units.forEach(unit => {
                    // Convert values to string and provide defaults
                    const id = unit.id ? String(unit.id) : '';
                    const unitName = unit.unit ? String(unit.unit) : '';
                    
                    tbody.innerHTML += `
                        <tr>
                            <td>${escapeHtml(id)}</td>
                            <td>${escapeHtml(unitName)}</td>
                            <td><center><a href="edit_unit.php?id=${escapeHtml(id)}" class="text-success">Edit</a></center></td>
                            <td><center><a href="#" onclick="deleteUnit('${escapeHtml(id)}')" class="text-error">Delete</a></center></td>
                        </tr>
                    `;
                });

                if (data.data.pagination) {
                    renderPagination(data.data.pagination);
                } else {
                    document.getElementById('paginationContainer').innerHTML = '';
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No units found</td></tr>';
                document.getElementById('paginationContainer').innerHTML = '';
            }
        } else {
            throw new Error(data.message || 'Failed to load units');
        }
    } catch (error) {
        console.error('Error loading units:', error);
        document.getElementById('unitTableBody').innerHTML = 
            '<tr><td colspan="4" class="text-center text-error">Error loading units. Please try again.</td></tr>';
        document.getElementById('paginationContainer').innerHTML = '';
    } finally {
        spinner.style.display = 'none';
    }
}

// Render pagination controls
function renderPagination(pagination) {
    const container = document.getElementById('paginationContainer');
    let html = `
        <div class="pagination-container">
            <div class="dataTables_info">
                Showing ${((pagination.current_page - 1) * pagination.records_per_page) + 1} to 
                ${Math.min(pagination.current_page * pagination.records_per_page, pagination.total_records)} 
                of ${pagination.total_records} entries
            </div>
            <div class="dataTables_paginate">
                <button class="btn" onclick="changePage(1)" ${pagination.current_page === 1 ? 'disabled' : ''}>
                    First
                </button>
                <button class="btn" onclick="changePage(${pagination.current_page - 1})" ${pagination.current_page === 1 ? 'disabled' : ''}>
                    Previous
                </button>
                <span class="page-numbers">`;

    for (let i = Math.max(1, pagination.current_page - 2); 
         i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
        html += `
            <button class="btn ${i === pagination.current_page ? 'btn-info' : ''}" onclick="changePage(${i})">
                ${i}
            </button>`;
    }

    html += `
                </span>
                <button class="btn" onclick="changePage(${pagination.current_page + 1})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                    Next
                </button>
                <button class="btn" onclick="changePage(${pagination.total_pages})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                    Last
                </button>
            </div>
        </div>`;

    container.innerHTML = html;
}

// Change page
function changePage(page) {
    currentPage = page;
    loadUnits();
}

// Delete unit
async function deleteUnit(id) {
    if (!id) {
        showErrorMessage('Invalid unit ID');
        return;
    }

    if (!confirm('Are you sure you want to delete this unit?')) {
        return;
    }

    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/unit/delete_unit.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: String(id) })
        });
        
        const data = await response.json();
        
        if (data.success && data.status === 200) {
            showSuccessMessage('Unit deleted successfully!');
            currentPage = 1; // Reset to first page
            loadUnits();
        } else {
            showErrorMessage(data.message || 'Error deleting unit');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error deleting unit. Please try again.');
    }
}

// Form submission
document.getElementById('addUnitForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const unitData = {
        unit: this.unitname.value.trim()
    };

    if (!unitData.unit) {
        showErrorMessage('Unit name is required');
        return;
    }
    
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/unit/create_unit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(unitData)
        });
        
        const data = await response.json();
        
        if (data.success && data.status === 201) {
            showSuccessMessage('Unit added successfully!');
            this.reset();
            currentPage = 1; // Reset to first page
            loadUnits();
        } else {
            showErrorMessage(data.message || 'Error creating unit');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error creating unit. Please try again.');
    }
});

// Utility functions
function showSuccessMessage(message) {
    const successDiv = document.getElementById('success');
    successDiv.style.display = 'block';
    successDiv.querySelector('strong').nextSibling.textContent = ' ' + message;
    setTimeout(() => {
        successDiv.style.display = 'none';
    }, 3000);
}

function showErrorMessage(message) {
    const errorDiv = document.getElementById('error');
    document.getElementById('errorMessage').textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 3000);
}

function escapeHtml(unsafe) {
    // Handle null, undefined, or non-string values
    if (unsafe === null || unsafe === undefined) return '';
    
    // Convert to string if it's not already a string
    const str = String(unsafe);
    
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', loadUnits);
</script>

<?php include "footer.php"; ?>