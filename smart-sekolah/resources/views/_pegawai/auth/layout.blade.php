<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('_admin._layout.favicon')

    <title>{{ env('APP_NAME') }}</title>

    <link rel="stylesheet" href="{{ url('admin-ui') }}/assets/css/styles.min.css" />
</head>

<body>
    <div class="page-wrapper"
         id="main-wrapper"
         data-layout="vertical"
         data-navbarbg="skin6"
         data-sidebartype="full"
         data-sidebar-position="fixed"
         data-header-position="fixed">

        @yield('content')

    </div>

    <script src="{{ url('admin-ui') }}/assets/libs/jquery/dist/jquery.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.querySelector('form');

            if (!form) {
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');

            if (!submitButton) {
                return;
            }

            const buttonText = submitButton.innerHTML;

            window.addEventListener("pageshow", function () {
                submitButton.classList.remove("disabled");
                submitButton.disabled = false;
                submitButton.innerHTML = buttonText;
            });

            form.addEventListener("submit", function (e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();

                    submitButton.classList.remove("disabled");
                    submitButton.disabled = false;
                    submitButton.innerHTML = buttonText;

                    form.classList.add('was-validated');

                    return false;
                }

                submitButton.classList.add("disabled");
                submitButton.disabled = true;
                submitButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span> Memproses ...';
            });
        });
    </script>
</body>

</html>