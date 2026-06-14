@extends('layouts.classroom')

@section('classroom_content')
<div class="container-fluid m-0 p-0" style="min-height: 100vh; display: flex;">
    <div class="flex-grow-1 p-4 bg-white">
        <h5 style="font-weight: bold; margin-top: 20px;">Lớp học</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label style-label">Khóa học</label>
                <div class="form-control style-input">{{ $class->course->name ?? 'Chưa xếp khóa' }}</div>
            </div>
            <div class="col-md-3">
                <label class="form-label style-label">Tên lớp học</label>
                <div class="form-control style-input">{{ $class->class_name ?? 'N/A' }}</div>
            </div>
            <div class="col-md-3">
                <label class="form-label style-label">Phòng học</label>
                <div class="form-control style-input">{{ $class->room ?? 'Chưa xếp phòng' }}</div>
            </div>
            <div class="col-md-3">
                <label class="form-label style-label">Trạng thái</label>
                <div class="form-control style-input">
                    {{ $class->status === 'active' ? 'Đang hoạt động' : 'Kết thúc' }}
                </div>
            </div>
        </div>

        <h5 style="font-weight: bold; margin-top: 30px;">Thông tin Nhân sự</h5>
        <table class="table table-bordered text-center align-middle mt-2">
            <thead style="background-color: #7A0C0C; color: white;">
                <tr>
                    <th>#</th>
                    <th>Mã nhân sự</th>
                    <th>Họ tên</th>
                    <th>Chức vụ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($class->users as $index => $staff)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $staff->employee_code ?? 'M00' . $staff->id }}</td>
                    <td>{{ $staff->name }}</td>
                    <td>
                        @if($staff->role === 'teacher')
                            Giảng viên
                        @elseif($staff->role === 'assistant')
                            Trợ lý lớp học
                        @else
                            Nhân sự quản lý
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-muted">Chưa có nhân sự nào được gán vào lớp này.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <h5 style="font-weight: bold; margin-top: 30px;">Mục tiêu đầu ra</h5>
        <div class="p-3 border rounded post-target-box" style="background-color: #fafafa; line-height: 1.6;">
            <p class="m-0">{!! nl2br(e($class->course->output_target ?? "Chưa cập nhật mục tiêu đầu ra cho khóa học này.")) !!}</p>
        </div>
    </div>
</div>

<style>
    .style-label {
        color: #7A0C0C;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .style-input {
        background-color: #FFF9E6 !important;
        border: 1px solid #BCA574 !important;
        border-radius: 5px;
        color: #333;
        height: auto;
        padding: 8px 12px;
    }
    .post-target-box {
        border: 1px dashed #BCA574 !important;
        font-size: 0.95rem;
        color: #444;
    }
</style>
@endsection