<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traffic School Certificate</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .certificate { 
            width: 800px; 
            margin: 0 auto; 
            border: 2px solid #000; 
            position: relative;
            overflow: hidden;
        }
        .certificate::before {
            content: 'DummiesTrafficSchool.com';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            font-weight: bold;
            color: rgba(200, 200, 200, 0.15);
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
            width: 150%;
        }
        .certificate > * {
            position: relative;
            z-index: 1;
        }
        .top-section { display: grid; grid-template-columns: 2fr 2fr 1fr; grid-template-rows: auto auto; border-bottom: 2px solid #000; }
        .school-info { grid-column: 1; grid-row: 1; padding: 10px; border-right: 2px solid #000; }
        .middle-section { grid-column: 2; grid-row: 1 / 3; padding: 10px; border-right: 2px solid #000; }
        .cert-number { grid-column: 3; grid-row: 1; padding: 10px; text-align: center; }
        .student-info { grid-column: 1; grid-row: 2; padding: 10px; border-right: 2px solid #000; border-top: 2px solid #000; }
        .photo-section { grid-column: 3; grid-row: 2; padding: 10px; text-align: center; display: flex; align-items: center; justify-content: center; }
        .completion-section { padding: 10px; border-bottom: 2px solid #000; }
        .details-section { display: flex; }
        .details-left, .details-right { flex: 1; }
        .detail-row { display: flex; border-bottom: 1px solid #000; }
        .detail-label { flex: 1; padding: 5px; border-right: 1px solid #000; font-weight: bold; }
        .detail-value { flex: 1; padding: 5px; background: #90EE90; }
        .address-section { padding: 10px; border-bottom: 2px solid #000; }
        .signature-section { padding: 20px; }
        .signature-row { display: flex; margin-bottom: 20px; }
        .signature-box { flex: 1; text-align: center; }
        .highlight { background: #90EE90; padding: 2px; }
        .photo-placeholder { width: 120px; height: 140px; background: #f0f0f0; border: 1px solid #ccc; display: none !important }
        .action-buttons { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            z-index: 1000; 
            display: flex; 
            gap: 10px; 
        }
        .btn { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: bold; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 5px; 
        }
        .btn-print { background: #007bff; color: white; }
        .btn-download { background: #28a745; color: white; }
        .btn:hover { opacity: 0.8; }
        @media print {
            .action-buttons { display: none; }
        }
    </style>
</head>
<body>
    <!-- Action Buttons -->
    <div class="action-buttons">
        <button onclick="printCertificate()" class="btn btn-print">
            🖨️ Print Certificate
        </button>
        <a href="{{ url('/certificate/download?' . http_build_query(request()->all())) }}" class="btn btn-download">
            📄 Download PDF
        </a>
    </div>

    <div class="certificate">
        <!-- Top Section with Grid Layout -->
        <div class="top-section">
            <div class="school-info">
                <strong>DummiesTrafficSchool.com</strong><br>
                4235 Hillsboro Pike #300644<br>
                Nashville, TN 37215
            </div>
            <div class="middle-section" style="display: flex; align-items: center; justify-content: center; text-align: center;">
                @if(isset($state_stamp) && $state_stamp && $state_stamp->logo_path)
                    <div>
                        <img src="{{ asset('storage/' . $state_stamp->logo_path) }}" alt="{{ $state_stamp->state_name }} Stamp" style="max-width: 150px; max-height: 100px;">
                        <div style="font-size: 10px; margin-top: 5px;">{{ $state_stamp->state_name }}</div>
                    </div>
                @else
                    <div style="padding: 20px; color: #999;">
                        <div style="font-size: 48px;">🏛️</div>
                        <div style="font-size: 10px; margin-top: 5px;">State Seal</div>
                    </div>
                @endif
            </div>
            <div class="cert-number">
                (TVS OL 016)<br>
                Certificate<br>
                Number:<br>
                <span class="highlight">{{ $certificate_number }}</span>
            </div>
            <div class="student-info">
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
                    <div class="detail-label">Student's Driver Lic. Number</div>
                    <div class="detail-value">{{ $license_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Citation / Docket Number:</div>
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

    <script>
        function printCertificate() {
            window.print();
        }
    </script>
</body>
</html>
