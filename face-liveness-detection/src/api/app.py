"""
FastAPI application for Face Recognition + Liveness + Absensi
"""

from fastapi import FastAPI, APIRouter
from fastapi.middleware.cors import CORSMiddleware

# router lama (liveness only)
from src.api.routes import router as detect_router

# router baru
from src.api.register import router as register_router
from src.api.absen import router as absen_router

from utils.logging import setup_logger
import config.settings as settings

logger = setup_logger("liveness_api")

# =========================
# INIT APP
# =========================
app = FastAPI(
    title="Face Absensi API",
    description="Face Recognition + Liveness Detection for Absensi Guru",
    version=settings.__version__ if hasattr(settings, "__version__") else "1.0.0",
)

# =========================
# CORS
# =========================
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# =========================
# ROOT ROUTER
# =========================
root_router = APIRouter()

@root_router.get("/")
async def root():
    return {"message": "Face Absensi API running"}

@root_router.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "version": app.version,
    }

# =========================
# INCLUDE ROUTER
# =========================
app.include_router(root_router)

# liveness lama
app.include_router(detect_router)

# register wajah
app.include_router(register_router)

# absensi
app.include_router(absen_router, prefix="/face")

# =========================
# EVENTS
# =========================
@app.on_event("startup")
async def startup_event():
    logger.info("Starting Face Absensi API")

@app.on_event("shutdown")
async def shutdown_event():
    logger.info("Shutting down Face Absensi API")
