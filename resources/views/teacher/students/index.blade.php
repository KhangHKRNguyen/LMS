@extends('layouts.classroom')

@section('title', 'Quản lý lớp học - Danh sách học viên')

{{-- Kích hoạt trạng thái Active cho menu Học Viên --}}
@section('menu_hoc_vien_class', 'btn w-100 text-start py-2 fw-bold text-white')
@section('menu_hoc_vien_style', 'background-color: #800000;')

@section('classroom_content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold m-0"><a href="#" class="text-decoration-none text-dark"><i class="bi bi-arrow-left me-2"></i>LỚP HỌC - U206</a></h5>
        <small class="text-muted fw-bold">Tổng số học viên: 20</small>
    </div>
    <div class="d-flex align-items-center gap-3">
        <select class="form-select form-select-sm border-danger fw-semibold text-dark" style="width: 130px;">
            <option>Buổi học</option>
        </select>
        <i class="bi bi-bell-fill fs-5 text-warning" style="cursor:pointer;"></i>
        <div class="rounded-circle bg-secondary" style="width: 35px; height: 35px; background: url('https://via.placeholder.com/35') no-repeat center/cover;"></div>
    </div>
</div>

{{-- MA TRẬN THEO DÕI CHUYÊN CẦN --}}
<div class="table-responsive shadow-sm border rounded">
    <table class="table table-hover align-middle text-center m-0">
        <thead style="background-color: #800000; color: white;">
            <tr>
                <th style="width: 50px;">#</th>
                <th>Mã HV</th>
                <th class="text-start">Họ tên</th>
                <th>Buổi 1</th>
                <th>Buổi 2</th>
                <th>Buổi 3</th>
                <th>.....</th>
                <th>Tổng thiếu bài</th>
                <th>Cảnh báo</th>
            </tr>
        </thead>
        <tbody>
            @php
                $mockMatrix = [
                    ['id' => '101424', 'name' => 'Nguyễn Văn A', 'b1' => 'Đủ', 'b2' => 'Thiếu', 'b3' => 'Thiếu', 'total' => 7, 'alarm' => 'Mức 1'],
                    ['id' => '101424', 'name' => 'Nguyễn Văn A', 'b1' => 'Đủ', 'b2' => 'Thiếu', 'b3' => 'Thiếu', 'total' => 7, 'alarm' => 'Mức 1'],
                    ['id' => '101424', 'name' => 'Nguyễn Văn A', 'b1' => 'Đủ', 'b2' => 'Thiếu', 'b3' => 'Thiếu', 'total' => 9, 'alarm' => 'Mức 2'],
                    ['id' => '101424', 'name' => 'Nguyễn Văn A', 'b1' => 'Đủ', 'b2' => 'Thiếu', 'b3' => 'Thiếu', 'total' => 1, 'alarm' => '—'],
                    ['id' => '101424', 'name' => 'Nguyễn Văn A', 'b1' => 'Đủ', 'b2' => 'Đủ', 'b3' => 'Đủ', 'total' => 2, 'alarm' => '—'],
                ];
            @endphp
            @foreach($mockMatrix as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-secondary fw-medium">{{ $row['id'] }}</td>
                <td class="text-start fw-semibold">{{ $row['name'] }}</td>
                <td><span class="badge px-3 py-1 text-dark bg-success-subtle border border-success" style="background-color:#CCFFCC !important;">Đủ</span></td>
                <td><span class="badge px-3 py-1 text-dark bg-danger-subtle border border-danger" style="background-color:#FFCCCC !important;">{{ $row['b2'] }}</span></td>
                <td><span class="badge px-3 py-1 text-dark bg-danger-subtle border border-danger" style="background-color:#FFCCCC !important;">{{ $row['b3'] }}</span></td>
                <td class="text-muted">...</td>
                <td class="fw-bold">{{ $row['total'] }}</td>
                <td class="fw-bold {{ $row['alarm'] !== '—' ? 'text-danger' : 'text-muted' }}">{{ $row['alarm'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- PAGINATION --}}
<div class="d-flex justify-content-center mt-4 gap-1">
    <button class="btn btn-sm btn-secondary px-3 py-1" disabled>Trước</button>
    <button class="btn btn-sm text-white px-3 py-1" style="background-color:#800000;">1</button>
    <button class="btn btn-sm btn-light border px-3 py-1">2</button>
    <button class="btn btn-sm btn-light border px-3 py-1">3</button>
    <button class="btn btn-sm btn-secondary px-3 py-1">Tiếp theo</button>
</div>
@endsection