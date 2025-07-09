# Email Setup Guide for Password Reset

This guide will help you configure PHP Mailer to send password reset emails.

## Prerequisites

1. PHP Mailer is already installed in your project (located in `vendor/phpmailer/`)
2. You have an email account (Gmail, Outlook, Yahoo, etc.)

## Step 1: Configure Email Settings

Edit the file `config/mail_config.php` and update the following settings:

### For Gmail Users:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');  // Your Gmail address
define('SMTP_PASSWORD', 'your-app-password');     // App password (not your regular password)
define('SMTP_SECURE', 'tls');
define('FROM_EMAIL', 'your-email@gmail.com');
```

### For Outlook/Hotmail Users:

```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@outlook.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_SECURE', 'tls');
define('FROM_EMAIL', 'your-email@outlook.com');
```

### For Yahoo Users:

```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@yahoo.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_SECURE', 'tls');
define('FROM_EMAIL', 'your-email@yahoo.com');
```

## Step 2: Gmail App Password Setup (Recommended)

If you're using Gmail, follow these steps to create an App Password:

1. Go to your Google Account settings
2. Enable 2-Step Verification if not already enabled
3. Go to Security → App passwords
4. Generate a new app password for "Mail"
5. Use this 16-character password as your `SMTP_PASSWORD`

## Step 3: Test the Configuration

1. Run the test script: `http://localhost/project2/e_learning/test_mailer.php`
2. Check if all tests pass
3. If you want to test actual email sending, uncomment the test section in `test_mailer.php`

## Step 4: Update Reset Link URL

Make sure the reset link URL in `config/mail_config.php` matches your actual domain:

```php
define('RESET_LINK_BASE_URL', 'http://localhost/project2/e_learning/auth/reset_password.php');
```

Change `localhost/project2/e_learning` to your actual domain when deploying.

## Common Issues and Solutions

### Issue: "SMTP connect() failed"
- Check your SMTP settings
- Verify your email and password
- For Gmail, make sure you're using an App Password, not your regular password

### Issue: "Authentication failed"
- Double-check your username and password
- For Gmail, ensure 2-factor authentication is enabled and you're using an App Password

### Issue: "Connection timeout"
- Check your internet connection
- Verify the SMTP host and port
- Some networks block SMTP ports - try using port 465 with SSL instead of 587 with TLS

## Security Notes

1. **Never commit your email password to version control**
2. Consider using environment variables for sensitive data
3. Use App Passwords instead of regular passwords when possible
4. Regularly rotate your email passwords

## Production Deployment

When deploying to production:

1. Update the `RESET_LINK_BASE_URL` to your production domain
2. Use environment variables for sensitive configuration
3. Remove debug logging (the reset link logging in `forgot_password.php`)
4. Test the email functionality thoroughly

## Troubleshooting

### Check PHP Error Logs
Look for mailer errors in your PHP error logs:
- XAMPP: `xampp/php/logs/php_error_log`
- Linux: `/var/log/apache2/error.log`

### Test SMTP Connection
You can test your SMTP connection using telnet:
```bash
telnet smtp.gmail.com 587
```

### Verify PHP Extensions
Make sure these PHP extensions are enabled:
- `openssl`
- `mbstring`

## Support

If you continue to have issues:
1. Check the PHP Mailer documentation: https://github.com/PHPMailer/PHPMailer
2. Verify your email provider's SMTP settings
3. Test with a different email provider 