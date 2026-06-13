@extends('layouts.app')

@section('title', 'Trang chủ Học viên')

@section('content')
<div class="container-fluid px-4">
    <div class="text-center my-4">
        <h3 class="fw-bold text-danger text-uppercase mb-1">XIN CHÀO, {{ mb_uppercase($student->name ?? 'TÊN HỌC VIÊN') }}</h3>
        <p class="text-secondary fw-semibold">{{ $currentDateString }}</p>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="position-relative" style="width: 280px;">
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" id="searchClass" class="form-control ps-5 border-secondary-subtle" placeholder="Search" style="border-radius: 4px; height: 36px; font-size: 14px;">
        </div>
    </div>

    <div class="card border-0 shadow-sm custom-table-card" style="border-radius: 4px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0 text-center" style="font-size: 14px;">
                <thead style="background-color: #800000; color: white;">
                    <tr>
                        <th scope="col" class="py-3" style="width: 50px;">#</th>
                        <th scope="col" class="py-3">Mã lớp</th>
                        <th scope="col" class="py-3">Tên lớp</th>
                        <th scope="col" class="py-3">Số học viên</th>
                        <th scope="col" class="py-3">Khóa học</th>
                        <th scope="col" class="py-3">Trạng thái</th>
                        <th scope="col" class="py-3" style="width: 120px;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="classTableBody">
                    @forelse($classes as $index => $class)
                        <tr>
                            <td class="fw-bold">{{ $index + 1 }}</td>
                            {{-- Sử dụng trường dữ liệu hoặc tạm thời dùng ID lớp nếu không có cột mã lớp riêng --}}
                            <td class="text-muted fw-semibold">U{{ str_pad($class->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="fw-bold text-dark text-start ps-4">{{ $class->class_name }}</td>
                            <td>{{ $class->students_count ?? 0 }}</td>
                            <td>{{ $class->course->name ?? 'Chưa cập nhật' }}</td>
                            <td>
                                {{-- Kiểm tra trạng thái lớp học dựa trên trường status hoặc end_time --}}
                                @if($class->status === 'active' || ($class->end_time && $class->end_time->isFuture()))
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5" style="border-radius: 20px; font-size: 12px;">
                                        Đang hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5" style="border-radius: 20px; font-size: 12px;">
                                        Kết thúc
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('student.assignments.index', ['class_id' => $class->id]) }}" 
                                class="btn btn-sm btn-primary px-3 text-white fw-semibold">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted py-4">Bạn chưa tham gia vào lớp học nào trong hệ thống.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Đồng bộ CSS theo phong cách bảng sẫm màu đỏ đô của trung tâm */
    .table th {
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
    }
    .table td {
        padding-top: 12px;
        padding-bottom: 12px;
        border-color: #f2f2f2;
    }
    .table-striped>tbody>tr:nth-of-type(odd)>* {
        --bs-table-bg-type: rgba(0, 0, 0, 0.01);
    }
    /* Hiệu ứng trỏ chuột nhẹ vào hàng */
    .table-hover>tbody>tr:hover>* {
        --bs-table-bg-type: rgba(0, 0, 0, 0.03);
    }
</style>

<script>
    document.getElementById('searchClass').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#classTableBody tr');
        
        rows.forEach(function(row) {
            let text = row.textContent.toLowerCase();
            if(text.includes(value)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endsection