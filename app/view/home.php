<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solution Hub</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6f42c1, #fd7e14);
            font-family: 'Arial', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .home-container {
            text-align: center;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            border-radius: 20px;
            color: #333; /* 👈 ADD THIS */
            
        }
        .btn-enter {
            background-color: #fff;
            color: #007bff;
            border: none;
            font-weight: bold;
            padding: 10px 20px;
        }
        .btn-enter:hover {
            background-color: #e2e6ea;
            color: #0056b3;
        }
a.white-link {
    color: #6f42c1;
    font-weight: bold;
}
        a.white-link:hover {
            color: #ccc; /* hover effect */
        }
    .input-group-text {
      background-color: transparent;
      border: none;
    }
    </style>
</head>
<body>
    <div class="home-container">
        <h1 class="mb-4">Welcome to the MIS Support Hub</h1>
        <p class="mb-4">Less stress. More solutions.</p>
        <a href="/login"class="white-link">Jump In</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>