import os

# =====================
# Limit resource hosting
# HARUS sebelum import numpy/onnx
# =====================

os.environ["OMP_NUM_THREADS"] = "1"
os.environ["OPENBLAS_NUM_THREADS"] = "1"
os.environ["ORT_LOG_SEVERITY_LEVEL"] = "4"
os.environ["ORT_DISABLE_ALL_WARNINGS"] = "1"

import sys
import json
import numpy as np
import onnxruntime as rt


# =====================
# Validasi Parameter
# =====================

if len(sys.argv) < 8:
    print(json.dumps({
        "error": "Parameter tidak lengkap"
    }))
    sys.exit()


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
# Lokasi model ONNX
# =====================

BASE_DIR = os.path.dirname(
    os.path.abspath(__file__)
)

MODEL_PATH = os.path.join(
    BASE_DIR,
    "model_kematangan_rf.onnx"
)


# =====================
# Setting ONNX Runtime
# =====================

options = rt.SessionOptions()

options.intra_op_num_threads = 1
options.inter_op_num_threads = 1


session = rt.InferenceSession(
    MODEL_PATH,
    sess_options=options,
    providers=[
        "CPUExecutionProvider"
    ]
)


# =====================
# Bentuk Input AI
# =====================

data_baru = np.array(
    [[
        hari,
        suhu,
        kelembapan,
        ph,
        co2,
        pengaduk,
        kipas
    ]],
    dtype=np.float32
)


# =====================
# Prediksi
# =====================

input_name = session.get_inputs()[0].name

hasil = session.run(
    None,
    {
        input_name: data_baru
    }
)


kematangan = float(hasil[0][0][0])
sisa_hari = float(hasil[0][0][1])


# =====================
# Return JSON Laravel
# =====================

output = {
    "kematangan_pct": round(kematangan, 2),
    "sisa_hari": round(sisa_hari, 1)
}

print(json.dumps(output))