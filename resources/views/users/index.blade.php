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
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Groups</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Groups</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="https://sb-admin-pro.startbootstrap.com/assets/img/illustrations/profiles/profile-5.png" /></div>
                                    Tiger Nixon
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="c1b5a8a6a4b381a4aca0a8adefa2aeac">[email&#160;protected]</a></td>
                            <td>Administrator</td>
                            <td>
                                <span class="badge bg-green-soft text-green">Sales</span>
                                <span class="badge bg-blue-soft text-blue">Developers</span>
                                <span class="badge bg-red-soft text-red">Marketing</span>
                                <span class="badge bg-purple-soft text-purple">Managers</span>
                                <span class="badge bg-yellow-soft text-yellow">Customer</span>
                            </td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-2.png" /></div>
                                    Garrett Winters
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="caadbda3a4beafb8b9af8aafa7aba3a6e4a9a5a7">[email&#160;protected]</a></td>
                            <td>Administrator</td>
                            <td>
                                <span class="badge bg-green-soft text-green">Sales</span>
                                <span class="badge bg-blue-soft text-blue">Developers</span>
                                <span class="badge bg-red-soft text-red">Marketing</span>
                                <span class="badge bg-purple-soft text-purple">Managers</span>
                                <span class="badge bg-yellow-soft text-yellow">Customer</span>
                            </td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-3.png" /></div>
                                    Ashton Cox
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="3f5e4c574b50515c7f5a525e5653115c5052">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td>
                                <span class="badge bg-green-soft text-green">Sales</span>
                                <span class="badge bg-red-soft text-red">Marketing</span>
                            </td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-4.png" /></div>
                                    Cedric Kelly
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="b5d6d0d1c7dcd6ded0d9f5d0d8d4dcd99bd6dad8">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td>
                                <span class="badge bg-green-soft text-green">Sales</span>
                                <span class="badge bg-purple-soft text-purple">Managers</span>
                            </td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-5.png" /></div>
                                    Airi Satou
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="0667756772697346636b676f6a2865696b">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-yellow-soft text-yellow">Customer</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-6.png" /></div>
                                    Brielle Williamson
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="90f2e7f9fcfcf9f1fde3fffed0f5fdf1f9fcbef3fffd">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-yellow-soft text-yellow">Customer</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-1.png" /></div>
                                    Herrod Chandler
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="cba3aab9b9a4afa88baea6aaa2a7e5a8a4a6">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-green-soft text-green">Sales</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-2.png" /></div>
                                    Rhona Davidson
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="a2d0cacdccc3c6c3d4cbc6d1cdcce2c7cfc3cbce8cc1cdcf">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td>
                                <span class="badge bg-green-soft text-green">Sales</span>
                                <span class="badge bg-purple-soft text-purple">Managers</span>
                            </td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-3.png" /></div>
                                    Colleen Hurst
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="62010a1710111622070f030b0e4c010d0f">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-blue-soft text-blue">Developers</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-4.png" /></div>
                                    Sonya Frost
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="1764716578646357727a767e7b3974787a">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-yellow-soft text-yellow">Customer</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-5.png" /></div>
                                    Jena Gaines
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="7e141b101f191f17101b0d3e1b131f1712501d1113">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-yellow-soft text-yellow">Customer</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-6.png" /></div>
                                    Quinn Flynn
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="a2d3c4cedbcccce2c7cfc3cbce8cc1cdcf">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td>
                                <span class="badge bg-green-soft text-green">Sales</span>
                                <span class="badge bg-purple-soft text-purple">Managers</span>
                            </td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-1.png" /></div>
                                    Charde Marshall
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="97f4fff6e5f3faf6e5e4ffd7f2faf6fefbb9f4f8fa">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-green-soft text-green">Sales</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-2.png" /></div>
                                    Haley Kennedy
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="83ebe8e6edede6e7fac3e6eee2eaefade0ecee">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-yellow-soft text-yellow">Customer</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-3.png" /></div>
                                    Tatyana Fitzpatrick
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="a9ddc8ddcfc0ddd3e9ccc4c8c0c587cac6c4">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-yellow-soft text-yellow">Customer</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-4.png" /></div>
                                    Michael Silva
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="34595d575c555158475d584255745159555d581a575b59">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-blue-soft text-blue">Developers</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-5.png" /></div>
                                    Paul Byrd
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="b4c4d6cdc6d0f4d1d9d5ddd89ad7dbd9">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-yellow-soft text-yellow">Customer</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-6.png" /></div>
                                    Gloria Little
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="c1a6adaeb3a8a0ada8b5b5ada481a4aca0a8adefa2aeac">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td>
                                <span class="badge bg-blue-soft text-blue">Developers</span>
                                <span class="badge bg-purple-soft text-purple">Managers</span>
                            </td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-1.png" /></div>
                                    Bradley Greer
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="25474257404057654048444c490b464a48">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-yellow-soft text-yellow">Customer</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2"><img class="avatar-img img-fluid" src="assets/img/illustrations/profiles/profile-2.png" /></div>
                                    Dai Rios
                                </div>
                            </td>
                            <td><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="7216001b1d0132171f131b1e5c111d1f">[email&#160;protected]</a></td>
                            <td>Registered</td>
                            <td><span class="badge bg-blue-soft text-blue">Developers</span></td>
                            <td>20 Jun 2021</td>
                            <td>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="user-management-edit-user.html"><i data-feather="edit"></i></a>
                                <a class="btn btn-datatable btn-icon btn-transparent-dark" href="#!"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="https://sb-admin-pro.startbootstrap.com/js/datatables/datatables-simple-demo.js"></script>
@endsection
