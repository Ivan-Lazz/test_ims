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
                        <h5>Update Party</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editPartyForm" class="form-horizontal">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="control-group">
                                <label class="control-label">First Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="First Name" name="firstname" id="firstname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Last Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Last Name" name="lastname" id="lastname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Business Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Business Name" name="businessname" id="businessname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Contact No. :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Contact No." name="contact" id="contact" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Address :</label>
                                <div class="controls">
                                    <textarea name="address" class="span11" id="address" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">City :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="City" name="city" id="city" required/>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage"></span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> Party updated successfully!
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
// Load party data
async function loadPartyData() {
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/party/read_single_party.php?id=<?php echo $id; ?>`);
        const data = await response.json();
        
        if (data.status === 200 && data.data) {
            document.getElementById('firstname').value = data.data.firstname;
            document.getElementById('lastname').value = data.data.lastname;
            document.getElementById('businessname').value = data.data.businessname;
            document.getElementById('contact').value = data.data.contact;
            document.getElementById('address').value = data.data.address || '';
            document.getElementById('city').value = data.data.city;
        } else if (data.status === 404) {
            showErrorMessage('Party not found');
            setTimeout(() => {
                window.location.href = 'add_new_party.php';
            }, 1500);
        } else {
            throw new Error(data.message || 'Error loading party data');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('Error loading party data');
        setTimeout(() => {
            window.location.href = 'add_new_party.php';
        }, 1500);
    }
}

// Form submission
document.getElementById('editPartyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const partyData = {};
    formData.forEach((value, key) => partyData[key] = value.trim());
    
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/party/update_party.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(partyData)
        });

        const data = await response.json();
        
        if (data.status === 200) {
            showSuccessMessage('Party updated successfully!');
            setTimeout(() => {
                window.location.href = 'add_new_party.php';
            }, 1500);
        } else if (data.status === 503) {
            showErrorMessage('Unable to update party');
        } else {
            showErrorMessage(data.message || 'Error updating party');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorMessage('An error occurred. Please try again.');
    }
});

function showSuccessMessage(message) {
    const successDiv = document.getElementById('success');
    successDiv.style.display = 'block';
    successDiv.querySelector('strong').nextSibling.textContent = ' ' + message;
}

function showErrorMessage(message) {
    const errorDiv = document.getElementById('error');
    document.getElementById('errorMessage').textContent = message;
    errorDiv.style.display = 'block';
}

// Load party data when page loads
document.addEventListener('DOMContentLoaded', loadPartyData);
</script>

<?php include "footer.php" ?>