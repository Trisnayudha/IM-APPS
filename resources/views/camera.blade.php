<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Camera QA</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        video,
        canvas {
            max-width: 100%;
        }
    </style>
</head>

<body>
    <h2>Ambil Foto & Jawab Soal</h2>

    <!-- Nomor WA sudah fix, jadi kita tidak tampilkan input field -->
    <p>Jawaban akan dikirimkan ke: <strong>083829314436</strong></p>

    <video id="video" autoplay playsinline></video><br />
    <button id="snap">Take Photo</button>
    <canvas id="canvas" style="display:none;"></canvas>

    <pre id="result"></pre>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const snap = document.getElementById('snap');
        const result = document.getElementById('result');
        // Nomor WA fixed, pakai format internasional (62...)
        const phoneValue = '6283829314436';

        // Akses kamera belakang
        navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment'
                },
                audio: false
            })
            .then(stream => video.srcObject = stream)
            .catch(err => console.error('Gagal akses kamera:', err));

        // Tangkap foto & kirim
        snap.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);

            const imgData = canvas.toDataURL('image/png');
            result.textContent = 'Mengirim ke server...';

            axios.post('{{ route('camera.capture') }}', {
                    image: imgData,
                    phone: phoneValue
                })
                .then(res => {
                    result.textContent = 'Jawaban: ' + res.data.answer;
                })
                .catch(err => {
                    console.error(err);
                    result.textContent = 'Error: ' + (err.response?.data?.message || err.message);
                });
        });
    </script>
</body>

</html>
