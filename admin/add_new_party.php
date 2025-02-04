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
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>Add Party</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Add New Party</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="addPartyForm" class="form-horizontal">
                            <div class="control-group">
                                <label class="control-label">First Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="First Name" name="firstname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Last Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Last Name" name="lastname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Business Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Business Name" name="businessname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Contact No. :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Contact No." name="contact" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Address :</label>
                                <div class="controls">
                                    <textarea name="address" class="span11" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">City :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="City" name="city" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage"></span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Party added successfully!
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-th"></i></span>
                        <h5>Party List</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <!-- Loading Spinner -->
                        <div id="loadingSpinner" class="loading-spinner" style="display: none;">
                            <i class="icon-spinner icon-spin"></i> Loading...
                        </div>
                        
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Business Name</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                    <th>City</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody id="partyTableBody"></tbody>
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

.alert {
    margin: 10px;
}

.text-center { text-align: center; }
.text-error { color: #b94a48; }
</style>

<script type="text/javascript">
// Global variables
let currentPage = 1;
const recordsPerPage = 10;

// Load parties with pagination
async function loadParties() {
    const spinner = document.getElementById('loadingSpinner');
    spinner.style.display = 'block';
    
    try {
        const url = `http://localhost/imsfin/IMS_API/api/party/read_parties.php?page=${currentPage}&per_page=${recordsPerPage}`;
        console.log('Fetching from URL:', url);
        
        const response = await fetch(url);
        console.log('Response status:', response.status);
        
        const data = await response.json();
        console.log('Received data:', data);
        
        const tbody = document.getElementById('partyTableBody');
        if (!tbody) {
            console.error('Table body element not found!');
            return;
        }
        
        tbody.innerHTML = '';
        
        // Handle old API format (direct array) or new format (with pagination)
        const parties = Array.isArray(data) ? data : (data.records || []);
        
        if (parties.length > 0) {
            parties.forEach(party => {
                tbody.innerHTML += `
                    <tr>
                        <td>${escapeHtml(party.firstname)}</td>
                        <td>${escapeHtml(party.lastname)}</td>
                        <td>${escapeHtml(party.businessname)}</td>
                        <td>${escapeHtml(party.contact)}</td>
                        <td>${escapeHtml(party.address || '')}</td>
                        <td>${escapeHtml(party.city)}</td>
                        <td><center><a href="edit_party.php?id=${party.id}" class="text-success">Edit</a></center></td>
                        <td><center><a href="#" onclick="deleteParty(${party.id})" class="text-error">Delete</a></center></td>
                    </tr>
                `;
            });

            // Add pagination if available in new format
            if (data.pagination && data.pagination.total_pages > 1) {
                renderPagination(data.pagination);
            } else {
                document.getElementById('paginationContainer').innerHTML = '';
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No parties found</td></tr>';
            document.getElementById('paginationContainer').innerHTML = '';
        }
    } catch (error) {
        console.error('Error loading parties:', error);
        const tbody = document.getElementById('partyTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-error">Error loading parties. Please try again.</td></tr>';
        }
    } finally {
        spinner.style.display = 'none';
    }
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
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
    loadParties();
}

// Delete party
async function deleteParty(id) {
    if (!confirm('Are you sure you want to delete this party?')) {
        return;
    }

    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/party/delete_party.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const data = await response.json();
        
        if (data.status === 200) {
            showSuccessMessage('Party deleted successfully!');
            currentPage = 1;
            await loadParties();
        } else {
            showErrorMessage(data.message || 'Error deleting party');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error deleting party. Please try again.');
    }
}

// Form submission
document.getElementById('addPartyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const partyData = {};
    formData.forEach((value, key) => partyData[key] = value.trim());
    
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/party/create_party.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(partyData)
        });

        const data = await response.json();
        
        if (data.status === 201) {
            showSuccessMessage('Party added successfully!');
            this.reset();
            currentPage = 1;
            await loadParties();
        } else {
            showErrorMessage(data.message || 'Error creating party');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error creating party. Please try again.');
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
    document.getElementById('errorMessage').textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', loadParties);
</script>

<?php include "footer.php"; ?>