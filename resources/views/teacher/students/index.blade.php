@extends('layouts.classroom')

@section('title', 'Quản lý lớp học - Danh sách học viên')

{{-- Kích hoạt trạng thái Active cho menu Học Viên --}}
@section('menu_hoc_vien_class', 'btn w-100 text-start py-2 fw-bold text-white')
@section('menu_hoc_vien_style', 'background-color: #800000;')

@section('classroom_content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold m-0">
            {{-- 1. ĐƯỜNG DẪN QUAY LẠI DASHBOARD --}}
            <a href="{{ route('teacher.dashboard') }}" class="text-decoration-none text-dark">
                <i class="bi bi-arrow-left me-2"></i>LỚP HỌC - {{ $class->class_name }} {{-- 2. TÊN LỚP ĐỘNG --}}
            </a>
        </h5>
        {{-- 3. ĐẾM TỔNG SỐ HỌC VIÊN THỰC TẾ QUA PHÂN TRANG --}}
        <small class="text-muted fw-bold">Tổng số học viên: {{ $students->total() }}</small>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        {{-- 4. HIỂN THỊ DANH SÁCH CÁC BUỔI HỌC TỪ DATABASE --}}
        <select id="session-filter" class="form-select form-select-sm border-danger fw-semibold text-dark" style="width: 160px;">
            <option value="all">-- Tất cả các buổi --</option> {{-- Đổi value rỗng thành "all" để xem toàn bộ --}}
            @foreach($class->lessonSessions as $index => $session)
                <option value="{{ $session->id }}">
                    Buổi {{ $index + 1 }} ({{ date('d/m/Y', strtotime($session->lesson_date)) }})
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- MA TRẬN THEO DÕI CHUYÊN CẦN --}}
<div class="table-responsive shadow-sm rounded border">
    <table class="table table-bordered align-middle text-center m-0">
        <thead style="background-color: #800000; color: white; font-size: 13px;">
            <tr>
                <th style="width: 50px; padding: 12px;">STT</th>
                <th style="width: 90px;">Mã số HV</th>
                <th class="text-start ps-4">Họ và tên</th>
                
                {{-- LẶP ĐỘNG TIÊU ĐỀ CÁC BUỔI HỌC CÓ TRONG DATABASE --}}
                @foreach($class->lessonSessions as $index => $session)
                    <th class="session-col" data-session-id="{{ $session->id }}">Buổi {{ $index + 1 }}</th>
                @endforeach
                
                <th>Tổng thiếu bài</th>
                <th>Cảnh báo</th>
            </tr>
        </thead>
        <tbody style="font-size: 14px;">
            @foreach ($students as $index => $student)
            <tr>
                {{-- Số thứ tự tự tăng theo trang --}}
                <td>{{ ($students->currentPage() - 1) * $students->perPage() + $index + 1 }}</td>
                <td class="text-secondary fw-medium">{{ $student->id }}</td>
                <td class="text-start ps-4 fw-semibold text-dark">{{ $student->name }}</td>
                
                @foreach($class->lessonSessions as $session)
                    @php
                        $status = $student->session_statuses[$session->id] ?? 'Chưa đến';
                    @endphp
                    {{-- Thêm class "session-col" và thuộc tính data-session-id vào đây --}}
                    <td class="text-center align-middle session-col" data-session-id="{{ $session->id }}">
                        @if($status === 'Thiếu')
                            <span class="badge px-3 py-1 text-danger bg-danger-subtle border border-danger" style="background-color:#FFCCCC !important; color: #990000 !important;">
                                Thiếu
                            </span>
                        @elseif($status === 'Đủ')
                            <span class="badge px-3 py-1 text-success bg-success-subtle border border-success" style="background-color:#CCFFCC !important; color: #006600 !important;">
                                Đủ
                            </span>
                        @else
                            <span class="text-muted fw-bold">—</span>
                        @endif
                    </td>
                @endforeach
                
                {{-- Hiển thị dữ liệu tính toán từ Controller --}}
                <td class="fw-bold text-dark">{{ $student->total_missing }}</td>
                <td class="fw-bold {{ $student->alarm_level !== '—' ? 'text-danger' : 'text-muted' }}">
                    {{ $student->alarm_level }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- PHÂN TRANG TỰ ĐỘNG CỦA LARAVEL --}}
<div class="d-flex justify-content-center mt-4">
    {{ $students->links() }}
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sessionFilter = document.getElementById('session-filter');
    const sessionColumns = document.querySelectorAll('.session-col');

    sessionFilter.addEventListener('change', function () {
        const selectedSessionId = this.value;

        sessionColumns.forEach(col => {
            // Nếu chọn "Tất cả" (all) thì hiện lại toàn bộ các buổi
            if (selectedSessionId === 'all') {
                col.style.display = ''; 
            } else {
                // Nếu trùng với ID được chọn thì hiện, không trùng thì ẩn đi
                if (col.getAttribute('data-session-id') === selectedSessionId) {
                    col.style.display = '';
                } else {
                    col.style.display = 'none';
                }
            }
        });
    });
});
</script>
@endsection