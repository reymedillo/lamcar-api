<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
Route::get('/', function () {
    return view('welcome');
});
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['before','api']], function () {
    Route::post('/v1/test-push',                                'AdminController@Testpush');
    Route::get('/v1/fares',                                     'FareController@getShow');
    Route::get('/v1/howtourl',                                  'ContentController@getHowtourl');
    Route::get('/v1/disclaimer',                                'ContentController@getDisclaimer');
    Route::get('/v1/isopen',                                    'BusinessHourController@getIsopen');
    Route::get('/v1/cars/available',                            'CarController@getCarsAvailable');

    Route::get('/v1/payments/{token}',                          'PaymentController@getShow');
    Route::get('/v1/payments/{token}/result',                   'PaymentController@getResult');
    Route::post('/v1/payments/{token}',                         'PaymentController@postCharge');

    Route::post('/v1/users', [
        'as' => 'user.create',
        'uses' => 'UserController@postCreate'
    ]);
    Route::post('/v1/admins/auth/login', [
        'as' => 'admin.login',
        'uses' => 'Auth\AuthController@postLogin'
    ]);
    Route::post('/v1/cars/auth/login', [
        'as' => 'car.login',
        'uses' => 'Auth\AuthController@postLogin'
    ]);
});

Route::group(['middleware' => ['before','api','auth:admin']], function () {
    // users routes
    Route::post('/v1/admins/{id}/refresh', [
        'as' => 'admin.refresh',
        'uses' => 'Auth\AuthController@postRefresh'
    ]);

    Route::get('/v1/orders',                                    'OrderController@getIndex');
    Route::get('/v1/orders/{order_id}',                         'OrderController@getShow');
    Route::get('/v1/orders/{order_id}/sendpush',                'OrderController@getSendpush');

    Route::post('/v1/cars',                                     'CarController@postCreate');
    Route::get('/v1/cars',                                      'CarController@getIndex');
    Route::get('/v1/cars/{id}',                                 'CarController@getShow');
    Route::put('/v1/cars/{id}',                                 'CarController@putUpdate');
    Route::delete('/v1/cars/{order_id}',                        'CarController@deleteDestroy');

    Route::get('/v1/car_types',                                 'CarController@getTypes');
    Route::get('/v1/business_hour',                             'BusinessHourController@getBusinessHr');
    Route::post('/v1/business_hour',                            'BusinessHourController@editBusinessHr');

    Route::get('/v1/drivers',                                   'DriverController@getIndex');
    Route::post('/v1/drivers',                                   'DriverController@postCreate');
    Route::get('/v1/drivers/{id}',                                   'DriverController@getEdit');
    Route::put('/v1/drivers/{id}',                                   'DriverController@putEdit');
    Route::delete('/v1/drivers/{id}',                                   'DriverController@deleteDestroy');

    //Not included
    Route::post('/v1/admins/{id}/logout',                       'Auth\AuthController@postLogout');
    Route::post('/v1/admins',                                   ['as' => 'admin.create', 'uses' => 'AdminController@postCreate']);

});

Route::group(['middleware' => ['before','api','auth:user']], function () {
    // users routes
    Route::post('/v1/users/{id}/refresh',[
        'as' => 'user.refresh',
        'uses' => 'Auth\AuthController@postRefresh'
    ]);
    Route::post('/v1/users/{id}/device',[
        'as' => 'user.setDevice',
        'uses' => 'UserController@postSetDevice'
    ]);
    Route::post('/v1/users/{id}/orders',                        'UserController@postOrder');
    Route::get('/v1/users/{id}/orders',                         'UserController@getUserOrderList');
    Route::get('/v1/users/{id}/orders/{order_id}',              'UserController@getOrder');
    Route::get('/v1/users/{id}/orders/{order_id}/settlement',   'UserController@getOrderSettlement');
    Route::post('/v1/users/{id}/orders/{order_id}/cancel',      'UserController@postOrderCancel');

    Route::get('/v1/users/{id}/orders/{order_id}/canceltoken',  'UserController@getCancelToken');
    Route::get('/v1/users/{id}/orders/{order_id}/paymenttoken', 'UserController@getPaymentToken');
});

Route::group(['middleware' => ['before','api','auth:car']], function () {
    // users routes
    Route::post('/v1/cars/{id}/refresh', [
        'as' => 'car.refresh',
        'uses' => 'Auth\AuthController@postRefresh'
    ]);
    Route::post('/v1/cars/{id}/location',[
        'as' => 'car.setLocation',
        'uses' => 'CarController@postSetLocation'
    ]);
    Route::post('/v1/cars/{id}/device',[
        'as' => 'car.setDevice',
        'uses' => 'CarController@postSetDevice'
    ]);
    Route::get('/v1/cars/{id}/orders/inreceive',                'CarController@getInreceive');
    Route::get('/v1/cars/{id}/orders/{order_id}',               'CarController@getOrder');
    Route::post('/v1/cars/{id}/orders/{order_id}/accept',       'CarController@postOrderAccept');
    Route::post('/v1/cars/{id}/orders/{order_id}/arrive',       'CarController@postOrderArrive');
    Route::post('/v1/cars/{id}/orders/{order_id}/pickup',       'CarController@postOrderPickup');
    Route::post('/v1/cars/{id}/orders/{order_id}/dropoff',      'CarController@postOrderDropoff');
    Route::post('/v1/cars/{id}/logout',                         'Auth\AuthController@postLogout');
});


