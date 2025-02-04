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
            <a href="#" class="tip-bottom"><i class="icon-home"></i>Purchase Report</a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <form class="form-inline" id="reportForm">
                <div class="form-group">
                    <label for="start_date">Select Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Select End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="records_per_page">Records per page:</label>
                    <select id="records_per_page" class="form-control" onchange="changePageSize(this.value)">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Show Purchase Report</button>
                <button type="button" class="btn btn-warning" onclick="resetSearch()">Clear Search</button>
            </form>

            <br>

            <div class="row-fluid">
                <div class="span12">
                    <div class="widget-content nopadding">
                        <div id="loadingSpinner" class="spinner" style="display: none;">
                            <div class="spinner-border"></div>
                            <span>Loading...</span>
                        </div>
                        <div id="purchaseTableContainer"></div>
                        <div id="pagination" class="pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let totalPages = 1;
let recordsPerPage = 10;

document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('start_date').value = formatDateForInput(firstDay);
    document.getElementById('end_date').value = formatDateForInput(today);
    
    loadPurchases(true);
    
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (startDate > endDate) {
            showError('Start date cannot be later than end date');
            return;
        }
        
        currentPage = 1;
        loadPurchases(true);
    });
});

async function loadPurchases(useFilters = false) {
    showSpinner();
    try {
        let url = new URL('http://localhost/imsfin/IMS_API/api/reports/get_purchase_report.php', window.location.origin);
        url.searchParams.append('page', currentPage);
        url.searchParams.append('limit', recordsPerPage);
        
        if (useFilters) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            if (startDate && endDate) {
                url.searchParams.append('start_date', startDate);
                url.searchParams.append('end_date', endDate);
            }
        }

        const response = await fetch(url);
        const result = await response.json();

        if (result.status !== 200 || !result.success) {
            throw new Error(result.message || result.error || 'Failed to load purchases');
        }

        displayPurchases(result.data);
        updatePagination(result.pagination);

    } catch (error) {
        console.error('Error:', error);
        handleApiError(error);
    } finally {
        hideSpinner();
    }
}

function handleApiError(error) {
    let errorMessage = 'An error occurred. Please try again.';
    
    if (error.response) {
        const errorData = error.response;
        switch (errorData.status) {
            case 400:
                errorMessage = errorData.message || 'Invalid request. Please check your inputs.';
                break;
            case 500:
                errorMessage = 'Database error occurred. Please try again later.';
                break;
            default:
                errorMessage = errorData.message || 'Failed to load purchase data.';
        }
    } else if (error.message) {
        errorMessage = error.message;
    }
    
    showError(errorMessage);
    clearTable();
}

function displayPurchases(purchases) {
    const container = document.getElementById('purchaseTableContainer');
    
    let html = `
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Serial No</th>
                    <th>Purchased By</th>
                    <th>Product Company</th>
                    <th>Product Name</th>
                    <th>Product Unit</th>
                    <th>Packing Size</th>
                    <th>Product Qty</th>
                    <th>Price</th>
                    <th>Party Name</th>
                    <th>Purchase Type</th>
                    <th>Expiry Date</th>
                    <th>Purchase Date</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (purchases && purchases.length > 0) {
        purchases.forEach((purchase, index) => {
            const actualIndex = (currentPage - 1) * recordsPerPage + index + 1;
            html += `
                <tr>
                    <td>${actualIndex}</td>
                    <td>${purchase.username || '-'}</td>
                    <td>${purchase.company_name || '-'}</td>
                    <td>${purchase.product_name || '-'}</td>
                    <td>${purchase.unit || '-'}</td>
                    <td>${purchase.packing_size || '-'}</td>
                    <td class="text-right">${formatNumber(purchase.quantity, true)}</td>
                    <td class="text-right">₱${formatNumber(purchase.price)}</td>
                    <td>${purchase.party_name || '-'}</td>
                    <td>${purchase.purchase_type || '-'}</td>
                    <td>${formatDate(purchase.expiry_date)}</td>
                    <td>${formatDate(purchase.purchase_date)}</td>
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="12" class="text-center">No purchase records found</td>
            </tr>
        `;
    }

    html += '</tbody></table>';
    container.innerHTML = html;
}

function updatePagination(pagination) {
    if (!pagination || pagination.total_pages <= 1) {
        document.getElementById('pagination').innerHTML = '';
        return;
    }

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
        Page ${currentPage} of ${totalPages} 
        (${pagination.total_records} total records)
    </div>`;

    paginationContainer.innerHTML = html;
}

function changePage(page) {
    if (page < 1 || page > totalPages || page === currentPage) return;
    currentPage = page;
    loadPurchases(true);
}

function changePageSize(size) {
    recordsPerPage = parseInt(size);
    currentPage = 1;
    loadPurchases(true);
}

function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatNumber(number, isQuantity = false) {
    if (!number) return '0.00';
    return isQuantity 
        ? parseInt(number).toLocaleString('en-US')
        : parseFloat(number).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}

function resetSearch() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('start_date').value = formatDateForInput(firstDay);
    document.getElementById('end_date').value = formatDateForInput(today);
    document.getElementById('records_per_page').value = "10";
    
    recordsPerPage = 10;
    currentPage = 1;
    loadPurchases(true);
}

function showSpinner() {
    document.getElementById('loadingSpinner').style.display = 'flex';
}

function hideSpinner() {
    document.getElementById('loadingSpinner').style.display = 'none';
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-error';
    errorDiv.innerHTML = `
        <button class="close" data-dismiss="alert">×</button>
        <strong>Error!</strong> ${message}
    `;
    
    const container = document.getElementById('purchaseTableContainer');
    container.insertBefore(errorDiv, container.firstChild);
    
    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}

function clearTable() {
    document.getElementById('purchaseTableContainer').innerHTML = `
        <div class="alert alert-info">
            No purchase records to display. Please try different search criteria.
        </div>
    `;
    document.getElementById('pagination').innerHTML = '';
}
</script>

<style>
.spinner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.form-group {
    margin-right: 15px;
}

.btn {
    margin-right: 10px;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination ul {
    list-style: none;
    padding: 0;
    display: flex;
    gap: 5px;
}

.pagination .page-item {
    margin: 0 2px;
}

.pagination .page-link {
    padding: 8px 12px;
    border: 1px solid #ddd;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
}

.pagination .active .page-link {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination .disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    cursor: not-allowed;
}

.pagination-info {
    text-align: center;
    margin-top: 10px;
    color: #6c757d;
}

#records_per_page {
    width: auto;
    display: inline-block;
}
</style>

<?php include "footer.php"; ?>