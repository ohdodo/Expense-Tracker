<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-black min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-gray-900 rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-white text-center mb-6">Login</h1>

        @if(session('error'))
            <div class="bg-red-600 text-white p-2 rounded mb-4 text-center">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-gray-300 mb-1">Email</label>
                <input type="email" id="email" name="email" required
                    class="w-full bg-gray-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Your email address" value="{{ old('email') }}">
                @error('email')
                    <p class="text-red-500 mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-gray-300 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full bg-gray-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Your password">
                @error('password')
                    <p class="text-red-500 mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition duration-200 mt-6">
                Login
            </button>
        </form>

        <p class="text-gray-400 text-center mt-6">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300">Sign up</a>
        </p>
    </div>
</body>

</html>