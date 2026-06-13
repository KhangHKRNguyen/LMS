@extends('student.classes.layout')

@section('title', 'Tài liệu học tập')
@section('active_materials', 'active-custom')

@section('class_content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-dark m-0 text-uppercase"><i class="bi bi-folder-check text-success"></i> Kho lưu trữ tài liệu bài giảng</h6>
    <span class="text-muted fs-7">Tổng số: <strong>3 tài liệu</strong></span>
</div>

<div class="table-responsive shadow-sm" style="border-radius: 8px;">
    <table class="table-arena m-0">
        <thead>
            <tr>
                <th style="width: 60px;">STT</th>
                <th style="text-align: left; padding-left: 20px;">Tên Bài Học / Chủ Đề Tài Liệu</th>
                <th style="width: 240px; text-align: left;">Tên File Đính Kèm</th>
                <th style="width: 140px;">Ngày Đăng tải</th>
                <th style="width: 130px;">Tải Xuống</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td style="text-align: left; padding-left: 20px;" class="fw-bold text-dark">Bài 1: Tổng quan về kiến trúc MVC và vòng đời Request trong Laravel</td>
                <td style="text-align: left;"><span class="text-danger fw-semibold"><i class="bi bi-file-earmark-pdf"></i> Cuu_Phap_Laravel_Basic.pdf</span></td>
                <td class="text-muted fs-7 fw-medium">10/06/2026</td>
                <td>
                    <a href="#" class="btn btn-sm btn-outline-success px-3 fw-semibold fs-8" style="border-radius: 4px;">
                        <i class="bi bi-download"></i> Tải về
                    </a>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td style="text-align: left; padding-left: 20px;" class="fw-bold text-dark">Bài 2: Thao tác với Database bằng Migration & Seeder chuyên sâu</td>
                <td style="text-align: left;"><span class="text-primary fw-semibold"><i class="bi bi-file-earmark-ppt"></i> Slide_Database_Migration.pptx</span></td>
                <td class="text-muted fs-7 fw-medium">12/06/2026</td>
                <td>
                    <a href="#" class="btn btn-sm btn-outline-success px-3 fw-semibold fs-8" style="border-radius: 4px;">
                        <i class="bi bi-download"></i> Tải về
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection