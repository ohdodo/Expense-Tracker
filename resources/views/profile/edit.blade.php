<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .profile-picture-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e5e7eb;
        }
        .profile-picture-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            border: 4px solid #e5e7eb;
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
                    <h1 class="text-2xl font-bold text-white">Profile Settings</h1>
                </div>
                <a href="{{ route('expenses.index') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition duration-200">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
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
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Profile Information</h2>
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <!-- Profile Picture Section -->
                    <div class="flex flex-col items-center space-y-4">
                        <div class="relative">
                            @if($currentUser->profile_picture && file_exists(public_path('storage/' . $currentUser->profile_picture)))
                                <img src="{{ asset('storage/' . $currentUser->profile_picture) }}" 
                                     alt="Profile Picture" 
                                     class="profile-picture-preview"
                                     id="profilePreview">
                            @else
                                <div class="profile-picture-placeholder" id="profilePlaceholder">
                                    {{ $currentUser->getInitials() }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex space-x-2">
                            <label for="profile_picture" class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                {{ $currentUser->profile_picture ? 'Change Photo' : 'Choose Photo' }}
                            </label>
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden" onchange="previewImage(this)">
                            
                            @if($currentUser->profile_picture)
                                <a href="{{ route('profile.remove-picture') }}" 
                                   onclick="return confirm('Are you sure you want to remove your profile picture?')"
                                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                    Remove
                                </a>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500">JPG, PNG, GIF up to 2MB</p>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="name" id="name" required
                            value="{{ old('name', $currentUser->name) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="email" id="email" required
                            value="{{ old('email', $currentUser->email) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Preferred Currency</label>
                        <select name="currency" id="currency"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="USD" {{ old('currency', $currentUser->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="EUR" {{ old('currency', $currentUser->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            <option value="GBP" {{ old('currency', $currentUser->currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                            <option value="JPY" {{ old('currency', $currentUser->currency) == 'JPY' ? 'selected' : '' }}>JPY (¥)</option>
                            <option value="PHP" {{ old('currency', $currentUser->currency) == 'PHP' ? 'selected' : '' }}>PHP (₱)</option>
                        </select>
                    </div>

                    <div>
                        <label for="monthly_budget" class="block text-sm font-medium text-gray-700 mb-2">Monthly Budget (Optional)</label>
                        <input type="number" step="0.01" name="monthly_budget" id="monthly_budget"
                            value="{{ old('monthly_budget', $currentUser->monthly_budget) }}"
                            placeholder="Enter your monthly budget limit"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">This will be displayed on your dashboard to track overall spending</p>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-4 py-2 rounded-lg transition duration-200 transform hover:scale-105">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Change Password</h2>
                
                <form action="{{ route('profile.password') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" id="current_password" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="new_password" id="new_password" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white px-4 py-2 rounded-lg transition duration-200 transform hover:scale-105">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePreview');
                    const placeholder = document.getElementById('profilePlaceholder');
                    
                    if (preview) {
                        preview.src = e.target.result;
                    } else if (placeholder) {
                        // Replace placeholder with image
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'profile-picture-preview';
                        img.id = 'profilePreview';
                        placeholder.parentNode.replaceChild(img, placeholder);
                    }
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>
