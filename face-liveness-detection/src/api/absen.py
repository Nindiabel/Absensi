from fastapi import APIRouter, UploadFile, File
# pyrefly: ignore [missing-import]
import face_recognition
import numpy as np
import cv2
import requests
import ast
from utils.db import get_db
from src.core.detector import LivenessDetector
from fastapi import Form

router = APIRouter()

detector = LivenessDetector()

# Menyimpan state session untuk deteksi kedipan (session_id -> dict)
blink_states = {}

@router.post("/absen")
async def absen(file: UploadFile = File(...), session_id: str = Form(None)):
    
    contents = await file.read()
    npimg = np.frombuffer(contents, np.uint8)
    frame = cv2.imdecode(npimg, cv2.IMREAD_COLOR)
    
    # Resize frame to max width 480px to significantly speed up processing
    h, w = frame.shape[:2]
    if w > 480:
        scale = 480 / float(w)
        frame = cv2.resize(frame, (0, 0), fx=scale, fy=scale)
        
    rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)

    # ======================
    # LIVENESS & EAR CHECK
    # ======================
    # Gunakan threshold yang sangat tinggi (0.98) agar foto/hp langsung ditolak (spoof)
    live, score, ear = detector.is_live(rgb, threshold=0.98)

    if not live:
        return {"status": "spoof"}

    # ======================
    # STATEFUL BLINK CHECK
    # ======================
    if session_id:
        if session_id not in blink_states:
            blink_states[session_id] = {"closed_detected": False, "has_blinked": False, "frames": 0}
        
        state = blink_states[session_id]
        state["frames"] += 1
        
        EAR_THRESHOLD = 0.21
        
        if ear < EAR_THRESHOLD:
            # Mata tertutup
            state["closed_detected"] = True
        else:
            # Mata terbuka
            if state["closed_detected"]:
                state["has_blinked"] = True
                
        if not state["has_blinked"]:
            # Jika sudah lebih dari 6 frame (sekitar 3 detik) tidak berkedip, anggap foto/spoof
            if state["frames"] > 6:
                return {"status": "spoof"}
            return {"status": "waiting_blink"}

    # ======================
    # FACE RECOGNITION
    # ======================
    encodings = face_recognition.face_encodings(rgb)

    if len(encodings) == 0:
        return {"status": "no_face"}

    face = encodings[0]

    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT * FROM data_wajah_member WHERE status_aktif=1")
    rows = cursor.fetchall()

    for row in rows:
        db_embedding = np.array(ast.literal_eval(row["data_embedding_wajah"]))
        dist = np.linalg.norm(db_embedding - face)

        if dist < 0.45:
            member_id = row["member_id"]

            # Hapus baris requests.post("http://localhost/absen-api"...) agar tidak error 
            # (Laravel akan yang menghandle absen langsung)
            
            return {
                "status": "success",
                "member_id": member_id,
                "distance": float(dist),
                "ear": float(ear) if ear is not None else 0.0,
                "liveness_score": float(score) if score is not None else 0.0
            }

    return {"status": "unknown"}
