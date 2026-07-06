@extends('layouts.app')


@section('content')

<div class="row g-4">


    {{-- ================= MODE CONTROL ================= --}}

    <div class="col-xl-4 col-md-6">

        <div class="card-modern">


            <h5 class="fw-bold mb-4">
                <i class="bi bi-sliders"></i>
                Mode Operasi
            </h5>



            <div class="mode-toggle-group">


                <div class="form-check">

                    <input
                        class="form-check-input visually-hidden"
                        type="radio"
                        name="mode"
                        id="modeAuto"
                        value="auto"
                        {{ ($control['mode'] ?? 'auto') == 'auto' ? 'checked' : '' }}>


                    <label class="form-check-label" for="modeAuto">
                        AUTO
                    </label>

                </div>



                <div class="form-check">

                    <input
                        class="form-check-input visually-hidden"
                        type="radio"
                        name="mode"
                        id="modeManual"
                        value="manual"
                        {{ ($control['mode'] ?? '') == 'manual' ? 'checked' : '' }}>


                    <label class="form-check-label" for="modeManual">
                        MANUAL
                    </label>

                </div>


            </div>


        </div>

    </div>






    {{-- ================= STATUS DEVICE ================= --}}

    <div class="col-xl-8 col-md-6">

        <div class="card-modern">


            <h5 class="fw-bold mb-4">

                <i class="bi bi-cpu"></i>
                Status Perangkat

            </h5>





            {{-- ================= KIPAS ================= --}}

            <div class="device-box">


                <div class="d-flex justify-content-between">


                    <div>

                        <div class="device-title">
                            🌬 Aerasi (Blower)
                        </div>

                        <div class="device-sub">
                            Status aktual perangkat
                        </div>

                    </div>



                    <span id="kipasValue"
                        class="badge {{ ($currentData['kipas'] ?? 0) == 1 ? 'bg-success':'bg-secondary' }}">

                        {{ ($currentData['kipas'] ?? 0) == 1 ? 'ON':'OFF' }}

                    </span>


                </div>




                <div class="manual-area mt-3">


                    <hr>


                    <div class="d-flex justify-content-between">


                        <b>Manual Command</b>


                        <div class="form-check form-switch">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="blowerToggle"
                                {{ ($control['kipas_manual'] ?? 0)==1 ? 'checked':'' }}>

                        </div>


                    </div>


                </div>


            </div>







            {{-- ================= PENGADUK ================= --}}

            <div class="device-box mt-3">


                <div class="d-flex justify-content-between">


                    <div>

                        <div class="device-title">
                            🔄 Pengaduk
                        </div>


                        <div class="device-sub">
                            Status aktual perangkat
                        </div>


                    </div>




                    <span id="pengadukValue"
                        class="badge {{ ($currentData['pengaduk'] ?? 0) == 1 ? 'bg-success':'bg-secondary' }}">

                        {{ ($currentData['pengaduk'] ?? 0) == 1 ? 'ON':'OFF' }}

                    </span>



                </div>





                <div class="manual-area mt-3">


                    <hr>


                    <div class="d-flex justify-content-between">


                        <b>Manual Command</b>



                        <div class="form-check form-switch">


                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="pengadukToggle"
                                {{ ($control['pengaduk_manual'] ?? 0)==1 ? 'checked':'' }}>


                        </div>


                    </div>


                </div>


            </div>


        </div>


    </div>


</div>



@endsection






@push('scripts')


<script>


// ================================
// CSRF
// ================================

const token =
document
.querySelector('meta[name="csrf-token"]')
.content;





// ================================
// KIRIM COMMAND KE FIREBASE
// ================================

function sendControl(type,value)
{


    fetch('/device-control',
    {

        method:'POST',

        headers:
        {

            'Content-Type':'application/json',

            'X-CSRF-TOKEN':token

        },


        body:JSON.stringify(
        {

            type:type,

            value:value

        })


    });


}






// ================================
// MODE AUTO / MANUAL
// ================================


modeAuto.onchange = function(){


    if(this.checked)
    {

        sendControl(
            'mode',
            'auto'
        );


        updateModeView('auto');

    }

}





modeManual.onchange = function(){


    if(this.checked)
    {

        sendControl(
            'mode',
            'manual'
        );


        updateModeView('manual');

    }

}







// ================================
// SWITCH MANUAL DEVICE
// ================================


blowerToggle.onchange=function()
{


    sendControl(
        'kipas',
        this.checked ? 1 : 0
    );

}



pengadukToggle.onchange=function()
{


    sendControl(
        'pengaduk',
        this.checked ? 1 : 0
    );


}








// ================================
// HIDE SHOW MANUAL COMMAND
// ================================


function updateModeView(mode)
{


    document
    .querySelectorAll('.manual-area')
    .forEach(function(el){


        if(mode=='auto')
        {

            el.style.display='none';

        }
        else
        {

            el.style.display='block';

        }


    });


}








// ================================
// REALTIME REFRESH
// ================================


function refreshControl()
{


fetch('/dashboard-data')


.then(res=>res.json())


.then(data=>{


    let control =
    data.control ?? {};


    let sensor =
    data.currentData ?? {};





    // MODE


    if(control.mode=='auto')
    {

        modeAuto.checked=true;

    }
    else
    {

        modeManual.checked=true;

    }



    updateModeView(
        control.mode
    );



// =======================
// STATUS DEVICE
// =======================


let kipasStatus;
let pengadukStatus;



if(control.mode == 'manual')
{

    // MANUAL ikut command user

    kipasStatus =
        control.kipas_manual;


    pengadukStatus =
        control.pengaduk_manual;

}
else
{

    // AUTO ikut AI / sensor

    kipasStatus =
        sensor.kipas;


    pengadukStatus =
        sensor.pengaduk;

}







// KIPAS


kipasValue.innerHTML =
    kipasStatus==1
    ? 'ON'
    :'OFF';



kipasValue.className =
    kipasStatus==1
    ? 'badge bg-success'
    :'badge bg-secondary';








// PENGADUK


pengadukValue.innerHTML =
    pengadukStatus==1
    ? 'ON'
    :'OFF';



pengadukValue.className =
    pengadukStatus==1
    ? 'badge bg-success'
    :'badge bg-secondary';



    // SWITCH MANUAL


    blowerToggle.checked =
        control.kipas_manual==1;



    pengadukToggle.checked =
        control.pengaduk_manual==1;



});


}




// pertama load

refreshControl();


// realtime

setInterval(

    refreshControl,

    5000

);


</script>


@endpush