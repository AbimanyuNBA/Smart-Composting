import firebase_admin
from firebase_admin import credentials
from firebase_admin import db

import onnxruntime as rt
import numpy as np
from datetime import datetime


# ==========================
# FIREBASE
# ==========================

cred = credentials.Certificate(
    "firebase-admin.json"
)

firebase_admin.initialize_app(
    cred,
    {
        "databaseURL":
        "https://smart-composting-default-rtdb.asia-southeast1.firebasedatabase.app"
    }
)


# ==========================
# AMBIL ACTIVE BATCH
# ==========================

system = db.reference(
    "system"
).get()


active_batch = system.get(
    "active_batch"
)


if not active_batch:
    print("Tidak ada batch aktif")
    exit()


print(
    "Batch aktif:",
    active_batch
)


# ==========================
# CEK STATUS
# ==========================

status = db.reference(
    f"batches/{active_batch}/status"
).get()


if status != "active":

    print(
        "Batch tidak aktif:",
        status
    )

    exit()



# ==========================
# CURRENT DATA
# ==========================


ref_current = db.reference(
    f"batches/{active_batch}/current_data"
)


data = ref_current.get()


if not data:

    print(
        "Data kosong"
    )

    exit()



if data.get(
    "prediction_status"
) != "waiting":


    print(
        "Belum ada data baru"
    )

    exit()



print(
    "Proses AI..."
)



# ==========================
# RANDOM FOREST ONNX
# ==========================


session = rt.InferenceSession(
    "model_kematangan_rf.onnx"
)


input_name = (
    session
    .get_inputs()[0]
    .name
)


input_data = np.array(
    [[

        float(data["hari"]),

        float(data["suhu"]),

        float(data["kelembapan"]),

        float(data["ph"]),

        float(data["co2"]),

        float(data["pengaduk"]),

        float(data["kipas"])

    ]],
    dtype=np.float32
)


hasil = session.run(
    None,
    {
        input_name:
        input_data
    }
)



# sesuaikan output RF

kematangan = float(
    hasil[0][0][0]
)


sisa = float(
    hasil[1][0][0]
)



# ==========================
# UPDATE DATA
# ==========================


data[
    "kematangan_pct"
] = round(
    kematangan,
    2
)


data[
    "sisa_hari"
] = round(
    sisa
)


data[
    "prediction_status"
] = "completed"


data[
    "processed_at"
] = datetime.now().strftime(
    "%Y-%m-%d %H:%M:%S"
)



# update current

ref_current.set(
    data
)



# simpan history


db.reference(
    f"batches/{active_batch}/history"
).push(
    data
)



print(
    "Berhasil proses dan simpan history"
)