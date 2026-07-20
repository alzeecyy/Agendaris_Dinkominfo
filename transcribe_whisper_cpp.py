import os
import sys
import json
import wave
import av
import subprocess

# Ensure UTF-8 output
sys.stdout.reconfigure(encoding='utf-8')

if len(sys.argv) < 2:
    print(json.dumps({"error": "Missing audio path"}))
    sys.exit(1)

audio_path = sys.argv[1]
if not os.path.exists(audio_path):
    print(json.dumps({"error": f"Audio file not found: {audio_path}"}))
    sys.exit(1)

# Temp WAV file path
temp_wav = audio_path + ".temp_16k.wav"

# Select best available model: small > base > tiny
whisper_dir = os.path.join(os.path.dirname(__file__), "whisper-bin")
model_candidates = ["ggml-small.bin", "ggml-base.bin", "ggml-tiny.bin"]
model_bin = None
for candidate in model_candidates:
    candidate_path = os.path.join(whisper_dir, candidate)
    if os.path.exists(candidate_path):
        model_bin = candidate_path
        model_name = candidate
        break

if model_bin is None:
    print(json.dumps({"error": "No whisper model found in whisper-bin directory"}))
    sys.exit(1)

try:
    # Step 1: Resample audio to 16kHz mono WAV using PyAV
    container = av.open(audio_path)
    stream = container.streams.audio[0]
    resampler = av.AudioResampler(
        format='s16',
        layout='mono',
        rate=16000,
    )
    
    with wave.open(temp_wav, 'wb') as wav_file:
        wav_file.setnchannels(1)
        wav_file.setsampwidth(2)  # 16-bit = s16 (2 bytes)
        wav_file.setframerate(16000)
        
        for packet in container.demux(stream):
            for frame in packet.decode():
                resampled_frames = resampler.resample(frame)
                if resampled_frames:
                    for rf in resampled_frames:
                        data = rf.to_ndarray().tobytes()
                        wav_file.writeframes(data)

    # Step 2: Run whisper.cpp on the temp WAV
    possible_exes = [
        os.path.join(whisper_dir, "Release", "whisper-cli.exe"),
        os.path.join(whisper_dir, "whisper-cli.exe"),
        os.path.join(whisper_dir, "main.exe"),
        os.path.join(whisper_dir, "Release", "main.exe"),
    ]
    main_exe = None
    for exe in possible_exes:
        if os.path.exists(exe):
            main_exe = exe
            break
            
    if main_exe is None:
        raise FileNotFoundError(f"whisper.cpp executable not found in any of the expected locations: {possible_exes}")

    # Use fewer threads to reduce memory pressure
    # Use beam search (default bs=5) for better accuracy - do NOT use bs=1 (greedy, very inaccurate)
    # -ng: no GPU, -nfa: no flash attention, -l id: Indonesian language
    cpu_threads = str(min(4, os.cpu_count() or 2))
    cmd = [
        main_exe,
        "-m", model_bin,
        "-f", temp_wav,
        "-nt",          # no timestamps in output
        "-l", "id",     # Indonesian language
        "-ng",          # no GPU
        "-t", cpu_threads,
        "--word-thold", "0.01",  # filter very low-confidence words
    ]
    
    result = subprocess.run(
        cmd,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        text=True,
        encoding="utf-8",
        errors="replace"
    )
    
    transcript = result.stdout.strip()
    
    # Clean up temp WAV file
    if os.path.exists(temp_wav):
        os.remove(temp_wav)
        
    print(json.dumps({
        "status": "success",
        "text": transcript,
        "model": model_name,
        "stderr": result.stderr.strip()
    }))
    
except Exception as e:
    # Ensure temp WAV is deleted on error
    if os.path.exists(temp_wav):
        try:
            os.remove(temp_wav)
        except:
            pass
    print(json.dumps({"error": str(e)}))
    sys.exit(1)
