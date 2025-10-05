<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GradeReportController extends Controller
{
    private const PASSING_GRADE = 61;

    public function index(Request $request): View
    {
        $gradesInput = $request->old('grades', [null]);

        return view('grades.index', [
            'gradesInput' => $gradesInput,
            'report' => null,
            'passingGrade' => self::PASSING_GRADE,
        ]);
    }

    public function store(Request $request): View
    {
        $validated = $request->validate([
            'grades' => ['required', 'array', 'min:1'],
            'grades.*' => ['required', 'numeric', 'between:0,100'],
        ]);

        $grades = array_map(static fn ($grade) => (float) $grade, $validated['grades']);
        $studentCount = count($grades);
        $total = array_sum($grades);
        $average = $total / $studentCount;
        $highest = max($grades);
        $lowest = min($grades);
        $approvedCount = count(array_filter($grades, fn (float $grade) => $grade >= self::PASSING_GRADE));
        $failedCount = $studentCount - $approvedCount;

        $students = [];
        foreach ($grades as $index => $grade) {
            $students[] = [
                'number' => $index + 1,
                'grade' => $grade,
                'status' => $grade >= self::PASSING_GRADE ? 'Aprobado' : 'Reprobado',
            ];
        }

        $report = [
            'students' => $students,
            'studentCount' => $studentCount,
            'average' => $average,
            'highest' => $highest,
            'lowest' => $lowest,
            'approvedCount' => $approvedCount,
            'failedCount' => $failedCount,
        ];

        $formattedGradesInput = array_map(static function (float $grade): string {
            $formatted = number_format($grade, 2, '.', '');
            return rtrim(rtrim($formatted, '0'), '.');
        }, $grades);

        return view('grades.index', [
            'gradesInput' => $formattedGradesInput,
            'report' => $report,
            'passingGrade' => self::PASSING_GRADE,
        ]);
    }
}
