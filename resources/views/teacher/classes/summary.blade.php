@extends('layouts.classroom')

@section('title', 'Tổng kết lớp học - Giảng viên')

@section('classroom_content')
<style>
    .table-arena th { background-color: #800000 !important; color: white !important; text-align: center; vertical-align: middle; font-size: 13px; border: 1px solid #dee2e6; }
    .table-arena td { vertical-align: middle; text-align: center; border: 1px solid #dee2e6; font-size: 13.5px; }
    .stat-card { border-left: 4px solid #800000; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-2px); }
    .dashboard-card { border-top: 4px solid #DF8A14; }
</style>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h5 class="fw-bold m-0 text-dark">BẢNG TỔNG KẾT LỚP HỌC - {{ $class->class_name }}</h5>
        <small class="text-muted fw-semibold">Khóa học chuyên môn: {{ $class->course->name ?? 'N/A' }} (Chuẩn đầu ra: {{ $class->course->output_overall ?? '—' }})</small>
    </div>
</div>

<div class="row g-3 mb-4 align-items-center">
    <div class="col-12">
        <div class="card border-0 shadow-sm stat-card bg-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-start">
                <div>
                    <span class="text-muted d-block fs-7 fw-semibold text-uppercase">Tổng số học viên</span>
                    <h3 class="fw-bold m-0 text-dark mt-1">{{ $students->count() }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm bg-white mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-arena mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Mã học viên</th>
                        <th class="text-start ps-3">Họ tên học viên</th>
                        <th>Điểm giữa khóa</th>
                        <th>Điểm cuối khóa</th>
                        <th>Tổng buổi nghỉ</th>
                        <th>Tổng thiếu bài</th>
                        <th>Trạng thái đầu ra</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-bold text-secondary">{{ $student->id }}</td>
                            <td class="text-start ps-3 fw-semibold text-dark">{{ $student->name }}</td>
                            <td class="fw-bold text-primary">{{ $student->midterm_grade ?? '—' }}</td>
                            <td class="fw-bold text-primary">{{ $student->final_grade ?? '—' }}</td>
                            <td>
                                <span class="{{ $student->total_absent >= 5 ? 'text-danger fw-bold bg-danger-subtle px-2 py-0.5 rounded' : 'text-dark' }}">
                                    {{ $student->total_absent }}
                                </span>
                            </td>
                            <td>
                                <span class="{{ $student->total_missing >= 9 ? 'text-danger fw-bold bg-danger-subtle px-2 py-0.5 rounded' : 'text-dark' }}">
                                    {{ $student->total_missing }}
                                </span>
                            </td>
                            <td>
                                @if($student->output_status === 'Đạt')
                                    <span class="badge bg-success-subtle text-success px-3 py-2 border border-success-subtle rounded-pill fw-bold">Đạt</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 border border-danger-subtle rounded-pill fw-bold">Không đạt</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-5 text-muted text-center">
                                Không tìm thấy dữ liệu học viên trong danh sách tổng kết lớp này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<hr class="my-5 opacity-25">

<div id="dashboardSection" class="card border-0 shadow-sm bg-white mb-4 dashboard-card">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
            DASHBOARD TỔNG KẾT & PHÂN TÍCH TIẾN ĐỘ LỚP HỌC
        </h5>
        <span class="badge bg-secondary-subtle text-secondary fw-semibold">Số liệu thời gian thực</span>
    </div>
    
    <div class="card-body p-4">
        <div class="row g-3 mb-5">
            <div class="col-6 col-lg-3">
                <div class="p-3 border rounded bg-light">
                    <small class="text-muted d-block fw-medium mb-1">Sĩ Số Lớp Học</small>
                    <span class="h4 fw-bold text-dark">{{ $totalStudents }}</span> <small class="text-muted">học viên</small>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="p-3 border rounded bg-light">
                    <small class="text-muted d-block fw-medium mb-1">Tỷ Lệ Đạt Đầu Ra</small>
                    <span class="h4 fw-bold text-success">{{ $passRate }}%</span> <small class="text-muted">({{ $passedCount }} HV)</small>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="p-3 border rounded bg-light">
                    <small class="text-muted d-block fw-medium mb-1">Tỷ Lệ Không Đạt</small>
                    <span class="h4 fw-bold text-danger">{{ $failRate }}%</span> <small class="text-muted">({{ $failedCount }} HV)</small>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="p-3 border rounded bg-light">
                    <small class="text-muted d-block fw-medium mb-1">Điểm Cuối Kỳ TB</small>
                    <span class="h4 fw-bold text-primary">{{ $avgFinalGrade }}</span> <small class="text-muted">/10</small>
                </div>
            </div>
            <div class="col-12 mt-3">
                <div class="p-2 px-3 border border-warning-subtle bg-warning-subtle text-warning-emphasis rounded fs-7 d-flex align-items-center gap-2">
                    <span>Hệ thống ghi nhận có tổng cộng <strong>{{ $totalStudentsMissing }}</strong> học viên đang bị tồn đọng bài tập chưa nộp hoặc nộp muộn quá hạn.</span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center border-end pe-md-4">
                <h6 class="fw-bold text-center text-secondary mb-3">Biểu đồ tỷ lệ kết quả đầu ra</h6>
                <div style="width: 100%; max-width: 240px; position: relative;">
                    <canvas id="pieChartOutput"></canvas>
                </div>
            </div>

            <div class="col-12 col-md-8">
                <h6 class="fw-bold text-secondary mb-3">Biểu đồ giám sát vi phạm Chuyên cần & Thiếu bài tập từng học viên</h6>
                <div style="width: 100%; height: 300px;">
                    <canvas id="barChartViolations"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Hàm cuộn trang mượt mà đến vùng Dashboard
    function scrollToDashboard() {
        const element = document.getElementById("dashboardSection");
        if (element) {
            element.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Lấy dữ liệu an toàn được mã hóa từ Laravel Controller
        const rawData = {!! json_encode($chartData) !!};

        // 1. CẤU HÌNH BIỂU ĐỒ TRÒN (PIE CHART) - TỶ LỆ ĐẠT / KHÔNG ĐẠT
        const ctxPie = document.getElementById('pieChartOutput').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['Đạt đầu ra', 'Không đạt'],
                datasets: [{
                    data: [rawData.passed, rawData.failed],
                    backgroundColor: ['#198754', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // 2. CẤU HÌNH BIỂU ĐỒ CỘT KÉP (BAR CHART) - SỐ BUỔI NGHỈ & BÀI THIẾU
        const ctxBar = document.getElementById('barChartViolations').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: rawData.labels,
                datasets: [
                    {
                        label: 'Số buổi nghỉ học',
                        data: rawData.absents,
                        backgroundColor: '#e74c3c',
                        borderRadius: 4,
                        barPercentage: 0.8,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Số lần thiếu bài tập',
                        data: rawData.missings,
                        backgroundColor: '#f39c12',
                        borderRadius: 4,
                        barPercentage: 0.8,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 11 } },
                        title: { display: true, text: 'Số lần vi phạm', font: { weight: 'bold' } }
                    }
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false }
                }
            }
        });
    });
</script>
@endsection