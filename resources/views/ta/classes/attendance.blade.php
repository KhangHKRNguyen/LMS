@extends('layouts.taclassroom')

@section('title', 'Điểm danh lớp học - TA')

@section('taclassroom_content')
<style>
    .radio-present:checked { background-color: #198754 !important; border-color: #198754 !important; }
    .radio-absent:checked { background-color: #dc3545 !important; border-color: #dc3545 !important; }
    .table-arena th { background-color: #800000 !important; color: white !important; text-align: center; vertical-align: middle; font-size: 13px; border: 1px solid #dee2e6; }
    .table-arena td { vertical-align: middle; text-align: center; border: 1px solid #dee2e6; font-size: 13.5px; }
    .disabled-session { background-color: #f8f9fa; color: #6c757d; }
</style>

<div class="mb-3">
    <h5 class="fw-bold m-0 text-dark">QUẢN LÝ LỚP HỌC - {{ $class->class_name }}</h5>
    <small class="text-muted fw-semibold">Khóa học chuyên môn: {{ $class->course->name ?? 'N/A' }}</small>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('ta.classes.attendance.store', $class->id) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-arena align-middle">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 50px;">STT</th>
                            <th rowspan="2" style="min-width: 180px;">Họ và tên</th>
                            {{-- Tiêu đề các buổi học --}}
                            @foreach($lessonSessions as $index => $session)
                                <th colspan="2" class="{{ !$session->is_editable ? 'bg-secondary text-white' : '' }}">
                                    Buổi {{ $index + 1 }} <br>
                                    <small style="font-size: 11px;">({{ \Carbon\Carbon::parse($session->lesson_date)->format('d/m') }})</small>
                                    @if(!$session->is_editable)
                                        <br><span class="badge bg-light text-dark" style="font-size: 9px;">Đã khóa</span>
                                    @endif
                                </th>
                            @endforeach
                            <th rowspan="2" style="width: 100px;">Tổng vắng</th>
                            <th rowspan="2" style="width: 110px;">Cảnh báo</th>
                        </tr>
                        <tr>
                            {{-- Các cột con Có mặt / Vắng --}}
                            @foreach($lessonSessions as $session)
                                <th style="font-size: 11px; padding: 4px;">Đủ</th>
                                <th style="font-size: 11px; padding: 4px;">Vắng</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $key => $student)
                            @php $totalAbsent = 0; @endphp
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td class="text-start fw-semibold text-dark">{{ $student->name }}</td>

                                @foreach($lessonSessions as $session)
                                    @php
                                        // Lấy trạng thái lưu trong Database nếu có
                                        $dbStatus = $attendanceMatrix[$student->id][$session->id] ?? null;

                                        if ($session->is_editable) {
                                            // Nếu trong ngưỡng cho phép chỉnh sửa: Default là 'present' nếu DB chưa có dữ liệu
                                            $currentStatus = $dbStatus ?? 'present';
                                        } else {
                                            // Nếu NGOÀI ngưỡng cho phép chỉnh sửa: 
                                            // Đã qua ngày hoặc chưa tới ngày, nếu trước đó không có dữ liệu vắng thì tính là "Mặc định Đủ"
                                            $currentStatus = $dbStatus ?? 'present';
                                        }

                                        if ($currentStatus === 'absent') {
                                            $totalAbsent++;
                                        }
                                    @endphp

                                    {{-- Ô Chọn Có Mặt --}}
                                    <td class="{{ !$session->is_editable ? 'disabled-session' : '' }}">
                                        <input type="radio" 
                                               name="attendance[{{ $session->id }}][{{ $student->id }}]" 
                                               value="present" 
                                               class="form-check-input radio-present"
                                               {{ $currentStatus === 'present' ? 'checked' : '' }}
                                               {{ !$session->is_editable ? 'disabled' : '' }}>
                                    </td>

                                    {{-- Ô Chọn Vắng Mặt --}}
                                    <td class="{{ !$session->is_editable ? 'disabled-session' : '' }}">
                                        <input type="radio" 
                                               name="attendance[{{ $session->id }}][{{ $student->id }}]" 
                                               value="absent" 
                                               class="form-check-input radio-absent"
                                               {{ $currentStatus === 'absent' ? 'checked' : '' }}
                                               {{ !$session->is_editable ? 'disabled' : '' }}>
                                    </td>
                                @endforeach

                                {{-- Tính toán hiển thị tổng số buổi vắng --}}
                                <td class="fw-bold text-dark">{{ $totalAbsent }}</td>
                                <td>
                                    @if($totalAbsent >= 5)
                                        <span class="text-danger fw-bold">Mức 2</span>
                                    @elseif($totalAbsent >= 3)
                                        <span class="text-warning fw-bold">Mức 1</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + (count($lessonSessions) * 2) }}" class="p-4 text-muted text-center">
                                    Không tìm thấy dữ liệu học viên trong lớp học này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->isNotEmpty() && $lessonSessions->isNotEmpty())
                <div class="text-end mt-4">
                    <button type="submit" class="btn text-white fw-bold px-4 py-2 shadow-sm" style="background-color: #800000; border-radius: 4px; font-size: 14px;">
                        LƯU THAY ĐỔI ĐIỂM DANH
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection