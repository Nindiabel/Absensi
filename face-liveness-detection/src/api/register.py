from fastapi import APIRouter, UploadFile, File, Form, HTTPException
# pyrefly: ignore [missing-import]
import face_recognition
import numpy as np
import cv2
from utils.db import get_db

router = APIRouter(prefix="/face", tags=["Face"])

@router.post("/register-face")
async def register_face(member_id: int = Form(...), file: UploadFile = File(...)):
    """
    Endpoint registrasi wajah anggota.
    Membaca file upload dengan OpenCV, memaksa menjadi RGB 8-bit.
    Kompatibel untuk semua foto HP modern.
    """
    try:
        # ===============================
        # 1. Baca file bytes
        # ===============================
        contents = await file.read()
        if not contents:
            raise HTTPException(status_code=400, detail="File kosong")

        # ===============================
        # 2. Decode gambar apa adanya
        # ===============================
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_UNCHANGED)  # baca semua jenis gambar

        if img is None:
            raise HTTPException(status_code=400, detail="Gagal membaca gambar, pastikan file valid")

        # ===============================
        # 3. Force convert ke RGB 8-bit
        # ===============================
        if len(img.shape) == 2:
            # Grayscale → RGB
            img_rgb = cv2.cvtColor(img, cv2.COLOR_GRAY2RGB)
        elif img.shape[2] == 4:
            # RGBA → RGB
            img_rgb = cv2.cvtColor(img, cv2.COLOR_RGBA2RGB)
        elif img.shape[2] == 3:
            # BGR → RGB
            img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        else:
            raise HTTPException(status_code=400, detail="Unsupported image channels")

        # Pastikan 8-bit uint8
        if img_rgb.dtype != np.uint8:
            img_rgb = cv2.convertScaleAbs(img_rgb)

        # Pastikan contiguous memory
        img_rgb = np.ascontiguousarray(img_rgb)

        # ===============================
        # 4. Encode wajah
        # ===============================
        encodings = face_recognition.face_encodings(img_rgb)
        if len(encodings) == 0:
            return {"status": "no_face", "message": "Wajah tidak terdeteksi"}

        embedding = encodings[0].tolist()

        # ===============================
        # 5. Simpan ke database
        # ===============================
        db = get_db()
        cursor = db.cursor()
        cursor.execute("""
            INSERT INTO data_wajah_member (member_id, data_embedding_wajah)
            VALUES (%s, %s)
            ON DUPLICATE KEY UPDATE data_embedding_wajah=%s
        """, (member_id, str(embedding), str(embedding)))
        db.commit()
        cursor.close()
        db.close()

        # ===============================
        # 6. Response sukses
        # ===============================
        return {
            "status": "registered",
            "member_id": member_id,
            "embedding_length": len(embedding)
        }

    except HTTPException as he:
        raise he
    except Exception as e:
        print("ERROR REGISTER FACE:", e)
        raise HTTPException(status_code=500, detail=f"Internal server error: {e}")
