@php
    // Safety check for currentUser variable
    $currentUser = $currentUser ?? null;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profile Settings - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.x/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .logo-placeholder {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-bg shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="logo-container">
                    <div class="logo-placeholder">ET</div>
                    <h1 class="text-2xl font-bold text-white">Expense Tracker</h1>
                </div>
                <a href="{{ route('expenses.index') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition duration-200">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            @if(!$currentUser)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <strong>Error:</strong> User session not found. 
                    <a href="{{ route('login') }}" class="underline">Please login again</a>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 gradient-bg">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile Settings
                        </h2>
                    </div>

                    <div class="p-6">
                        @if(session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                                @foreach($errors->all() as $error)
                                    <p class="text-sm">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Profile Picture -->
                            <div class="mb-6">
                                <label class="block font-medium text-gray-700 mb-2">Profile Picture</label>
                                <div class="flex items-center space-x-4">
                                    <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center">
                                        @if($currentUser->profile_picture ?? false)
                                            <img src="{{ asset('storage/' . $currentUser->profile_picture) }}" alt="Profile" class="w-20 h-20 rounded-full object-cover">
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <input type="file" name="profile_picture" accept="image/*" class="border border-gray-300 rounded-lg px-3 py-2">
                                </div>
                                @error('profile_picture')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                            </div>

                            <!-- Name -->
                            <div class="mb-6">
                                <label for="name" class="block font-medium text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $currentUser->name ?? '') }}" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-6">
                                <label for="email" class="block font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $currentUser->email ?? '') }}" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('email')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                            </div>

                            <!-- Phone -->
                            <div class="mb-6">
                                <label for="phone" class="block font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $currentUser->phone ?? '') }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('phone')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                            </div>

                            <!-- Currency Preference -->
                            <div class="mb-6">
                                <label for="currency" class="block font-medium text-gray-700 mb-2">Preferred Currency</label>
                                <select name="currency" id="currency" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="PHP" {{ old('currency', $currentUser->currency ?? 'PHP') == 'PHP' ? 'selected' : '' }}>Philippine Peso (₱)</option>
                                    <option value="USD" {{ old('currency', $currentUser->currency ?? '') == 'USD' ? 'selected' : '' }}>US Dollar ($)</option>
                                    <option value="EUR" {{ old('currency', $currentUser->currency ?? '') == 'EUR' ? 'selected' : '' }}>Euro (€)</option>
                                    <option value="GBP" {{ old('currency', $currentUser->currency ?? '') == 'GBP' ? 'selected' : '' }}>British Pound (£)</option>
                                </select>
                                @error('currency')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                            </div>

                            <!-- Monthly Budget -->
                            <div class="mb-6">
                                <label for="monthly_budget" class="block font-medium text-gray-700 mb-2">Monthly Budget Limit</label>
                                <input type="number" step="0.01" name="monthly_budget" id="monthly_budget" value="{{ old('monthly_budget', $currentUser->monthly_budget ?? '') }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('monthly_budget')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-lg transition duration-200 transform hover:scale-105">
                                    Update Profile
                                </button>
                            </div>
                        </form>

                        <!-- Change Password Section -->
                        <div class="border-t border-gray-200 mt-8 pt-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Change Password</h3>
                            <form action="{{ route('profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="current_password" class="block font-medium text-gray-700 mb-2">Current Password</label>
                                        <input type="password" name="current_password" id="current_password" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('current_password')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div>
                                        <label for="new_password" class="block font-medium text-gray-700 mb-2">New Password</label>
                                        <input type="password" name="new_password" id="new_password" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('new_password')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="new_password_confirmation" class="block font-medium text-gray-700 mb-2">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                <div class="flex justify-end mt-4">
                                    <button type="submit" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg transition duration-200">
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Current Profile Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 mt-6 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Profile Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Name:</span>
                            <p class="font-medium">{{ $currentUser->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Email:</span>
                            <p class="font-medium">{{ $currentUser->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Phone:</span>
                            <p class="font-medium">{{ $currentUser->phone ?? 'Not set' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Currency:</span>
                            <p class="font-medium">
                                @if($currentUser && method_exists($currentUser, 'getCurrencySymbol'))
                                    {{ $currentUser->getCurrencySymbol() }} ({{ $currentUser->currency ?? 'PHP' }})
                                @else
                                    ₱ (PHP)
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">Monthly Budget:</span>
                            <p class="font-medium">
                                @if($currentUser->monthly_budget ?? false)
                                    @if($currentUser && method_exists($currentUser, 'getCurrencySymbol'))
                                        {{ $currentUser->getCurrencySymbol() }}{{ number_format($currentUser->monthly_budget, 2) }}
                                    @else
                                        ₱{{ number_format($currentUser->monthly_budget, 2) }}
                                    @endif
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">Member Since:</span>
                            <p class="font-medium">
                                @if($currentUser->created_at ?? false)
                                    {{ $currentUser->created_at->format('M d, Y') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>

</html>