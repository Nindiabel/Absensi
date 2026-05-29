<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title fw-bold mb-0">Absensi Face Recognition</h4>
                    <small class="text-muted">Arahkan wajah Anda ke kamera untuk melakukan absen masuk atau pulang.</small>
                </div>
                <a href="{{ base_url($page['route']) }}" class="btn btn-outline-secondary btn-sm" navigate>
                    ← Kembali
                </a>
            </div>
            <div class="card-body text-center">
                
                <div class="mb-3 d-flex justify-content-center align-items-center gap-2">
                    <label class="fw-semibold mb-0">Pilih Kamera:</label>
                    <select id="cameraSelect" class="form-select w-auto"></select>
                </div>

                <div class="video-container mx-auto position-relative shadow" style="width: 100%; max-width: 380px; max-height: 50vh; border-radius: 12px; overflow: hidden; background: #000; display: flex; justify-content: center; align-items: center;">
                    <video id="videoElement" autoplay playsinline style="width: 100%; height: 100%; max-height: 50vh; transform: scaleX(-1); object-fit: cover;"></video>
                    <canvas id="canvasElement" style="display:none;"></canvas>
                    
                    <div id="scanOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); display: none !important;">
                        <div class="text-white">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                            <h5 class="mt-2 text-white">Mendeteksi Wajah...</h5>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button id="btnStartScan" class="btn btn-success btn-lg px-5 fw-bold" disabled>
                        <i class="bx bx-scan"></i> Mulai Absen
                    </button>
                </div>

                <div id="statusMessage" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
function initScanWebcam() {
    const video = document.getElementById('videoElement');
    const canvas = document.getElementById('canvasElement');
    const btnStartScan = document.getElementById('btnStartScan');
    const scanOverlay = document.getElementById('scanOverlay');
    const statusMessage = document.getElementById('statusMessage');
    const cameraSelect = document.getElementById('cameraSelect');
    
    if (!video) return;

    let stream = null;
    let scanInterval = null;
    let isScanning = false;
    let isProcessing = false;
    let sessionId = Math.random().toString(36).substring(2, 15);

    // CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function stopStream() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }

    function startStream(deviceId) {
        stopStream();
        
        const constraints = {
            video: deviceId ? { deviceId: { exact: deviceId } } : { facingMode: "user" }
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(function (s) {
                stream = s;
                video.srcObject = stream;
                btnStartScan.removeAttribute('disabled');
            })
            .catch(function (error) {
                console.error("Camera access denied or error:", error);
                statusMessage.innerHTML = '<div class="alert alert-danger">Gagal mengakses kamera. Pastikan izin kamera diberikan.</div>';
            });
    }

    // Get list of cameras and auto start
    if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
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

                if (videoDevices.length > 0) {
                    startStream(videoDevices[0].deviceId);
                }
                
                initialStream.getTracks().forEach(t => t.stop());
            });
        }).catch(function(error) {
            console.error("Failed to get initial permissions:", error);
            statusMessage.innerHTML = '<div class="alert alert-danger">Gagal mengakses kamera. Pastikan izin kamera diberikan.</div>';
        });
    } else {
        statusMessage.innerHTML = '<div class="alert alert-danger">Browser Anda tidak mendukung akses kamera.</div>';
    }

    $(cameraSelect).on('change', function() {
        startStream(this.value);
    });

    btnStartScan.addEventListener('click', function() {
        if (isScanning) {
            stopScanning();
        } else {
            startScanning();
        }
    });

    function startScanning() {
        isScanning = true;
        btnStartScan.innerHTML = '<i class="bx bx-stop-circle"></i> Hentikan Absen';
        btnStartScan.classList.replace('btn-success', 'btn-danger');
        statusMessage.innerHTML = '<div class="alert alert-warning">Silakan kedipkan mata Anda...</div>';
        
        captureAndSend();
        scanInterval = setInterval(captureAndSend, 500);
    }

    function stopScanning() {
        isScanning = false;
        clearInterval(scanInterval);
        if (btnStartScan) {
            btnStartScan.innerHTML = '<i class="bx bx-scan"></i> Mulai Absen';
            btnStartScan.classList.replace('btn-danger', 'btn-success');
        }
        if (scanOverlay) {
            scanOverlay.style.setProperty('display', 'none', 'important');
        }
    }

    function captureAndSend() {
        if (!stream || !isScanning || isProcessing) return;

        isProcessing = true;
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        scanOverlay.style.setProperty('display', 'flex', 'important');

        canvas.toBlob(function(blob) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('session_id', sessionId);
            formData.append('file', blob, 'scan.jpg');

            $.ajax({
                url: '/admin/absensi/scan-wajah',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.success) {
                        stopScanning();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (typeof loadPage === 'function') {
                                    var targetUrl = "{{ base_url($page['route']) }}";
                                    window.history.pushState(null, '', targetUrl);
                                    loadPage(targetUrl);
                                } else {
                                    window.location.href = "{{ base_url($page['route']) }}";
                                }
                            });
                        } else {
                            alert(res.message);
                            window.location.href = "{{ base_url($page['route']) }}";
                        }
                    } else {
                        statusMessage.innerHTML = '<div class="alert alert-danger">' + res.message + '</div>';
                        setTimeout(() => {
                            scanOverlay.style.setProperty('display', 'none', 'important');
                            statusMessage.innerHTML = '';
                            isProcessing = false;
                        }, 2500);
                    }
                },
                error: function (xhr) {
                    let msg = 'Terjadi kesalahan server saat menghubungi Python API.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    statusMessage.innerHTML = '<div class="alert alert-danger">' + msg + '</div>';
                    setTimeout(() => {
                        scanOverlay.style.setProperty('display', 'none', 'important');
                        statusMessage.innerHTML = '';
                        isProcessing = false;
                    }, 2500);
                }
            });

        }, 'image/jpeg', 0.9);
    }

    $(document).one('click', 'a[navigate], a[navigate-api]', function() {
        stopStream();
        if (scanInterval) {
            clearInterval(scanInterval);
        }
    });
}

setTimeout(initScanWebcam, 300);
</script>
