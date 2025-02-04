<?php
session_start();
if(!isset($_SESSION['admin'])) {
    ?>
    <script type="text/javascript">
        window.location = "index.php";
    </script>
    <?php
}
include "header.php";
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="dashboard.php" class="tip-bottom">
                <i class="icon-home"></i> Dashboard
            </a>
            <a href="#" class="current">Expiry Report</a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon"><i class="icon-time"></i></span>
                    <h5>Product Expiry Report</h5>
                </div>
                <div class="widget-content">
                    <div class="row-fluid">
                        <form id="reportForm" class="form-horizontal">
                            <div class="control-group">
                                <label class="control-label">Date Range:</label>
                                <div class="controls">
                                    <input type="date" id="start_date" class="span3" required>
                                    <span class="help-inline">to</span>
                                    <input type="date" id="end_date" class="span3" required>
                                    <select id="records_per_page" class="span2">
                                        <option value="10">10 per page</option>
                                        <option value="25">25 per page</option>
                                        <option value="50">50 per page</option>
                                        <option value="100">100 per page</option>
                                    </select>
                                    <button type="submit" class="btn btn-success">Show Report</button>
                                    <button type="button" class="btn btn-warning" onclick="resetSearch()">Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row-fluid" style="margin-bottom: 20px;">
                        <div class="span4">
                            <div class="stat-box">
                                <div class="stat-header bg-danger">Expired Products</div>
                                <div class="stat-value" id="expired-count">0</div>
                            </div>
                        </div>
                        <div class="span4">
                            <div class="stat-box">
                                <div class="stat-header bg-warning">Expiring Soon</div>
                                <div class="stat-value" id="expiring-count">0</div>
                            </div>
                        </div>
                        <div class="span4">
                            <div class="stat-box">
                                <div class="stat-header bg-success">Valid Products</div>
                                <div class="stat-value" id="valid-count">0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading and Error Messages -->
                    <div id="loadingSpinner" class="loading-spinner" style="display: none;">
                        <i class="icon-spinner icon-spin"></i> Loading...
                    </div>
                    <div id="errorMessage" class="alert alert-error" style="display: none;">
                        <button class="close" data-dismiss="alert">×</button>
                        <span></span>
                    </div>

                    <!-- Report Table -->
                    <div id="reportTableContainer"></div>
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

document.addEventListener('DOMContentLoaded', function() {
    // Set default dates to current month
    const today = new Date();
    const nextMonth = new Date(today);
    nextMonth.setMonth(nextMonth.getMonth() + 1);
    
    document.getElementById('start_date').value = formatDateForInput(today);
    document.getElementById('end_date').value = formatDateForInput(nextMonth);
    
    // Add event listeners
    document.getElementById('records_per_page').addEventListener('change', function(e) {
        recordsPerPage = parseInt(e.target.value);
        currentPage = 1;
        loadReport();
    });
    
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        loadReport();
    });

    // Initial load
    loadReport();
});

async function loadReport() {
    showSpinner();
    try {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        if (new Date(startDate) > new Date(endDate)) {
            throw new Error('Start date cannot be later than end date');
        }

        const url = new URL('http://localhost/imsfin/IMS_API/api/reports/get_expiry_report.php', window.location.origin);
        url.searchParams.append('page', currentPage);
        url.searchParams.append('limit', recordsPerPage);
        url.searchParams.append('start_date', startDate);
        url.searchParams.append('end_date', endDate);

        const response = await fetch(url);
        const result = await response.json();

        if (result.status !== 200 || !result.success) {
            throw new Error(result.message || result.error || 'Failed to load report');
        }

        updateSummary(result.summary);
        displayReport(result.data);
        updatePagination(result.pagination);

    } catch (error) {
        // Handle specific status codes
        if (error.response) {
            const errorData = await error.response.json();
            switch (errorData.status) {
                case 500:
                    showError('Database error occurred. Please try again later.');
                    break;
                case 400:
                    showError(errorData.message || 'Invalid request. Please check your inputs.');
                    break;
                default:
                    showError(errorData.message || 'An error occurred while loading the report.');
            }
        } else {
            showError(error.message);
        }
        clearReport();
    } finally {
        hideSpinner();
    }
}

function displayReport(data) {
    const container = document.getElementById('reportTableContainer');
    
    let html = `
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Product Company</th>
                    <th>Product Name</th>
                    <th>Unit</th>
                    <th>Packing Size</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Purchase Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (data && data.length > 0) {
        data.forEach((item, index) => {
            const actualIndex = (currentPage - 1) * recordsPerPage + index + 1;
            const statusClass = getStatusClass(item.expiry_status);
            
            html += `
                <tr>
                    <td>${actualIndex}</td>
                    <td>${item.company_name || '-'}</td>
                    <td>${item.product_name || '-'}</td>
                    <td>${item.unit || '-'}</td>
                    <td>${item.packing_size || '-'}</td>
                    <td class="text-right">${formatNumber(item.quantity, true)}</td>
                    <td class="text-right">₱${formatNumber(item.price)}</td>
                    <td>${formatDate(item.purchase_date)}</td>
                    <td>${formatDate(item.expiry_date)}</td>
                    <td><span class="status-badge ${statusClass}">${item.expiry_status}</span></td>
                </tr>
            `;
        });
    } else {
        html += '<tr><td colspan="10" class="text-center">No records found</td></tr>';
    }

    html += '</tbody></table>';
    container.innerHTML = html;
}

function updateSummary(summary) {
    if (!summary) return;
    
    document.getElementById('expired-count').textContent = summary.expired_count || 0;
    document.getElementById('expiring-count').textContent = summary.expiring_soon_count || 0;
    document.getElementById('valid-count').textContent = summary.valid_count || 0;
}

function updatePagination(pagination) {
    if (!pagination || pagination.total_pages <= 1) {
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    totalPages = pagination.total_pages;
    let html = `<div class="btn-group">`;
    
    // Previous button
    html += `<button class="btn ${currentPage === 1 ? 'disabled' : ''}" 
            onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
            <i class="icon-chevron-left"></i></button>`;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<button class="btn ${i === currentPage ? 'btn-primary' : ''}" 
                    onclick="changePage(${i})">${i}</button>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<button class="btn disabled">...</button>`;
        }
    }

    // Next button
    html += `<button class="btn ${currentPage === totalPages ? 'disabled' : ''}" 
            onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
            <i class="icon-chevron-right"></i></button>`;

    html += `</div>`;
    document.getElementById('pagination').innerHTML = html;
}

function changePage(page) {
    if (page < 1 || page > totalPages || page === currentPage) return;
    currentPage = page;
    loadReport();
}

function getStatusClass(status) {
    switch(status) {
        case 'Expired': return 'bg-danger';
        case 'Expiring Soon': return 'bg-warning';
        case 'Valid': return 'bg-success';
        default: return '';
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

function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}

function formatNumber(number, isQuantity = false) {
    if (!number) return '0';
    return isQuantity 
        ? parseInt(number).toLocaleString('en-US')
        : parseFloat(number).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function resetSearch() {
    const today = new Date();
    const nextMonth = new Date(today);
    nextMonth.setMonth(nextMonth.getMonth() + 1);
    
    document.getElementById('start_date').value = formatDateForInput(today);
    document.getElementById('end_date').value = formatDateForInput(nextMonth);
    document.getElementById('records_per_page').value = "10";
    
    recordsPerPage = 10;
    currentPage = 1;
    loadReport();
}

function showSpinner() {
    document.getElementById('loadingSpinner').style.display = 'block';
}

function hideSpinner() {
    document.getElementById('loadingSpinner').style.display = 'none';
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.querySelector('span').textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => errorDiv.style.display = 'none', 3000);
}

function clearReport() {
    document.getElementById('reportTableContainer').innerHTML = '';
    document.getElementById('pagination').innerHTML = '';
    updateSummary({expired_count: 0, expiring_soon_count: 0, valid_count: 0});
}
</script>

<style>
.loading-spinner {
    text-align: center;
    padding: 20px;
    background: rgba(255,255,255,0.8);
}

.stat-box {
    padding: 15px;
    text-align: center;
    border-radius: 4px;
    border: 1px solid #ddd;
    margin-bottom: 20px;
}

.stat-header {
    font-size: 16px;
    padding: 5px;
    margin: -15px -15px 10px -15px;
    border-radius: 4px 4px 0 0;
    color: white;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
    color: white;
}

.bg-danger {
    background-color: #dc3545;
}

.bg-warning {
    background-color: #ffc107;
}

.bg-success {
    background-color: #28a745;
}

.text-right {
    text-align: right !important;
}

.text-center {
    text-align: center !important;
}

.btn-group {
    display: inline-flex;
    gap: 5px;
}

.table th {
    background: #f8f9fa;
}

.controls {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>

<?php include "footer.php"; ?>