<?php

namespace App\Services;

use App\Models\CourseScore;
use App\Models\GpaSummary;
use App\Models\GradeSetting;
use App\Models\StudentStat;
use Illuminate\Support\Facades\DB;

class GradingService
{
    /**
     * Determine the grade letter and weight from a numeric score.
     */
    public function calculateScoreLetterAndWeight(float $numericScore): ?GradeSetting
    {
        // Find the matching GradeSetting where numericScore falls within min and max
        // Add >= and <= carefully, typically limits overlap edge cases, 
        // assuming standard config (e.g. 85-100 = A)
        return GradeSetting::where('min_score', '<=', $numericScore)
            ->where('max_score', '>=', $numericScore)
            ->orderBy('weight', 'desc') // Bias towards higher if exact edge match
            ->first();
    }

    /**
     * Calculate and save Semester GPA (IPS)
     * formula: sum(weight * sks) / sum(sks) for a specific period
     */
    public function calculateIPS(string $studentId, string $krsPeriodId): GpaSummary
    {
        $courseScores = CourseScore::with('schedule.course')
            ->where('student_id', $studentId)
            ->where('krs_period_id', $krsPeriodId)
            ->whereNotNull('final_weight')
            ->get();

        $totalSks = 0;
        $totalQualityPoints = 0;

        foreach ($courseScores as $score) {
            $sks = $score->schedule->course->sks;
            $weight = $score->final_weight;

            $totalSks += $sks;
            $totalQualityPoints += ($sks * $weight);
        }

        $ips = $totalSks > 0 ? round($totalQualityPoints / $totalSks, 2) : 0;

        $gpaSummary = GpaSummary::updateOrCreate(
            ['student_id' => $studentId, 'krs_period_id' => $krsPeriodId],
            ['ips' => $ips, 'total_sks_semester' => $totalSks]
        );

        // Calculate cumulative right after a semester change
        $this->calculateIPK($studentId);

        return $gpaSummary;
    }

    /**
     * Calculate and save Cumulative GPA (IPK)
     * Fetches all history, distincts by Course ID picking highest final weight.
     */
    public function calculateIPK(string $studentId): void
    {
        $allScores = CourseScore::with('schedule.course')
            ->where('student_id', $studentId)
            ->whereNotNull('final_weight')
            ->get();

        $bestGrades = [];

        // Group by course_id and keep only the max weight
        foreach ($allScores as $score) {
            $courseId = $score->schedule->course_id;

            if (!isset($bestGrades[$courseId])) {
                $bestGrades[$courseId] = $score;
            } else {
                if ($score->final_weight > $bestGrades[$courseId]->final_weight) {
                    $bestGrades[$courseId] = $score;
                }
            }
        }

        $totalCumulativeSks = 0;
        $totalCumulativeQualityPoints = 0;

        foreach ($bestGrades as $bestScore) {
            $sks = $bestScore->schedule->course->sks;
            $weight = $bestScore->final_weight;

            $totalCumulativeSks += $sks;
            $totalCumulativeQualityPoints += ($sks * $weight);
        }

        $ipk = $totalCumulativeSks > 0 ? round($totalCumulativeQualityPoints / $totalCumulativeSks, 2) : 0;

        // Update the LATEST active GpaSummary for IPK consistency
        // (Or update all to reflect historical cumulative track)
        GpaSummary::where('student_id', $studentId)
            ->update([
                'ipk' => $ipk,
                'total_sks_cumulative' => $totalCumulativeSks
            ]);

        // Optionally update StudentStat if IPK defines next semester SKS quotas
        // Example: IPK > 3.0 = 24 SKS, else 20 SKS
        $maxSks = $ipk >= 3.0 ? 24 : 20;
        StudentStat::updateOrCreate(
            ['student_id' => $studentId],
            ['max_sks_allowed' => $maxSks]
        );
    }
}
