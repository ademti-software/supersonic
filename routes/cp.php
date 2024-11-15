<?php

use AdemtiApps\Supersonic\Controllers\ActionInfoController;
use AdemtiApps\Supersonic\Controllers\Search\CollectionEntriesController;
use AdemtiApps\Supersonic\Controllers\Search\TermsController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'middleware' => ['statamic.cp.authenticated'],
    ],
    function() {
        Route::get('/!/ademti-apps/supersonic/actions/', ActionInfoController::class)
             ->name('ademti-apps.supersonic.action-info');
        Route::get('/!/ademti-apps/supersonic/search/collection-entries/{collection}', CollectionEntriesController::class)
             ->name('ademti-apps.supersonic.search.collection-entries');
        Route::get('/!/ademti-apps/supersonic/search/terms/{taxonomy}', TermsController::class)
             ->name('ademti-apps.supersonic.search.terms');
    }
);
