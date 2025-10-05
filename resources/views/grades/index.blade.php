<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte de Calificaciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f5f5f5;
        }

        .dashboard-grid {
            display: grid;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, minmax(300px, 1fr));
            }
        }

        @media (min-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(3, minmax(320px, 1fr));
            }
        }

        .dashboard-empty {
            min-height: 360px;
        }

        @media (min-width: 1200px) {
            .dashboard-empty {
                grid-column: span 2;
            }
        }

        .grade-row {
            background-color: #fbfbfb;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="dashboard-grid">
                    <div class="card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h1 class="h4 mb-3">Captura de estudiantes</h1>
                            <p class="text-muted mb-4">Ingresa una calificacion (0-100) para cada estudiante y genera el reporte general.</p>

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <h2 class="h6 mb-2">Hay errores en el formulario</h2>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('grades.store') }}" novalidate>
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label">Nota minima para aprobar</label>
                                    <input type="text" class="form-control" value="{{ $passingGrade }}" readonly>
                                </div>

                                <div id="grades-container">
                                    @foreach ($gradesInput as $index => $value)
                                        <div class="border rounded mb-3 p-3 grade-row">
                                            <div class="mb-3">
                                                <label class="form-label">Calificacion del estudiante {{ $index + 1 }}</label>
                                                <input type="number" name="grades[]" class="form-control" min="0" max="100" step="0.01" value="{{ $value ?? '' }}" required>
                                            </div>
                                            <div class="d-grid">
                                                <button type="button" class="btn btn-outline-danger remove-grade" @if ($loop->count === 1) disabled @endif>Eliminar</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="d-flex flex-column flex-sm-row gap-3">
                                    <button type="button" class="btn btn-outline-primary" id="add-grade">Agregar estudiante</button>
                                    <button type="submit" class="btn btn-primary">Generar reporte</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if ($report)
                        <div class="card shadow-sm">
                            <div class="card-body p-4 p-md-5">
                                <h2 class="h5 mb-4">Visualizacion</h2>
                                <div class="mb-4">
                                    <canvas id="gradesBarChart" height="220"></canvas>
                                </div>
                                <div>
                                    <canvas id="gradesPieChart" height="220"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-body p-4 p-md-5 d-flex flex-column gap-4">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h2 class="h4 mb-0">Calificaciones</h2>
                                        <span class="badge bg-primary">Actualizado</span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Estudiante</th>
                                                    <th scope="col">Calificacion</th>
                                                    <th scope="col">Estatus</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($report['students'] as $student)
                                                    <tr>
                                                        <td>Estudiante {{ $student['number'] }}</td>
                                                        <td>{{ number_format($student['grade'], 2) }}</td>
                                                        <td>
                                                            <span class="badge {{ $student['status'] === 'Aprobado' ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $student['status'] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="p-3 border rounded bg-light">
                                            <h3 class="h6 text-uppercase text-muted">Totales</h3>
                                            <p class="mb-1">Total estudiantes: <strong>{{ $report['studentCount'] }}</strong></p>
                                            <p class="mb-1">Aprobados (&gt;= {{ $passingGrade }}): <strong>{{ $report['approvedCount'] }}</strong></p>
                                            <p class="mb-0">Reprobados: <strong>{{ $report['failedCount'] }}</strong></p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 border rounded bg-light">
                                            <h3 class="h6 text-uppercase text-muted">Estadisticas</h3>
                                            <p class="mb-1">Promedio general: <strong>{{ number_format($report['average'], 2) }}</strong></p>
                                            <p class="mb-1">Calificacion mas alta: <strong>{{ number_format($report['highest'], 2) }}</strong></p>
                                            <p class="mb-0">Calificacion mas baja: <strong>{{ number_format($report['lowest'], 2) }}</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card shadow-sm dashboard-empty">
                            <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-center align-items-center text-center">
                                <div class="display-6 mb-3">📊</div>
                                <p class="text-muted mb-0">Aun no hay datos. Genera el reporte para visualizar las graficas, el listado y el resumen.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <template id="grade-template">
        <div class="border rounded mb-3 p-3 grade-row">
            <div class="mb-3">
                <label class="form-label"></label>
                <input type="number" name="grades[]" class="form-control" min="0" max="100" step="0.01" required>
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-outline-danger remove-grade">Eliminar</button>
            </div>
        </div>
    </template>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('grades-container');
            const template = document.getElementById('grade-template');
            const addButton = document.getElementById('add-grade');

            const updateRows = () => {
                const rows = container.querySelectorAll('.grade-row');
                rows.forEach((row, index) => {
                    const label = row.querySelector('label');
                    if (label) {
                        label.textContent = `Calificacion del estudiante ${index + 1}`;
                    }

                    const removeButton = row.querySelector('.remove-grade');
                    if (removeButton) {
                        removeButton.disabled = rows.length === 1;
                    }
                });
            };

            container.addEventListener('click', (event) => {
                if (event.target.classList.contains('remove-grade')) {
                    const rows = container.querySelectorAll('.grade-row');
                    if (rows.length > 1) {
                        event.target.closest('.grade-row').remove();
                        updateRows();
                    }
                }
            });

            addButton.addEventListener('click', () => {
                const clone = template.content.cloneNode(true);
                const input = clone.querySelector('input');
                if (input) {
                    input.value = '';
                }
                container.appendChild(clone);
                updateRows();
            });

            updateRows();

            const reportData = @json($report);
            if (reportData) {
                const labels = reportData.students.map((student) => `Est ${student.number}`);
                const grades = reportData.students.map((student) => Number(student.grade));
                const highest = Math.max(...grades);
                const lowest = Math.min(...grades);
                const barColors = grades.map((grade) => {
                    if (grade === highest) {
                        return '#198754';
                    }
                    if (grade === lowest) {
                        return '#dc3545';
                    }
                    return '#0d6efd';
                });

                const barCtx = document.getElementById('gradesBarChart');
                if (barCtx) {
                    new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Calificacion',
                                data: grades,
                                backgroundColor: barColors,
                                borderRadius: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => `Calificacion: ${context.parsed.y.toFixed(2)}`,
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    suggestedMax: 100,
                                },
                            },
                        },
                    });
                }

                const pieCtx = document.getElementById('gradesPieChart');
                if (pieCtx) {
                    new Chart(pieCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Aprobados', 'Reprobados'],
                            datasets: [{
                                data: [reportData.approvedCount, reportData.failedCount],
                                backgroundColor: ['#198754', '#dc3545'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => `${context.label}: ${context.parsed}`,
                                    },
                                },
                            },
                        },
                    });
                }
            }
        });
    </script>
</body>
</html>
