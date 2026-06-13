@extends('layouts.classroom')

@section('title', 'Kết quả tổng kết lớp học')

@section('classroom_content')
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="text-danger fw-bold m-0" style="color: var(--primary-color) !important; letter-spacing: 0.5px;">KẾT QUẢ TỔNG KẾT LỚP HỌC</h3>
            <p class="text-muted fw-medium mt-1">Lớp chủ nhiệm / giảng dạy: <span class="text-dark fw-bold">{{ $courseClass->class_name }} - {{ $courseClass->course->name ?? 'Chưa cập nhật tên khóa học' }}</span></p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select border-secondary-subtle" style="width: 200px; height: 42px; border-radius: 6px;">
                <option value="" selected>Tất cả bài học</option>
            </select>
        </div>
    </div>
</div>

{{-- KHỐI THỐNG KÊ TOP CARDS --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 p-3" style="border-left: 4px solid var(--primary-color); border-radius: 6px;">
            <small class="text-muted fw-semibold">SĨ SỐ LỚP</small>
            <h3 class="fw-bold text-dark mt-1 mb-0">{{ $stats['total_students'] }} <span class="fs-6 text-muted font-normal">học viên</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 p-3" style="border-left: 4px solid #03543F; border-radius: 6px;">
            <small class="text-muted fw-semibold">ĐÃ HOÀN THÀNH (ĐẠT)</small>
            <h3 class="fw-bold text-success mt-1 mb-0">{{ $stats['completed_count'] }} / {{ $stats['total_students'] }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 p-3" style="border-left: 4px solid #713F12; border-radius: 6px;">
            <small class="text-muted fw-semibold">ĐIỂM TRUNG BÌNH CUỐI KỲ</small>
            <h3 class="fw-bold text-warning mt-1 mb-0">{{ $stats['avg_final_grade'] ?: '--' }} <span class="fs-6 text-muted font-normal">/ 10</span></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 p-3" style="border-left: 4px solid #0284C7; border-radius: 6px;">
            <small class="text-muted fw-semibold">TỶ LỆ ĐẠT TỐT NGHIỆP</small>
            <h3 class="fw-bold text-info mt-1 mb-0">{{ $stats['pass_rate'] }}%</h3>
        </div>
    </div>
</div>

{{-- BẢNG DỮ LIỆU ĐỘNG --}}
<div class="table-responsive shadow-sm border rounded">
    <table class="table table-hover align-middle text-center m-0">
        <thead style="background-color: #800000; color: white;">
            <tr>
                <th style="width: 60px; background-color: #800000; color: white;">#</th>
                <th style="background-color: #800000; color: white;">Mã học viên</th>
                <th class="text-start ps-4" style="background-color: #800000; color: white;">Họ tên</th>
                <th style="background-color: #800000; color: white;">Điểm giữa khóa</th>
                <th style="background-color: #800000; color: white;">Điểm cuối khóa</th>
                <th style="background-color: #800000; color: white;">Tổng buổi nghỉ</th>
                <th style="background-color: #800000; color: white;">Tổng thiếu bài</th>
                <th style="width: 160px; background-color: #800000; color: white;">Trạng thái đầu ra</th>
            </tr>
        </thead>
        <tbody>
            @forelse($studentsPaginator as $index => $student)
            <tr>
                <td>{{ $studentsPaginator->firstItem() + $index }}</td>
                <td class="fw-semibold text-dark">{{ $student->id }}</td>
                <td class="text-start ps-4 fw-medium">{{ $student->name }}</td>
                
                {{-- Điểm giữa khóa --}}
                <td>
                    @if(!is_null($student->midterm_grade))
                        <span class="fw-bold text-dark">{{ number_format($student->midterm_grade, 1) }}</span>
                    @else
                        <span class="text-muted">--</span>
                    @endif
                </td>

                {{-- Điểm cuối khóa --}}
                <td>
                    @if(!is_null($student->final_grade))
                        <span class="fw-bold text-danger fs-6">{{ number_format($student->final_grade, 1) }}</span>
                    @else
                        <span class="text-muted">--</span>
                    @endif
                </td>

                {{-- Chuyên cần và thiếu bài --}}
                <td class="{{ $student->total_absences >= 5 ? 'text-danger fw-bold' : '' }}">{{ $student->total_absences }}</td>
                <td class="{{ $student->total_missing >= 9 ? 'text-danger fw-bold' : '' }}">{{ $student->total_missing }}</td>

                {{-- Khối trạng thái đầu ra tô màu background nguyên ô theo mockup --}}
                @if($student->output_status === 'Đạt')
                    <td class="fw-bold text-success" style="background-color: #CCFFCC !important; color: #006600 !important;">Đạt</td>
                @else
                    <td class="fw-bold text-danger" style="background-color: #FFCCCC !important; color: #CC0000 !important;">Không đạt</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-muted py-4">Chưa có dữ liệu tổng kết cho lớp học này.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- PHÂN TRANG BOOTSTRAP DYNAMIC --}}
<div class="d-flex justify-content-center mt-4">
    {{ $studentsPaginator->links() }}
</div>
@endsection