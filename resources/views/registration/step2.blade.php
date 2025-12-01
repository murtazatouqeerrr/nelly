<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create your Account - Step 2</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f8f9fa; 
            color: #212529;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #0d6efd; font-size: 28px; margin: 0; }
        .header p { color: #6c757d; margin: 10px 0 0 0; }
        .registration-form { background: white; padding: 40px; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; align-items: center; }
        .form-group { flex: 1; position: relative; }
        .form-group.full-width { flex: 2; }
        .form-group label { 
            display: block; 
            color: #212529; 
            font-weight: bold; 
            margin-bottom: 8px; 
            font-size: 14px;
        }
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            font-size: 16px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .searchable-dropdown { position: relative; }
        .search-input { width: 100%; padding: 12px; border: 1px solid #dee2e6; border-radius: 0.375rem; font-size: 16px; box-sizing: border-box; }
        .search-input:focus { outline: none; border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); }
        .dropdown-list { 
            position: absolute; 
            top: 100%; 
            left: 0; 
            right: 0; 
            background: white; 
            border: 1px solid #dee2e6; 
            border-top: none;
            max-height: 300px; 
            overflow-y: auto; 
            z-index: 1000;
            display: none;
        }
        .dropdown-list.show { display: block; }
        .dropdown-item { 
            padding: 10px 12px; 
            cursor: pointer; 
            border-bottom: 1px solid #f0f0f0;
        }
        .dropdown-item:hover { background: #f8f9fa; }
        .dropdown-item.selected { background: #e7f3ff; color: #0d6efd; font-weight: bold; }
        .load-more-btn { 
            padding: 10px 12px; 
            background: #0d6efd; 
            color: white; 
            border: none; 
            cursor: pointer; 
            width: 100%; 
            text-align: center;
            font-weight: bold;
        }
        .load-more-btn:hover { background: #0b5ed7; }
        .phone-group { display: flex; gap: 10px; }
        .phone-group input { width: 80px; }
        .radio-group { display: flex; gap: 20px; margin-top: 8px; }
        .radio-group label { font-weight: normal; margin-bottom: 0; }
        .birthday-group { display: flex; gap: 10px; }
        .birthday-group select { width: 100px; }
        .court-section { 
            background: #e7f3ff; 
            border: 1px solid #b6d7ff; 
            padding: 30px; 
            margin: 30px 0; 
            border-radius: 0.375rem; 
        }
        .court-section h3 { color: #0d6efd; text-align: center; margin: 0 0 20px 0; }
        .note-section { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            padding: 20px; 
            margin-top: 30px; 
            border-radius: 0.375rem; 
        }
        .note-section strong { color: #856404; }
        .note-text { color: #856404; margin-top: 10px; text-align: center; }
        .button-row { display: flex; justify-content: space-between; margin-top: 30px; gap: 20px; }
        .btn { 
            padding: 12px 30px; 
            border: none; 
            border-radius: 0.375rem; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            text-decoration: none; 
            display: inline-block;
            transition: background-color 0.15s ease-in-out;
            flex: 1;
            text-align: center;
        }
        .btn-next { background: #0d6efd; color: white; }
        .btn-next:hover { background: #0b5ed7; }
        .btn-back { background: #6c757d; color: white; }
        .btn-back:hover { background: #5c636a; }
        .validation-errors { background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px; }
        .validation-errors ul { margin: 10px 0 0 20px; padding: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Personal Information</h1>
            <p>Step 2 of 4 - Tell us about yourself</p>
        </div>
        
        <form method="POST" action="{{ route('register.process', 2) }}">
            @csrf
            
            @if(session('error'))
                <div style="background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px;">
                    <strong>Error:</strong> {{ session('error') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="validation-errors">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="registration-form">
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="mailing_address">Address</label>
                        <input type="text" id="mailing_address" name="mailing_address" value="{{ old('mailing_address', session('registration_step_2.mailing_address')) }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="{{ old('city', session('registration_step_2.city')) }}" pattern="[a-zA-Z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes allowed" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <div class="searchable-dropdown">
                            <input type="text" id="state-search" class="search-input" placeholder="Search state...">
                            <div id="state-dropdown" class="dropdown-list"></div>
                            <input type="hidden" id="state" name="state" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="zip">Zip Code</label>
                        <input type="text" id="zip" name="zip" value="{{ old('zip', session('registration_step_2.zip')) }}" pattern="\d{5}(-\d{4})?" maxlength="10" title="5 digits or 5+4 format (e.g., 12345 or 12345-6789)" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <div class="phone-group">
                            <input type="text" name="phone_1" maxlength="3" pattern="\d{3}" title="3 digits only" value="{{ old('phone_1', session('registration_step_2.phone_1')) }}" required>
                            <input type="text" name="phone_2" maxlength="3" pattern="\d{3}" title="3 digits only" value="{{ old('phone_2', session('registration_step_2.phone_2')) }}" required>
                            <input type="text" name="phone_3" maxlength="4" pattern="\d{4}" title="4 digits only" value="{{ old('phone_3', session('registration_step_2.phone_3')) }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <div class="radio-group">
                            <label><input type="radio" name="gender" value="male" {{ old('gender', session('registration_step_2.gender')) == 'male' ? 'checked' : '' }}> Male</label>
                            <label><input type="radio" name="gender" value="female" {{ old('gender', session('registration_step_2.gender')) == 'female' ? 'checked' : '' }}> Female</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Birthday</label>
                        <div class="birthday-group">
                            <select name="birth_month" required>
                                <option value="">Month</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('birth_month', session('registration_step_2.birth_month')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <select name="birth_day" required>
                                <option value="">Day</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ old('birth_day', session('registration_step_2.birth_day')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <select name="birth_year" required>
                                <option value="">Year</option>
                                @for($i = date('Y'); $i >= 1950; $i--)
                                    <option value="{{ $i }}" {{ old('birth_year', session('registration_step_2.birth_year')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="driver_license">Driver License Number</label>
                        <input type="text" id="driver_license" name="driver_license" value="{{ old('driver_license', session('registration_step_2.driver_license')) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="license_state">License State</label>
                        <select id="license_state" name="license_state" required>
                            <option value="">Select a state</option>
                            <option value="AL">Alabama</option>
                            <option value="FL">Florida</option>
                            <option value="TX">Texas</option>
                            <option value="CA">California</option>
                            <option value="NY">New York</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="license_class">License Class</label>
                        <select id="license_class" name="license_class" required>
                            <option value="Other">Other</option>
                            <option value="Class A">Class A</option>
                            <option value="Class B">Class B</option>
                            <option value="Class C">Class C</option>
                            <option value="Class D">Class D</option>
                            <option value="Class E">Class E</option>
                        </select>
                    </div>
                </div>
                
                <div class="court-section">
                    <h3>Court Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="court_selected">Court Selected</label>
                            <div class="searchable-dropdown">
                                <input type="text" id="court-search" class="search-input" placeholder="Search court..." disabled>
                                <div id="court-dropdown" class="dropdown-list"></div>
                                <input type="hidden" id="court_selected" name="court_selected" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="citation_number">Citation Number</label>
                            <input type="text" id="citation_number" name="citation_number" value="{{ old('citation_number', session('registration_step_2.citation_number')) }}" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Traffic School Due Date</label>
                            <div class="birthday-group">
                                <select name="due_month" required>
                                    <option value="">Month</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ old('due_month', session('registration_step_2.due_month')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                <select name="due_day" required>
                                    <option value="">Day</option>
                                    @for($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}" {{ old('due_day', session('registration_step_2.due_day')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                <select name="due_year" required>
                                    <option value="">Year</option>
                                    @for($i = date('Y'); $i <= date('Y') + 2; $i++)
                                        <option value="{{ $i }}" {{ old('due_year', session('registration_step_2.due_year')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="button-row">
                    <a href="{{ route('register.step', 1) }}" class="btn btn-back">Back</a>
                    <button type="submit" class="btn btn-next">Next</button>
                </div>
            </div>
        </form>
        
        <div class="note-section">
            <strong>Note:</strong>
            <div class="note-text">
                Incorrectly entered information leading to the need for certificate resubmission will incur a $3.00 charge. By proceeding, you agree to this term.
            </div>
        </div>
    </div>

    <script>
        let statesData = [];
        let countiesData = [];
        let courtsData = [];
        let courtPage = 1;
        let selectedState = '';
        let selectedCounty = '';

        // Load states on page load
        async function loadStates() {
            try {
                const response = await fetch('/api/courts/states');
                statesData = await response.json();
                console.log('States loaded:', statesData);
                renderStateDropdown(statesData);
            } catch (error) {
                console.error('Error loading states:', error);
            }
        }

        // Render state dropdown
        function renderStateDropdown(states) {
            const dropdown = document.getElementById('state-dropdown');
            dropdown.innerHTML = states.map(state => 
                `<div class="dropdown-item" data-value="${state}">${state}</div>`
            ).join('');
            
            dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', selectState);
            });
        }

        // Select state
        function selectState(e) {
            selectedState = e.target.dataset.value;
            document.getElementById('state').value = selectedState;
            document.getElementById('state-search').value = selectedState;
            document.getElementById('state-dropdown').classList.remove('show');
            
            // Reset court dropdown
            document.getElementById('court_selected').value = '';
            document.getElementById('court-search').value = '';
            document.getElementById('court-search').disabled = false;
            courtPage = 1;
            selectedCounty = '';
            loadCounties(selectedState);
        }

        // Load counties for selected state
        async function loadCounties(state) {
            try {
                const response = await fetch(`/api/courts/by-state/${state}`);
                countiesData = await response.json();
                console.log('Counties for ' + state + ':', countiesData);
                renderCountyDropdown(countiesData);
            } catch (error) {
                console.error('Error loading counties:', error);
            }
        }

        // Render county dropdown
        function renderCountyDropdown(counties) {
            const dropdown = document.getElementById('court-dropdown');
            if (!counties || counties.length === 0) {
                dropdown.innerHTML = '<div class="dropdown-item">No counties found</div>';
                return;
            }
            
            dropdown.innerHTML = counties.map(county => 
                `<div class="dropdown-item" data-value="${county}">${county}</div>`
            ).join('');
            
            dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', selectCounty);
            });
            
            dropdown.classList.add('show');
        }

        // Select county and load courts
        async function selectCounty(e) {
            selectedCounty = e.target.dataset.value;
            document.getElementById('court-dropdown').classList.remove('show');
            courtPage = 1;
            await loadCourts(selectedState, selectedCounty, 1);
        }

        // Load courts for selected state and county
        async function loadCourts(state, county, page = 1) {
            try {
                const response = await fetch(`/api/courts/by-county/${state}/${county}?page=${page}`);
                const data = await response.json();
                console.log('Courts response:', data);
                
                if (page === 1) {
                    courtsData = data.courts || [];
                } else {
                    courtsData = [...courtsData, ...(data.courts || [])];
                }
                
                renderCourtDropdown(courtsData, data.has_more);
            } catch (error) {
                console.error('Error loading courts:', error);
            }
        }

        // Render court dropdown
        function renderCourtDropdown(courts, hasMore) {
            const dropdown = document.getElementById('court-dropdown');
            
            if (!courts || courts.length === 0) {
                dropdown.innerHTML = '<div class="dropdown-item">No courts found</div>';
                dropdown.classList.add('show');
                return;
            }
            
            let html = courts.map(court => {
                const courtName = typeof court === 'string' ? court : court.court;
                return `<div class="dropdown-item" data-value="${courtName}, ${selectedCounty}, ${selectedState}">${courtName}, ${selectedCounty}, ${selectedState}</div>`;
            }).join('');
            
            if (hasMore) {
                html += `<button type="button" class="load-more-btn">Load More</button>`;
            }
            
            dropdown.innerHTML = html;
            dropdown.classList.add('show');
            
            dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', selectCourt);
            });
            
            const loadMoreBtn = dropdown.querySelector('.load-more-btn');
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    courtPage++;
                    loadCourts(selectedState, selectedCounty, courtPage);
                });
            }
        }

        // Select court
        function selectCourt(e) {
            document.getElementById('court_selected').value = e.target.dataset.value;
            document.getElementById('court-search').value = e.target.textContent;
            document.getElementById('court-dropdown').classList.remove('show');
        }

        // State search
        document.getElementById('state-search').addEventListener('focus', () => {
            document.getElementById('state-dropdown').classList.add('show');
        });

        document.getElementById('state-search').addEventListener('input', (e) => {
            const search = e.target.value.toLowerCase();
            const filtered = statesData.filter(state => state.toLowerCase().includes(search));
            renderStateDropdown(filtered);
            document.getElementById('state-dropdown').classList.add('show');
        });

        // Court search
        document.getElementById('court-search').addEventListener('focus', () => {
            if (selectedCounty) {
                document.getElementById('court-dropdown').classList.add('show');
            }
        });

        document.getElementById('court-search').addEventListener('input', (e) => {
            const search = e.target.value.toLowerCase();
            const filtered = courtsData.filter(court => {
                const courtName = typeof court === 'string' ? court : court.court;
                return `${courtName}, ${selectedCounty}, ${selectedState}`.toLowerCase().includes(search);
            });
            renderCourtDropdown(filtered, false);
            document.getElementById('court-dropdown').classList.add('show');
        });

        // Close dropdowns on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.searchable-dropdown')) {
                document.getElementById('state-dropdown').classList.remove('show');
                document.getElementById('court-dropdown').classList.remove('show');
            }
        });

        // Load states on page load
        loadStates();
        
        // Validation for phone number fields - only allow digits
        document.querySelectorAll('input[name="phone_1"], input[name="phone_2"], input[name="phone_3"]').forEach(input => {
            input.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '');
            });
        });
        
        // Validation for zip code - only allow digits and hyphen
        document.getElementById('zip').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^\d-]/g, '');
        });
        
        // Validation for city - only allow letters, spaces, hyphens, apostrophes
        document.getElementById('city').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^a-zA-Z\s\-']/g, '');
        });
    </script>
