<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arena LMS')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm m-3">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm m-3">
            Vui lòng kiểm tra lại thông tin biểu mẫu!
        </div>
    @endif

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<style>
.dropdown-menu-item:hover {
    background-color: #f8fafc;
    border-radius: 4px;
}

.dropdown-menu-item {
    transition: background-color 0.2s ease;
}
</style>