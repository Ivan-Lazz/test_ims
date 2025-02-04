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
            <a href="#" class="current">Stock List</a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-th"></i></span>
                        <h5>Stock List</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <!-- Search and Page Size Controls -->
                        <div class="widget-controls">
                            <div class="controls-row">
                                <div class="search-box">
                                    <input type="text" id="searchInput" class="span3" 
                                           placeholder="Search products..." onkeyup="debounce(handleSearch, 500)()">
                                </div>
                                <div class="page-size">
                                    <label class="control-label">Records per page:</label>
                                    <select id="records_per_page" class="span2" onchange="changePageSize(this.value)">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="loadingSpinner" class="spinner" style="display: none;">
                            <div class="spinner-border"></div>
                            <span>Loading...</span>
                        </div>
                        
                        <div id="errorMessage" class="alert alert-error" style="display:none">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Error!</strong> <span id="errorText"></span>
                        </div>
                        
                        <div id="stockTableContainer"></div>
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
let currentSearch = '';

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

document.addEventListener('DOMContentLoaded', function() {
    loadStockData();
});

function handleSearch() {
    const searchValue = document.getElementById('searchInput').value;
    currentSearch = searchValue;
    currentPage = 1;
    loadStockData();
}

async function loadStockData() {
    showSpinner();
    hideError();
    
    try {
        let url = new URL('http://localhost/imsfin/IMS_API/api/stock/get_stock.php', window.location.origin);
        url.searchParams.append('page', currentPage);
        url.searchParams.append('limit', recordsPerPage);
        
        if (currentSearch) {
            url.searchParams.append('search', currentSearch);
        }

        const response = await fetch(url);
        const result = await response.json();
        
        // Check both status code and success flag
        if (result.status !== 200 || !result.success) {
            throw new Error(result.message || result.error || 'Failed to load stock data');
        }

        displayStockData(result.data);
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
        const statusCode = error.response.status;
        switch (statusCode) {
            case 400:
                errorMessage = error.message || 'Invalid request. Please check your inputs.';
                break;
            case 500:
                errorMessage = 'Database error occurred. Please try again later.';
                break;
            default:
                errorMessage = error.message || 'Failed to load stock data.';
        }
    } else if (error.message) {
        errorMessage = error.message;
    }
    
    showError(errorMessage);
    clearStockTable();
}

function displayStockData(stocks) {
    const container = document.getElementById('stockTableContainer');
    
    let html = `
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>Product Company</th>
                    <th>Product Name</th>
                    <th>Product Unit</th>
                    <th>Packing Size</th>
                    <th>Product Quantity</th>
                    <th>Product Selling Price</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (stocks && stocks.length > 0) {
        stocks.forEach((stock, index) => {
            const actualIndex = (currentPage - 1) * recordsPerPage + index + 1;
            const lowStock = parseInt(stock.product_qty) <= 10;
            const rowClass = lowStock ? 'low-stock' : '';
            
            html += `
                <tr class="${rowClass}">
                    <td>${actualIndex}</td>
                    <td>${stock.product_company}</td>
                    <td>${stock.product_name}</td>
                    <td>${stock.product_unit}</td>
                    <td>${stock.packing_size}</td>
                    <td class="text-right ${lowStock ? 'text-danger' : ''}">${formatNumber(stock.product_qty)}</td>
                    <td class="text-right">₱${stock.product_selling_price}</td>
                    <td>
                        <center>
                            <a href="edit_stock_master.php?id=${stock.id}" class="btn btn-primary btn-mini">
                                <i class="icon-pencil"></i> Edit
                            </a>
                        </center>
                    </td>
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="8" class="text-center">No stock records found</td>
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
        Page ${currentPage} of ${totalPages} (${pagination.total_records} total records)
    </div>`;

    paginationContainer.innerHTML = html;
}

function changePage(page) {
    if (page < 1 || page > totalPages || page === currentPage) return;
    currentPage = page;
    loadStockData();
}

function changePageSize(size) {
    recordsPerPage = parseInt(size);
    currentPage = 1;
    loadStockData();
}

function formatNumber(number) {
    if (!number) return '0';
    return parseFloat(number).toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

function clearStockTable() {
    document.getElementById('stockTableContainer').innerHTML = `
        <div class="alert alert-info">
            No stock records to display. Please try different search criteria.
        </div>
    `;
    document.getElementById('pagination').innerHTML = '';
}

function showSpinner() {
    document.getElementById('loadingSpinner').style.display = 'flex';
}

function hideSpinner() {
    document.getElementById('loadingSpinner').style.display = 'none';
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    document.getElementById('errorText').textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 3000);
}

function hideError() {
    const errorDiv = document.getElementById('errorMessage');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
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
    flex-direction: column;
}

.widget-controls {
    padding: 15px;
    background: #f9f9f9;
    border-bottom: 1px solid #ddd;
}

.controls-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.search-box {
    flex: 1;
    max-width: 300px;
}

.search-box input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
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

.btn-mini {
    padding: 2px 6px;
    font-size: 11px;
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.low-stock {
    background-color: #fff3cd !important;
}

.text-danger {
    color: #dc3545 !important;
    font-weight: bold;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.05);
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-error {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.close {
    float: right;
    font-size: 21px;
    font-weight: bold;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: .2;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
}

.page-size {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>

<?php include "footer.php"; ?>