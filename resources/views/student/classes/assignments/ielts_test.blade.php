@extends('layouts.app')

@section('title', 'Phòng thi trực tuyến - IELTS Full Test')

@section('content')
<div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 10px; z-index: 1020; border-radius: 8px;">
    <div class="card-body bg-dark text-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-3" style="border-radius: 8px;">
        <div>
            <span class="badge bg-danger text-uppercase fw-bold px-2 py-1 fs-8 mb-1">Đang tính giờ</span>
            <h5 class="m-0 fw-bold">[TEST-PRO] IELTS Simulated Academic Full Test #04</h5>
        </div>
        
        <div class="d-flex align-items-center gap-4">
            <div class="text-center bg-secondary bg-opacity-25 px-3 py-1.5 rounded border border-secondary">
                <span class="fs-8 text-secondary-subtle d-block text-uppercase fw-semibold" style="font-size: 11px;">Thời gian còn lại</span>
                <span class="fs-4 fw-mono text-warning fw-bold"><i class="bi bi-clock"></i> 02:40:15</span>
            </div>
            
            <button type="button" class="btn btn-danger fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#confirmSubmitExamModal" style="height: 46px; border-radius: 4px;">
                NỘP BÀI THI <i class="bi bi-send-check-fill ms-1"></i>
            </button>
        </div>
    </div>
</div>

<ul class="nav nav-pills nav-justified mb-4 p-1.5 bg-white shadow-sm rounded-3" id="ieltsExamTab" role="tablist" style="border: 1px solid #E2E8F0;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold py-3 text-uppercase d-flex align-items-center justify-content-center gap-2" id="listening-tab" data-bs-toggle="tab" data-bs-target="#listening" type="button" role="tab"><i class="bi bi-headphones"></i> 1. Listening</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-3 text-uppercase d-flex align-items-center justify-content-center gap-2" id="reading-tab" data-bs-toggle="tab" data-bs-target="#reading" type="button" role="tab"><i class="bi bi-book"></i> 2. Reading</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-3 text-uppercase d-flex align-items-center justify-content-center gap-2" id="writing-tab" data-bs-toggle="tab" data-bs-target="#writing" type="button" role="tab"><i class="bi bi-pencil-square"></i> 3. Writing</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-3 text-uppercase d-flex align-items-center justify-content-center gap-2" id="speaking-tab" data-bs-toggle="tab" data-bs-target="#speaking" type="button" role="tab"><i class="bi bi-mic"></i> 4. Speaking</button>
    </li>
</ul>

<div class="tab-content" id="ieltsExamTabContent">
    
    <div class="tab-pane fade show active" id="listening" role="tabpanel" aria-labelledby="listening-tab">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-body border-0 shadow-sm p-4 bg-white mb-4" style="border-radius: 8px;">
                    <div class="p-3 rounded-3 mb-4 d-flex align-items-center gap-3 border" style="background-color: #F8FAFC;">
                        <i class="bi bi-play-circle-fill text-primary fs-2"></i>
                        <div class="flex-grow-1">
                            <label class="form-label fw-bold m-0 fs-7 text-dark">Bấm phát để nghe đoạn hội thoại (Chỉ được nghe 1 lần duy nhất):</label>
                            <audio class="w-100 mt-2" controls>
                                <source src="#" type="audio/mpeg">
                            </audio>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold text-dark mb-3">PART 1: Questions 1 - 5. Complete the notes below. Write NO MORE THAN TWO WORDS AND/OR A NUMBER.</h6>
                    <div class="p-3 border rounded mb-3 fs-7 bg-light text-secondary lh-lg">
                        <strong>Transport Survey Notes:</strong> <br>
                        Name of clerk: Eddie Smith <br>
                        1. Destination of traveler: <input type="text" class="form-control d-inline-block mx-2 border-secondary-subtle" style="width: 180px; height: 30px; font-size:13px;" placeholder="Type answer..."> <br>
                        2. Frequency of public transport usage: <input type="text" class="form-control d-inline-block mx-2 border-secondary-subtle" style="width: 180px; height: 30px; font-size:13px;" placeholder="Type answer...">
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                @include('student.classes.assignments.partials.question_palette', ['total' => 10, 'prefix' => 'L'])
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="reading" role="tabpanel" aria-labelledby="reading-tab">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card card-body border-0 shadow-sm p-4 bg-white overflow-y-auto" style="border-radius: 8px; max-height: 600px;">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">READING PASSAGE 1</h5>
                    <p class="fw-bold text-secondary fs-7">The Rise of Artificial Intelligence in Modern Education</p>
                    <div class="text-secondary fs-7 lh-base" style="text-align: justify;">
                        <p>Paragraph A: Artificial Intelligence (AI) has rapidly transformed from a futuristic concept into a tangible reality within global educational frameworks...</p>
                        <p>Paragraph B: One of the primary advantages of incorporating intelligent tutoring systems into classrooms is customization. Traditional group-teaching models often fail to cater to individual student speeds...</p>
                        <p>Paragraph C: However, critics raise substantial concerns regarding data privacy and the potential loss of critical human interactions between students and mentors...</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-body border-0 shadow-sm p-4 bg-white mb-4" style="border-radius: 8px;">
                    <h6 class="fw-bold text-dark mb-3">Questions 11 - 13: Choose the correct letter, A, B, C or D.</h6>
                    <div class="mb-4">
                        <p class="fs-7 fw-medium text-dark">11. What is the primary benefit of custom AI systems according to Paragraph B?</p>
                        <div class="form-check mb-2 fs-7">
                            <input class="form-check-input" type="radio" name="q11" id="q11_a">
                            <label class="form-check-input-label text-secondary" for="q11_a">A. Reducing the financial burden on schools.</label>
                        </div>
                        <div class="form-check mb-2 fs-7">
                            <input class="form-check-input" type="radio" name="q11" id="q11_b">
                            <label class="form-check-input-label text-secondary" for="q11_b">B. Customizing learning speeds for individual students.</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="writing" role="tabpanel" aria-labelledby="writing-tab">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-body border-0 shadow-sm p-4 bg-white mb-4" style="border-radius: 8px;">
                    <div class="alert alert-warning border-0 p-3 mb-3 fs-7" style="border-radius: 6px;">
                        <strong>WRITING TASK 2:</strong> You should spend about 40 minutes on this task. Write at least 250 words.
                    </div>
                    <p class="fw-bold text-dark fs-7 mb-3">Topic: Some people believe that wild animals should be protected in artificial environments like zoos. Others think they should live freely in their natural habitats. Discuss both views and give your opinion.</p>
                    
                    <div class="position-relative">
                        <textarea class="form-control border-secondary-subtle p-3 fs-7" rows="12" placeholder="Write your essay here..." style="border-radius: 6px; line-height: 1.6;"></textarea>
                        <span class="position-absolute bottom-0 end-0 m-3 badge bg-dark text-white fw-medium px-2 py-1 fs-8">Words: 0 / 250</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-body border-0 shadow-sm p-3 bg-white" style="border-radius: 8px;">
                    <span class="fs-8 text-muted d-block fw-bold text-uppercase mb-2">Trạng thái Task</span>
                    <div class="d-flex justify-content-between text-secondary fs-7 mb-1"><span>Task 1:</span> <span class="text-success fw-bold">Đã hoàn thành</span></div>
                    <div class="d-flex justify-content-between text-secondary fs-7"><span>Task 2:</span> <span class="text-danger fw-bold">Đang viết...</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="speaking" role="tabpanel" aria-labelledby="speaking-tab">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-body border-0 shadow-sm p-4 bg-white text-center mb-4" style="border-radius: 8px;">
                    <div class="text-start alert alert-info border-0 fs-7 mb-4">
                        <strong>PART 2: Individual Long Turn (2-3 mins).</strong> Read the prompt carefully. You will have 1 minute to prepare your speech before recording.
                    </div>
                    
                    <h5 class="fw-bold text-dark mb-4">"Describe a memorable long journey you went on."</h5>
                    
                    <div class="my-4 py-4 position-relative">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10 border border-danger border-2 text-danger mb-3 pulse-animation-indicator" style="width: 90px; height: 90px;">
                            <i class="bi bi-mic-fill fs-1"></i>
                        </div>
                        <span class="fw-bold d-block text-danger fs-7 mb-1 text-uppercase tracking-wider">Đang ghi âm giọng nói</span>
                        <span class="text-muted fs-8 fw-mono">Thời lượng: 01:24 / 03:00</span>
                    </div>

                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <button type="button" class="btn btn-outline-secondary px-3 fs-7 fw-medium"><i class="bi bi-arrow-clockwise"></i> Ghi âm lại</button>
                        <button type="button" class="btn btn-danger px-4 fs-7 fw-bold"><i class="bi bi-stop-fill"></i> Dừng ghi âm</button>
                        <button type="button" class="btn btn-success px-3 fs-7 fw-medium" disabled><i class="bi bi-play-fill"></i> Nghe lại bài nói</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-body border-0 shadow-sm p-3 bg-white" style="border-radius: 8px;">
                    <span class="fs-8 text-muted d-block fw-bold text-uppercase mb-2">Tiến trình Ghi âm</span>
                    <div class="d-flex align-items-center justify-content-between fs-7 text-secondary"><span class="fw-medium">Lưu trữ tập tin nộp:</span> <span class="badge bg-light text-dark border">Audio_Part2.wav</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmSubmitExamModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="confirmSubmitLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px;">
            <div class="modal-header bg-danger text-white py-3" style="border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h6 class="modal-title fw-bold text-uppercase" id="confirmSubmitLabel"><i class="bi bi-exclamation-triangle-fill text-warning"></i> Cảnh báo xác nhận nộp bài thi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="text-danger mb-3"><i class="bi bi-shield-lock-fill" style="font-size: 50px;"></i></div>
                <h5 class="fw-bold text-dark mb-2">Bạn có chắc chắn muốn nộp bài?</h5>
                <p class="text-muted fs-7 mb-4">Hệ thống ghi nhận bạn đã hoàn thành các phần thi kỹ năng. Sau khi bấm nút xác nhận, bạn sẽ **không thể chỉnh sửa** bất kỳ câu trả lời nào nữa.</p>
                
                <div class="text-start p-3 bg-light rounded border border-secondary-subtle" style="font-size: 13px;">
                    <div class="d-flex justify-content-between text-secondary mb-1.5"><span>Kỹ năng Listening:</span> <span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Đã làm 10/10 câu</span></div>
                    <div class="d-flex justify-content-between text-secondary mb-1.5"><span>Kỹ năng Reading:</span> <span class="text-warning fw-bold"><i class="bi bi-exclamation-circle"></i> Chưa làm xong (Còn 3 câu trống)</span></div>
                    <div class="d-flex justify-content-between text-secondary mb-1.5"><span>Kỹ năng Writing:</span> <span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Đã có dữ liệu</span></div>
                    <div class="d-flex justify-content-between text-secondary"><span>Kỹ năng Speaking:</span> <span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Đã đính kèm tệp ghi âm</span></div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 d-flex justify-content-end gap-2 p-3" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                <button type="button" class="btn btn-sm btn-secondary px-3 py-2 fw-medium" data-bs-dismiss="modal" style="border-radius: 4px;">Tiếp tục làm bài</button>
                <a href="#" class="btn btn-sm btn-danger px-4 py-2 fw-bold" style="border-radius: 4px;">XÁC NHẬN NỘP</a>
            </div>
        </div>
    </div>
</div>

<style>
    #ieltsExamTab .nav-link { color: #475569; border: 1px solid transparent; transition: all 0.2s; }
    #ieltsExamTab .nav-link.active { background-color: var(--primary-color) !important; color: white !important; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .fw-mono { font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    
    /* Hiệu ứng nhấp nháy cho vòng tròn thu âm */
    @keyframes pulse-red {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.5); }
        70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(220, 53, 69, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    .pulse-animation-indicator { animation: pulse-red 2s infinite; }
</style>
@endsection