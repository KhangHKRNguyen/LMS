<?php

namespace App\Console\Commands;

use App\Models\AssignmentDistribution;
use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\LessonSession;
use App\Models\Submission;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDueNotifications extends Command
{
    protected $signature = 'notifications:send-due';

    protected $description = 'Send assignment-opened and learning warning notifications.';

    public function handle(NotificationService $notifications): int
    {
        AssignmentDistribution::query()
            ->whereNotNull('open_time')
            ->where('open_time', '<=', now())
            ->with('lessonSession.courseClass.students', 'assignment')
            ->chunkById(100, function ($distributions) use ($notifications) {
                foreach ($distributions as $distribution) {
                    $notifications->notifyAssignmentOpened($distribution);
                }
            });

        CourseClass::with('students')->chunkById(50, function ($classes) use ($notifications) {
            foreach ($classes as $class) {
                $lessonSessionIds = LessonSession::where('course_class_id', $class->id)->pluck('id');
                $distributionIds = AssignmentDistribution::whereIn('lesson_session_id', $lessonSessionIds)
                    ->where('close_time', '<', now())
                    ->pluck('id');

                foreach ($class->students as $student) {
                    $totalAbsent = Attendance::where('user_id', $student->id)
                        ->whereIn('lesson_session_id', $lessonSessionIds)
                        ->whereIn('status', ['absent', 'Vắng'])
                        ->count();

                    $submittedCount = Submission::where('user_id', $student->id)
                        ->whereIn('assignment_distribution_id', $distributionIds)
                        ->count();

                    $notifications->notifyAttendanceWarning($class, $student, $totalAbsent);
                    $notifications->notifyMissingAssignmentWarning($class, $student, max(0, $distributionIds->count() - $submittedCount));
                }
            }
        });

        $this->info('Due notifications processed.');

        return self::SUCCESS;
    }
}
