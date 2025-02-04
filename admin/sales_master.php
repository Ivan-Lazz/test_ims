<?php

session_start();
if (isset($_SESSION['cart'])) {
    error_log('Current cart contents: ' . print_r($_SESSION['cart'], true));
}
if(!isset($_SESSION['admin'])) {
    ?>
    <script type="text/javascript">
        window.location = "index.php";
    </script>
    <?php
}

include "header.php";
include "../user/connection.php";

// Get initial bill ID
$bill_id = 0;
$res = mysqli_query($conn, "select * from billing_header order by id desc limit 1");
while($row = mysqli_fetch_array($res)) {
    $bill_id = $row['id'];
}
?>

<div id="content">
    <form name="form1" action="" method="post" class="form-horizontal nopadding">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($_SESSION['admin']); ?>">
        <div id="content-header">
            <div id="breadcrumb">
                <a href="index.html" class="tip-bottom">
                    <i class="icon-home"></i>Sale a products
                </a>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row-fluid" style="background-color: white; min-height: 100px; padding:10px;">
                <div class="span12">
                    <div class="widget-box">
                        <div class="widget-title">
                            <span class="icon"><i class="icon-align-justify"></i></span>
                            <h5>Sale a Products</h5>
                        </div>

                        <div class="widget-content nopadding">
                            <div class="span4">
                                <br>
                                <div>
                                    <label>Full Name</label>
                                    <input type="text" class="span12" name="full_name" id="full_name" required>
                                </div>
                            </div>

                            <div class="span3">
                                <br>
                                <div>
                                    <label>Bill Type</label>
                                    <select class="span12" name="bill_type" id="bill_type">
                                        <option value="Cash">Cash</option>
                                        <option value="Debit">Debit</option>
                                    </select>
                                </div>
                            </div>

                            <div class="span2">
                                <br>
                                <div>
                                    <label>Date</label>
                                    <input type="text" class="span12" name="bill_date" id="bill_date"
                                        value="<?php echo date('Y-m-d') ?>" readonly>
                                </div>
                            </div>

                            <div class="span2">
                                <br>
                                <div>
                                    <label>Bill No</label>
                                    <input type="text" class="span12" name="bill_no" id="bill_no"
                                        value="<?php echo str_pad($bill_id + 1, 5, '0', STR_PAD_LEFT); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row-fluid" style="background-color: white; min-height: 100px; padding:10px;">
                <div class="span12">
                    <center><h4>Select A Product</h4></center>

                    <div class="span2">
                        <div>
                            <label>Product Company</label>
                            <select class="span11" name="company_name" id="company_name">
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>

                    <div class="span2">
                        <div>
                            <label>Product Name</label>
                            <select class="span11" name="product_name" id="product_name">
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>

                    <div class="span1">
                        <div>
                            <label>Unit</label>
                            <select class="span11" name="unit" id="unit">
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>

                    <div class="span2">
                        <div>
                            <label>Packing Size</label>
                            <select class="span11" name="packing_size" id="packing_size">
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>

                    <div class="span1">
                        <div>
                            <label>Price</label>
                            <input type="text" class="span11" name="price" id="price" readonly value="0">
                        </div>
                    </div>

                    <div class="span1">
                        <div>
                            <label>Enter Qty</label>
                            <input type="number" class="span11" name="qty" id="qty" value="0" min="0">
                        </div>
                    </div>

                    <div class="span1">
                        <div>
                            <label>Total</label>
                            <input type="text" class="span11" name="total" id="total" value="0" readonly>
                        </div>
                    </div>

                    <div class="span1">
                        <div>
                            <label>&nbsp;</label>
                            <button type="button" class="span11 btn btn-success" id="addToCartBtn">Add</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row-fluid" style="background-color: white; min-height: 100px; padding:10px;">
                <div class="span12">
                    <center><h4>Taken Products</h4></center>
                    <div id="cartItems"></div>

                    <h4>
                        <div style="float: right">
                            <span style="float:left;">Total:&#8369;</span>
                            <span style="float: left" id="cartTotal">0</span>
                        </div>
                    </h4>

                    <br><br><br><br>

                    <center>
                        <button type="button" id="generateBillBtn" class="btn btn-success">Generate Bill</button>
                    </center>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize existing functionality
    loadCompanies();
    loadCart(); // Load initial cart state
    
    // Add event listener for Add to Cart button
    document.getElementById('addToCartBtn').addEventListener('click', addToCart);
    
    // Other existing event listeners
    document.getElementById('company_name').addEventListener('change', function() {
        if (this.value) {
            loadProducts(this.value);
            resetFields(['product_name', 'unit', 'packing_size', 'price', 'qty', 'total']);
        }
    });

    document.getElementById('product_name').addEventListener('change', function() {
        if (this.value) {
            const company_name = document.getElementById('company_name').value;
            loadUnits(this.value, company_name);
            resetFields(['unit', 'packing_size', 'price', 'qty', 'total']);
        }
    });

    document.getElementById('unit').addEventListener('change', function() {
        if (this.value) {
            const company_name = document.getElementById('company_name').value;
            const product_name = document.getElementById('product_name').value;
            loadPackingSizes(this.value, product_name, company_name);
            resetFields(['packing_size', 'price', 'qty', 'total']);
        }
    });

    document.getElementById('packing_size').addEventListener('change', function() {
        if (this.value) {
            loadPrice();
            resetFields(['qty', 'total']);
        }
    });

    document.getElementById('qty').addEventListener('input', calculateTotal);
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Alt + A to add to cart
        if (e.altKey && e.key === 'a') {
            e.preventDefault();
            addToCart();
        }
        
        // Alt + G to generate bill
        if (e.altKey && e.key === 'g') {
            e.preventDefault();
            if (validateCart()) {
                generateBill();
            }
        }
    });
    
    // Add form submission prevention
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        return false;
    });
});

// Loading companies
async function loadCompanies() {
    showSpinner();
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/company/get_companies.php');
        const result = await response.json();
        
        if (!result.data) {
            throw new Error(result.message || 'Failed to load companies');
        }

        const select = document.getElementById('company_name');
        select.innerHTML = '<option value="">Select</option>';
        
        result.data.forEach(company => {
            const option = document.createElement('option');
            option.value = company.companyname;
            option.textContent = company.companyname;
            select.appendChild(option);
        });
    } catch (error) {
        showErrorMessage('Error loading companies: ' + error.message);
    } finally {
        hideSpinner();
    }
}


// Load products based on company
async function loadProducts(company_name) {
    showSpinner();
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/product/get_products_by_company.php?company_name=${encodeURIComponent(company_name)}`);
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to load products');
        }

        const select = document.getElementById('product_name');
        select.innerHTML = '<option value="">Select</option>';
        
        if (result.data && result.data.length > 0) {
            result.data.forEach(product => {
                const option = document.createElement('option');
                option.value = product.product_name;
                option.textContent = product.product_name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        showErrorMessage('Error loading products: ' + error.message);
    } finally {
        hideSpinner();
    }
}

// Load units
async function loadUnits(product_name, company_name) {
    showSpinner();
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/unit/get_units_by_product.php?product_name=${encodeURIComponent(product_name)}&company_name=${encodeURIComponent(company_name)}`);
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to load units');
        }

        const select = document.getElementById('unit');
        select.innerHTML = '<option value="">Select</option>';
        
        if (result.data && result.data.length > 0) {
            result.data.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.unit;
                option.textContent = unit.unit;
                select.appendChild(option);
            });
        }
    } catch (error) {
        showErrorMessage('Error loading units: ' + error.message);
    } finally {
        hideSpinner();
    }
}

// Load packing sizes
async function loadPackingSizes(unit, product_name, company_name) {
    showSpinner();
    try {
        const response = await fetch(
            `http://localhost/imsfin/IMS_API/api/product/get_packing_sizes.php?unit=${encodeURIComponent(unit)}&product_name=${encodeURIComponent(product_name)}&company_name=${encodeURIComponent(company_name)}`
        );
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to load packing sizes');
        }

        const select = document.getElementById('packing_size');
        select.innerHTML = '<option value="">Select</option>';
        
        if (result.data && result.data.length > 0) {
            result.data.forEach(size => {
                const option = document.createElement('option');
                option.value = size.packing_size;
                option.textContent = size.packing_size;
                select.appendChild(option);
            });
        }
    } catch (error) {
        showErrorMessage('Error loading packing sizes: ' + error.message);
    } finally {
        hideSpinner();
    }
}

// Load price
async function loadPrice() {
    showSpinner();
    try {
        const company_name = document.getElementById('company_name').value;
        const product_name = document.getElementById('product_name').value;
        const unit = document.getElementById('unit').value;
        const packing_size = document.getElementById('packing_size').value;

        const response = await fetch(
            `http://localhost/imsfin/IMS_API/api/product/get_product_price.php?company_name=${encodeURIComponent(company_name)}&product_name=${encodeURIComponent(product_name)}&unit=${encodeURIComponent(unit)}&packing_size=${encodeURIComponent(packing_size)}`
        );
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to load price');
        }

        document.getElementById('price').value = result.data.price;
        calculateTotal();
    } catch (error) {
        showErrorMessage('Error loading price: ' + error.message);
    } finally {
        hideSpinner();
    }
}


// Calculate total
function calculateTotal() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const qty = parseFloat(document.getElementById('qty').value) || 0;
    document.getElementById('total').value = (price * qty).toFixed(2);
}

// Add to cart
async function addToCart() {
    showSpinner();
    try {
        // Get form values
        const cartData = {
            company_name: document.getElementById('company_name').value,
            product_name: document.getElementById('product_name').value,
            unit: document.getElementById('unit').value,
            packing_size: document.getElementById('packing_size').value,
            price: parseFloat(document.getElementById('price').value),
            qty: parseInt(document.getElementById('qty').value)
        };

        // Validate all fields
        for (const [key, value] of Object.entries(cartData)) {
            if (!value || value === 0) {
                throw new Error(`Please select/enter ${key.replace('_', ' ')}`);
            }
        }

        // Validate quantity
        if (cartData.qty <= 0) {
            throw new Error('Quantity must be greater than 0');
        }

        // Make API request
        const response = await fetch('http://localhost/imsfin/IMS_API/api/sales/cart/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(cartData)
        });

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to add to cart');
        }

        showSuccessMessage('Product added to cart successfully');
        await loadCart(); // Reload cart items
        resetProductForm(); // Reset form fields
    } catch (error) {
        showErrorMessage(error.message);
    } finally {
        hideSpinner();
    }
}

function resetFields(fields) {
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element.tagName === 'SELECT') {
            element.innerHTML = '<option value="">Select</option>';
        } else if (element.tagName === 'INPUT') {
            element.value = field === 'qty' ? '0' : '';
        }
    });
}

// Load cart items
async function loadCart() {
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/sales/cart/get_cart_items.php');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to load cart');
        }

        const cartHtml = generateCartHtml(result.data);
        document.getElementById('cartItems').innerHTML = cartHtml;
        
        // Load cart total
        const totalResponse = await fetch('http://localhost/imsfin/IMS_API/api/sales/cart/get_cart_total.php');
        const totalResult = await totalResponse.json();
        
        if (!totalResult.success) {
            throw new Error(totalResult.message || 'Failed to load cart total');
        }

        document.getElementById('cartTotal').textContent = formatNumber(totalResult.data.total);
    } catch (error) {
        showErrorMessage('Error loading cart: ' + error.message);
    }
}

// Generate cart HTML
function generateCartHtml(items) {
    if (!items || !items.length) {
        return '<p>Cart is empty</p>';
    }

    return `
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product Company</th>
                    <th>Product Name</th>
                    <th>Unit</th>
                    <th>Packing Size</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                ${items.map(item => {
                    const price = parseFloat(item.price);
                    const qty = parseFloat(item.qty);
                    const total = price * qty;
                    
                    return `
                        <tr>
                            <td>${item.company_name}</td>
                            <td>${item.product_name}</td>
                            <td>${item.unit}</td>
                            <td>${item.packing_size}</td>
                            <td>₱${formatNumber(price)}</td>
                            <td>
                                <input type="number" min="1" value="${qty}" 
                                    onchange="updateCartItem(${item.session_id}, this.value)">
                            </td>
                            <td>₱${formatNumber(total)}</td>
                            <td>
                                <button onclick="deleteCartItem(${item.session_id})" 
                                        class="btn btn-danger btn-small">
                                    <i class="icon-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('')}
            </tbody>
        </table>
    `;
}

function formatNumber(number) {
    return parseFloat(number).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Update cart item
async function updateCartItem(sessionId, qty) {
    showSpinner();
    try {
        if (qty <= 0) {
            throw new Error('Quantity must be greater than 0');
        }

        const response = await fetch('http://localhost/imsfin/IMS_API/api/sales/cart/update_cart_item.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                session_id: sessionId,
                qty: parseInt(qty)
            })
        });

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to update cart');
        }

        showSuccessMessage('Cart updated successfully');
        await loadCart();
    } catch (error) {
        showErrorMessage(error.message);
        await loadCart(); // Reload cart to reset quantities
    } finally {
        hideSpinner();
    }
}

// Delete cart item
async function deleteCartItem(sessionId) {
    if (!confirm('Are you sure you want to remove this item?')) {
        return;
    }

    showSpinner();
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/sales/cart/delete_cart_item.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                session_id: sessionId
            })
        });

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to delete item');
        }

        showSuccessMessage('Item removed from cart');
        await loadCart();
    } catch (error) {
        showErrorMessage(error.message);
    } finally {
        hideSpinner();
    }
}

// Generate bill
async function generateBill() {
    try {
        const fullName = document.getElementById('full_name').value.trim();
        if (!fullName) {
            throw new Error('Please enter full name');
        }

        // Validate cart
        const cartTable = document.getElementById('cartItems');
        if (!cartTable || cartTable.innerHTML.includes('Cart is empty')) {
            throw new Error('Please add items to the cart before generating bill');
        }

        const username = document.querySelector('input[name="username"]').value;
        const billData = {
            full_name: fullName,
            bill_type: document.getElementById('bill_type').value,
            bill_no: document.getElementById('bill_no').value,
            username: username
        };

        const submitButton = document.getElementById('generateBillBtn');
        submitButton.disabled = true;
        showSpinner();

        const response = await fetch('http://localhost/imsfin/IMS_API/api/sales/bills/create_bill.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(billData)
        });

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to generate bill');
        }

        showSuccessMessage('Bill generated successfully');
        window.location.href = 'view_bills.php';

    } catch (error) {
        showErrorMessage(error.message);
    } finally {
        hideSpinner();
        const submitButton = document.getElementById('generateBillBtn');
        submitButton.disabled = false;
    }
}

function validateCart() {
    const cartTable = document.getElementById('cartItems');
    if (!cartTable || cartTable.innerHTML.includes('Cart is empty')) {
        alert('Cart is empty. Please add items before generating bill.');
        return false;
    }
    return true;
}

// Update the button click handler
document.getElementById('generateBillBtn').onclick = function(e) {
    e.preventDefault();
    if (validateCart()) {
        generateBill();
    }
};
// Reset product form
function resetProductForm() {
    document.getElementById('product_name').innerHTML = '<option value="">Select</option>';
    document.getElementById('unit').innerHTML = '<option value="">Select</option>';
    document.getElementById('packing_size').innerHTML = '<option value="">Select</option>';
    document.getElementById('price').value = '0';
    document.getElementById('qty').value = '0';
    document.getElementById('total').value = '0';
    document.getElementById('company_name').value = '';
}

// Reset entire form
function resetForm() {
    document.getElementById('full_name').value = '';
    document.getElementById('bill_type').value = 'Cash';
    resetProductForm();
    
    // Generate new bill number
    const currentBillNo = parseInt(document.getElementById('bill_no').value);
    document.getElementById('bill_no').value = String(currentBillNo + 1).padStart(5, '0');
}

// Add error handling for network issues
window.addEventListener('online', function() {
    loadCart(); // Reload cart when connection is restored
});

window.addEventListener('offline', function() {
    alert('Internet connection lost. Please check your connection.');
});

// Add validation functions
function validateQty(qty) {
    return qty > 0;
}

function validatePrice(price) {
    return price > 0;
}

// Add helper functions
function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2);
}

// Add these helper functions for displaying messages
function showSuccessMessage(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'alert alert-success';
    successDiv.style.position = 'fixed';
    successDiv.style.top = '20px';
    successDiv.style.right = '20px';
    successDiv.style.zIndex = '9999';
    successDiv.textContent = message;
    document.body.appendChild(successDiv);
    setTimeout(() => {
        successDiv.remove();
    }, 1500);
}

function showErrorMessage(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.style.position = 'fixed';
    errorDiv.style.top = '20px';
    errorDiv.style.right = '20px';
    errorDiv.style.zIndex = '9999';
    errorDiv.textContent = message;
    document.body.appendChild(errorDiv);
    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}


// Add the necessary CSS
const styles = `
    .alert {
        padding: 15px;
        border: 1px solid transparent;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
`;

// Add styles to the document
const styleSheet = document.createElement('style');
styleSheet.textContent = styles;
document.head.appendChild(styleSheet);

function showSpinner() {
    document.getElementById('loadingSpinner').style.display = 'flex';
}

function hideSpinner() {
    document.getElementById('loadingSpinner').style.display = 'none';
}

// Add form validation
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent actual form submission
    return false;
});

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Alt + A to add to cart
    if (e.altKey && e.key === 'a') {
        e.preventDefault();
        document.getElementById('addToCartBtn').click();
    }
    
    // Alt + G to generate bill
    if (e.altKey && e.key === 'g') {
        e.preventDefault();
        document.getElementById('generateBillBtn').click();
    }
});
</script>

<style>
/* Add some styling for better UX */
.loading {
    opacity: 0.5;
    pointer-events: none;
}

.table input[type="number"] {
    width: 60px;
}

.alert {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
}

.btn-small {
    padding: 2px 6px;
    font-size: 11px;
}

/* Add loading spinner */
.spinner {
    display: none;
    width: 40px;
    height: 40px;
    position: fixed;
    top: 50%;
    left: 50%;
    margin-top: -20px;
    margin-left: -20px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Add loading spinner element -->
<div id="loadingSpinner" class="spinner"></div>
<?php
include "footer.php";
?>