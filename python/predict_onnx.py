import os

os.environ["ORT_LOG_SEVERITY_LEVEL"] = "4"
import sys
import json
import numpy as np
import onnxruntime as rt



# =====================
# Ambil parameter CMD
# =====================

hari = float(sys.argv[1])
suhu = float(sys.argv[2])
kelembapan = float(sys.argv[3])
ph = float(sys.argv[4])
co2 = float(sys.argv[5])
pengaduk = float(sys.argv[6])
kipas = float(sys.argv[7])

# =====================
# Load ONNX
# =====================

import os

BASE_DIR = os.path.dirname(
    os.path.abspath(__file__)
)

MODEL_PATH = os.path.join(
    BASE_DIR,
    "model_kematangan_rf.onnx"
)

session = rt.InferenceSession(
    MODEL_PATH
)

# =====================
# Bentuk Input
# =====================

data_baru = np.array([
    [
        hari,
        suhu,
        kelembapan,
        ph,
        co2,
        pengaduk,
        kipas
    ]
], dtype=np.float32)

# =====================
# Prediksi
# =====================

input_name = session.get_inputs()[0].name

hasil = session.run(
    None,
    {input_name: data_baru}
)


os.environ["ORT_DISABLE_ALL_WARNINGS"] = "1"

kematangan = float(hasil[0][0][0])
sisa_hari = float(hasil[0][0][1])

# =====================
# Output JSON
# =====================

output = {
    "kematangan_pct": round(kematangan, 2),
    "sisa_hari": round(sisa_hari, 1)
}

print(json.dumps(output))