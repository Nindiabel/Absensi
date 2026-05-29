"""
API routes for the face liveness detection system.
FINAL VERSION FOR ATTENDANCE SYSTEM
"""

import base64
from typing import Optional, List
import numpy as np
import cv2

from fastapi import APIRouter, UploadFile, File, HTTPException
from pydantic import BaseModel

from src.core.detector import LivenessDetector
from utils.logging import setup_logger

logger = setup_logger("liveness_api_routes")

# ===============================
# RESPONSE MODEL
# ===============================
class LivenessResponse(BaseModel):
    is_live: bool
    live_probability: float
    spoof_probability: float
    message: str
    status: str

router = APIRouter(prefix="/detect", tags=["detection"])

detector = None

def get_detector():
    global detector
    if detector is None:
        logger.info("Loading liveness model...")
        detector = LivenessDetector()
        logger.info("Model ready")
    return detector

# =====================================================
# 🔴 IMAGE ENDPOINT → SELALU SPOOF (ANTI FOTO)
# =====================================================
@router.post("/image", response_model=LivenessResponse)
async def detect_from_image(file: UploadFile = File(...)):
    """
    Endpoint ini sengaja selalu SPOOF.
    Absensi harus pakai /capture multi-frame.
    """

    liveness_detector = get_detector()

    contents = await file.read()
    nparr = np.frombuffer(contents, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

    if img is None:
        raise HTTPException(400, "Invalid image")

    rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    probs = liveness_detector.detect(rgb)[0]

    return LivenessResponse(
        is_live=False,
        live_probability=float(probs[1]),
        spoof_probability=float(probs[0]),
        message="Use webcam capture endpoint",
        status="SPOOF"
    )

# =====================================================
# 🟢 ENDPOINT UTAMA ABSENSI (MULTI FRAME)
# =====================================================
@router.post("/capture", response_model=LivenessResponse)
async def detect_capture(files: List[UploadFile] = File(...)):
    """
    Kirim 3 frame dari webcam.
    Ini endpoint utama absensi.
    """

    if len(files) < 3:
        raise HTTPException(400, "Kirim minimal 3 frame")

    liveness_detector = get_detector()

    frames_rgb = []

    for f in files[:3]:
        contents = await f.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            raise HTTPException(400, "Invalid image")

        rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        frames_rgb.append(rgb)

    # ===============================
    # 1. MODEL SCORE
    # ===============================
    probs = [liveness_detector.detect(fr)[0] for fr in frames_rgb]
    live_scores = [float(p[1]) for p in probs]

    avg_live = float(np.mean(live_scores))
    avg_spoof = float(1 - avg_live)

    # ===============================
    # 2. MOTION CHECK
    # ===============================
    diffs = []
    for i in range(len(frames_rgb)-1):
        g1 = cv2.cvtColor(frames_rgb[i], cv2.COLOR_RGB2GRAY)
        g2 = cv2.cvtColor(frames_rgb[i+1], cv2.COLOR_RGB2GRAY)

        diff = cv2.absdiff(g1, g2)
        diffs.append(np.mean(diff))

    motion_score = float(np.mean(diffs))

    # ===============================
    # 3. BLINK CHECK
    # ===============================
    blink_total = 0
    try:
        from src.core.blink import detect_blink
        for fr in frames_rgb:
            bgr = cv2.cvtColor(fr, cv2.COLOR_RGB2BGR)
            blink_total += detect_blink(bgr)
    except:
        blink_total = 0

    # ===============================
    # RULE ABSENSI FINAL
    # ===============================
    MODEL_THR = 0.8
    MOTION_THR = 1.2
    BLINK_MIN = 1

    is_live = (
        avg_live > MODEL_THR and
        (motion_score > MOTION_THR or blink_total >= BLINK_MIN)
    )

    status = "LIVE" if is_live else "SPOOF"
    message = "Real_face_detected" if is_live else "Fake_or_photo_detected"

    return LivenessResponse(
        is_live=bool(is_live),
        live_probability=float(avg_live),
        spoof_probability=float(avg_spoof),
        message=message,
        status=status
    )
