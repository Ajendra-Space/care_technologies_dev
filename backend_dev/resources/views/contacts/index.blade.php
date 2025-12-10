<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>CRM - Contacts Management</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

    <style>
        .contact-card { transition: transform 0.2s; }
        .contact-card:hover { transform: translateY(-5px); }
        .profile-img { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; }
        .custom-field-badge { background: #f0f0f0; padding: 2px 8px; border-radius: 4px; font-size: 0.85em; margin-right: 5px; }
        .table-responsive { overflow-x: auto; }
        .section { padding: 60px 0; }
        .header { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .logo { text-decoration: none; color: #0d6efd; }
        .sitename { margin: 0; font-size: 24px; font-weight: 600; color: #0d6efd !important; }
        h2 { color: #0d6efd; }
        .navmenu ul { list-style: none; padding: 0; margin: 0; display: flex; gap: 20px; }
        .navmenu a { text-decoration: none; color: #333; padding: 10px 15px; }
        .navmenu a:hover { color: #007bff; }
    </style>
</head>
<body>
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between p-3">
            <a href="/" class="logo d-flex align-items-center">
                <h1 class="sitename">CRM System</h1>
            </a>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="/" class="active">Contacts</a></li>
                    <li><button class="btn btn-primary btn-sm" id="manageCustomFieldsBtn"><i class="bi bi-plus-circle"></i> Manage Custom Fields</button></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <section class="section">
            <div class="container" data-aos="fade-up">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-4">Contacts Management</h2>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by name or email...">
                    </div>
                    <div class="col-md-2">
                        <select id="genderFilter" class="form-control">
                            <option value="">All Genders</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="email" id="emailFilter" class="form-control" placeholder="Filter by email...">
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-primary" id="addContactBtn">
                            <i class="bi bi-plus-circle"></i> Add Contact
                        </button>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Profile</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Gender</th>
                                        <th>Custom Fields</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="contactsTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="paginationContainer"></div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalTitle">Add Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="contactForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="contactId" name="contact_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contactName" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="contactEmail" name="email" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="contactPhone" name="phone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male">
                                        <label class="form-check-label" for="genderMale">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female">
                                        <label class="form-check-label" for="genderFemale">Female</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="genderOther" value="other">
                                        <label class="form-check-label" for="genderOther">Other</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Profile Image</label>
                                <input type="file" class="form-control" id="profileImage" name="profile_image" accept="image/*">
                                <div id="profileImagePreview" class="mt-2"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Additional File</label>
                                <input type="file" class="form-control" id="additionalFile" name="additional_file">
                            </div>
                        </div>

                        <div id="customFieldsContainer"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mergeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Merge Contacts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="mergeForm">
                    <div class="modal-body">
                        <input type="hidden" id="secondaryContactId" name="secondary_contact_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Master Contact <span class="text-danger">*</span></label>
                            <select class="form-control" id="masterContactSelect" name="master_contact_id" required>
                                <option value="">Select Master Contact</option>
                            </select>
                            <small class="text-muted">The master contact will retain all its data. Data from the secondary contact will be merged into it.</small>
                        </div>

                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This action cannot be undone. The secondary contact will be marked as merged.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm Merge</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="customFieldsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Custom Fields</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <button class="btn btn-sm btn-success mb-3" id="addCustomFieldBtn">
                        <i class="bi bi-plus-circle"></i> Add Custom Field
                    </button>
                    <div id="customFieldsList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Custom Field Modal -->
    <div class="modal fade" id="addCustomFieldModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Custom Field</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addCustomFieldForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Field Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customFieldName" name="field_name" required 
                                placeholder="e.g., Birthday, Company Name, Address, Pincode (6 chars)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Field Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="customFieldType" name="field_type" required>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="email">Email</option>
                                <option value="tel">Phone/Tel</option>
                                <option value="url">URL</option>
                                <option value="date">Date</option>
                                <option value="datetime-local">Date & Time</option>
                                <option value="textarea">Textarea</option>
                                <option value="select">Select (Dropdown)</option>
                                <option value="password">Password</option>
                            </select>
                            <small class="text-muted">For Pincode use "Text" type and add "(6 chars)" in field name for max length</small>
                        </div>
                        <div class="mb-3" id="selectOptionsContainer" style="display: none;">
                            <label class="form-label">Options (one per line)</label>
                            <textarea class="form-control" id="customFieldOptions" name="field_options" rows="4"
                                placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                            <small class="text-muted">Only required for "Select" type. Enter one option per line.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Field</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let currentPage = 1;

        $(document).ready(function() {
            loadContacts();
            loadCustomFieldsForForm();
            loadCustomFieldsList();
        });

        $('#searchInput, #genderFilter, #emailFilter').on('input change', function() {
            currentPage = 1;
            loadContacts();
        });

        function loadContacts(page = 1) {
            currentPage = page;
            $('#contactsTableBody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
            
            $.ajax({
                url: '{{ route("contacts.list") }}',
                method: 'GET',
                data: {
                    page: page,
                    search: $('#searchInput').val(),
                    gender: $('#genderFilter').val(),
                    email_filter: $('#emailFilter').val(),
                },
                success: function(response) {
                    console.log('Contacts response:', response);
                    
                    // Handle both paginated and direct response formats
                    let contacts = null;
                    let pagination = null;
                    
                    if (response && response.data && Array.isArray(response.data)) {
                        // Paginated response format
                        contacts = response.data;
                        pagination = {
                            current_page: response.current_page || 1,
                            last_page: response.last_page || 1,
                            per_page: response.per_page || 10,
                            total: response.total || 0,
                            from: response.from || 0,
                            to: response.to || 0
                        };
                    } else if (Array.isArray(response)) {
                        // Direct array response
                        contacts = response;
                        pagination = {
                            current_page: 1,
                            last_page: 1,
                            per_page: response.length,
                            total: response.length,
                            from: 1,
                            to: response.length
                        };
                    } else {
                        console.error('Unexpected response format:', response);
                        contacts = [];
                        pagination = {
                            current_page: 1,
                            last_page: 1,
                            per_page: 10,
                            total: 0,
                            from: 0,
                            to: 0
                        };
                    }
                    
                    renderContacts(contacts);
                    renderPagination(pagination);
                },
                error: function(xhr, status, error) {
                    let message = 'Error loading contacts';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (error) {
                        message = 'Error: ' + error;
                    }
                    $('#contactsTableBody').html('<tr><td colspan="7" class="text-center text-danger">' + message + '</td></tr>');
                    showAlert(message, 'danger');
                    console.error('Contacts load error:', xhr, status, error);
                }
            });
        }

        function renderContacts(contacts) {
            console.log('Rendering contacts:', contacts);
            let html = '';
            
            // Check if contacts is valid
            if (!contacts) {
                console.error('Contacts is null or undefined');
                html = '<tr><td colspan="7" class="text-center text-danger">No data received</td></tr>';
                $('#contactsTableBody').html(html);
                return;
            }
            
            if (!Array.isArray(contacts)) {
                console.error('Contacts is not an array:', typeof contacts, contacts);
                html = '<tr><td colspan="7" class="text-center text-danger">Invalid data format</td></tr>';
                $('#contactsTableBody').html(html);
                return;
            }
            
            if (contacts.length === 0) {
                html = '<tr><td colspan="7" class="text-center">No contacts found</td></tr>';
            } else {
                contacts.forEach(function(contact) {
                    if (!contact) {
                        console.warn('Skipping null contact');
                        return;
                    }
                    
                    let profileImg = contact.profile_image 
                        ? `/storage/${contact.profile_image}` 
                        : 'https://via.placeholder.com/80';
                    
                    let customFieldsHtml = '';
                    if (contact.custom_field_values && Array.isArray(contact.custom_field_values) && contact.custom_field_values.length > 0) {
                        contact.custom_field_values.forEach(function(cf) {
                            if (cf && cf.custom_field && cf.custom_field.field_name) {
                                customFieldsHtml += `<span class="custom-field-badge">${cf.custom_field.field_name}: ${cf.field_value || ''}</span>`;
                            }
                        });
                    }
                    if (!customFieldsHtml) {
                        customFieldsHtml = '<span class="text-muted">None</span>';
                    }

                    html += `
                        <tr>
                            <td><img src="${profileImg}" class="profile-img" alt="Profile"></td>
                            <td>${contact.name}</td>
                            <td>${contact.email}</td>
                            <td>${contact.phone || '-'}</td>
                            <td>${contact.gender ? contact.gender.charAt(0).toUpperCase() + contact.gender.slice(1) : '-'}</td>
                            <td>${customFieldsHtml}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editContact(${contact.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteContact(${contact.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="initiateMerge(${contact.id})">
                                    <i class="bi bi-arrows-collapse"></i> Merge
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#contactsTableBody').html(html);
        }

        function renderPagination(response) {
            if (response.last_page <= 1) {
                $('#paginationContainer').html('');
                return;
            }

            let html = '<nav><ul class="pagination justify-content-center">';
            
            if (response.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadContacts(${response.current_page - 1}); return false;">Previous</a></li>`;
            }

            for (let i = 1; i <= response.last_page; i++) {
                html += `<li class="page-item ${i === response.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadContacts(${i}); return false;">${i}</a>
                </li>`;
            }

            if (response.current_page < response.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadContacts(${response.current_page + 1}); return false;">Next</a></li>`;
            }

            html += '</ul></nav>';
            $('#paginationContainer').html(html);
        }

        $('#addContactBtn').click(function() {
            $('#contactModalTitle').text('Add Contact');
            $('#contactForm')[0].reset();
            $('#contactId').val('');
            $('#profileImagePreview').html('');
            loadCustomFieldsForForm();
            $('#contactModal').modal('show');
        });

        $('#contactForm').submit(function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            let contactId = $('#contactId').val();
            let url = contactId ? `/contacts/${contactId}` : '/contacts';
            let method = contactId ? 'POST' : 'POST'; // always POST; spoof PUT when editing

            if (contactId) {
                formData.append('_method', 'PUT');
            }

            $('.custom-field-input').each(function() {
                let fieldId = $(this).data('field-id');
                formData.append(`custom_fields[${fieldId}]`, $(this).val());
            });

            $.ajax({
                url: url,
                method: method,
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        $('#contactModal').modal('hide');
                        loadContacts(currentPage);
                    }
                },
                error: function(xhr) {
                    let messages = [];
                    
                    if (xhr.responseJSON) {
                        // Handle Laravel validation errors
                        if (xhr.responseJSON.errors) {
                            // Collect all validation errors
                            Object.keys(xhr.responseJSON.errors).forEach(function(field) {
                                xhr.responseJSON.errors[field].forEach(function(error) {
                                    messages.push(error);
                                });
                            });
                        } else if (xhr.responseJSON.message) {
                            messages.push(xhr.responseJSON.message);
                        }
                    }
                    
                    // If no specific errors found, show generic message
                    if (messages.length === 0) {
                        messages.push('Error saving contact. Please check all fields.');
                    }
                    
                    // Show all error messages
                    messages.forEach(function(msg) {
                        showAlert(msg, 'danger');
                    });
                }
            });
        });

        function editContact(id) {
            $.ajax({
                url: `/contacts/${id}`,
                method: 'GET',
                success: function(contact) {
                    $('#contactModalTitle').text('Edit Contact');
                    $('#contactId').val(contact.id);
                    $('#contactName').val(contact.name);
                    $('#contactEmail').val(contact.email);
                    $('#contactPhone').val(contact.phone);
                    $(`input[name="gender"][value="${contact.gender}"]`).prop('checked', true);

                    if (contact.profile_image) {
                        $('#profileImagePreview').html(`<img src="/storage/${contact.profile_image}" class="profile-img">`);
                    }

                    loadCustomFieldsForForm(contact.custom_field_values);
                    $('#contactModal').modal('show');
                },
                error: function() {
                    showAlert('Error loading contact', 'danger');
                }
            });
        }

        function deleteContact(id) {
            if (confirm('Are you sure you want to delete this contact?')) {
                $.ajax({
                    url: `/contacts/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            loadContacts(currentPage);
                        }
                    },
                    error: function() {
                        showAlert('Error deleting contact', 'danger');
                    }
                });
            }
        }

        function loadCustomFieldsForForm(contactCustomFields = []) {
            $.ajax({
                url: '/custom-fields?include_options=1',
                method: 'GET',
                success: function(fields) {
                    // Handle both array and paginated response
                    if (fields.data) {
                        fields = fields.data;
                    }
                    if (!Array.isArray(fields)) {
                        fields = [];
                    }
                    
                    let html = '<div class="row mb-3"><div class="col-12"><h6>Custom Fields</h6></div></div>';
                    
                    fields.forEach(function(field) {
                        let value = '';
                        if (contactCustomFields.length > 0) {
                            let existingField = contactCustomFields.find(cf => cf.custom_field_id === field.id);
                            if (existingField) {
                                value = existingField.field_value;
                            }
                        }

                        html += '<div class="row mb-3">';
                        html += `<div class="col-md-12">`;
                        html += `<label class="form-label">${field.field_name}</label>`;
                        
                        let maxLength = '';
                        // Check if field name contains length hint (e.g., "Pincode (6 chars)")
                        let lengthMatch = field.field_name.match(/\((\d+)\s*char/i);
                        if (lengthMatch) {
                            maxLength = `maxlength="${lengthMatch[1]}"`;
                        }

                        if (field.field_type === 'textarea') {
                            html += `<textarea class="form-control custom-field-input" data-field-id="${field.id}" name="custom_fields[${field.id}]" ${maxLength}>${value}</textarea>`;
                        } else if (field.field_type === 'select') {
                            html += `<select class="form-control custom-field-input" data-field-id="${field.id}" name="custom_fields[${field.id}]">`;
                            html += '<option value="">Select...</option>';
                            if (field.field_options) {
                                let options = Array.isArray(field.field_options) ? field.field_options : field.field_options.split('\n');
                                options.forEach(function(option) {
                                    option = option.trim();
                                    if (option) {
                                        html += `<option value="${option}" ${value === option ? 'selected' : ''}>${option}</option>`;
                                    }
                                });
                            }
                            html += '</select>';
                        } else {
                            html += `<input type="${field.field_type}" class="form-control custom-field-input" data-field-id="${field.id}" name="custom_fields[${field.id}]" value="${value}" ${maxLength}>`;
                        }
                        
                        html += '</div></div>';
                    });

                    $('#customFieldsContainer').html(html);
                }
            });
        }

        function initiateMerge(secondaryContactId) {
            $('#secondaryContactId').val(secondaryContactId);
            
            $.ajax({
                url: '/contacts/merge/list',
                method: 'GET',
                success: function(contacts) {
                    let html = '<option value="">Select Master Contact</option>';
                    contacts.forEach(function(contact) {
                        if (contact.id != secondaryContactId) {
                            html += `<option value="${contact.id}">${contact.name} (${contact.email})</option>`;
                        }
                    });
                    $('#masterContactSelect').html(html);
                    $('#mergeModal').modal('show');
                }
            });
        }

        $('#mergeForm').submit(function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '/contacts/merge',
                method: 'POST',
                data: {
                    master_contact_id: $('#masterContactSelect').val(),
                    secondary_contact_id: $('#secondaryContactId').val(),
                },
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        $('#mergeModal').modal('hide');
                        loadContacts(currentPage);
                    }
                },
                error: function(xhr) {
                    let messages = [];
                    
                    if (xhr.responseJSON) {
                        // Handle Laravel validation errors
                        if (xhr.responseJSON.errors) {
                            Object.keys(xhr.responseJSON.errors).forEach(function(field) {
                                xhr.responseJSON.errors[field].forEach(function(error) {
                                    messages.push(error);
                                });
                            });
                        } else if (xhr.responseJSON.message) {
                            messages.push(xhr.responseJSON.message);
                        }
                    }
                    
                    if (messages.length === 0) {
                        messages.push('Error merging contacts');
                    }
                    
                    messages.forEach(function(msg) {
                        showAlert(msg, 'danger');
                    });
                }
            });
        });

        $('#manageCustomFieldsBtn').click(function() {
            loadCustomFieldsList();
            $('#customFieldsModal').modal('show');
        });

        function loadCustomFieldsList() {
            $.ajax({
                url: '/custom-fields',
                method: 'GET',
                success: function(fields) {
                    // Handle both array and paginated response
                    if (fields.data) {
                        fields = fields.data;
                    }
                    if (!Array.isArray(fields)) {
                        fields = [];
                    }
                    
                    let html = '<table class="table"><thead><tr><th>Field Name</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                    fields.forEach(function(field) {
                        html += `
                            <tr>
                                <td>${field.field_name}</td>
                                <td>${field.field_type}</td>
                                <td>${field.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editCustomField(${field.id})">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteCustomField(${field.id})">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                    html += '</tbody></table>';
                    $('#customFieldsList').html(html);
                }
            });
        }

        // Show/Hide select options field based on field type
        $('#customFieldType').change(function() {
            if ($(this).val() === 'select') {
                $('#selectOptionsContainer').show();
            } else {
                $('#selectOptionsContainer').hide();
            }
        });

        $('#addCustomFieldBtn').click(function() {
            $('#addCustomFieldForm')[0].reset();
            $('#selectOptionsContainer').hide();
            $('#addCustomFieldModal').modal('show');
        });

        $('#addCustomFieldForm').submit(function(e) {
            e.preventDefault();
            
            let formData = {
                field_name: $('#customFieldName').val(),
                field_type: $('#customFieldType').val(),
                is_active: 1
            };

            // If select type, process options
            if ($('#customFieldType').val() === 'select') {
                let options = $('#customFieldOptions').val().split('\n').map(function(opt) {
                    return opt.trim();
                }).filter(function(opt) {
                    return opt.length > 0;
                });
                if (options.length > 0) {
                    formData.field_options = options;
                }
            }

            $.ajax({
                url: '/custom-fields',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        $('#addCustomFieldModal').modal('hide');
                        loadCustomFieldsList();
                        loadCustomFieldsForForm();
                    }
                },
                error: function(xhr) {
                    let messages = [];
                    
                    if (xhr.responseJSON) {
                        // Handle Laravel validation errors
                        if (xhr.responseJSON.errors) {
                            Object.keys(xhr.responseJSON.errors).forEach(function(field) {
                                xhr.responseJSON.errors[field].forEach(function(error) {
                                    messages.push(error);
                                });
                            });
                        } else if (xhr.responseJSON.message) {
                            messages.push(xhr.responseJSON.message);
                        }
                    }
                    
                    if (messages.length === 0) {
                        messages.push('Error creating custom field');
                    }
                    
                    messages.forEach(function(msg) {
                        showAlert(msg, 'danger');
                    });
                }
            });
        });

        function deleteCustomField(id) {
            if (confirm('Are you sure you want to delete this custom field?')) {
                $.ajax({
                    url: `/custom-fields/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            loadCustomFieldsList();
                            loadCustomFieldsForForm();
                        }
                    }
                });
            }
        }

        function showAlert(message, type) {
            // Create a unique ID for this alert to allow multiple alerts
            let alertId = 'alert-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            let icon = type === 'danger' ? '⚠️' : type === 'success' ? '✓' : 'ℹ️';
            let alertHtml = `<div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed" role="alert" style="z-index: 9999; top: 20px; right: 20px; min-width: 300px; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <strong>${icon} ${type === 'danger' ? 'Error' : type === 'success' ? 'Success' : 'Info'}:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            
            // Append to body instead of replacing, so multiple alerts can show
            $('body').append(alertHtml);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                $('#' + alertId).fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        $('#profileImage').change(function(e) {
            let file = e.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#profileImagePreview').html(`<img src="${e.target.result}" class="profile-img">`);
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

