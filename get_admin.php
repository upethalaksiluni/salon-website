<?php 
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("
            SELECT id, username, fullname, email, phone, status, last_login, created_at
            FROM admin 
            WHERE id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Prepare HTML for modal
            $html = '
            <div class="modal-header">
                <h5 class="modal-title">Edit Administrator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAdminForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="admin_id" value="'.$admin['id'].'">
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="'.$admin['username'].'" readonly>
                        <small class="text-muted">Username cannot be changed</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="fullname" value="'.$admin['fullname'].'" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="'.$admin['email'].'" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone" value="'.$admin['phone'].'">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active" '.($admin['status'] === 'active' ? 'selected' : '').'>Active</option>
                            <option value="inactive" '.($admin['status'] === 'inactive' ? 'selected' : '').'>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Admin</button>
                </div>
            </form>';

            echo json_encode(['success' => true, 'html' => $html]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Admin not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>