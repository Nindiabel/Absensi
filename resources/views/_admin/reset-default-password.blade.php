<!doctype html>
<html lang="en">

<head>
    <title>SmartKeuangan</title>

    @include('_admin._layout.favicon')
    {{-- @include('_admin._layout.head') --}}
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <div
            class="position-relative overflow-hidden text-bg-light min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-8 col-lg-6 col-xxl-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <a href="" class="text-nowrap logo-img text-center d-block py-3 w-100">
                                    <img src="{{ url('admin-ui') }}/assets/images/logos/logo-center.png" alt=""
                                        width="400" class="img-fluid">
                                </a>
                                <h3 class="text-center mb-1">Perbarui Password</h3>
                                <p class="text-center">Silahkan masukkan password baru anda!</p>

                                @if (Session::has('success'))
                                    <div class="alert alert-success mt-3">
                                        {{ Session::get('success') }}
                                        @php
                                            Session::forget('success');
                                        @endphp
                                    </div>
                                @endif
                                @if (Session::has('error'))
                                    <div class="alert alert-warning mt-3">
                                        {{ Session::get('error') }}
                                        @php
                                            Session::forget('error');
                                        @endphp
                                    </div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <b>Terjadi kesalahan pada proses input data</b> <br>
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form class="form w-100" action="<?= base_url('auth/reset-default-password') ?>" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password"
                                            id="password" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="re_password" class="form-label">Ulangi Password</label>
                                        <input type="password" class="form-control" name="re_password"
                                            id="re_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2"
                                        style="letter-spacing: 1px"><b>SIMPAN PASSWORD</b></button>
                                </form>
                                {{-- <hr>
                                <a href="{{ route('google-login') }}"
                                    class="btn btn-danger w-100 py-8 fs-4 mb-4 rounded-2 text-start shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" class="me-1"
                                        viewBox="0 0 128 128">
                                        <path fill="currentColor"
                                            d="M44.59 4.21a63.28 63.28 0 0 0 4.33 120.9a67.6 67.6 0 0 0 32.36.35a57.13 57.13 0 0 0 25.9-13.46a57.44 57.44 0 0 0 16-26.26a74.3 74.3 0 0 0 1.61-33.58H65.27v24.69h34.47a29.72 29.72 0 0 1-12.66 19.52a36.2 36.2 0 0 1-13.93 5.5a41.3 41.3 0 0 1-15.1 0A37.2 37.2 0 0 1 44 95.74a39.3 39.3 0 0 1-14.5-19.42a38.3 38.3 0 0 1 0-24.63a39.25 39.25 0 0 1 9.18-14.91A37.17 37.17 0 0 1 76.13 27a34.3 34.3 0 0 1 13.64 8q5.83-5.8 11.64-11.63c2-2.09 4.18-4.08 6.15-6.22A61.2 61.2 0 0 0 87.2 4.59a64 64 0 0 0-42.61-.38" />
                                    </svg>
                                    Login dengan <b>Google</b>
                                </a> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ url('admin-ui') }}/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="{{ url('admin-ui') }}/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- solar icons -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector('form');
            const submitButton = form.querySelector('button[type="submit"]');
            const buttonText = submitButton.textContent;

            window.addEventListener("popstate", function(e) {
                submitButton.classList.remove("disabled");
                submitButton.innerHTML = buttonText;
            });
            form.addEventListener("submit", function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    submitButton.classList.remove("disabled");
                    submitButton.innerHTML = buttonText;
                    form.appendChild(errorElement);
                } else {
                    submitButton.classList.add("disabled");
                    submitButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span> Memproses ...';
                }
            });
        });
    </script>
</body>

</html>
