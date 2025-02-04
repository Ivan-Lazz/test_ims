<?php

session_start();
if(!isset($_SESSION['admin'])){
    ?>
    <script type="text/javascript">
        window.location= "index.php";
    </script>
    <?php
}
// include "../user/connection.php";
include "header.php";
?>

<!--main-container-part-->
<div id="content">
    <div id="content-header">
        <div id="breadcrumb"><a href="index.html" class="tip-bottom"><i class="icon-home"></i>
            Add User</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid" style="background-color: white; min-height: 1000px; padding:10px;">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title"> <span class="icon"> <i class="icon-align-justify"></i> </span>
                        <h5>Add New User</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form id="addUserForm" class="form-horizontal" name="form1">
                            <div class="control-group">
                                <label class="control-label">First Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="First Name" name="firstname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Last Name :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Last Name" name="lastname" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Username :</label>
                                <div class="controls">
                                    <input type="text" class="span11" placeholder="Username" name="username" required/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Password</label>
                                <div class="controls">
                                    <input type="password" class="span11" placeholder="Enter Password" name="password" required />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Select Role</label>
                                <div class="controls">
                                    <select name="role" class="span11">
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="alert alert-danger alert-dismissible" style="display:none" id="error">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Error!</strong> <span id="errorMessage">Username already taken!</span>
                            </div>
                            <div class="alert alert-success alert-dismissible" style="display:none" id="success">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Success!</strong> User registered successfully!
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="widget-content nopadding">
                    <div class="alert alert-danger alert-dismissible" style="display:none" id="nodata">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Error!</strong> No Data Found!
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                        </tbody>
                    </table>
                    <div id="paginationContainer"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
const recordsPerPage = 10;
// Function to load users
async function loadUsers() {
    try {
        const response = await fetch(`http://localhost/imsfin/IMS_API/api/user/read_users.php?page=${currentPage}&per_page=${recordsPerPage}`);
        const data = await response.json();
        
        const tbody = document.getElementById('userTableBody');
        tbody.innerHTML = '';
        
        // Check both status code and success flag
        if (data.status === 200 && data.success && data.records && data.records.length > 0) {
            data.records.forEach(user => {
                tbody.innerHTML += `
                    <tr>
                        <td>${user.firstname}</td>
                        <td>${user.lastname}</td>
                        <td>${user.username}</td>
                        <td class="center">${user.role}</td>
                        <td class="center">${user.status}</td>
                        <td><center><a href="edit_user.php?id=${user.id}" class="text-success">Edit</a></center></td>
                        <td><center><a href="#" onclick="deleteUser(${user.id})" class="text-error">Delete</a></center></td>
                    </tr>
                `;
            });

            if (data.pagination && data.pagination.total_pages > 1) {
                renderPagination(data.pagination);
            } else {
                document.getElementById('paginationContainer').innerHTML = '';
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No users found</td></tr>';
            document.getElementById('paginationContainer').innerHTML = '';
            document.getElementById('nodata').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('nodata').style.display = 'block';
    }
}


function renderPagination(pagination) {
    const container = document.getElementById('paginationContainer');
    let html = `
        <div class="pagination-container">
            <div class="dataTables_info">
                Showing ${((pagination.current_page - 1) * pagination.records_per_page) + 1} to 
                ${Math.min(pagination.current_page * pagination.records_per_page, pagination.total_records)} 
                of ${pagination.total_records} entries
            </div>
            <div class="dataTables_paginate">
                <button class="btn" onclick="changePage(1)" ${pagination.current_page === 1 ? 'disabled' : ''}>
                    First
                </button>
                <button class="btn" onclick="changePage(${pagination.current_page - 1})" ${pagination.current_page === 1 ? 'disabled' : ''}>
                    Previous
                </button>
                <span class="page-numbers">`;

    for (let i = Math.max(1, pagination.current_page - 2); 
         i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
        html += `
            <button class="btn ${i === pagination.current_page ? 'btn-info' : ''}" onclick="changePage(${i})">
                ${i}
            </button>`;
    }

    html += `
                </span>
                <button class="btn" onclick="changePage(${pagination.current_page + 1})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                    Next
                </button>
                <button class="btn" onclick="changePage(${pagination.total_pages})" 
                    ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
                    Last
                </button>
            </div>
        </div>`;

    container.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    loadUsers();
}

function changePage(page) {
    currentPage = page;
    loadUsers();
}

// Function to delete user
async function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }

    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/user/delete_user.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.status === 200 && data.message === "User was deleted.") {
            currentPage = 1; // Reset to first page
            loadUsers();
        } else {
            alert(data.message || 'Failed to delete user');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting the user');
    }
}

// Form submission handler
document.getElementById('addUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const userData = {};
    formData.forEach((value, key) => userData[key] = value);
    
    try {
        const response = await fetch('http://localhost/imsfin/IMS_API/api/user/create_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        const data = await response.json();
        
        if (data.status === 201 && data.message === "User was created.") {
            document.getElementById('error').style.display = 'none';
            document.getElementById('success').style.display = 'block';
            this.reset();
            currentPage = 1; // Reset to first page
            loadUsers();
            setTimeout(() => {
                document.getElementById('success').style.display = 'none';
            }, 1500);
        } else {
            document.getElementById('success').style.display = 'none';
            document.getElementById('errorMessage').textContent = data.message;
            document.getElementById('error').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('success').style.display = 'none';
        document.getElementById('errorMessage').textContent = 'An error occurred. Please try again.';
        document.getElementById('error').style.display = 'block';
    }
});

// Load users when page loads
document.addEventListener('DOMContentLoaded', loadUsers);
</script>


<style>
.loading-spinner {
    text-align: center;
    padding: 20px;
    background-color: #f9f9f9;
    border-bottom: 1px solid #ddd;
}

.pagination-container {
    margin-top: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background-color: #f9f9f9;
    border-top: 1px solid #ddd;
}

.dataTables_info {
    color: #666;
    padding: 8px 0;
}

.dataTables_paginate {
    text-align: right;
}

.dataTables_paginate .btn {
    margin: 0 2px;
    padding: 4px 10px;
    border: 1px solid #ddd;
}

.dataTables_paginate .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f5f5f5;
}

.page-numbers {
    margin: 0 10px;
    display: inline-block;
}

.page-numbers .btn {
    min-width: 35px;
}

.btn-info {
    color: #ffffff;
    background-color: #49afcd;
    border-color: #2f96b4;
}
</style>
<?php include "footer.php" ?>