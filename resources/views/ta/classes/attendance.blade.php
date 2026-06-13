@extends('layouts.app')

@section('title', 'Điểm danh lớp học - TA')

@section('content')
<style>
    /* Custom style chấm tròn màu sắc cho radio như bản vẽ */
    .radio-present:checked {
        background-color: #198754 !important;
        border-color: #198754 !important;
    }
    .radio-absent:checked {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
    }
    .table-arena th {
        background-color: #800000 !important;
        color: white !important;
        vertical-align: middle;
        font-size: 13px;
        border: 1px solid #dee2e6;
    }
    .table-arena td {
        vertical-align: middle;
        border: 1px solid #dee2e6;
        font-size: 13.5px;
    }
    .sidebar-menu .active-menu {
        background-color: #800000 !important;
        color: white !important;
        font-weight: bold;
    }
    .sidebar-menu .btn-menu {
        border: 2px solid #800000;
        color: #800000;
        margin-bottom: 8px;
    }
    .sidebar-menu .btn-menu:hover {
        background-color: #800000;
        color: white;
    }
</style>

<div class="row g-4">
    <div class="col-md-3 col-lg-2">
        <div class="sidebar-menu p-3 rounded shadow-sm d-flex flex-column h-100" style="background-color: #FFFDE7; min-height: 400px;">
            <div class="text-center fw-bold border-bottom pb-2 mb-3 text-uppercase text-muted" style="font-size: 11px;">MENU QUẢN LÝ</div>
            <a href="#" class="btn active-menu w-100 py-2 text-start rounded-0 ps-3">
                Điểm danh
            </a>
            <a href="{{ route('ta.leave_requests.index') }}" class="btn btn-menu bg-white w-100 py-2 text-start rounded-0 ps-3">
                Đơn xin nghỉ
            </a>
            <a href="#" class="btn btn-menu bg-white w-100 py-2 text-start rounded-0 ps-3">
                Kết quả tổng kết
            </a>
        </div>
    </div>

    <div class="col-md-9 col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <a href="{{ route('ta.dashboard') }}" class="text-decoration-none text-secondary small fw-bold">
                    <i class="bi bi-arrow-left"></i> LỚP HỌC - {{ $class->id }}
                </a>
                <h4 class="fw-bold text-dark mt-1 mb-0">Tổng số học viên: {{ $students->count() }}</h4>
            </div>
            <div>
                <select class="form-select border-secondary-subtle" style="width: 130px;">
                    <option selected>Buổi học</option>
                </select>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 py-2 shadow-sm mb-3">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('ta.classes.attendance.store', $class->id) }}" method="POST">
            @csrf
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-bordered table-hover text-center m-0 table-arena bg-white">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 50px;">#</th>
                            <th rowspan="2" style="width: 110px;">Mã HV</th>
                            <th rowspan="2" class="text-start ps-3">Họ tên</th>
                            
                            @foreach($lessonSessions as $index => $session)
                                <th colspan="2">Buổi {{ $index + 1 }}</th>
                            @endforeach
                            
                            <th rowspan="2" style="width: 130px;">Tổng buổi nghỉ</th>
                            <th rowspan="2" style="width: 120px;">Cảnh báo</th>
                        </tr>
                        <tr>
                            @foreach($lessonSessions as $session)
                                <th style="font-size: 11px; background-color: #f8f9fa !important; color: #198754 !important; width: 45px;">Đủ</th>
                                <th style="font-size: 11px; background-color: #f8f9fa !important; color: #dc3545 !important; width: 45px;">Vắng</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $key => $student)
                            @php $totalAbsent = 0; @endphp
                            <tr>
                                <td class="text-muted small">{{ $key + 1 }}</td>
                                <td class="fw-bold text-secondary">#{{ $student->id }}</td>
                                <td class="text-start fw-medium text-dark ps-3">{{ $student->name }}</td>
                                
                                @foreach($lessonSessions as $session)
                                    @php
                                        $status = $attendanceMatrix[$student->id][$session->id] ?? 'present';
                                        if($status === 'absent') { $totalAbsent++; }
                                    @endphp
                                    <td>
                                        <input type="radio" name="attendance[{{ $session->id }}][{{ $student->id }}]" value="present" {{ $status !== 'absent' ? 'checked' : '' }} class="form-check-input radio-present">
                                    </td>
                                    <td>
                                        <input type="radio" name="attendance[{{ $session->id }}][{{ $student->id }}]" value="absent" {{ $status === 'absent' ? 'checked' : '' }} class="form-check-input radio-absent">
                                    </td>
                                @endforeach

                                <td class="fw-bold text-dark fs-6">{{ $totalAbsent }}</td>
                                
                                <td>
                                    @if($totalAbsent >= 8)
                                        <span class="text-danger fw-bold">Mức 2</span>
                                    @elseif($totalAbsent >= 4)
                                        <span class="text-warning fw-bold">Mức 1</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + (count($lessonSessions) * 2) }}" class="p-4 text-muted text-center">
                                    Không tìm thấy dữ liệu học viên trong lớp học này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->isNotEmpty() && $lessonSessions->isNotEmpty())
                <div class="text-end mt-3">
                    <button type="submit" class="btn text-white fw-bold px-4 py-2" style="background-color: #800000; border-radius: 4px; font-size: 14px;">
                        LƯU THAY ĐỔI ĐIỂM DANH
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection