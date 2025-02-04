<?php

session_start();
if(!isset($_SESSION['admin'])){
    ?>
    <script type="text/javascript">
        window.location= "index.php";
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
                    <div class="widget-title"> <span class="icon"> <i class="icon-align-justify"></i> </span>
                        <h5>Update User</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="editUserForm" class="form-horizontal" name="form1">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="control-group">
                                <label class="control-label">First Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="First name" name="firstname" id="firstname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Last Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Last name" name="lastname" id="lastname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Username :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Username" name="username" id="username" readonly/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Password</label>
                                <div class="controls">
                                    <input type="password" class="span11" placeholder="Enter Password" name="password" id="password" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Select Role</label>
                                <div class="controls">
                                    <select name="role" class="span11" id="role">
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Select Status</label>
                                <div class="controls">
                                    <select name="status" class="span11" id="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> User updated successfully!
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
// Helper function for showing errors
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.innerHTML = `
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Error!</strong> ${message}
    `;
    const form = document.getElementById('editUserForm');
    form.parentNode.insertBefore(errorDiv, form);
    setTimeout(() => errorDiv.remove(), 3000);
}

// Load user data
async function loadUserData() {
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/user/read_single_user.php?id=<?php echo $id; ?>`);
        const result = await response.json();
        
        if (result.success && result.status_code === 200 && result.data) {
            const userData = result.data;
            // Set form values
            document.getElementById('firstname').value = userData.firstname || '';
            document.getElementById('lastname').value = userData.lastname || '';
            document.getElementById('username').value = userData.username || '';
            document.getElementById('password').value = '';
            document.getElementById('password').placeholder = 'Leave empty to keep current password';
            document.getElementById('role').value = userData.role || 'user';
            document.getElementById('status').value = userData.status || 'active';
        } else {
            throw new Error(result.message || 'Failed to load user data');
        }
    } catch (error) {
        showError(error.message || 'Error loading user data');
        setTimeout(() => {
            window.location.href = 'add_new_user.php';
        }, 3000);
    }
}

// Update user form submission handler
document.getElementById('editUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        // Validate required fields
        const firstname = document.getElementById('firstname').value.trim();
        const lastname = document.getElementById('lastname').value.trim();
        const username = document.getElementById('username').value.trim();

        if (!firstname || !lastname || !username) {
            throw new Error('Please fill all required fields');
        }

        // Gather form data
        const userData = {
            id: document.querySelector('input[name="id"]').value,
            firstname: firstname,
            lastname: lastname,
            username: username,
            role: document.getElementById('role').value,
            status: document.getElementById('status').value
        };

        // Add password only if provided
        const password = document.getElementById('password').value.trim();
        if (password) {
            userData.password = password;
        }
        
        const response = await fetch('http://localhost/imsfin/IMS_API/api/user/update_user.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        const result = await response.json();
        
        if (response.ok && result.status === 200) {
            // Show success message
            const successDiv = document.getElementById('success');
            successDiv.style.display = 'block';
            successDiv.scrollIntoView({ behavior: 'smooth' });
            
            // Redirect after success
            setTimeout(() => {
                window.location.href = 'add_new_user.php';
            }, 1500);
        } else {
            throw new Error(result.message || 'Failed to update user');
        }
    } catch (error) {
        showError(error.message);
    }
});

// Add form validation
const form = document.getElementById('editUserForm');
const inputs = form.querySelectorAll('input[required]');
inputs.forEach(input => {
    input.addEventListener('invalid', function(e) {
        e.preventDefault();
        this.classList.add('error');
    });
    
    input.addEventListener('input', function() {
        this.classList.remove('error');
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadUserData();
    
    // Add input validation
    const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.trim();
        });
    });
});

// Add styles
const styles = `
    .error {
        border-color: #dc3545 !important;
    }
    
    .alert {
        margin-bottom: 15px;
    }
    
    .success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = styles;
document.head.appendChild(styleSheet);
</script>

<?php include "footer.php" ?>