@extends('_admin.auth.layout')

@section('content')
    <div class="position-relative overflow-hidden text-bg-light min-vh-100 d-flex align-items-center justify-content-center">
        <div class="d-flex align-items-center justify-content-center w-100">
            <div class="row justify-content-center w-100">
                <div class="col-md-8 col-lg-6 col-xxl-3">
                    <div class="card mb-0">
                        <div class="card-body">
                            <a href="{{ url('/') }}" class="text-nowrap logo-img text-center d-block py-3 w-100">
                                <img src="{{ url('admin-ui') }}/assets/images/logos/logo-2.png"
                                     alt="Logo"
                                     width="400"
                                     class="img-fluid">
                            </a>

                            <p class="text-center">Silahkan Login</p>

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
                                <div class="alert alert-danger mt-3">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form class="form w-100" action="{{ url('pegawai/auth/login') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email"
                                           class="form-control"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           autocomplete="email"
                                           required
                                           autofocus>
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password"
                                           class="form-control"
                                           id="password"
                                           name="password"
                                           autocomplete="current-password"
                                           required>
                                </div>

                                <button type="submit"
                                        class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2"
                                        style="letter-spacing: 1px">
                                    MASUK
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection