<?php
session_start();
if(!isset($_SESSION['admin'])) {
    ?>
    <script type="text/javascript">
        window.location = "index.php";
    </script>
    <?php
}
include 'header.php';
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="dashboard.php" class="tip-bottom">
                <i class="icon-home"></i> Dashboard
            </a>
            <a href="#" class="current">Party Purchase Report</a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon"><i class="icon-list"></i></span>
                    <h5>Party Purchase Report</h5>
                </div>
                <div class="widget-content">
                    <form id="reportForm" class="form-inline">
                        <div class="control-group">
                            <label class="control-label">Select Company: </label>
                            <div class="controls">
                                <select class="span4" id="party_name" name="party_name" required>
                                    <option value="">Select Company</option>
                                </select>
                                <span class="help-inline">Records per page:</span>
                                <select id="records_per_page" class="span2" onchange="changePageSize(this.value)">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <button type="submit" class="btn btn-success">Show Report</button>
                                <button type="button" class="btn btn-warning" onclick="resetSearch()">Reset</button>
                            </div>
                        </div>
                    </form>

                    <div id="loadingSpinner" class="loading-spinner" style="display: none;">
                        <i class="icon-spinner icon-spin"></i> Loading...
                    </div>
                    
                    <div id="errorMessage" class="alert alert-error" style="display: none;">
                        <button class="close" data-dismiss="alert">×</button>
                        <span></span>
                    </div>

                    <div id="reportTableContainer">
                        <div class="initial-message">
                            Please select a company to view purchase details
                        </div>
                    </div>
                    <div id="pagination" class="pagination"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
let currentPage = 1;
let totalPages = 1;
let recordsPerPage = 10;
let currentParty = '';

document.addEventListener('DOMContentLoaded', function() {
    loadParties();
    
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const selectedParty = document.getElementById('party_name').value;
        if (!selectedParty) {
            showError('Please select a company first');
            return;
        }
        currentParty = selectedParty;
        currentPage = 1;
        loadReport(true);
    });
});

async function loadParties() {
    showSpinner();
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/reports/get_party_report.php');
        const result = await response.json();
        
        if (result.status !== 200 || !result.success) {
            throw new Error(result.message || result.error || 'Failed to load companies');
        }

        const select = document.getElementById('party_name');
        select.innerHTML = '<option value="">Select Company</option>';
        
        if (result.data && result.data.length > 0) {
            result.data.forEach(party => {
                const option = document.createElement('option');
                option.value = party.businessname;
                option.textContent = party.businessname;
                select.appendChild(option);
            });
        } else {
            throw new Error('No companies found');
        }
    } catch (error) {
        console.error('Error loading companies:', error);
        handleApiError(error);
    } finally {
        hideSpinner();
    }
}


async function loadReport(useFilters = false) {
    if (!currentParty) return;

    showSpinner();
    try {
        const url = new URL('http://localhost/imsfin/IMS_API/api/reports/get_party_report.php', window.location.origin);
        url.searchParams.append('party_name', currentParty);
        url.searchParams.append('page', currentPage);
        url.searchParams.append('limit', recordsPerPage);

        const response = await fetch(url);
        const result = await response.json();

        if (result.status !== 200 || !result.success) {
            throw new Error(result.message || result.error || 'Failed to load report');
        }

        displayReport(result.data, currentParty, result.summary);
        updatePagination(result.pagination);
    } catch (error) {
        console.error('Error loading report:', error);
        handleApiError(error);
        displayReport([], currentParty);
    } finally {
        hideSpinner();
    }
}

function handleApiError(error) {
    let errorMessage = 'An error occurred. Please try again.';
    
    if (error.response) {
        const statusCode = error.response.status;
        switch (statusCode) {
            case 400:
                errorMessage = error.message || 'Invalid request. Please check your inputs.';
                break;
            case 500:
                errorMessage = 'Database error occurred. Please try again later.';
                break;
            default:
                errorMessage = error.message || 'Failed to load data.';
        }
    } else if (error.message) {
        errorMessage = error.message;
    }
    
    showError(errorMessage);
}

function displayReport(data, partyName, summary = {}) {
    const container = document.getElementById('reportTableContainer');
    
    if (!partyName) {
        container.innerHTML = `
            <div class="initial-message">
                Please select a company to view purchase details
            </div>
        `;
        return;
    }

    let html = `
        <div class="report-header">
            <h4>Purchase Report for: ${partyName}</h4>
        </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Serial No</th>
                    <th>Company Name</th>
                    <th>Product Name</th>
                    <th>Unit</th>
                    <th>Packing Size</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Purchase Type</th>
                    <th>Purchase Date</th>
                    <th>Username</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (data && data.length > 0) {
        let pageTotal = 0;
        
        data.forEach((item, index) => {
            const actualIndex = (currentPage - 1) * recordsPerPage + index + 1;
            const total = parseFloat(item.price) * parseFloat(item.quantity);
            pageTotal += total;

            html += `
                <tr>
                    <td>${actualIndex}</td>
                    <td>${item.company_name || '-'}</td>
                    <td>${item.product_name || '-'}</td>
                    <td>${item.unit || '-'}</td>
                    <td>${item.packing_size || '-'}</td>
                    <td class="text-right">${formatNumber(item.quantity)}</td>
                    <td class="text-right">₱${formatNumber(item.price)}</td>
                    <td class="text-right">₱${formatNumber(total)}</td>
                    <td>${item.purchase_type || '-'}</td>
                    <td>${formatDate(item.purchase_date)}</td>
                    <td>${item.username || '-'}</td>
                </tr>
            `;
        });

        // Add totals rows
        html += `
            <tr class="subtotal-row">
                <td colspan="7" class="text-right"><strong>Page Total:</strong></td>
                <td class="text-right"><strong>₱${formatNumber(pageTotal)}</strong></td>
                <td colspan="3"></td>
            </tr>`;

        if (summary && summary.total_amount) {
            html += `
                <tr class="total-row">
                    <td colspan="7" class="text-right"><strong>Grand Total:</strong></td>
                    <td class="text-right"><strong>₱${formatNumber(summary.total_amount)}</strong></td>
                    <td colspan="3"></td>
                </tr>`;
        }
    } else {
        html += `
            <tr>
                <td colspan="11" class="text-center">No purchase records found for ${partyName}</td>
            </tr>`;
    }

    html += '</tbody></table>';
    container.innerHTML = html;
}

function updatePagination(pagination) {
    if (!pagination) return;

    totalPages = pagination.total_pages;
    const paginationContainer = document.getElementById('pagination');
    
    let html = '<ul class="pagination">';
    
    // Previous button
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
    </li>`;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (
            i === 1 || 
            i === totalPages || 
            (i >= currentPage - 2 && i <= currentPage + 2)
        ) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>`;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Next button
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
    </li>`;

    html += '</ul>';
    html += `<div class="pagination-info">
        Page ${currentPage} of ${totalPages} (${pagination.total_records} total records)
    </div>`;

    paginationContainer.innerHTML = html;
}


function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadReport(true);
}

function changePageSize(size) {
    recordsPerPage = parseInt(size);
    currentPage = 1;
    if (currentParty) {
        loadReport(true);
    }
}

function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatNumber(number) {
    if (!number) return '0.00';
    return parseFloat(number)
        .toFixed(2)
        .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function showSpinner() {
    document.getElementById('loadingSpinner').style.display = 'flex';
}

function hideSpinner() {
    document.getElementById('loadingSpinner').style.display = 'none';
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.style.display = 'block';
    errorDiv.querySelector('span').textContent = message;
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 3000);
}

function resetSearch() {
    document.getElementById('party_name').value = '';
    currentParty = '';
    currentPage = 1;
    document.getElementById('reportTableContainer').innerHTML = `
        <div class="initial-message">
            Please select a company to view purchase details
        </div>
    `;
    document.getElementById('pagination').innerHTML = '';
}
</script>

<style>
.loading-spinner {
    text-align: center;
    padding: 20px;
    font-size: 16px;
}

.icon-spin {
    animation: spin 1s infinite linear;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.initial-message {
    text-align: center;
    padding: 20px;
    color: #666;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 20px 0;
}

.control-group {
    margin-bottom: 20px;
}

.controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.help-inline {
    padding: 0 10px;
}

.total-row {
    background-color: #f8f9fa !important;
    font-weight: bold;
}

.subtotal-row {
    background-color: #e9ecef !important;
}

.text-right {
    text-align: right !important;
}

.text-center {
    text-align: center !important;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination ul {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 5px;
}

.pagination .page-link {
    padding: 6px 12px;
    border: 1px solid #ddd;
    background-color: #fff;
    color: #333;
    text-decoration: none;
    border-radius: 3px;
}

.pagination .active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

.pagination .disabled .page-link {
    color: #999;
    pointer-events: none;
    background-color: #f8f9fa;
}

.pagination-info {
    text-align: center;
    margin-top: 10px;
    color: #666;
}

.widget-content {
    padding: 15px;
}

.report-header {
    margin-bottom: 20px;
}

.report-header h4 {
    margin: 0;
    padding: 10px 0;
    border-bottom: 2px solid #eee;
}

.alert {
    margin-bottom: 15px;
}
</style>

<?php include 'footer.php'; ?>