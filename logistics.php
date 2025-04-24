<?php
require_once 'includes/auth.php';
requireLogin();
if ($_SESSION['role'] !== 'logistics') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$user_id = $_SESSION['user_id'];
?>
<?php include 'includes/header.php'; ?>

<?php if ($page === 'dashboard'): ?>
    <div class="dashboard-cards">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assigned Shipments</h3>
                <div class="card-icon shipments">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
            <div class="card-value">
                <?php 
                $shipments = getAllShipments();
                echo count($shipments);
                ?>
            </div>
            <div class="card-description">Total shipments to manage</div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Active Chats</h3>
                <div class="card-icon messages">
                    <i class="fas fa-comments"></i>
                </div>
            </div>
            <div class="card-value">
                <?php 
                $conversations = getUserConversations($user_id);
                echo count($conversations);
                ?>
            </div>
            <div class="card-description">Ongoing conversations</div>
        </div>
    </div>
    
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Recent Shipments</h3>
            <a href="<?php echo BASE_URL; ?>logistics.php?page=shipments" class="btn btn-primary btn-sm">
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
                $shipments = array_slice(getAllShipments(), 0, 5);
                foreach ($shipments as $shipment): 
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
                            <a href="<?php echo BASE_URL; ?>chat.php?shipment_id=<?php echo $shipment['id']; ?>&customer_id=<?php echo $shipment['customer_id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-comment"></i> Chat
                            </a>
                            <button class="btn btn-success btn-sm update-status" data-id="<?php echo $shipment['id']; ?>">
                                <i class="fas fa-sync-alt"></i> Update
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
    
<?php elseif ($page === 'shipments'): ?>
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">All Shipments</h3>
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
                    <th>Last Update</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (getAllShipments() as $shipment): ?>
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
                        <td><?php echo date('M d, Y', strtotime($shipment['updated_at'])); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>chat.php?shipment_id=<?php echo $shipment['id']; ?>&customer_id=<?php echo $shipment['customer_id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-comment"></i> Chat
                            </a>
                            <button class="btn btn-success btn-sm update-status" data-id="<?php echo $shipment['id']; ?>">
                                <i class="fas fa-sync-alt"></i> Update
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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