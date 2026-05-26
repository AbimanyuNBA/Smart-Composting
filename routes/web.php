<?php

use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FirestoreClient;




Route::get('/', function () {
    return view('welcome');
});



Route::get('/firebase-test', function () {

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/firebase/firebase_credentials.json'));

    $firestore = new FirestoreClient([
        'projectId' => 'smart-composting',
    ]);

    return 'Firebase Connected Successfully';
});
