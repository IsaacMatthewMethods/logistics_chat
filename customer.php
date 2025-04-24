<?php
require_once 'includes/auth.php';
requireLogin();
if ($_SESSION['role'] !== 'customer') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$customer_id = $_SESSION['user_id'];
?>
<?php include 'includes/header.php'; ?>

<?php if ($page === 'dashboard'): ?>
    <div class="dashboard-cards">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">My Shipments</h3>
                <div class="card-icon shipments">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
            <div class="card-value">
                <?php 
                $shipments = getCustomerShipments($customer_id);
                echo count($shipments);
                ?>
            </div>
            <div class="card-description">Total shipments</div>
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
                $conversations = getUserConversations($customer_id);
                echo count($conversations);
                ?>
            </div>
            <div class="card-description">Ongoing conversations</div>
        </div>
    </div>
    
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Recent Shipments</h3>
            <a href="<?php echo BASE_URL; ?>customer.php?page=shipments" class="btn btn-primary btn-sm">
                <i class="fas fa-eye"></i> View All
            </a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Tracking #</th>
                    <th>Status</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $shipments = array_slice(getCustomerShipments($customer_id), 0, 5);
                foreach ($shipments as $shipment): 
                ?>
                    <tr>
                        <td><?php echo e($shipment['tracking_number']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo e($shipment['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $shipment['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo e($shipment['origin']); ?></td>
                        <td><?php echo e($shipment['destination']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($shipment['created_at'])); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>chat.php?shipment_id=<?php echo $shipment['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-comment"></i> Chat
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
<?php elseif ($page === 'shipments'): ?>
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">My Shipments</h3>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Tracking #</th>
                    <th>Status</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Date</th>
                    <th>Last Update</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (getCustomerShipments($customer_id) as $shipment): ?>
                    <tr>
                        <td><?php echo e($shipment['tracking_number']); ?></td>
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
                            <a href="<?php echo BASE_URL; ?>chat.php?shipment_id=<?php echo $shipment['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-comment"></i> Chat
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>