<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Thông tin cá nhân') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Cập nhật thông tin hồ sơ cá nhân của bạn (Tên và Email không thể thay đổi).") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Avatar Section -->
        <div class="border-b pb-6">
            <label class="block text-sm font-semibold text-gray-900 mb-3">Ảnh đại diện</label>
            <div class="flex items-end gap-4">
                <div>
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-24 h-24 rounded-lg object-cover border-2 border-gray-200">
                    @else
                        <div class="w-24 h-24 rounded-lg bg-gray-200 flex items-center justify-center border-2 border-gray-300">
                            <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <input type="file" name="avatar" id="avatar" class="block w-full text-sm text-gray-500 border border-gray-300 rounded-lg p-2 cursor-pointer" accept="image/*">
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF (Max 2MB)</p>
                </div>
            </div>
        </div>

        <!-- Name (Read-only) -->
        <div>
            <x-input-label for="name" :value="__('Tên (không thể thay đổi)')" />
            <x-text-input id="name" type="text" class="mt-1 block w-full bg-gray-50" :value="$user->name" disabled />
        </div>

        <!-- Email (Read-only) -->
        <div>
            <x-input-label for="email" :value="__('Email (không thể thay đổi)')" />
            <x-text-input id="email" type="email" class="mt-1 block w-full bg-gray-50" :value="$user->email" disabled />
        </div>

        <!-- Gender -->
        <div>
            <x-input-label for="gender" :value="__('Giới tính')" />
            <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-red-700 focus:ring-red-700">
                <option value="">-- Chọn giới tính --</option>
                <option value="Nam" {{ old('gender', $user->gender) === 'Nam' ? 'selected' : '' }}>Nam</option>
                <option value="Nữ" {{ old('gender', $user->gender) === 'Nữ' ? 'selected' : '' }}>Nữ</option>
                <option value="Khác" {{ old('gender', $user->gender) === 'Khác' ? 'selected' : '' }}>Khác</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('gender')" />
        </div>

        <!-- Date of Birth -->
        <div>
            <x-input-label for="dob" :value="__('Ngày sinh')" />
            <x-text-input id="dob" name="dob" type="date" class="mt-1 block w-full" :value="old('dob', $user->dob?->format('Y-m-d'))" />
            <x-input-error class="mt-2" :messages="$errors->get('dob')" />
        </div>

        <!-- Phone -->
        <div>
            <x-input-label for="phone" :value="__('Số điện thoại')" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" placeholder="Ví dụ: 0912 345 678" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <!-- Save Button -->
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Lưu thay đổi') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 font-semibold"
                >{{ __('✓ Lưu thành công!') }}</p>
            @endif
        </div>
    </form>
</section>
