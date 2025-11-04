<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;

class NotificationService
{
    /**
     * Gửi một thông báo đến một người dùng cụ thể hoặc tất cả mọi người.
     *
     * @param User|null $user Người dùng nhận thông báo. Null để gửi cho tất cả.
     * @param string $type Loại thông báo (success, info, warning, error).
     * @param string $title Tiêu đề thông báo.
     * @param string $message Nội dung thông báo.
     */
    public function notify(?User $user, string $type, string $title, string $message)
    {
        Notification::create([
            'user_id' => $user?->id, // Sử dụng null-safe operator, sẽ là null nếu $user là null
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
