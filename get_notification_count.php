<?php
class NotificationHandler {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createNotification($userId, $receiverType, $type, $title, $content, $relatedId = null) {
        $query = "INSERT INTO notifications 
                  (user_id, receiver_type, notification_type, title, content, related_id) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                $userId, 
                $receiverType, 
                $type, 
                $title, 
                $content, 
                $relatedId
            ]);
        } catch (Exception $e) {
            error_log("Notification creation error: " . $e->getMessage());
            return false;
        }
    }

    public function getUnreadCount($receiverType, $userId = null) {
        $query = "SELECT COUNT(*) FROM notifications 
                  WHERE receiver_type = ? AND is_read = 0";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            if ($userId !== null) {
                $query .= " AND (user_id = ? OR user_id IS NULL)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$receiverType, $userId]);
            } else {
                $stmt->execute([$receiverType]);
            }
            
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Unread count error: " . $e->getMessage());
            return 0;
        }
    }

    public function markAsRead($notificationId, $userId = null) {
        $query = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        
        try {
            $stmt = $this->conn->prepare($query);
            
            if ($userId !== null) {
                $query .= " AND user_id = ?";
                $stmt = $this->conn->prepare($query);
                return $stmt->execute([$notificationId, $userId]);
            }
            
            return $stmt->execute([$notificationId]);
        } catch (Exception $e) {
            error_log("Mark as read error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserNotifications($receiverType, $userId = null, $limit = 10) {
        $query = "SELECT n.*, 
                         a.appointment_date, 
                         a.appointment_time, 
                         u.fullname as sender_name
                  FROM notifications n
                  LEFT JOIN appointment a ON n.related_id = a.id
                  LEFT JOIN user u ON n.user_id = u.id
                  WHERE n.receiver_type = ?";
        
        try {
            if ($userId !== null) {
                $query .= " AND (n.user_id = ? OR n.user_id IS NULL)";
                $query .= " ORDER BY n.created_at DESC LIMIT ?";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$receiverType, $userId, $limit]);
            } else {
                $query .= " ORDER BY n.created_at DESC LIMIT ?";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$receiverType, $limit]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get notifications error: " . $e->getMessage());
            return [];
        }
    }
}
?>

<?php
session_start();
include "db_connect.php";
include "notification_handler.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

try {
    $notificationHandler = new NotificationHandler($conn);
    $receiverType = isset($_SESSION['admin_id']) ? 'admin' : 'client';
    $userId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : $_SESSION['user_id'];
    
    $count = $notificationHandler->getUnreadCount($receiverType, $userId);
    echo json_encode(['success' => true, 'count' => $count]);
} catch (Exception $e) {
    error_log("Error in get_notification_count.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'count' => 0]);
}
?>