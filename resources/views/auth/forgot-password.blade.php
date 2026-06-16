<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Quên mật khẩu? Không vấn đề gì. Chỉ cần cho chúng tôi biết địa chỉ email của bạn và chúng tôi sẽ gửi cho bạn một liên kết đặt lại mật khẩu.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="captcha" :value="__('Mã xác nhận (Captcha)')" />
            
            <div class="flex items-center mt-1 gap-2">
                <img src="{{ url('/captcha-image') }}" id="captcha-img" class="rounded border" alt="Captcha">
                <button type="button" 
                        onclick="document.getElementById('captcha-img').src = '{{ url('/captcha-image') }}?' + Math.random()" 
                        class="text-sm text-gray-500 hover:underline">
                    {{ __('Đổi mã khác') }}
                </button>
            </div>

            <x-text-input id="captcha" class="block mt-2 w-full" type="text" name="captcha" required placeholder="Nhập chữ hoa chữ thường chính xác" />
            <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button style="background-color: #990000;">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
