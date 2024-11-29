<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Aquencher OTP Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            background-color: #ffffff;
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #0174CF;
        }   
        p {
            font-size: 13px;
            color: #555;
        }
        .otp-code {
            font-size: 20px;
            font-weight: bold;
            color: #0174CF;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello,</h2>
        <p>You have requested to reset your password. Please use the following OTP code to proceed:</p>
        <p class="otp-code">{{ $otp }}</p>
        <p>This code is valid for 3 minutes. If you did not request this, please ignore this email.</p>
        <p>Thank you,<br>The Aquencher Team</p>
    </div>
</body>
</html>