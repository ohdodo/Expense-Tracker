<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    @vite('resources/css/app.css')
</head>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li class="text-red-500">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<body class="bg-black min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-gray-900 rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-white text-center mb-6">Create Account</h1>

        <form method="POST" action="{{ route('register.submit')}}" class="space-y-4">
            @csrf
            <!-- Full Name -->
            <div>
                <label for="name" class="block text-gray-300 mb-1">Full Name</label>
                <input type="text" id="name" name="name" required
                    class="w-full bg-gray-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Your full name">
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-gray-300 mb-1">Email</label>
                <input type="text" id="email" name="email" required
                    class="w-full bg-gray-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Email">
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-gray-300 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full bg-gray-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Create password">
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="confirm_password" class="block text-gray-300 mb-1">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                    class="w-full bg-gray-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Confirm password">
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition duration-200 mt-6">
                Register
            </button>
        </form>

        <p class="text-gray-400 text-center mt-6">
            Already have an account?
            <a href="#" class="text-blue-400 hover:text-blue-300">Sign in</a>
        </p>
    </div>
</body>

</html>