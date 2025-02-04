<?php
// edit_unit.php
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
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>Home</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-align-justify"></i></span>
                        <h5>Update Unit</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editUnitForm" class="form-horizontal">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="control-group">
                                <label class="control-label">Unit Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Unit Name" name="unitname" id="unitname" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage">Unit already exists!</span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Unit updated successfully!
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
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Load unit data
async function loadUnitData() {
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/unit/read_single_unit.php?id=<?php echo $id; ?>`);
        const data = await response.json();
        
        // Check for successful response
        if (!data.success || data.status !== 200) {
            throw new Error(data.message || 'Failed to load unit data');
        }

        // Check if data exists and has the unit property
        if (data.data && data.data.unit) {
            document.getElementById('unitname').value = data.data.unit;
        } else {
            throw new Error('Unit data not found');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage(error.message || 'Error loading unit data');
    }
}

// Form submission
document.getElementById('editUnitForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const unitName = document.getElementById('unitname').value.trim();
    
    if (!unitName) {
        showErrorMessage('Unit name is required');
        return;
    }
    
    const unitData = {
        id: <?php echo $id; ?>,
        unit: unitName
    };
    
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/unit/update_unit.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(unitData)
        });
        
        const data = await response.json();
        
        // Check response status and success flag
        if (data.success && data.status === 200) {
            document.getElementById('error').style.display = 'none';
            showSuccessMessage('Unit updated successfully!');
            
            // Redirect after showing success message
            setTimeout(() => {
                window.location.href = 'add_new_unit.php';
            }, 1500);
        } else {
            throw new Error(data.message || 'Error updating unit');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage(error.message || 'Error updating unit. Please try again.');
    }
});

// Load unit data when page loads
document.addEventListener('DOMContentLoaded', loadUnitData);
</script>

<?php include "footer.php" ?>