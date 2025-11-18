<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .content h2 { color: #0d6efd; }
        .button { display: inline-block; background: #0d6efd; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { text-align: center; color: #6c757d; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Our Platform!</h1>
        </div>
        <div class="content">
            <p>Hi {{ $user->first_name }},</p>
            
            <p>Thank you for registering with us! We're excited to have you on board.</p>
            
            <h2>Get Started</h2>
            <p>Browse our comprehensive course catalog and start learning today. Whether you're looking to improve your driving skills or complete a traffic school requirement, we have the right course for you.</p>
            
            <p>Your account is now active and ready to use. Simply log in with your credentials to access all available courses.</p>
            
            <a href="{{ route('login') }}" class="button">Browse Courses Now</a>
            
            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                If you have any questions or need assistance, please don't hesitate to contact our support team.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Our Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
