<?php
class NotificationHandler {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createNotification($userId, $receiverType, $type, $title, $content, $relatedId = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (
                    user_id, receiver_type, notification_type, 
                    title, content, related_id
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $userId,
                $receiverType,
                $type,
                $title,
                $content,
                $relatedId
            ]);
        } catch (PDOException $e) {
            error_log("Notification creation error: " . $e->getMessage());
            return false;
        }
    }

    public function getUnreadCount($receiverType, $userId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM notifications 
                    WHERE receiver_type = ? 
                    AND is_read = 0";
            $params = [$receiverType];
            
            if ($userId !== null) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }

    public function markAsRead($notificationId, $userId = null) {
        try {
            $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
            $params = [$notificationId];

            if ($userId) {
                $sql .= " AND (user_id = ? OR user_id IS NULL)";
                $params[] = $userId;
            }

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }

    public function getUserNotifications($receiverType, $userId = null, $limit = 10) {
        try {
            // MySQL-specific JOIN optimization
            $sql = "
                SELECT STRAIGHT_JOIN
                    n.*,
                    a.appointment_date,
                    a.appointment_time,
                    u.fullname as sender_name
                FROM notifications n
                LEFT JOIN appointment a FORCE INDEX (PRIMARY)
                    ON n.related_id = a.id
                LEFT JOIN user u FORCE INDEX (PRIMARY)
                    ON n.user_id = u.id
                WHERE n.receiver_type = ?
                AND (? IS NULL OR n.user_id = ?)
                ORDER BY n.created_at DESC
                LIMIT ?
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$receiverType, $userId, $userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
}
?>