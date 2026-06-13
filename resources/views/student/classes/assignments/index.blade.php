@extends('student.classes.layout')

@section('title', 'Bài tập lớp học')

@section('class_content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-dark m-0 text-uppercase">
        <i class="bi bi-list-task text-danger"></i> Danh sách bài tập
    </h6>
    <span class="text-muted fs-7">Tổng số: <strong>{{ $assignments->count() }} bài tập</strong></span>
</div>

<div class="table-responsive shadow-sm" style="border-radius: 8px;">
    <table class="table-arena m-0">
        <thead>
            <tr>
                <th style="width: 50px;">STT</th>
                <th>Mã Bài</th>
                <th style="text-align: left;">Tiêu Đề</th>
                <th>Hạn Nộp</th>
                <th>Loại</th>
                <th>Trạng Thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignments as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="fw-bold text-dark">#{{ $item->id }}</td>
                <td style="text-align: left;" class="fw-medium">{{ $item->title }}</td>
                <td class="text-secondary fs-7">{{ $item->due_time->format('H:i - d/m/Y') }}</td>
                <td>{{ $item->typeLabel() }}</td>
                <td>
                    @php $sub = $item->submissions->first(); @endphp
                    @if($sub)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-8">
                            {{ $sub->status }}
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 fs-8">
                            Chưa nộp
                        </span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('student.assignments.show', $item->id) }}" 
                       class="btn btn-sm btn-primary px-3 fw-bold fs-8">
                       {{ $sub ? 'Xem bài' : 'Làm bài' }}
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection