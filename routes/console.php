<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('grades:report', function () {
    $studentCount = null;

    while ($studentCount === null) {
        $input = $this->ask('Cuantos estudiantes deseas registrar?');
        $input = $input !== null ? trim($input) : '';

        if ($input === '' || !ctype_digit($input) || (int) $input < 1) {
            $this->error('Ingresa un numero entero positivo.');
            continue;
        }

        $studentCount = (int) $input;
    }

    $passingGrade = 61;
    $grades = [];

    for ($i = 1; $i <= $studentCount; $i++) {
        while (true) {
            $answer = $this->ask("Calificacion del estudiante {$i} (0-100)");
            $answer = $answer !== null ? trim($answer) : '';

            if ($answer === '' || !is_numeric($answer)) {
                $this->error('Ingresa un numero valido.');
                continue;
            }

            $grade = (float) $answer;

            if ($grade < 0 || $grade > 100) {
                $this->error('La calificacion debe estar entre 0 y 100.');
                continue;
            }

            $grades[] = $grade;
            break;
        }
    }

    $total = array_sum($grades);
    $average = $total / $studentCount;
    $highest = max($grades);
    $lowest = min($grades);
    $approved = array_filter($grades, fn (float $grade) => $grade >= $passingGrade);
    $failedCount = $studentCount - count($approved);

    $this->newLine();
    $this->info('Reporte de Calificaciones');
    $this->line(str_repeat('-', 30));

    $rows = [];
    foreach ($grades as $index => $grade) {
        $rows[] = [
            'Estudiante' => $index + 1,
            'Calificacion' => number_format($grade, 2),
            'Estatus' => $grade >= $passingGrade ? 'Aprobado' : 'Reprobado',
        ];
    }

    $this->table(['Estudiante', 'Calificacion', 'Estatus'], $rows);

    $this->line("Total de estudiantes: {$studentCount}");
    $this->line('Promedio general: ' . number_format($average, 2));
    $this->line('Calificacion mas alta: ' . number_format($highest, 2));
    $this->line('Calificacion mas baja: ' . number_format($lowest, 2));
    $this->line('Aprobados (>= 61): ' . count($approved));
    $this->line('Reprobados: ' . $failedCount);
})->purpose('Generar un reporte interactivo de calificaciones');
