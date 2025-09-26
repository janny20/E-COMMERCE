<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - ShopSphere</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border: 1px solid #e8e8e8;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #7C3AED;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
        }
        .content p {
            margin: 0 0 15px;
        }
        .button-container {
            text-align: center;
            margin: 20px 0;
        }
        .button {
            background-color: #7C3AED;
            color: #ffffff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            background-color: #f4f7f6;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
        .link {
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>ShopSphere</h1></div>
        <div class="content">
            <h2>Password Reset Request</h2>
            <p>We received a request to reset your password. If you didn't make this request, you can safely ignore this email.</p>
            <p>To reset your password, click the button below. This link is valid for 1 hour.</p>
            <div class="button-container"><a href="<?php echo htmlspecialchars($reset_link); ?>" class="button">Reset Your Password</a></div>
            <p>If you're having trouble with the button, copy and paste this URL into your browser:</p>
            <p><a href="<?php echo htmlspecialchars($reset_link); ?>" class="link"><?php echo htmlspecialchars($reset_link); ?></a></p>
        </div>
        <div class="footer">&copy; <?php echo date('Y'); ?> ShopSphere. All rights reserved.</div>
    </div>
</body>
</html>