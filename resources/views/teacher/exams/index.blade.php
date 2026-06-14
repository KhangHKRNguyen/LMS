@extends('layouts.teacher')

@section('teacher_content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0 text-dark">NGÂN HÀNG ĐỀ THI</h4>
            <small class="text-muted fw-semibold">Tổng số đề thi: {{ $exams->total() }}</small>
        </div>
        <a href="{{ route('teacher.exams.create') }}" class="btn btn-sm text-white fw-bold px-3 shadow-sm" style="background-color: #800000;">
            <i class="bi bi-plus-lg"></i> THÊM ĐỀ THI MỚI
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th class="text-start">Tiêu đề đề thi</th>
                            <th>Loại đề</th>
                            <th>Số lượng câu</th>
                            <th>Người tạo</th>
                            <th>Ngày khởi tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>{{ $exam->id }}</td>
                                <td class="text-start fw-bold text-dark">{{ $exam->title }}</td>
                                <td><span class="badge bg-secondary">{{ $exam->assignmentType->name }}</span></td>
                                <td><span class="badge bg-info text-dark">{{ $exam->questions_count }} câu</span></td>
                                <td>{{ $exam->user->name ?? 'Giảng viên' }}</td>
                                <td>{{ $exam->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('teacher.assignments.assign', $exam->id) }}" class="btn btn-sm btn-outline-success py-1 px-2">Giao bài</a>
                                        <a href="{{ route('teacher.exams.show', $exam->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">Chi tiết</a>
                                        <a href="{{ route('teacher.exams.edit', $exam->id) }}" class="btn btn-sm btn-outline-warning py-1 px-2">Sửa</a>
                                        <form action="{{ route('teacher.exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc xóa đề thi này không?')" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted">Trống ngân hàng đề.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {{ $exams->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection