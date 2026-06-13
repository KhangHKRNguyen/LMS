@extends('layouts.classroom')

@section('title', 'Quản lý bài nộp - Giảng viên')

{{-- Kích hoạt trạng thái Active cho menu Bài Nộp --}}
@section('menu_bai_nop_class', 'btn w-100 text-start py-2 fw-bold text-white')
@section('menu_bai_nop_style', 'background-color: #800000;')

@section('classroom_content')
{{-- CHỂ ĐỘ VIEW 1: TỔNG QUAN CÁC BÀI TẬP ĐÃ GIAO --}}
<div id="exerciseOverviewSection" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold m-0"><i class="bi bi-arrow-left me-2"></i>LỚP HỌC - U206 (Danh mục bài tập)</h5>
    </div>
    <div class="row">
        <div class="col-md-2 border rounded p-2 bg-light-subtle">
            <select class="form-select form-select-sm mb-2"><option>Buổi học</option></select>
            <div class="list-group list-group-flush small fw-bold">
                <button class="list-group-item list-group-item-action text-danger active-session border-start border-3 border-danger">Buổi 1</button>
                <button class="list-group-item list-group-item-action">Buổi 2</button>
                <button class="list-group-item list-group-item-action">Buổi 3</button>
                <button class="list-group-item list-group-item-action">Buổi 16</button>
            </div>
        </div>
        <div class="col-md-10">
            <table class="table table-bordered text-center align-middle small">
                <thead style="background-color: #800000; color: white;">
                    <tr>
                        <th>#</th><th>Buổi</th><th>Bài tập</th><th>Thời gian làm</th><th>Thời gian mở</th><th>Thời gian đóng</th><th>Loại</th><th>Số lần làm lại</th><th>Chờ chấm</th><th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td><td>1</td><td class="fw-semibold">Đề kiểm tra Speaking</td><td>30 phút</td><td>2026-06-03 19:00</td><td>2026-06-10 23:59</td><td>Bài tập về nhà</td><td>2</td><td class="fw-bold text-danger">8</td>
                        <td><a href="javascript:void(0)" onclick="switchView('detail')" class="text-primary fw-bold text-decoration-none">Xem bài nộp</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- CHẾ ĐỘ VIEW 2: CHI TIẾT BÀI NỘP CỦA HỌC VIÊN --}}
<div id="submissionDetailSection" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold m-0"><a href="javascript:void(0)" onclick="switchView('overview')" class="text-decoration-none text-dark"><i class="bi bi-arrow-left me-2"></i>LỚP HỌC - U206</a></h5>
            <small class="text-muted fw-bold">Tổng số bài nộp: 13</small>
        </div>
    </div>
    <div class="table-responsive border shadow-sm rounded">
        <table class="table text-center align-middle m-0">
            <thead style="background-color: #800000; color: white;">
                <tr>
                    <th>#</th><th>Mã học viên</th><th class="text-start">Học viên</th><th>Thời gian nộp</th><th>Trạng thái</th><th>Điểm</th><th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @for($i=1; $i<=3; $i++)
                <tr>
                    <td>{{$i}}</td><td class="text-secondary">101424</td><td class="text-start fw-semibold">Nguyễn Văn A</td><td>2026-06-03 19:00:00</td>
                    <td><span class="badge bg-success-subtle text-success border border-success px-3 py-1" style="background-color:#E8F5E9 !important;">Đã chấm</span></td>
                    <td class="fw-bold text-dark">9.0</td>
                    <td><a href="#" class="btn btn-sm text-primary fw-bold">Chấm bài</a></td>
                </tr>
                @endfor
                @for($i=4; $i<=6; $i++)
                <tr>
                    <td>{{$i}}</td><td class="text-secondary">101424</td><td class="text-start fw-semibold">Nguyễn Văn A</td><td>2026-06-03 19:00:00</td>
                    <td><span class="badge bg-danger-subtle text-danger border border-danger px-3 py-1" style="background-color:#FFEBEE !important;">Chưa chấm</span></td>
                    <td class="text-muted">—</td>
                    <td><a href="#" class="btn btn-sm text-primary fw-bold text-decoration-underline">Chấm bài</a></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>

<script>
    function switchView(view) {
        document.getElementById('exerciseOverviewSection').style.display = (view === 'overview') ? 'block' : 'none';
        document.getElementById('submissionDetailSection').style.display = (view === 'detail') ? 'block' : 'none';
    }
</script>
@endsection