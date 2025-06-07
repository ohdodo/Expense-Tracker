<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Expense</title>
    <style>
        /* Simple modal styles */
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
        }

        .modal-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .modal h2 {
            margin-top: 0;
        }

        .close-btn {
            position: absolute;
            right: 12px;
            top: 12px;
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 8px 10px;
            margin-top: 6px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        button.submit-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button.submit-btn:hover {
            background: #1e40af;
        }

        .error-message {
            color: #dc2626;
            font-size: 13px;
            margin-top: -10px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>

    <div class="modal-bg" id="modal">
        <div class="modal">
            <button class="close-btn" onclick="window.history.back();">&times;</button>

            <h2>Add New Expense</h2>

            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf

                <label for="amount">Amount</label>
                <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" required>
                @error('amount')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label for="category">Category</label>
                <input type="text" name="category" id="category" value="{{ old('category') }}" required>
                @error('category')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label for="date">Date</label>
                <input type="date" name="date" id="date" value="{{ old('date') }}" required>
                @error('date')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <label for="description">Description (optional)</label>
                <textarea name="description" id="description">{{ old('description') }}</textarea>
                @error('description')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                <button type="submit" class="submit-btn">Add Expense</button>
            </form>
        </div>
    </div>

</body>

</html>