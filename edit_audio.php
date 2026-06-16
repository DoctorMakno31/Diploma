<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: adminstr.php');
    exit();
}

$connection = mysqli_connect('127.0.0.1', 'root', '', 'MySite');
if (!$connection) {
    die('ERROR: ' . mysqli_connect_error());
}

$project_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$project_result = mysqli_query($connection, "SELECT * FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");
$project = mysqli_fetch_assoc($project_result);

if (!$project || !$project['original_file_path'] || !file_exists($project['original_file_path'])) {
    header('Location: cabinet.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование аудио - <?php echo htmlspecialchars($project['name']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }
        .editor-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }
        .audio-section, .controls-section {
            background: rgba(0,0,0,0.3);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }
        .control-group {
            margin-bottom: 25px;
        }
        .control-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #ddd;
        }
        input[type="range"] {
            width: 100%;
            height: 6px;
            background: #4CAF50;
            border-radius: 3px;
            -webkit-appearance: none;
        }
        input[type="range"]:focus {
            outline: none;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            cursor: pointer;
        }
        .value-display {
            display: inline-block;
            background: rgba(0,0,0,0.5);
            padding: 5px 10px;
            border-radius: 5px;
            margin-left: 10px;
            font-family: monospace;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s, background 0.2s;
            margin: 5px;
        }
        .btn-primary {
            background: #2196F3;
        }
        .btn-save {
            background: #FF9800;
        }
        .btn-exit {
            background: #4CAF50;
        }
        .btn-apply {
            background: #9C27B0;
        }
        .btn-apply:hover {
            background: #7B1FA2;
        }
        .playback-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 15px 0;
        }
        .canvas-container {
            position: relative;
            width: 100%;
        }
        canvas {
            width: 100%;
            height: 150px;
            background: rgba(0,0,0,0.5);
            border-radius: 10px;
            margin: 15px 0;
            cursor: pointer;
            display: block;
        }
        #playbackLine {
            position: absolute;
            width: 2px;
            height: 150px;
            background-color: #ff3333;
            top: 0;
            left: 0;
            pointer-events: none;
            box-shadow: 0 0 4px #ff0000;
            z-index: 10;
        }
        .trim-controls {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }
        .trim-sliders {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .trim-sliders div {
            flex: 1;
        }
        .time-display {
            font-family: monospace;
            font-size: 14px;
            background: rgba(0,0,0,0.5);
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 5px;
        }
        .file-info {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }
        .status-message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        .status-success {
            background: #4CAF50;
            display: block;
        }
        .status-error {
            background: #f44336;
            display: block;
        }
        .status-info {
            background: #2196F3;
            display: block;
        }
        h3 {
            margin-bottom: 15px;
            color: #4CAF50;
        }
        .current-time {
            font-size: 14px;
            text-align: center;
            margin-top: 5px;
            font-family: monospace;
            color: #ff3333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎵 Редактирование аудио: <?php echo htmlspecialchars($project['name']); ?></h1>
            <p>Файл: <?php echo basename($project['original_file_path']); ?></p>
            <div>
                <a href="cabinet.php" class="btn">← Назад к проектам</a>
                <a href="download.php?type=original&id=<?php echo $project['id']; ?>" class="btn">📥 Скачать оригинал</a>
            </div>
        </div>

        <div id="statusMessage" class="status-message"></div>

        <div class="editor-grid">
            <div class="audio-section">
                <div class="playback-controls">
                    <button class="btn" id="playPauseBtn">▶ Воспроизвести</button>
                    <button class="btn" id="stopBtn">⏹️ Стоп</button>
                </div>
                
                <h3>📊 Визуализация и обрезание</h3>
                <div class="canvas-container">
                    <canvas id="waveformCanvas" width="800" height="150"></canvas>
                    <div id="playbackLine"></div>
                </div>
                <div class="current-time" id="currentTimeDisplay">0:00 / <span id="totalTimeDisplay">0:00</span></div>
                <p style="font-size: 12px; color: #aaa; margin-top: 5px; text-align: center;">💡 Кликни по волне — перемотка | 🎯 Ползунки — выбор фрагмента | 🔴 Красная линия — текущая позиция</p>
                
                <div class="trim-controls">
                    <div class="trim-sliders">
                        <div>
                            <label>🎯 Начало (Start)</label>
                            <input type="range" id="startTrim" min="0" max="100" value="0" step="0.1">
                            <div><span class="time-display" id="startTime">0:00</span></div>
                        </div>
                        <div>
                            <label>⏹️ Конец (End)</label>
                            <input type="range" id="endTrim" min="0" max="100" value="100" step="0.1">
                            <div><span class="time-display" id="endTime">0:00</span></div>
                        </div>
                    </div>
                    <button class="btn" id="resetTrimBtn" style="margin-top: 10px; width: 100%;">🔄 Сбросить (весь трек)</button>
                </div>

                <div class="file-info">
                    <strong>Оригинальный файл:</strong> <?php echo basename($project['original_file_path']); ?><br>
                    <strong>Размер:</strong> <?php echo round($project['file_size'] / 1024 / 1024, 2); ?> MB
                </div>
            </div>

            <div class="controls-section">
                <h3>🎛️ Аудиоэффекты</h3>
                
                <div class="control-group">
                    <label>
                        🔊 Громкость
                        <span class="value-display" id="volumeValue">0 dB</span>
                    </label>
                    <input type="range" id="volumeGain" 
                           min="-30" max="20" step="1" value="0">
                </div>

                <div class="control-group">
                    <label>
                        🎚️ Эквалайзер - Низкие (Bass)
                        <span class="value-display" id="bassValue">0 dB</span>
                    </label>
                    <input type="range" id="bassEQ" 
                           min="-20" max="20" step="1" value="0">
                </div>

                <div class="control-group">
                    <label>
                        🎚️ Эквалайзер - Средние (Mid)
                        <span class="value-display" id="midValue">0 dB</span>
                    </label>
                    <input type="range" id="midEQ" 
                           min="-20" max="20" step="1" value="0">
                </div>

                <div class="control-group">
                    <label>
                        🎚️ Эквалайзер - Высокие (Treble)
                        <span class="value-display" id="trebleValue">0 dB</span>
                    </label>
                    <input type="range" id="trebleEQ" 
                           min="-20" max="20" step="1" value="0">
                </div>

                <button class="btn btn-apply" id="applyEffectsBtn" style="width: 100%; margin-top: 10px;">
                    ✨ Применить эффекты к фрагменту
                </button>

                <button class="btn btn-primary" id="resetEffectsBtn" style="width: 100%; margin-top: 10px;">
                    🔄 Сбросить все эффекты
                </button>
                
                <button class="btn btn-save" id="saveBtn" style="width: 100%; margin-top: 10px;">
                    💾 Скачать обработанный WAV
                </button>

                <button class="btn btn-exit" id="saveExitBtn" style="width: 100%; margin-top: 10px;">
                    💾 Сохранить и вернуться
                </button>
            </div>
        </div>
    </div>

    <script>
        let audioContext, audioBuffer, originalBuffer;
        let isPlaying = false, startPercent = 0, endPercent = 100, duration = 0;
        let currentPlaybackTime = 0;
        let animationFrameId = null;
        let currentSourceNode = null;
        
        const canvas = document.getElementById('waveformCanvas');
        const ctx = canvas.getContext('2d');
        const playbackLine = document.getElementById('playbackLine');
        const currentTimeDisplay = document.getElementById('currentTimeDisplay');
        const totalTimeDisplay = document.getElementById('totalTimeDisplay');
        
        const startSlider = document.getElementById('startTrim');
        const endSlider = document.getElementById('endTrim');
        const startTimeSpan = document.getElementById('startTime');
        const endTimeSpan = document.getElementById('endTime');
        const playPauseBtn = document.getElementById('playPauseBtn');
        const stopBtn = document.getElementById('stopBtn');
        
        function formatTime(seconds) {
            if (isNaN(seconds)) seconds = 0;
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
        
        function updatePlaybackLine() {
            if (!duration) return;
            const width = canvas.clientWidth;
            const percent = (currentPlaybackTime / duration) * 100;
            const x = (percent / 100) * width;
            playbackLine.style.left = Math.max(0, Math.min(width, x)) + 'px';
            currentTimeDisplay.innerHTML = formatTime(currentPlaybackTime) + ' / ' + formatTime(duration);
        }
        
        function drawWaveform() {
            if (!audioBuffer) return;
            const width = canvas.width;
            const height = canvas.height;
            const data = audioBuffer.getChannelData(0);
            const step = Math.ceil(data.length / width);
            
            ctx.clearRect(0, 0, width, height);
            ctx.fillStyle = 'rgba(0,0,0,0.5)';
            ctx.fillRect(0, 0, width, height);
            
            ctx.beginPath();
            ctx.strokeStyle = '#888';
            ctx.lineWidth = 1;
            for (let i = 0; i < width; i++) {
                let min = 1.0, max = -1.0;
                for (let j = 0; j < step; j++) {
                    const datum = data[(i * step) + j];
                    if (datum < min) min = datum;
                    if (datum > max) max = datum;
                }
                const yMin = (0.5 + min * 0.5) * height;
                const yMax = (0.5 + max * 0.5) * height;
                if (i === 0) ctx.moveTo(i, yMin);
                ctx.lineTo(i, yMin);
                ctx.lineTo(i, yMax);
            }
            ctx.stroke();
            
            const startX = (startPercent / 100) * width;
            const endX = (endPercent / 100) * width;
            ctx.fillStyle = 'rgba(76, 175, 80, 0.3)';
            ctx.fillRect(startX, 0, endX - startX, height);
            ctx.strokeStyle = '#4CAF50';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(startX, 0);
            ctx.lineTo(startX, height);
            ctx.moveTo(endX, 0);
            ctx.lineTo(endX, height);
            ctx.stroke();
            
            updatePlaybackLine();
        }
        
        function updateTrimStart() {
            startPercent = parseFloat(startSlider.value);
            if (startPercent >= endPercent) {
                startPercent = Math.max(0, endPercent - 0.5);
                startSlider.value = startPercent;
            }
            const startSec = (startPercent / 100) * duration;
            startTimeSpan.textContent = formatTime(startSec);
            if (!isPlaying) {
                currentPlaybackTime = startSec;
                updatePlaybackLine();
            }
            drawWaveform();
        }
        
        function updateTrimEnd() {
            endPercent = parseFloat(endSlider.value);
            if (endPercent <= startPercent) {
                endPercent = Math.min(100, startPercent + 0.5);
                endSlider.value = endPercent;
            }
            const endSec = (endPercent / 100) * duration;
            endTimeSpan.textContent = formatTime(endSec);
            if (isPlaying && currentPlaybackTime > endSec) {
                currentPlaybackTime = endSec;
                updatePlaybackLine();
            }
            drawWaveform();
        }
        
        function resetTrim() {
            startSlider.value = 0;
            endSlider.value = 100;
            updateTrimStart();
            updateTrimEnd();
        }
        
        function playAudio() {
            if (isPlaying) {
                pauseAudio();
                return;
            }
            
            const startSec = (startPercent / 100) * duration;
            const endSec = (endPercent / 100) * duration;
            
            if (currentPlaybackTime < startSec || currentPlaybackTime > endSec) {
                currentPlaybackTime = startSec;
            }
            
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioContext.state === 'suspended') {
                audioContext.resume();
            }
            
            if (currentSourceNode) {
                try { currentSourceNode.stop(); } catch(e) {}
            }
            
            currentSourceNode = audioContext.createBufferSource();
            currentSourceNode.buffer = audioBuffer;
            currentSourceNode.playbackRate.value = 1.0;
            currentSourceNode.connect(audioContext.destination);
            currentSourceNode.start(0, currentPlaybackTime);
            
            isPlaying = true;
            playPauseBtn.textContent = '⏸️ Пауза';
            
            // Анимация линии и контроль конца фрагмента
            if (animationFrameId) cancelAnimationFrame(animationFrameId);
            let startTime = audioContext.currentTime;
            let startPos = currentPlaybackTime;
            
            function animate() {
                if (!isPlaying || !currentSourceNode) {
                    animationFrameId = null;
                    return;
                }
                const elapsed = audioContext.currentTime - startTime;
                currentPlaybackTime = startPos + elapsed;
                if (currentPlaybackTime >= endSec) {
                    currentPlaybackTime = endSec;
                    updatePlaybackLine();
                    stopAudio();
                    animationFrameId = null;
                    return;
                }
                updatePlaybackLine();
                animationFrameId = requestAnimationFrame(animate);
            }
            animate();
            
            currentSourceNode.onended = () => {
                if (isPlaying) {
                    isPlaying = false;
                    playPauseBtn.textContent = '▶ Воспроизвести';
                    if (animationFrameId) cancelAnimationFrame(animationFrameId);
                    updatePlaybackLine();
                }
            };
        }
        
        function pauseAudio() {
            if (currentSourceNode && isPlaying) {
                try { currentSourceNode.stop(); } catch(e) {}
                isPlaying = false;
                playPauseBtn.textContent = '▶ Воспроизвести';
                if (animationFrameId) cancelAnimationFrame(animationFrameId);
            }
        }
        
        function stopAudio() {
            if (currentSourceNode) {
                try { currentSourceNode.stop(); } catch(e) {}
            }
            isPlaying = false;
            playPauseBtn.textContent = '▶ Воспроизвести';
            currentPlaybackTime = (startPercent / 100) * duration;
            updatePlaybackLine();
            if (animationFrameId) cancelAnimationFrame(animationFrameId);
        }
        
        async function applyEffectsToFragment() {
            if (!originalBuffer) {
                showStatus('Аудио не загружено', 'error');
                return;
            }
            
            showStatus('Применение эффектов к фрагменту...', 'info');
            
            const startSec = (startPercent / 100) * duration;
            const endSec = (endPercent / 100) * duration;
            const sampleRate = originalBuffer.sampleRate;
            const startSample = Math.floor(startSec * sampleRate);
            const endSample = Math.floor(endSec * sampleRate);
            const totalSamples = originalBuffer.length;
            
            const volume = parseFloat(document.getElementById('volumeGain').value);
            const bassVal = parseFloat(document.getElementById('bassEQ').value);
            const midVal = parseFloat(document.getElementById('midEQ').value);
            const trebleVal = parseFloat(document.getElementById('trebleEQ').value);
            
            const originalData = originalBuffer.getChannelData(0);
            const resultData = new Float32Array(totalSamples);
            for (let i = 0; i < totalSamples; i++) resultData[i] = originalData[i];
            
            const fragmentSamples = endSample - startSample;
            const fragmentCtx = new OfflineAudioContext(1, fragmentSamples, sampleRate);
            const fragmentBuffer = fragmentCtx.createBuffer(1, fragmentSamples, sampleRate);
            const fragmentData = fragmentBuffer.getChannelData(0);
            for (let i = 0; i < fragmentSamples; i++) fragmentData[i] = originalData[startSample + i];
            
            const source = fragmentCtx.createBufferSource();
            source.buffer = fragmentBuffer;
            const gain = fragmentCtx.createGain();
            gain.gain.value = Math.pow(10, volume / 20);
            const bass = fragmentCtx.createBiquadFilter();
            bass.type = 'lowshelf'; bass.frequency.value = 200; bass.gain.value = bassVal;
            const mid = fragmentCtx.createBiquadFilter();
            mid.type = 'peaking'; mid.frequency.value = 1000; mid.Q.value = 1; mid.gain.value = midVal;
            const treble = fragmentCtx.createBiquadFilter();
            treble.type = 'highshelf'; treble.frequency.value = 5000; treble.gain.value = trebleVal;
            
            source.connect(gain);
            gain.connect(bass);
            bass.connect(mid);
            mid.connect(treble);
            treble.connect(fragmentCtx.destination);
            source.start();
            
            try {
                const processedFragment = await fragmentCtx.startRendering();
                const processedData = processedFragment.getChannelData(0);
                for (let i = 0; i < fragmentSamples; i++) resultData[startSample + i] = processedData[i];
                
                const finalCtx = new OfflineAudioContext(1, totalSamples, sampleRate);
                const finalBuffer = finalCtx.createBuffer(1, totalSamples, sampleRate);
                const finalData = finalBuffer.getChannelData(0);
                for (let i = 0; i < totalSamples; i++) finalData[i] = resultData[i];
                
                audioBuffer = finalBuffer;
                
                drawWaveform();
                
                if (isPlaying) {
                    stopAudio();
                }
                
                currentPlaybackTime = startSec;
                updatePlaybackLine();
                
                showStatus('Эффекты применены к фрагменту!', 'success');
            } catch (error) {
                console.error(error);
                showStatus('Ошибка при применении эффектов', 'error');
            }
        }
        
        function resetEffects() {
            if (!originalBuffer) return;
            audioBuffer = originalBuffer;
            drawWaveform();
            showStatus('Эффекты сброшены, оригинальный трек восстановлен', 'success');
            if (isPlaying) {
                stopAudio();
            }
            currentPlaybackTime = (startPercent / 100) * duration;
            updatePlaybackLine();
        }
        
        function bufferToWav(buffer) {
            const numChannels = buffer.numberOfChannels;
            const sampleRate = buffer.sampleRate;
            const bitDepth = 16;
            let samples = buffer.getChannelData(0);
            let dataLength = samples.length * (bitDepth / 8);
            let bufferLength = 44 + dataLength;
            const arrayBuffer = new ArrayBuffer(bufferLength);
            const view = new DataView(arrayBuffer);
            
            const writeString = (view, offset, str) => {
                for (let i = 0; i < str.length; i++) view.setUint8(offset + i, str.charCodeAt(i));
            };
            
            writeString(view, 0, 'RIFF');
            view.setUint32(4, bufferLength - 8, true);
            writeString(view, 8, 'WAVE');
            writeString(view, 12, 'fmt ');
            view.setUint32(16, 16, true);
            view.setUint16(20, 1, true);
            view.setUint16(22, numChannels, true);
            view.setUint32(24, sampleRate, true);
            view.setUint32(28, sampleRate * numChannels * (bitDepth / 8), true);
            view.setUint16(32, numChannels * (bitDepth / 8), true);
            view.setUint16(34, bitDepth, true);
            writeString(view, 36, 'data');
            view.setUint32(40, dataLength, true);
            
            let offset = 44;
            for (let i = 0; i < samples.length; i++) {
                const sample = Math.max(-1, Math.min(1, samples[i]));
                const value = sample < 0 ? sample * 0x8000 : sample * 0x7FFF;
                view.setInt16(offset, value, true);
                offset += 2;
            }
            return new Blob([view], { type: 'audio/wav' });
        }
        
        async function saveProcessedAudio() {
            if (!audioBuffer) { alert('Аудио не загружено'); return; }
            showStatus('Сохранение аудио...', 'info');
            
            const wavBlob = bufferToWav(audioBuffer);
            const url = URL.createObjectURL(wavBlob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'processed_' + Date.now() + '.wav';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showStatus('Аудио сохранено!', 'success');
        }
        
        async function saveAndExit() {
            if (!audioBuffer) { alert('Аудио не загружено'); return; }
            showStatus('Сохранение...', 'info');
            
            const wavBlob = bufferToWav(audioBuffer);
            const formData = new FormData();
            formData.append('saved_audio', wavBlob);
            formData.append('project_id', <?php echo $project_id; ?>);
            
            await fetch('save_audio.php', { method: 'POST', body: formData });
            window.location.href = 'cabinet.php';
        }
        
        function showStatus(message, type) {
            const el = document.getElementById('statusMessage');
            el.textContent = message;
            el.className = 'status-message status-' + type;
            setTimeout(() => { el.style.display = 'none'; }, 3000);
            el.style.display = 'block';
        }
        
        async function initAudio() {
            showStatus('Загрузка аудио...', 'info');
            const response = await fetch('<?php echo $project['original_file_path']; ?>');
            const arrayBuffer = await response.arrayBuffer();
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            originalBuffer = await audioContext.decodeAudioData(arrayBuffer);
            audioBuffer = originalBuffer;
            duration = audioBuffer.duration;
            totalTimeDisplay.textContent = formatTime(duration);
            drawWaveform();
            updateTrimStart();
            updateTrimEnd();
            showStatus('Аудио загружено. Настрой ползунки и нажми "Применить эффекты к фрагменту"', 'success');
            
            const resizeObserver = new ResizeObserver(() => {
                drawWaveform();
                updatePlaybackLine();
            });
            resizeObserver.observe(canvas);
        }
        
        document.getElementById('volumeGain').addEventListener('input', (e) => {
            const val = parseFloat(e.target.value);
            document.getElementById('volumeValue').textContent = (val > 0 ? '+' : '') + val + ' dB';
        });
        document.getElementById('bassEQ').addEventListener('input', (e) => {
            const val = parseFloat(e.target.value);
            document.getElementById('bassValue').textContent = (val > 0 ? '+' : '') + val + ' dB';
        });
        document.getElementById('midEQ').addEventListener('input', (e) => {
            const val = parseFloat(e.target.value);
            document.getElementById('midValue').textContent = (val > 0 ? '+' : '') + val + ' dB';
        });
        document.getElementById('trebleEQ').addEventListener('input', (e) => {
            const val = parseFloat(e.target.value);
            document.getElementById('trebleValue').textContent = (val > 0 ? '+' : '') + val + ' dB';
        });
        
        document.getElementById('resetEffectsBtn').addEventListener('click', resetEffects);
        document.getElementById('saveBtn').addEventListener('click', saveProcessedAudio);
        document.getElementById('saveExitBtn').addEventListener('click', saveAndExit);
        document.getElementById('resetTrimBtn').addEventListener('click', resetTrim);
        document.getElementById('applyEffectsBtn').addEventListener('click', applyEffectsToFragment);
        playPauseBtn.addEventListener('click', playAudio);
        stopBtn.addEventListener('click', stopAudio);
        startSlider.addEventListener('input', updateTrimStart);
        endSlider.addEventListener('input', updateTrimEnd);
        
        canvas.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const percent = (x / rect.width) * 100;
            const time = (percent / 100) * duration;
            
            if (time < (startPercent / 100) * duration) {
                currentPlaybackTime = (startPercent / 100) * duration;
            } else if (time > (endPercent / 100) * duration) {
                currentPlaybackTime = (endPercent / 100) * duration;
            } else {
                currentPlaybackTime = time;
            }
            
            updatePlaybackLine();
            
            if (isPlaying && currentSourceNode) {
                // Перезапускаем с новой позиции
                playAudio();
            }
        });
        
        initAudio();
    </script>
</body>
</html>