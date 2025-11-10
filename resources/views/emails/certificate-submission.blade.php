<!DOCTYPE html>
<html>
<head>
    <title>Certificate Submission</title>
</head>
<body>
    <h2>Certificate Submission</h2>
    
    <p>Please find the attached certificate for the following student:</p>
    
    <ul>
        <li><strong>Student Name:</strong> {{ $certificate->student_name }}</li>
        <li><strong>Course:</strong> {{ $certificate->course_name }}</li>
        <li><strong>Completion Date:</strong> {{ $certificate->completion_date->format('M d, Y') }}</li>
        <li><strong>Certificate ID:</strong> {{ $certificate->certificate_number }}</li>
    </ul>
    
    <p>This certificate has been automatically submitted for state compliance.</p>
    
    <p>Best regards,<br>
    Traffic School Administration</p>
</body>
</html>
