@extends('layouts.admin')

@section('main-content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-fluid px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
                        Users List
                    </h1>
                </div>
                <div class="col-12 col-xl-auto mb-3">
                    <a class="btn btn-sm btn-light text-primary" href="user-management-groups-list.html">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users me-1"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        Manage Groups
                    </a>
                    <a class="btn btn-sm btn-light text-primary" href="user-management-add-user.html">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-plus me-1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                        Add New User
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
    <!-- Main page content-->
    <div class="container-fluid px-4">
        <div class="card">
            <div class="card-body">
                <form>
                    <!-- Form Row-->
                    <div class="row gx-3 mb-3">
                        <!-- Form Group (first name)-->
                        <div class="col-md-6">
                            <label class="small mb-1" for="inputFirstName">First name</label>
                            <input class="form-control" id="inputFirstName" type="text" placeholder="Enter your first name" value="">
                        </div>
                        <!-- Form Group (last name)-->
                        <div class="col-md-6">
                            <label class="small mb-1" for="inputLastName">Last name</label>
                            <input class="form-control" id="inputLastName" type="text" placeholder="Enter your last name" value="">
                        </div>
                    </div>
                    <!-- Form Group (email address)-->
                    <div class="mb-3">
                        <label class="small mb-1" for="inputEmailAddress">Email address</label>
                        <input class="form-control" id="inputEmailAddress" type="email" placeholder="Enter your email address" value="">
                    </div>
                    <!-- Form Group (Group Selection Checkboxes)-->
                    <div class="mb-3">
                        <label class="small mb-1">Group(s)</label>
                        <div class="form-check">
                            <input class="form-check-input" id="groupSales" type="checkbox" value="">
                            <label class="form-check-label" for="groupSales">Sales</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" id="groupDevs" type="checkbox" value="">
                            <label class="form-check-label" for="groupDevs">Developers</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" id="groupMarketing" type="checkbox" value="">
                            <label class="form-check-label" for="groupMarketing">Marketing</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" id="groupManagers" type="checkbox" value="">
                            <label class="form-check-label" for="groupManagers">Managers</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" id="groupCustomer" type="checkbox" value="">
                            <label class="form-check-label" for="groupCustomer">Customer</label>
                        </div>
                    </div>
                    <!-- Form Group (Roles)-->
                    <div class="mb-3">
                        <label class="small mb-1">Role</label>
                        <select class="form-select" aria-label="Default select example">
                            <option selected="" disabled="">Select a role:</option>
                            <option value="administrator">Administrator</option>
                            <option value="registered">Registered</option>
                            <option value="edtior">Editor</option>
                            <option value="guest">Guest</option>
                        </select>
                    </div>
                    <!-- Submit button-->
                    <button class="btn btn-primary" type="button">Add user</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="https://sb-admin-pro.startbootstrap.com/js/datatables/datatables-simple-demo.js"></script>
@endsection
