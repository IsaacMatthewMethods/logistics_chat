<?php
require_once 'includes/auth.php';
requireLogin();
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<?php include 'includes/header.php'; ?>

<?php if ($page === 'dashboard'): ?>
    <div class="dashboard-cards">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Total Users</h3>
                <div class="card-icon users">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="card-value">
                <?php 
                $users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc();
                echo $users['count'];
                ?>
            </div>
            <div class="card-description">Registered users</div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Customers</h3>
                <div class="card-icon users">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
            <div class="card-value">
                <?php 
                $customers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc();
                echo $customers['count'];
                ?>
            </div>
            <div class="card-description">Registered customers</div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Logistics Staff</h3>
                <div class="card-icon users">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
            <div class="card-value">
                <?php 
                $logistics = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'logistics'")->fetch_assoc();
                echo $logistics['count'];
                ?>
            </div>
            <div class="card-description">Logistics representatives</div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Shipments</h3>
                <div class="card-icon shipments">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
            <div class="card-value">
                <?php 
                $shipments = $conn->query("SELECT COUNT(*) as count FROM shipments")->fetch_assoc();
                echo $shipments['count'];
                ?>
            </div>
            <div class="card-description">Total shipments</div>
        </div>
    </div>
    
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Recent Users</h3>
            <a href="<?php echo BASE_URL; ?>admin.php?page=users" class="btn btn-primary btn-sm">
                <i class="fas fa-eye"></i> View All
            </a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
                while ($user = $users->fetch_assoc()): 
                ?>
                    <tr>
                        <td><?php echo e($user['id']); ?></td>
                        <td><?php echo e($user['username']); ?></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><?php echo ucfirst(e($user['role'])); ?></td>
                        <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm edit-user" data-id="<?php echo $user['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-danger btn-sm delete-user" data-id="<?php echo $user['id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Recent Shipments</h3>
            <a href="<?php echo BASE_URL; ?>admin.php?page=shipments" class="btn btn-primary btn-sm">
                <i class="fas fa-eye"></i> View All
            </a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Tracking #</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $shipments = $conn->query("
                    SELECT s.*, u.username as customer_name 
                    FROM shipments s 
                    JOIN users u ON s.customer_id = u.id 
                    ORDER BY s.created_at DESC 
                    LIMIT 5
                ");
                while ($shipment = $shipments->fetch_assoc()): 
                ?>
                    <tr>
                        <td><?php echo e($shipment['tracking_number']); ?></td>
                        <td><?php echo e($shipment['customer_name']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo e($shipment['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $shipment['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo e($shipment['origin']); ?></td>
                        <td><?php echo e($shipment['destination']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($shipment['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-success btn-sm update-status" data-id="<?php echo $shipment['id']; ?>">
                                <i class="fas fa-sync-alt"></i> Update
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Add New User</h3>
            <form id="addUserForm">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="customer">Customer</option>
                        <option value="logistics">Logistics</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit User</h3>
            <form id="editUserForm">
                <input type="hidden" id="edit_user_id" name="user_id">
                
                <div class="form-group">
                    <label for="edit_username" class="form-label">Username</label>
                    <input type="text" id="edit_username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email" class="form-label">Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_role" class="form-label">Role</label>
                    <select id="edit_role" name="role" class="form-control" required>
                        <option value="customer">Customer</option>
                        <option value="logistics">Logistics</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_password" name="password" class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        const modals = {
            addUser: {
                modal: document.getElementById('addUserModal'),
                btn: document.getElementById('addUserBtn'),
                close: document.querySelector('#addUserModal .close')
            },
            editUser: {
                modal: document.getElementById('editUserModal'),
                close: document.querySelector('#editUserModal .close')
            }
        };
        
        // Open/close modals
        Object.values(modals).forEach(m => {
            if (m.btn) {
                m.btn.addEventListener('click', () => m.modal.style.display = 'block');
            }
            m.close.addEventListener('click', () => m.modal.style.display = 'none');
            window.addEventListener('click', (e) => {
                if (e.target === m.modal) {
                    m.modal.style.display = 'none';
                }
            });
        });
        
        // Add user form
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_user');
            
            fetch('ajax/admin_actions.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User created successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating user');
            });
        });
        
        // Edit user buttons
        document.querySelectorAll('.edit-user').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.id;
                
                fetch(`ajax/admin_actions.php?action=get_user&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_user_id').value = data.user.id;
                        document.getElementById('edit_username').value = data.user.username;
                        document.getElementById('edit_email').value = data.user.email;
                        document.getElementById('edit_role').value = data.user.role;
                        
                        modals.editUser.modal.style.display = 'block';
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching user data');
                });
            });
        });
        
        // Edit user form
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_user');
            
            fetch('ajax/admin_actions.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User updated successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating user');
            });
        });
        
        // Delete user buttons
        document.querySelectorAll('.delete-user').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this user?')) {
                    const userId = this.dataset.id;
                    
                    fetch('ajax/admin_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete_user&user_id=${userId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('User deleted successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting user');
                    });
                }
            });
        });
        
        // Update shipment status
        document.querySelectorAll('.update-status').forEach(btn => {
            btn.addEventListener('click', function() {
                const shipmentId = this.dataset.id;
                const newStatus = prompt('Update shipment status to (pending, in_transit, delivered, cancelled):');
                
                if (newStatus && ['pending', 'in_transit', 'delivered', 'cancelled'].includes(newStatus)) {
                    fetch('ajax/admin_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_shipment&shipment_id=${shipmentId}&status=${newStatus}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to update shipment: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating shipment');
                    });
                }
            });
        });
    });
    </script>
    
<?php elseif ($page === 'users'): ?>
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">All Users</h3>
            <button id="addUserBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
                while ($user = $users->fetch_assoc()): 
                ?>
                    <tr>
                        <td><?php echo e($user['id']); ?></td>
                        <td><?php echo e($user['username']); ?></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><?php echo ucfirst(e($user['role'])); ?></td>
                        <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm edit-user" data-id="<?php echo $user['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-danger btn-sm delete-user" data-id="<?php echo $user['id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Add New User</h3>
            <form id="addUserForm">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="customer">Customer</option>
                        <option value="logistics">Logistics</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit User</h3>
            <form id="editUserForm">
                <input type="hidden" id="edit_user_id" name="user_id">
                
                <div class="form-group">
                    <label for="edit_username" class="form-label">Username</label>
                    <input type="text" id="edit_username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email" class="form-label">Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_role" class="form-label">Role</label>
                    <select id="edit_role" name="role" class="form-control" required>
                        <option value="customer">Customer</option>
                        <option value="logistics">Logistics</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_password" name="password" class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        const modals = {
            addUser: {
                modal: document.getElementById('addUserModal'),
                btn: document.getElementById('addUserBtn'),
                close: document.querySelector('#addUserModal .close')
            },
            editUser: {
                modal: document.getElementById('editUserModal'),
                close: document.querySelector('#editUserModal .close')
            }
        };
        
        // Open/close modals
        Object.values(modals).forEach(m => {
            if (m.btn) {
                m.btn.addEventListener('click', () => m.modal.style.display = 'block');
            }
            m.close.addEventListener('click', () => m.modal.style.display = 'none');
            window.addEventListener('click', (e) => {
                if (e.target === m.modal) {
                    m.modal.style.display = 'none';
                }
            });
        });
        
        // Add user form
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_user');
            
            fetch('ajax/admin_actions.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User created successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating user');
            });
        });
        
        // Edit user buttons
        document.querySelectorAll('.edit-user').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.id;
                
                fetch(`ajax/admin_actions.php?action=get_user&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_user_id').value = data.user.id;
                        document.getElementById('edit_username').value = data.user.username;
                        document.getElementById('edit_email').value = data.user.email;
                        document.getElementById('edit_role').value = data.user.role;
                        
                        modals.editUser.modal.style.display = 'block';
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching user data');
                });
            });
        });
        
        // Edit user form
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_user');
            
            fetch('ajax/admin_actions.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User updated successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating user');
            });
        });
        
        // Delete user buttons
        document.querySelectorAll('.delete-user').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this user?')) {
                    const userId = this.dataset.id;
                    
                    fetch('ajax/admin_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete_user&user_id=${userId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('User deleted successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting user');
                    });
                }
            });
        });
    });
    </script>
    
<?php elseif ($page === 'shipments'): ?>
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">All Shipments</h3>
            <button id="addShipmentBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Shipment
            </button>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tracking #</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $shipments = $conn->query("
                    SELECT s.*, u.username as customer_name 
                    FROM shipments s 
                    JOIN users u ON s.customer_id = u.id 
                    ORDER BY s.created_at DESC
                ");
                while ($shipment = $shipments->fetch_assoc()): 
                ?>
                    <tr>
                        <td><?php echo e($shipment['id']); ?></td>
                        <td><?php echo e($shipment['tracking_number']); ?></td>
                        <td><?php echo e($shipment['customer_name']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo e($shipment['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $shipment['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo e($shipment['origin']); ?></td>
                        <td><?php echo e($shipment['destination']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($shipment['created_at'])); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($shipment['updated_at'])); ?></td>
                        <td>
                            <button class="btn btn-success btn-sm update-status" data-id="<?php echo $shipment['id']; ?>">
                                <i class="fas fa-sync-alt"></i> Update
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Add Shipment Modal -->
    <div id="addShipmentModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Add New Shipment</h3>
            <form id="addShipmentForm">
                <div class="form-group">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select id="customer_id" name="customer_id" class="form-control" required>
                        <?php 
                        $customers = getCustomers();
                        foreach ($customers as $customer): 
                        ?>
                            <option value="<?php echo $customer['id']; ?>"><?php echo e($customer['username']); ?> (<?php echo e($customer['email']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tracking_number" class="form-label">Tracking Number</label>
                    <input type="text" id="tracking_number" name="tracking_number" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="origin" class="form-label">Origin</label>
                    <input type="text" id="origin" name="origin" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="destination" class="form-label">Destination</label>
                    <input type="text" id="destination" name="destination" class="form-control" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Shipment
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        const modals = {
            addShipment: {
                modal: document.getElementById('addShipmentModal'),
                btn: document.getElementById('addShipmentBtn'),
                close: document.querySelector('#addShipmentModal .close')
            }
        };
        
        // Open/close modals
        Object.values(modals).forEach(m => {
            if (m.btn) {
                m.btn.addEventListener('click', () => m.modal.style.display = 'block');
            }
            m.close.addEventListener('click', () => m.modal.style.display = 'none');
            window.addEventListener('click', (e) => {
                if (e.target === m.modal) {
                    m.modal.style.display = 'none';
                }
            });
        });
        
        // Add shipment form
        document.getElementById('addShipmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_shipment');
            
            fetch('ajax/admin_actions.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Shipment created successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating shipment');
            });
        });
        
        // Update shipment status
        document.querySelectorAll('.update-status').forEach(btn => {
            btn.addEventListener('click', function() {
                const shipmentId = this.dataset.id;
                const newStatus = prompt('Update shipment status to (pending, in_transit, delivered, cancelled):');
                
                if (newStatus && ['pending', 'in_transit', 'delivered', 'cancelled'].includes(newStatus)) {
                    fetch('ajax/admin_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_shipment&shipment_id=${shipmentId}&status=${newStatus}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to update shipment: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating shipment');
                    });
                }
            });
        });
    });
    </script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>