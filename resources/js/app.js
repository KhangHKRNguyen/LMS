import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// 1. Tự động ẩn các thông báo Alert/Toast sau 4 giây
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert-toast');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
});

// 2. Hàm hỗ trợ đóng/mở Modal toàn cục (Dùng qua ID)
window.toggleModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.toggle('hidden');
        modal.classList.toggle('flex');
    }
};

// 3. Bộ đếm ngược thời gian làm bài
window.startCountdown = function(durationInSeconds, displayElementId, callbackOnTimeout) {
    let timer = durationInSeconds;
    const display = document.getElementById(displayElementId);
    
    if (!display) return;

    const interval = setInterval(() => {
        let minutes = parseInt(timer / 60, 10);
        let seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            clearInterval(interval);
            if (typeof callbackOnTimeout === 'function') {
                callbackOnTimeout();
            }
        }
    }, 1000);
    
    return interval; // Trả về để có thể clear nếu nộp bài sớm
};

// Xử lý đóng mở Modal xác nhận chung của toàn hệ thống (Popup xóa/khóa)
window.openArenaModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'flex';
}

window.closeArenaModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
}