<!DOCTYPE html>
<html>

<head>
    <title>Lấy lại mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: blanchedalmond;
            color: #333;
            padding: 20px;
        }

        h1 {
            color: #007bff;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: blanchedalmond;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        p {
            line-height: 1.6;
        }

        .footer {
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Quên mật khẩu!</h1>
        <p>Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
        <p>
            <a href="{{ $url }}"><button>Đặt lại mật khẩu</button></a>
        </p>
        <p>Liên kết đặt lại mật khẩu này sẽ hết hạn sau 60 phút.</p>
        <p>Nếu bạn không yêu cầu đặt lại mật khẩu thì không cần thực hiện thêm hành động nào.</p>
        <p>Trân trọng,</p>
        <p class="footer">Nếu bạn gặp khó khăn khi nhấp vào nút "Đặt lại mật khẩu", hãy sao chép và dán URL bên dưới vào trình duyệt web của bạn: <a href="{{ $url }}">{{ $url }}</a></p>
    </div>
</body>

</html>