import dlib
from scipy.spatial import distance
import os
import cv2

# ===============================
# PATH MODEL LANDMARK
# ===============================
BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "../../"))
model_path = os.path.join(BASE_DIR, "shape_predictor_68_face_landmarks.dat")

if not os.path.exists(model_path):
    raise RuntimeError(
        f"shape_predictor_68_face_landmarks.dat tidak ditemukan di:\n{model_path}"
    )

detector = dlib.get_frontal_face_detector()
predictor = dlib.shape_predictor(model_path)

LEFT_EYE = list(range(42, 48))
RIGHT_EYE = list(range(36, 42))

EAR_THRESHOLD = 0.21
BLINK_FRAMES = 2


# ===============================
# EAR FUNCTION
# ===============================
def eye_aspect_ratio(eye):
    A = distance.euclidean(eye[1], eye[5])
    B = distance.euclidean(eye[2], eye[4])
    C = distance.euclidean(eye[0], eye[3])
    return (A + B) / (2.0 * C)


# ===============================
# BLINK DETECTOR (NO GLOBAL!)
# ===============================
def detect_blink(frame):
    """
    Return jumlah kedipan pada frame.
    Tidak pakai global agar aman untuk API.
    """

    if frame is None:
        return 0

    blink_counter = 0
    total_blinks = 0

    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    faces = detector(gray)

    for face in faces:
        shape = predictor(gray, face)
        coords = [(shape.part(i).x, shape.part(i).y) for i in range(68)]

        leftEye = [coords[i] for i in LEFT_EYE]
        rightEye = [coords[i] for i in RIGHT_EYE]

        leftEAR = eye_aspect_ratio(leftEye)
        rightEAR = eye_aspect_ratio(rightEye)
        ear = (leftEAR + rightEAR) / 2.0

        if ear < EAR_THRESHOLD:
            blink_counter += 1
        else:
            if blink_counter >= BLINK_FRAMES:
                total_blinks += 1
            blink_counter = 0

    return total_blinks

# ===============================
# GET EAR VALUE
# ===============================
def get_ear(frame):
    if frame is None:
        return 1.0 # default open

    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    faces = detector(gray)

    for face in faces:
        shape = predictor(gray, face)
        coords = [(shape.part(i).x, shape.part(i).y) for i in range(68)]

        leftEye = [coords[i] for i in LEFT_EYE]
        rightEye = [coords[i] for i in RIGHT_EYE]

        leftEAR = eye_aspect_ratio(leftEye)
        rightEAR = eye_aspect_ratio(rightEye)
        ear = (leftEAR + rightEAR) / 2.0
        return ear

    return 1.0
