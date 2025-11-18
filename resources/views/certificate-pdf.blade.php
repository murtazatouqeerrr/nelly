<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traffic School Certificate</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; font-size: 12px; }
        .certificate { 
            width: 100%; 
            border: 2px solid #000; 
            position: relative;
            overflow: hidden;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 150%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            font-weight: bold;
            color: rgba(200, 200, 200, 0.15);
            text-align: center;
            z-index: 0;
            pointer-events: none;
            white-space: nowrap;
        }
        .content { position: relative; z-index: 1; }
        .top-section { display: table; width: 100%; border-bottom: 2px solid #000; }
        .school-info { display: table-cell; width: 33%; padding: 10px; border-right: 2px solid #000; vertical-align: top; }
        .middle-section { display: table-cell; width: 34%; padding: 10px; border-right: 2px solid #000; }
        .cert-number { display: table-cell; width: 33%; padding: 10px; text-align: center; vertical-align: top; }
        .student-info { display: table; width: 100%; border-bottom: 2px solid #000; }
        .student-name { display: table-cell; width: 33%; padding: 10px; border-right: 2px solid #000; }
        .photo-section { display: table-cell; width: 67%; padding: 10px; text-align: center; vertical-align: middle; border-left: 2px solid #000; }
        .completion-section { padding: 10px; border-bottom: 2px solid #000; }
        .details-section { display: table; width: 100%; }
        .details-left, .details-right { display: table-cell; width: 50%; }
        .detail-row { display: table; width: 100%; border-bottom: 1px solid #000; }
        .detail-label { display: table-cell; width: 50%; padding: 5px; border-right: 1px solid #000; font-weight: bold; }
        .detail-value { display: table-cell; width: 50%; padding: 5px; background: #90EE90; }
        .address-section { padding: 10px; border-bottom: 2px solid #000; }
        .signature-section { padding: 20px; }
        .signature-row { display: table; width: 100%; margin-bottom: 20px; }
        .signature-box { display: table-cell; width: 50%; text-align: center; }
        .highlight { background: #90EE90; padding: 2px; }
        .photo-placeholder { width: 100px; height: 120px; background: #f0f0f0; border: 1px solid #ccc; margin: 0 auto; display: none }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="watermark">DummiesTrafficSchool.com</div>
        <div class="content">
        <!-- Top Section -->
        <div class="top-section">
            <div class="school-info">
                <strong>DummiesTrafficSchool.com</strong><br>
                4235 Hillsboro Pike #300644<br>
                Nashville, TN 37215
            </div>
            <div class="middle-section">
                <!-- Middle content -->
            </div>
            <div class="cert-number">
                (TVS OL 016)<br>
                Certificate<br>
                Number:<br>
                <span class="highlight">{{ $certificate_number }}</span>
            </div>
        </div>
        
        <div class="student-info">
            <div class="student-name">
                <span class="highlight">{{ $student_name ?? 'N/A' }}</span><br>
                <span class="highlight">{!! $student_address ? str_replace("\n", "<br>", $student_address) : 'N/A' !!}</span>
            </div>
            <div class="photo-section">
                <div class="photo-placeholder"></div>
            </div>
        </div>

        <!-- Completion Section -->
        <div class="completion-section">
            This Certifies that ( <span class="highlight">{{ $student_name ?? 'N/A' }}</span> ) has 
            <span class="highlight">completed</span> on ( <span class="highlight">{{ $completion_date ?? 'N/A' }}</span> ) 
            a Court-approved ( <span class="highlight">English</span> ) (Internet course) 
            ( <span class="highlight">{{ $course_type ?? 'Traffic School Course' }}</span> ), and has correctly answered 
            ( <span class="highlight">{{ $score ?? 'N/A' }}</span> ) of the questions on the Final Exam for this course.
        </div>

        <!-- Details Section -->
        <div class="details-section">
            <div class="details-left">
                <div class="detail-row">
                    <div class="detail-label">Driver License Number:</div>
                    <div class="detail-value">{{ $license_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Citation Number:</div>
                    <div class="detail-value">{{ $citation_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Court:</div>
                    <div class="detail-value">{{ $court ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="details-right">
                <div class="detail-row">
                    <div class="detail-label">Student's Date of Birth:</div>
                    <div class="detail-value">{{ $birth_date ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Traffic School Due Date:</div>
                    <div class="detail-value">{{ $due_date ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">County</div>
                    <div class="detail-value">{{ $county ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Address Section -->
        <div class="address-section">
            <strong>Students Address:</strong><br>
            ( {{ $student_name ?? 'N/A' }} )<br>
            {!! $student_address ? str_replace("\n", "<br>", $student_address) : 'N/A' !!}<br><br>
            Only original certificates are acceptable To the Court. Photocopies are not acceptable.
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <p><strong>To be completed by the HSTS Owner :</strong></p>
            <p>I CERTIFY UNDER PENALTY THAT THE FOREGOING IS TRUE AND CORRECT.<br>
            ( PERJURY IS PUNISHABLE BY IMPRISONMENT , FINE OR BOTH. )</p>
            
            <div class="signature-row">
                <div class="signature-box">
                    <div style="height: 40px; border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                    Signature of HSTS Owner
                </div>
                <div class="signature-box">
                    L. Morera<br>
                    Printed Name of HSTS Owner
                </div>
            </div>
            
            <div class="signature-row">
                <div class="signature-box">
                    {{ date('m/d/Y') }}<br>
                    Date
                </div>
                <div class="signature-box"></div>
            </div>
            
            <div class="signature-row">
                <div class="signature-box">
                    <div style="height: 40px; border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                    Signature of Defendant
                </div>
                <div class="signature-box">
                    Signed under penalty of perjury.
                </div>
            </div>
        </div>
        </div>
    </div>
</body>
</html>
