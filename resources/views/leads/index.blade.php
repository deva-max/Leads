
@extends('layouts.app')
@section('content')


<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="p-4">Leads Management</h2>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

           
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="excel_file" class="form" required>
                            <button type="submit" class="btn btn-primary">Import Leads (Excel)</button>
                        </div>
                    </form>
                </div>
            
                <div class="col-md-6 text-end">
                    <button id="exportExcelBtn" class="btn btn-info">
                        <i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Leads (Excel)
                    </button>
                </div>
            </div>
            


            <!-- Leads Table -->
            <table id="leads-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                
            </table>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('#leads-table').DataTable({
            processing: true,
            serverside: true,
            ajax: '{{ route("leads.index") }}',
            columns: [
                { data: 'name', name: 'name'},
                { data: 'email', name: 'email'},
                { data: 'phone', name: 'phone'},
                { data: 'status', name: 'status'},
                { data: 'actions', name: 'actions', orderable: false, seachable: false},
            ],
        });
    });

    document.getElementById('exportExcelBtn').addEventListener('click', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('{{ route("leads.export") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'X-CSRF-TOKEN': csrfToken, // Add the CSRF token here
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.blob(); // Get the file as a Blob
        })
        .then(blob => {
            // Create a link to download the file
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'leads_export_' + new Date() +'.xlsx'; // Set the file name
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('There was an error exporting the Excel file:', error.message);
            alert('Failed to export the Excel file: ' + error.message);
        });
    });



    $('#leads-table').on('click', '.delete-button', function () {
        const leadId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form dynamically for deletion
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/leads/${leadId}`;

                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);

                    // Add DELETE method spoofing
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    
</script>
@endsection
