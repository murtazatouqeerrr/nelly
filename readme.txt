You are an expert Laravel architect. I want you to generate complete, production-ready Laravel code, migrations, models, events, listeners, notifications, and queues for an automated email system inside my custom CRM for an e-learning platform.
Your output must include:

File structures

Code for each file

Example Blade email templates

Best practices (queues, jobs, retries, notifications)

Integration instructions

✅ PROJECT OVERVIEW

I have a Laravel e-learning CRM. It manages:

Students

Courses

Enrollments

Payments

Course progress

Certificate generation

I want you to build a fully automated email notification system triggered by major events.

The Automated Emails Required:
1. Enrollment Confirmation Email

Trigger: When a user enrolls into a course
Should include:

Student name

Course name

Start date

Course link

Support contact

2. Payment Approved Email

Trigger: When payment status changes to "approved"
Should include:

Invoice info

Payment method

Order summary

Course access link

3. Course 100% Completion Email

Trigger: When progress reaches 100%
Should include:

Congratulation message

Summary of time spent

Next recommended course

Certificate link (if available)

4. Certificate Generated Email (with PDF attachment)

Trigger: When the system generates a PDF certificate
Should include:

Student name

Course name

Completion date

Certificate PDF attachment

Verification URL

5. Reminder Emails (Optional)

Course inactivity reminder (7 days)

Payment pending reminder

Assessment due reminder

If possible, create CRON examples and scheduled notifications.

✅ TECHNICAL REQUIREMENTS
A. Use Laravel Events & Listeners

Create events like:

UserEnrolled

PaymentApproved

CourseCompleted

CertificateGenerated

And matching listeners that send email notifications.

B. Use Laravel Notifications

Each email should be built using:

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

C. Queue All Emails

All emails must run through:

QUEUE_CONNECTION=database

Proper Jobs

Failed job handling

Supervisor instructions (Linux)

D. Certificate Attachment

Show how to:

Generate a PDF using barryvdh/laravel-dompdf

Store the certificate file

Attach it to the notification email

E. Blade Email Templates

Create templates in:

resources/views/emails/courses/enrolled.blade.php  
resources/views/emails/payments/approved.blade.php  
resources/views/emails/courses/completed.blade.php  
resources/views/emails/certificates/generated.blade.php  

F. Testing Instructions

Provide:

How to manually dispatch events

Tinker examples

Email preview with Mailhog / Mailtrap

Queue worker commands

✅ WHAT I EXPECT AS OUTPUT

Deliver the following:

1. Full folder & file structure

Explain where all the files will be placed.

2. Complete Laravel code

For:

Events

Listeners

Notifications

Controllers or Services that trigger events

Certificate generator

Queue configuration

Cron schedules

3. Email Templates (Blade)

Professional, HTML-based templates.

4. Installation Commands

Include composer installs, migrations, queue setup, supervisor config, etc.

5. Integration Guide

Explain how to integrate this with my existing Enrollment, Payment, and Certificate modules.