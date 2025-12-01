<!-- Vertical Sidebar Navigation -->
<nav style="width: 280px; height: 100vh; position: fixed; top: 0; left: 0; z-index: 1000; overflow-y: auto; overflow-x: hidden; background: var(--bg-secondary) !important;">
        <div class="p-3" style="padding-bottom: 2rem !important;">
            <a class="navbar-brand text-decoration-none d-block mb-4" href="/dashboard" style="font-weight: 700;">
                <div style="background: linear-gradient(135deg, #4a5d23, #6b7c2e); padding: 12px 16px; border-radius: 8px; display: inline-block; width: 100%; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    <div style="color: #ffd700 !important; font-weight: bold; font-size: 24px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); margin-bottom: 2px;">Dummies</div>
                    <div style="color: white !important; font-size: 14px; font-weight: 600; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">TRAFFIC SCHOOL.COM</div>
                </div>
            </a>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard" style="color: {{ request()->is('dashboard') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('dashboard') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('courses') ? 'active' : '' }}" href="/courses" style="color: {{ request()->is('courses') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('courses') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-book me-2"></i> Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('my-enrollments') ? 'active' : '' }}" href="/my-enrollments" style="color: {{ request()->is('my-enrollments') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('my-enrollments') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-user-graduate me-2"></i> My Enrollments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('generate-certificates') ? 'active' : '' }}" href="/generate-certificates" style="color: {{ request()->is('generate-certificates') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('generate-certificates') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-award me-2"></i> Generate Certificates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('my-certificates') ? 'active' : '' }}" href="/my-certificates" style="color: {{ request()->is('my-certificates') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('my-certificates') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-certificate me-2"></i> My Certificates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('open-ticket') ? 'active' : '' }}" href="/open-ticket" style="color: {{ request()->is('open-ticket') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('open-ticket') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-ticket-alt me-2"></i> Open a Ticket
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('profile') ? 'active' : '' }}" href="/profile" style="color: {{ request()->is('profile') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('profile') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('my-payments') ? 'active' : '' }}" href="/my-payments" style="color: {{ request()->is('my-payments') ? 'var(--text-primary)' : 'var(--text-secondary)' }} !important; transition: var(--transition); border-radius: 8px; margin: 4px 0; padding: 12px 16px !important; {{ request()->is('my-payments') ? 'background: var(--accent) !important;' : '' }}">
                        <i class="fas fa-receipt me-2"></i> My Payments
                    </a>
                </li>
                
                @if(auth()->check() && auth()->user()->role && (auth()->user()->role->slug === 'super-admin' || auth()->user()->role->slug === 'admin'))
                    <hr style="border-color: var(--border) !important; opacity: 0.3;">
                    <li class="nav-item">
                        <small style="color: var(--text-secondary) !important; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" class="px-3">ADMIN PANEL</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/dashboard') ? 'bg-primary rounded' : '' }}" href="/admin/dashboard">
                            <i class="fas fa-chart-line me-2"></i> Admin Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('create-course') ? 'bg-primary rounded' : '' }}" href="/create-course">
                            <i class="fas fa-plus-circle me-2"></i> Manage Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/enrollments') ? 'bg-primary rounded' : '' }}" href="/admin/enrollments">
                            <i class="fas fa-users me-2"></i> Enrollments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/certificates*') ? 'bg-primary rounded' : '' }}" href="/admin/certificates">
                            <i class="fas fa-certificate me-2"></i> Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/dicds-submissions*') ? 'bg-primary rounded' : '' }}" href="/admin/dicds-submissions">
                            <i class="fas fa-paper-plane me-2"></i> DICDS Submissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/users') ? 'bg-primary rounded' : '' }}" href="/admin/users">
                            <i class="fas fa-user-cog me-2"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/user-access') ? 'bg-primary rounded' : '' }}" href="/admin/user-access">
                            <i class="fas fa-lock me-2"></i> User Access
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-courses*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-courses">
                            <i class="fas fa-flag-usa me-2"></i> Florida Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-certificates*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-certificates">
                            <i class="fas fa-certificate me-2"></i> Florida Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/reports') ? 'bg-primary rounded' : '' }}" href="/admin/reports">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/payments') ? 'bg-primary rounded' : '' }}" href="/admin/payments">
                            <i class="fas fa-credit-card me-2"></i> Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/invoices') ? 'bg-primary rounded' : '' }}" href="/admin/invoices">
                            <i class="fas fa-file-invoice me-2"></i> Invoices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/certificates') ? 'bg-primary rounded' : '' }}" href="/admin/certificates">
                            <i class="fas fa-certificate me-2"></i> Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/state-integration') ? 'bg-primary rounded' : '' }}" href="/admin/state-integration">
                            <i class="fas fa-globe-americas me-2"></i> State Integration
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/email-templates') ? 'bg-primary rounded' : '' }}" href="/admin/email-templates">
                            <i class="fas fa-envelope me-2"></i> Email Templates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/notifications') ? 'bg-primary rounded' : '' }}" href="/admin/notifications">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/accessibility-settings') ? 'bg-primary rounded' : '' }}" href="/admin/accessibility-settings">
                            <i class="fas fa-universal-access me-2"></i> Accessibility
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/mobile-optimization') ? 'bg-primary rounded' : '' }}" href="/admin/mobile-optimization">
                            <i class="fas fa-mobile-alt me-2"></i> Mobile Optimization
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/pwa-management') ? 'bg-primary rounded' : '' }}" href="/admin/pwa-management">
                            <i class="fas fa-download me-2"></i> PWA Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/security-dashboard') ? 'bg-primary rounded' : '' }}" href="/admin/security-dashboard">
                            <i class="fas fa-shield-alt me-2"></i> Security Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/account-security') ? 'bg-primary rounded' : '' }}" href="/admin/account-security">
                            <i class="fas fa-lock me-2"></i> Account Security
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/data-export') ? 'bg-primary rounded' : '' }}" href="/admin/data-export">
                            <i class="fas fa-file-export me-2"></i> Data Export
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">FLORIDA SECURITY & AUDIT</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-security') ? 'bg-primary rounded' : '' }}" href="/admin/florida-security">
                            <i class="fas fa-shield-alt me-2"></i> Security Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-audit') ? 'bg-primary rounded' : '' }}" href="/admin/florida-audit">
                            <i class="fas fa-clipboard-list me-2"></i> Audit Trail
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-compliance') ? 'bg-primary rounded' : '' }}" href="/admin/florida-compliance">
                            <i class="fas fa-check-circle me-2"></i> Compliance Manager
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-data-export') ? 'bg-primary rounded' : '' }}" href="/admin/florida-data-export">
                            <i class="fas fa-download me-2"></i> Data Export Tool
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">FLORIDA MOBILE & ACCESSIBILITY</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-mobile') ? 'bg-primary rounded' : '' }}" href="/admin/florida-mobile">
                            <i class="fas fa-mobile-alt me-2"></i> Mobile Optimization
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-accessibility') ? 'bg-primary rounded' : '' }}" href="/admin/florida-accessibility">
                            <i class="fas fa-universal-access me-2"></i> Accessibility Settings
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">FLORIDA DICDS UI & WORKFLOW</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/welcome') ? 'bg-primary rounded' : '' }}" href="/dicds/welcome">
                            <i class="fas fa-home me-2"></i> DICDS Welcome
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/main-menu') ? 'bg-primary rounded' : '' }}" href="/dicds/main-menu">
                            <i class="fas fa-th-large me-2"></i> DICDS Main Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/dicds-user-management') ? 'bg-primary rounded' : '' }}" href="/admin/dicds-user-management">
                            <i class="fas fa-users-cog me-2"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/dicds-access-requests') ? 'bg-primary rounded' : '' }}" href="/admin/dicds-access-requests">
                            <i class="fas fa-key me-2"></i> Access Requests
                        </a>
                    </li>
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">FLORIDA DICDS</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-dashboard*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-dashboard">
                            <i class="fas fa-chart-pie me-2"></i> Florida Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/dicds-orders*') ? 'bg-primary rounded' : '' }}" href="/admin/dicds-orders">
                            <i class="fas fa-shopping-cart me-2"></i> DICDS Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/certificate-inventory*') ? 'bg-primary rounded' : '' }}" href="/admin/certificate-inventory">
                            <i class="fas fa-boxes me-2"></i> Certificate Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/compliance-reports*') ? 'bg-primary rounded' : '' }}" href="/admin/compliance-reports">
                            <i class="fas fa-file-alt me-2"></i> Compliance Reports
                        </a>
                    </li>
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">PAYMENT SYSTEM</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-payments*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-payments">
                            <i class="fas fa-dollar-sign me-2"></i> Florida Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/fee-remittances*') ? 'bg-primary rounded' : '' }}" href="/admin/fee-remittances">
                            <i class="fas fa-money-check-alt me-2"></i> Fee Remittances
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/pricing-rules*') ? 'bg-primary rounded' : '' }}" href="/admin/pricing-rules">
                            <i class="fas fa-tags me-2"></i> Pricing Rules
                        </a>
                    </li>
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">EMAIL & NOTIFICATIONS</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/florida-email-templates*') ? 'bg-primary rounded' : '' }}" href="/admin/florida-email-templates">
                            <i class="fas fa-envelope-open-text me-2"></i> Email Templates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/provider-menu') ? 'bg-primary rounded' : '' }}" href="/dicds/provider-menu">
                            <i class="fas fa-home me-2"></i> Provider Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/schools*') ? 'bg-primary rounded' : '' }}" href="/dicds/schools/maintain">
                            <i class="fas fa-school me-2"></i> Manage Schools
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/instructors*') ? 'bg-primary rounded' : '' }}" href="/dicds/instructors/manage">
                            <i class="fas fa-chalkboard-teacher me-2"></i> Manage Instructors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/certificates/order*') ? 'bg-primary rounded' : '' }}" href="/dicds/certificates/order">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Order Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/certificates/distribute*') ? 'bg-primary rounded' : '' }}" href="/dicds/certificates/distribute">
                            <i class="fas fa-share-alt me-2"></i> Distribute Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/reports*') ? 'bg-primary rounded' : '' }}" href="/dicds/reports/menu">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('dicds/web-service-info*') ? 'bg-primary rounded' : '' }}" href="/dicds/web-service-info">
                            <i class="fas fa-info-circle me-2"></i> Web Service Info
                        </a>
                    </li>
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">LEGAL & COMPLIANCE</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/legal-documents*') ? 'bg-primary rounded' : '' }}" href="/admin/legal-documents">
                            <i class="fas fa-file-contract me-2"></i> Legal Documents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/copyright-protection*') ? 'bg-primary rounded' : '' }}" href="/admin/copyright-protection">
                            <i class="fas fa-shield-alt me-2"></i> Copyright Protection
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/user-consents*') ? 'bg-primary rounded' : '' }}" href="/admin/user-consents">
                            <i class="fas fa-user-check me-2"></i> User Consents
                        </a>
                    </li>
                    
                    <hr class="text-white">
                    <li class="nav-item">
                        <small class="text-muted px-3">NEW MODULES</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/flhsmv/submissions*') ? 'bg-primary rounded' : '' }}" href="/admin/flhsmv/submissions">
                            <i class="fas fa-paper-plane me-2"></i> FLHSMV Submissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/payments/transactions*') ? 'bg-primary rounded' : '' }}" href="/admin/payments/transactions">
                            <i class="fas fa-credit-card me-2"></i> Payment Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/payments/stripe*') ? 'bg-primary rounded' : '' }}" href="/admin/payments/stripe">
                            <i class="fab fa-stripe me-2"></i> Stripe Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/course-timers*') ? 'bg-primary rounded' : '' }}" href="/admin/course-timers">
                            <i class="fas fa-clock me-2"></i> Course Timers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/state-stamps*') ? 'bg-primary rounded' : '' }}" href="/admin/state-stamps">
                            <i class="fas fa-stamp me-2"></i> State Stamps
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/support/tickets*') ? 'bg-primary rounded' : '' }}" href="/admin/support/tickets">
                            <i class="fas fa-ticket-alt me-2"></i> Support Tickets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/faqs*') ? 'bg-primary rounded' : '' }}" href="/admin/faqs">
                            <i class="fas fa-question me-2"></i> FAQs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/counties*') ? 'bg-primary rounded' : '' }}" href="/admin/counties">
                            <i class="fas fa-map-marker-alt me-2"></i> Counties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/coupons*') ? 'bg-primary rounded' : '' }}" href="/admin/coupons">
                            <i class="fas fa-tags me-2"></i> Coupons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/question-banks*') ? 'bg-primary rounded' : '' }}" href="/admin/question-banks">
                            <i class="fas fa-question-circle me-2"></i> Question Banks
                        </a>
                    </li>
                @endif
                
                <hr class="text-white">
                <li class="nav-item">
                    <form method="POST" action="/logout" style="display: inline;">
                        @csrf
                        <button type="submit" class="nav-link text-white btn btn-link" style="border: none; background: none; padding: 0.5rem 1rem;">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Push Notification Modal -->
    <div class="modal fade" id="pushNotificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" id="notificationHeader">
                    <h5 class="modal-title" id="notificationTitle">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="notificationMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time notification checker
        function checkForNotifications() {
            fetch('/api/check-notifications', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(notification => {
                if (notification && notification.title) {
                    showPushNotification(notification);
                }
            })
            .catch(error => {});
        }

        function showPushNotification(notification) {
            // Update modal content
            document.getElementById('notificationTitle').textContent = notification.title;
            document.getElementById('notificationMessage').textContent = notification.message;
            
            // Set header color based on type
            const header = document.getElementById('notificationHeader');
            header.className = 'modal-header';
            switch(notification.type) {
                case 'success':
                    header.classList.add('bg-success', 'text-white');
                    break;
                case 'warning':
                    header.classList.add('bg-warning', 'text-dark');
                    break;
                case 'error':
                    header.classList.add('bg-danger', 'text-white');
                    break;
                default:
                    header.classList.add('bg-info', 'text-white');
            }
            
            // Show modal
            if (typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(document.getElementById('pushNotificationModal'));
                modal.show();
                console.log('Modal shown');
            } else {
                console.log('Bootstrap not available');
                alert(`${notification.title}: ${notification.message}`);
            }
        }

        // Check for notifications every 3 seconds
        setInterval(checkForNotifications, 3000);
        
        // Check immediately on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(checkForNotifications, 1000);
        });
    </script>
