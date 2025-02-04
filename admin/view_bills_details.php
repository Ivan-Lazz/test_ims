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
$id = $_GET["id"];
?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="#" class="tip-bottom">
                <i class="icon-home"></i>Detailed Bills
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <center><h4>Detailed Bills</h4></center>
            
            <div id="loadingSpinner" class="spinner" style="display: none;">
                <div class="spinner-content">Loading...</div>
            </div>
            <div id="errorMessage" class="alert alert-error" style="display: none;">
                <button class="close" data-dismiss="alert">×</button>
                <span></span>
            </div>
            <div id="successMessage" class="alert alert-success" style="display: none;">
                <button class="close" data-dismiss="alert">×</button>
                <span></span>
            </div>
            <div id="billDetailsContainer"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadBillDetails();
});

async function loadBillDetails() {
    showSpinner();
    try {
        const response = await fetch('http://localhost/StockSyncz/IMS_API/api/sales/bills/get_bill_details.php?id=<?php echo $id; ?>');
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to load bill details');
        }

        displayBillDetails(result.data);
    } catch (error) {
        console.error('Error:', error);
        showError('Error loading bill details: ' + error.message);
    } finally {
        hideSpinner();
    }
}

async function processReturn(id) {
    if (!confirm('Are you sure you want to process this return?')) {
        return;
    }

    showSpinner();
    hideMessages();

    try {
        const response = await fetch('http://localhost/StockSyncz/IMS_API/api/sales/return/process_return.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to process return');
        }

        showSuccess('Product return processed successfully');
        // Reload the bill details to reflect the changes
        await loadBillDetails();
    } catch (error) {
        console.error('Error:', error);
        showError('Error processing return: ' + error.message);
    } finally {
        hideSpinner();
    }
}

function displayBillDetails(data) {
    const container = document.getElementById('billDetailsContainer');
    
    if (!data.header || !data.details) {
        container.innerHTML = '<div class="alert alert-error">No bill data available</div>';
        return;
    }

    const header = data.header;
    const details = data.details;

    // Format the date
    const formattedDate = header.date ? new Date(header.date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }) : '';

    // Display bill header information
    let html = `
        <div class="bill-header">
            <table class="table">
                <tr>
                    <td width="150"><strong>Bill No:</strong></td>
                    <td>${header.bill_no || ''}</td>
                </tr>
                <tr>
                    <td><strong>Generated By:</strong></td>
                    <td>${header.username || ''}</td>
                </tr>
                <tr>
                    <td><strong>Full Name:</strong></td>
                    <td>${header.full_name || ''}</td>
                </tr>
                <tr>
                    <td><strong>Bill Type:</strong></td>
                    <td>${header.bill_type || ''}</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td>${formattedDate}</td>
                </tr>
            </table>
        </div>

        <br>

        <div class="bill-details">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product Company</th>
                        <th>Product Name</th>
                        <th>Product Unit</th>
                        <th>Packaging Size</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
    `;

    // Display bill details
    if (details && details.length > 0) {
        details.forEach(detail => {
            const itemTotal = parseFloat(detail.price) * parseFloat(detail.qty);
            html += `
                <tr>
                    <td>${detail.product_company || ''}</td>
                    <td>${detail.product_name || ''}</td>
                    <td>${detail.product_unit || ''}</td>
                    <td>${detail.packaging_size || ''}</td>
                    <td>₱${formatNumber(detail.price)}</td>
                    <td>${detail.qty}</td>
                    <td>₱${formatNumber(itemTotal)}</td>
                    <td>
                        <button onclick="processReturn(${detail.id})" 
                                class="btn btn-danger btn-mini">
                            <i class="icon-undo"></i> Return
                        </button>
                    </td>
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="8" class="text-center">No items found in this bill</td>
            </tr>
        `;
    }

    html += `
                </tbody>
            </table>

            <div class="bill-total">
                <h4 style="text-align: right; margin-top: 20px;">
                    Grand Total: ₱${formatNumber(data.total)}
                </h4>
            </div>
        </div>
    `;

    container.innerHTML = html;
}

function formatNumber(number) {
    return parseFloat(number).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
}

function showSuccess(message) {
    const successDiv = document.getElementById('successMessage');
    successDiv.style.display = 'block';
    successDiv.querySelector('span').textContent = message;
}

function hideMessages() {
    document.getElementById('errorMessage').style.display = 'none';
    document.getElementById('successMessage').style.display = 'none';
}
</script>

<style>
.bill-header {
    margin-bottom: 20px;
    border: 1px solid #ddd;
    padding: 15px;
    background-color: #f9f9f9;
}

.bill-header table {
    margin-bottom: 0;
}

.bill-header td {
    padding: 5px;
}

.bill-details {
    margin-top: 20px;
}

.bill-total {
    margin-top: 20px;
    padding: 10px;
    border-top: 2px solid #ddd;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.05);
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

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

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

.btn-mini {
    padding: 2px 6px;
    font-size: 11px;
}

.text-center {
    text-align: center;
}
</style>

<?php include 'footer.php'; ?>