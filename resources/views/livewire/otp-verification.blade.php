<div class="space-y-4">
    @if($message)
        <div class="alert alert-{{ $messageType }} p-4 rounded-lg border">
            <div class="flex items-center">
                @if($messageType === 'success')
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                @elseif($messageType === 'error')
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                @endif
                <span class="text-sm font-medium">{{ $message }}</span>
            </div>
        </div>
    @endif

    @if(!$isVerified)
        <div class="space-y-4">
            <!-- Phone Number Input -->
            <div>
                <label for="recipient" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('filamentsmssender::filamentsmssender.phone_number') }}
                </label>
                <input
                    type="tel"
                    id="recipient"
                    wire:model="recipient"
                    placeholder="{{ __('filamentsmssender::filamentsmssender.phone_number_placeholder') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    @if($otpSent) disabled @endif
                >
                @error('recipient')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Identifier Input (Optional) -->
            <div>
                <label for="identifier" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('filamentsmssender::filamentsmssender.identifier_optional') }}
                </label>
                <input
                    type="text"
                    id="identifier"
                    wire:model="identifier"
                    placeholder="{{ __('filamentsmssender::filamentsmssender.identifier_placeholder') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    @if($otpSent) disabled @endif
                >
                @error('identifier')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            @if(!$otpSent)
                <!-- Send OTP Button -->
                <button
                    wire:click="sendOtp"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200"
                >
                    {{ __('filamentsmssender::filamentsmssender.send_otp') }}
                </button>
            @else
                <!-- OTP Code Input -->
                <div>
                    <label for="otpCode" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('filamentsmssender::filamentsmssender.enter_otp_code') }}
                    </label>
                    <input
                        type="text"
                        id="otpCode"
                        wire:model="otpCode"
                        placeholder="{{ __('filamentsmssender::filamentsmssender.otp_code_placeholder') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        maxlength="8"
                        autocomplete="one-time-code"
                    >
                    @error('otpCode')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button
                        wire:click="verifyOtp"
                        class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200"
                    >
                        {{ __('filamentsmssender::filamentsmssender.verify_otp') }}
                    </button>
                    <button
                        wire:click="resetForm"
                        class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200"
                    >
                        {{ __('filamentsmssender::filamentsmssender.reset') }}
                    </button>
                </div>

                <!-- Resend OTP -->
                <div class="text-center">
                    <button
                        wire:click="sendOtp"
                        class="text-sm text-blue-600 hover:text-blue-800 underline focus:outline-none"
                    >
                        {{ __('filamentsmssender::filamentsmssender.resend_otp') }}
                    </button>
                </div>
            @endif
        </div>
    @else
        <!-- Success State -->
        <div class="text-center py-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('filamentsmssender::filamentsmssender.verification_successful') }}</h3>
            <p class="text-gray-600 mb-4">{{ __('filamentsmssender::filamentsmssender.phone_verified_successfully') }}</p>
            <button
                wire:click="resetForm"
                class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200"
            >
                {{ __('filamentsmssender::filamentsmssender.verify_another_number') }}
            </button>
        </div>
    @endif
</div>
