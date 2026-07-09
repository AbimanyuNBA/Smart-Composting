import firebase_admin
from firebase_admin import credentials
from firebase_admin import db


# ============================
# FIREBASE INIT
# ============================

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


# ============================
# CEK SYSTEM
# ============================

system = (
    db.reference("system")
    .get()
)


active_batch = (
    system.get("active_batch")
    if system else None
)


if not active_batch:

    print("Tidak ada batch aktif")
    exit()



print(
    "Batch aktif:",
    active_batch
)



# ============================
# CEK CURRENT DATA
# ============================


path = (
    "batches/"
    + active_batch
    + "/current_data"
)


data = (
    db.reference(path)
    .get()
)


if not data:

    print("Data sensor kosong")
    exit()



print(
    "Data ditemukan"
)


print(data)