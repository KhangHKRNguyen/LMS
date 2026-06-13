<div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 8px;">
    <h6 class="fw-bold text-dark mb-3 border-bottom pb-2 text-uppercase fs-8 text-secondary">Danh sách câu hỏi nhanh</h6>
    <div class="d-flex flex-wrap gap-2">
        @for ($i = 1; $i <= $total; $i++)
            <button type="button" class="btn btn-sm p-0 d-flex align-items-center justify-content-center fw-bold rounded-circle {{ $i <= 3 ? 'btn-success text-white' : 'btn-outline-secondary' }}" style="width: 36px; height: 36px; font-size: 13px;">
                {{ $prefix }}{{ $i }}
            </button>
        @endfor
    </div>
    <hr class="my-3">
    <div class="d-flex gap-3 align-items-center text-muted" style="font-size: 12px;">
        <div><span class="d-inline-block bg-success rounded-circle me-1" style="width: 10px; height: 10px;"></span> Đã điền</div>
        <div><span class="d-inline-block bg-white border border-secondary rounded-circle me-1" style="width: 10px; height: 10px;"></span> Chưa làm</div>
    </div>
</div>