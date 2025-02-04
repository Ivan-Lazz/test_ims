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
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>Add Company</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <!-- Add Company Form Widget -->
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Add New Company</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="addCompanyForm" class="form-horizontal">
                            <div class="control-group">
                                <label class="control-label">Company Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Company Name" name="companyname" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage">Company already exists!</span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Company added successfully!
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Companies List Widget -->
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-th"></i></span>
                        <h5>Companies List</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <!-- Loading Spinner -->
                        <div id="loadingSpinner" class="loading-spinner" style="display: none;">
                            <i class="icon-spinner icon-spin"></i> Loading...
                        </div>
                        
                        <!-- Table -->
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Company Name</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody id="companyTableBody"></tbody>
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
/* Basic Styles */
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

/* Table Styles */
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-error { color: #b94a48; }

/* Pagination Styles */
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

/* Alert Styles */
.alert {
    margin: 10px;
}
</style>

<script type="text/javascript">
// Global variables
let currentPage = 1;
const recordsPerPage = 10;

// Load companies with pagination
async function loadCompanies() {
    const spinner = document.getElementById('loadingSpinner');
    spinner.style.display = 'block';
    
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/company/read_companies.php?page=${currentPage}&per_page=${recordsPerPage}`);
        const data = await response.json();
        
        const tbody = document.getElementById('companyTableBody');
        tbody.innerHTML = '';
        
        if (data.status === 200) {
            if (data.records && data.records.length > 0) {
                data.records.forEach(company => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${company.id}</td>
                            <td>${company.companyname}</td>
                            <td><center><a href="edit_company.php?id=${company.id}" class="text-success">Edit</a></center></td>
                            <td><center><a href="#" onclick="deleteCompany(${company.id})" class="text-error">Delete</a></center></td>
                        </tr>
                    `;
                });

                if (data.pagination && data.pagination.total_pages > 1) {
                    renderPagination(data.pagination);
                } else {
                    document.getElementById('paginationContainer').innerHTML = '';
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No companies found</td></tr>';
                document.getElementById('paginationContainer').innerHTML = '';
            }
        } else {
            throw new Error(data.message || 'Error loading companies');
        }
    } catch (error) {
        console.error('Error loading companies:', error);
        document.getElementById('companyTableBody').innerHTML = 
            '<tr><td colspan="4" class="text-center text-error">Error loading companies. Please try again.</td></tr>';
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
                <button class="btn" onclick="changePage(${pagination.current_page - 1})" 
                    ${pagination.current_page === 1 ? 'disabled' : ''}>Previous</button>
                <span class="page-numbers">`;

    // Show page numbers with current page highlighted
    for (let i = Math.max(1, pagination.current_page - 2); 
         i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
        html += `
            <button class="btn ${i === pagination.current_page ? 'btn-info' : ''}" 
                onclick="changePage(${i})">${i}</button>`;
    }

    html += `
                </span>
                <button class="btn" onclick="changePage(${pagination.current_page + 1})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>Next</button>
                <button class="btn" onclick="changePage(${pagination.total_pages})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>Last</button>
            </div>
        </div>`;

    container.innerHTML = html;
}

// Change page
function changePage(page) {
    currentPage = page;
    loadCompanies();
}

// Delete company
async function deleteCompany(id) {
    if (!confirm('Are you sure you want to delete this company?')) {
        return;
    }

    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/company/delete_company.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const data = await response.json();
        
        if (data.status === 200) {
            showSuccessMessage('Company deleted successfully!');
            // Reset to first page and reload
            currentPage = 1;
            await loadCompanies();
        } else {
            showErrorMessage(data.message || 'Error deleting company');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error deleting company. Please try again.');
    }
}

// Add company form handler
document.getElementById('addCompanyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const companyData = {
        companyname: this.companyname.value.trim()
    };
    
    if (!companyData.companyname) {
        showErrorMessage('Company name is required');
        return;
    }

    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/company/create_company.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(companyData)
        });

        const data = await response.json();
        
        if (data.status === 201) {
            showSuccessMessage('Company added successfully!');
            this.reset();
            // Reset to first page and reload
            currentPage = 1;
            await loadCompanies();
        } else {
            showErrorMessage(data.message || 'Error creating company');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error creating company. Please try again.');
    }
});

// Utility functions for showing messages
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
    errorDiv.style.display = 'block';
    document.getElementById('errorMessage').textContent = message;
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', loadCompanies);
</script>

<?php include 'footer.php'; ?>