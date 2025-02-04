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
                        <h5>Update Company</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editCompanyForm" class="form-horizontal">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="control-group">
                                <label class="control-label">Company Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Company Name" name="companyname" id="companyname" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage">Company already exists!</span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Company updated successfully!
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
// Load company data
async function loadCompanyData() {
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/company/read_single_company.php?id=<?php echo $id; ?>`);
        const data = await response.json();
        
        if (data.status === 200 && data.data) {
            document.getElementById('companyname').value = data.data.companyname;
        } else if (data.status === 404) {
            showErrorMessage('Company not found');
            setTimeout(() => {
                window.location.href = 'add_new_company.php';
            }, 1500);
        } else {
            throw new Error(data.message || 'Error loading company data');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error loading company data');
        setTimeout(() => {
            window.location.href = 'add_new_company.php';
        }, 1500);
    }
}
// Form submission
document.getElementById('editCompanyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const companyData = {
        id: <?php echo $id; ?>,
        companyname: document.getElementById('companyname').value.trim()
    };
    
    if (!companyData.companyname) {
        showErrorMessage('Company name is required');
        return;
    }
    
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/company/update_company.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(companyData)
        });

        const data = await response.json();
        
        if (data.status === 200) {
            document.getElementById('error').style.display = 'none';
            showSuccessMessage('Company updated successfully!');
            setTimeout(() => {
                window.location.href = 'add_new_company.php';
            }, 1500);
        } else if (data.status === 503) {
            showErrorMessage('Company already exists or unable to update company');
        } else {
            showErrorMessage(data.message || 'Error updating company');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error updating company. Please try again.');
    }
});

function showSuccessMessage(message) {
    const successDiv = document.getElementById('success');
    successDiv.style.display = 'block';
    successDiv.querySelector('strong').nextSibling.textContent = ' ' + message;
}

function showErrorMessage(message) {
    const errorDiv = document.getElementById('error');
    errorDiv.style.display = 'block';
    document.getElementById('errorMessage').textContent = message;
}

// Load company data when page loads
document.addEventListener('DOMContentLoaded', loadCompanyData);
</script>

<?php include "footer.php" ?>
