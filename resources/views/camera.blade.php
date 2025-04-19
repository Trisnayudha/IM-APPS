<!-- resources/views/camera.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Camera QA dengan Zoom & Fokus</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body {
            font-family: sans-serif;
            margin: 1rem;
        }

        video,
        canvas {
            max-width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        #controls {
            margin: 0.5rem 0;
        }

        #controls label {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        #controls input[type="range"] {
            vertical-align: middle;
            margin-right: 1rem;
        }

        #snap {
            padding: 0.5rem 1rem;
            font-size: 1rem;
            border: none;
            background: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        #snap:disabled {
            background: #aaa;
            cursor: not-allowed;
        }

        #result {
            margin-top: 1rem;
            white-space: pre-wrap;
            background: #f9f9f9;
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <h2>Ambil Foto & Jawab Soal</h2>

    <p>Jawaban akan dikirim ke: <strong>083829314436</strong></p>

    <!-- video stream -->
    <video id="video" autoplay playsinline></video>

    <!-- zoom & focus controls -->
    <div id="controls"></div>

    <!-- tombol capture -->
    <button id="snap">Take Photo</button>

    <!-- canvas hidden untuk capture -->
    <canvas id="canvas" style="display:none;"></canvas>

    <!-- hasil jawaban -->
    <pre id="result"></pre>

    <script>
        (async () => {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const snap = document.getElementById('snap');
            const result = document.getElementById('result');
            const controls = document.getElementById('controls');
            const phoneValue = '6283829314436';

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment'
                    },
                    audio: false
                });
                video.srcObject = stream;

                const track = stream.getVideoTracks()[0];
                const caps = track.getCapabilities();

                // Zoom slider
                if (caps.zoom) {
                    const zoomLabel = document.createElement('label');
                    zoomLabel.htmlFor = 'zoomSlider';
                    zoomLabel.textContent = 'Zoom:';
                    const zoomSlider = document.createElement('input');
                    zoomSlider.type = 'range';
                    zoomSlider.id = 'zoomSlider';
                    zoomSlider.min = caps.zoom.min;
                    zoomSlider.max = caps.zoom.max;
                    zoomSlider.step = caps.zoom.step;
                    zoomSlider.value = track.getSettings().zoom || caps.zoom.min;
                    zoomSlider.oninput = () => {
                        track.applyConstraints({
                                advanced: [{
                                    zoom: parseFloat(zoomSlider.value)
                                }]
                            })
                            .catch(console.warn);
                    };
                    controls.append(zoomLabel, zoomSlider);
                }

                // Focus slider
                if (caps.focusDistance) {
                    const focusLabel = document.createElement('label');
                    focusLabel.htmlFor = 'focusSlider';
                    focusLabel.textContent = 'Focus:';
                    const focusSlider = document.createElement('input');
                    focusSlider.type = 'range';
                    focusSlider.id = 'focusSlider';
                    focusSlider.min = caps.focusDistance.min;
                    focusSlider.max = caps.focusDistance.max;
                    focusSlider.step = caps.focusDistance.step;
                    focusSlider.value = track.getSettings().focusDistance || caps.focusDistance.min;
                    focusSlider.oninput = () => {
                        track.applyConstraints({
                                advanced: [{
                                    focusDistance: parseFloat(focusSlider.value)
                                }]
                            })
                            .catch(console.warn);
                    };
                    controls.append(focusLabel, focusSlider);
                }
            } catch (err) {
                console.error('Gagal akses kamera:', err);
                result.textContent = 'Gagal akses kamera: ' + err.message;
                snap.disabled = true;
                return;
            }

            // Capture & kirim
            snap.addEventListener('click', async () => {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                const imgData = canvas.toDataURL('image/png');

                result.textContent = 'Mengirim ke server...';
                snap.disabled = true;

                try {
                    const res = await axios.post('{{ route('camera.capture') }}', {
                        image: imgData,
                        phone: phoneValue
                    });
                    result.textContent = 'Jawaban: ' + res.data.answer;
                } catch (err) {
                    console.error(err);
                    result.textContent = 'Error: ' + (err.response?.data?.message || err.message);
                } finally {
                    snap.disabled = false;
                }
            });
        })();
    </script>
</body>

</html>
