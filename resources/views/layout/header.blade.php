<!doctype html>
<html lang="en">

<head>
    <title>Haq Bahoo | Milk Shop</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">

    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="sidebar">
                <a href="/"><img src="{{ asset('images/logo2.png') }}" alt="" height="100"
                        class="mb-3"></a>
                <ul class="navbar-nav flex-column" style="padding-left:14px">
                    <li class="nav-item">
                        <a class="nav-link" href="/milk/add">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            About
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dropdownId" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">Dropdown</a>
                        <div class="dropdown-menu" aria-labelledby="dropdownId0" style="border:none;width:100%">
                            <a class="dropdown-item" href="#">Action 1</a>
                            <a class="dropdown-item" href="#">Action 2</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            Contact
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="navbar d-flex justify-content-end">
        <span>
            <i class="fa-regular fa-bell" style="font-size:17px"></i>
            <label for="">Notification</label>
        </span>&nbsp;&nbsp;&nbsp;
        <span>
            <i class="fa-regular fa-user" style="font-size:17px"></i>&nbsp;
            <img src="{{ asset('images/admin.png') }}" alt="" class="rounded-circle" height="45"
                width="45" style="object-fit: cover;">
        </span>
    </div>
