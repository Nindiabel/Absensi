<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0">Registrasi Wajah Pegawai</h4>
                    <small class="text-muted">Ambil foto untuk didaftarkan ke sistem face recognition</small>
                </div>
                <a href="{{ base_url($page['route']) }}" class="btn btn-outline-secondary btn-sm" navigate>
                    ← Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 text-center">
                <form id="formRegistrasi" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4 text-start">
                        <label class="form-label fw-semibold">Pilih Pegawai <span class="text-danger">*</span></label>
                        <select name="member_id" class="form-select select2" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach ($members as $member)
                                @if (in_array(strtolower($member->category ?? ''), ['guru', 'tendik']) && !in_array($member->id, $registeredMemberIds))
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label fw-semibold">Pilih Kamera</label>
                        <select id="cameraSelect" class="form-select"></select>
                    </div>

                    <div class="mb-4">
                        <div class="video-container mx-auto" style="width: 100%; max-width: 480px; position: relative; border-radius: 8px; overflow: hidden; background: #000;">
                            <video id="webcam" autoplay playsinline style="width: 100%; height: auto;"></video>
                            <canvas id="canvas" style="display:none;"></canvas>
                        </div>
                        <div id="camera-error" class="text-danger mt-2" style="display:none;">
                            Gagal mengakses kamera. Pastikan memberikan izin.
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" id="btnCapture" class="btn btn-success px-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera me-2" viewBox="0 0 16 16">
                              <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1v6zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2z"/>
                              <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5zm0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                            </svg>
                            Ambil & Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function initWebcam() {
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('canvas');
    const btnCapture = document.getElementById('btnCapture');
    const cameraError = document.getElementById('camera-error');
    const cameraSelect = document.getElementById('cameraSelect');
    
    if (!video) return;

    let stream = null;

    function stopStream() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }

    function startStream(deviceId) {
        stopStream();
        
        const constraints = {
            video: deviceId ? { deviceId: { exact: deviceId } } : true
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(function (s) {
                stream = s;
                video.srcObject = stream;
                video.play();
                cameraError.style.display = 'none';
            })
            .catch(function (error) {
                console.error("Camera access denied or error:", error);
                cameraError.style.display = 'block';
            });
    }

    // Get list of cameras
    if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
        // Request basic permission first to get labels
        navigator.mediaDevices.getUserMedia({ video: true }).then(function(initialStream) {
            navigator.mediaDevices.enumerateDevices().then(function(devices) {
                cameraSelect.innerHTML = '';
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                
                videoDevices.forEach(function(device, index) {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.text = device.label || `Camera ${index + 1}`;
                    cameraSelect.appendChild(option);
                });

                // Start the stream with the currently selected camera
                if (videoDevices.length > 0) {
                    startStream(videoDevices[0].deviceId);
                }
                
                // We don't need the initial stream anymore if startStream creates a new one
                initialStream.getTracks().forEach(t => t.stop());
            });
        }).catch(function(error) {
            console.error("Failed to get initial permissions:", error);
            cameraError.innerText = "Gagal mengakses kamera. Pastikan memberikan izin.";
            cameraError.style.display = 'block';
        });
    } else {
        cameraError.innerText = "Browser Anda tidak mendukung akses kamera.";
        cameraError.style.display = 'block';
    }

    // When user changes camera
    $(cameraSelect).on('change', function() {
        startStream(this.value);
    });

    const onBeforeUnload = function() { stopStream(); };
    $(window).on('beforeunload', onBeforeUnload);

    $(document).one('click', 'a[navigate], a[navigate-api]', function() {
        stopStream();
    });

    $(btnCapture).off('click').on('click', function () {
        const member_id = $('select[name="member_id"]').val();
        
        if (!member_id) {
            alert('Pilih pegawai terlebih dahulu!');
            return;
        }

        if (!stream) {
            alert('Kamera tidak aktif!');
            return;
        }

        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(function(blob) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('member_id', member_id);
            formData.append('file', blob, 'face.jpg');

            const btn = $(btnCapture);
            const originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: '/admin/absensi/registrasi-wajah',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.success) {
                        alert('Registrasi wajah berhasil!');
                        stopStream();
                        
                        // Gunakan SPA router jika ada, atau fallback ke reload URL absolut
                        if (typeof loadPage === 'function') {
                            var targetUrl = "/admin/absensi/registrasi-wajah";
                            window.history.pushState(null, '', targetUrl);
                            loadPage(targetUrl);
                        } else {
                            window.location.href = '/admin/absensi/registrasi-wajah';
                        }
                    } else {
                        alert(res.message || 'Gagal registrasi wajah.');
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function (xhr) {
                    let msg = 'Terjadi kesalahan server.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                    btn.prop('disabled', false).html(originalText);
                }
            });
        }, 'image/jpeg', 0.9);
    });
}

setTimeout(initWebcam, 300);
</script>
