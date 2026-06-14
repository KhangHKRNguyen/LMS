<?php

namespace App\Services;

class IeltsScoreService
{
    /**
     * Tự động làm tròn điểm số theo quy tắc IELTS (.25 và .75)
     */
    private static function roundIelts(float $score): float
    {
        $floor = floor($score);
        $remainder = $score - $floor;

        if ($remainder < 0.25) {
            return $floor; // Ví dụ: 6.20 -> 6.0
        } elseif ($remainder >= 0.25 && $remainder < 0.75) {
            return $floor + 0.5; // Ví dụ: 6.30 hoặc 6.70 -> 6.5
        } else {
            return $floor + 1.0; // Ví dụ: 6.80 -> 7.0
        }
    }

    /**
     * Tính điểm Band tự động cho Reading / Listening dựa trên tỷ lệ câu đúng
     */
    public static function calculateSkillBand(int $correctCount, int $totalQuestions): float
    {
        if ($totalQuestions === 0) {
            return 0.0;
        }

        // Tính điểm thô trên thang 9
        $rawBand = ($correctCount / $totalQuestions) * 9;

        // Trả về điểm đã làm tròn
        return self::roundIelts($rawBand);
    }

    /**
     * Tính điểm Overall trung bình cộng của 4 kỹ năng
     */
    public static function calculateOverall(float $l, float $r, float $w, float $s): float
    {
        $average = ($l + $r + $w + $s) / 4;
        
        return self::roundIelts($average);
    }
}