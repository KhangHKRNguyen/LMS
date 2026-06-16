<?php

namespace App\Services;

use App\Models\AssignmentDistribution;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function send(
        string $title,
        string $content,
        array|Collection|User $recipients,
        ?int $senderId = null,
        ?string $type = null,
        bool $email = false,
        bool $deduplicate = false
    ): ?Notification {
        $recipientUsers = $this->normalizeRecipients($recipients);

        if ($type && (str_contains($type, 'assignment') || str_contains($type, 'submission'))) {
            $recipientUsers = $recipientUsers->reject(function ($user) {
                return $user->role_id == 3;
            })->values();
        }

        if ($recipientUsers->isEmpty()) {
            return null;
        }

        if ($deduplicate) {
            $notifiedUserIds = NotificationRecipient::query()
                ->whereIn('user_id', $recipientUsers->pluck('id'))
                ->whereHas('notification', function ($query) use ($type, $title) {
                    $query->where('type', $type)
                        ->where('title', $title);
                })
                ->pluck('user_id');

            $recipientUsers = $recipientUsers
                ->reject(fn (User $user) => $notifiedUserIds->contains($user->id))
                ->values();

            if ($recipientUsers->isEmpty()) {
                return null;
            }

            $existing = Notification::query()
                ->where('type', $type)
                ->where('title', $title)
                ->whereHas('recipients', fn ($query) => $query->whereIn('user_id', $recipientUsers->pluck('id')))
                ->exists();

            if ($existing) {
                return null;
            }
        }

        $notification = DB::transaction(function () use ($title, $content, $recipientUsers, $senderId, $type) {
            $notification = Notification::create([
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'sent_at' => now(),
                'sender_id' => $this->resolveSenderId($senderId, $recipientUsers->first()),
            ]);

            $notification->recipients()->createMany(
                $recipientUsers->map(fn (User $user) => [
                    'user_id' => $user->id,
                    'is_read' => false,
                ])->all()
            );

            return $notification;
        });

        if ($email) {
            $this->sendEmail($recipientUsers, $title, $content);
        }

        return $notification;
    }

    public function notifyMemberAdded(CourseClass $class, User $user): void
    {
        $this->send(
            'Bạn đã được thêm vào lớp học',
            "Bạn đã được thêm vào lớp {$class->class_name}.",
            $user,
            Auth::id(),
            'class_member_added'
        );
    }

    public function notifyAttendanceWarning(CourseClass $class, User $student, int $totalAbsent): void
    {
        if (!in_array($totalAbsent, [3, 4]) && $totalAbsent <= 4) {
            return;
        }

        $level = $totalAbsent > 4 ? 2 : 1;
        $message = $level === 1
            ? "Bạn đã vắng {$totalAbsent} buổi tại lớp {$class->class_name}. Đây là cảnh báo học tập mức 1."
            : "Bạn đã vắng {$totalAbsent} buổi tại lớp {$class->class_name}. Cảnh báo mức 2: mất quyền bảo đảm đầu ra.";

        $this->send(
            "Cảnh báo vắng học mức {$level} - {$class->class_name}",
            $message,
            $student,
            null,
            "attendance_warning_level_{$level}",
            true,
            true
        );
    }

    public function notifyMissingAssignmentWarning(CourseClass $class, User $student, int $totalMissing): void
    {
        if (!in_array($totalMissing, [7, 8]) && $totalMissing <= 8) {
            return;
        }

        $level = $totalMissing > 8 ? 2 : 1;
        $message = $level === 1
            ? "Bạn đang thiếu {$totalMissing} bài tập tại lớp {$class->class_name}. Đây là cảnh báo học tập mức 1."
            : "Bạn đang thiếu {$totalMissing} bài tập tại lớp {$class->class_name}. Cảnh báo mức 2: mất quyền bảo đảm đầu ra.";

        $this->send(
            "Cảnh báo thiếu bài tập mức {$level} - {$class->class_name}",
            $message,
            $student,
            null,
            "missing_assignment_warning_level_{$level}",
            true,
            true
        );
    }

    public function notifyAssignmentOpened(AssignmentDistribution $distribution): void
    {
        $distribution->loadMissing('assignment', 'lessonSession.courseClass.users');
        $class = $distribution->lessonSession?->courseClass;

        if (!$class) {
            return;
        }

        $this->send(
            'Bạn có bài tập mới: ' . ($distribution->assignment?->title ?? 'Bài tập'),
            "Bài tập {$distribution->assignment?->title} của lớp {$class->class_name} đã được mở.",
            $class->users->whereIn('role_id', [3, 4])->values(),
            $distribution->user_id,
            'assignment_opened',
            false,
            true
        );
    }

    public function notifyLeaveRequestSubmitted(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->loadMissing('student', 'lessonSession.courseClass.users');
        $class = $leaveRequest->lessonSession?->courseClass;

        if (!$class) {
            return;
        }

        $recipients = $class->users
            ->where('role_id', 3)
            ->values();

        $this->send(
            'Học viên gửi đơn xin nghỉ',
            "{$leaveRequest->student?->name} đã gửi đơn xin nghỉ tại lớp {$class->class_name}.",
            $recipients,
            $leaveRequest->user_id,
            'leave_request_submitted'
        );
    }

    private function normalizeRecipients(array|Collection|User $recipients): Collection
    {
        if ($recipients instanceof User) {
            return collect([$recipients]);
        }

        return collect($recipients)
            ->filter()
            ->map(fn ($recipient) => $recipient instanceof User ? $recipient : User::find($recipient))
            ->filter()
            ->unique('id')
            ->values();
    }

    private function resolveSenderId(?int $senderId, ?User $fallbackRecipient): int
    {
        if ($senderId && User::whereKey($senderId)->exists()) {
            return $senderId;
        }

        return Auth::id()
            ?? User::where('role_id', 1)->value('id')
            ?? $fallbackRecipient?->id
            ?? User::query()->value('id');
    }

    private function sendEmail(Collection $recipients, string $title, string $content): void
    {
        foreach ($recipients as $recipient) {
            if (!$recipient->email) {
                continue;
            }

            try {
                Mail::raw($content, function ($message) use ($recipient, $title) {
                    $message->to($recipient->email)->subject($title);
                });
            } catch (\Throwable $exception) {
                Log::warning('Cannot send notification email.', [
                    'user_id' => $recipient->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
