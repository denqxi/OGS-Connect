<!DOCTYPE html>
<html>
<head>
    <title>Excel Import Test</title>
</head>
<body>
    <h2>Upload Excel (Weekly → Daily)</h2>

    @if(session('success'))
        <p style="color: green">{{ session('success') }}</p>
    @endif

    <form action="{{ route('import.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Upload & Process</button>
    </form>
</body>
</html>
