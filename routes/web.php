<?php

declare(strict_types=1);

/** @var \App\Core\Router $router */

$router->get('/', 'HomeController@index');
$router->get('/lang/{locale}', 'LocaleController@switch');
$router->get('/health', 'HealthController@index');
$router->get('/cron/run', 'CronController@run');
$router->get('/help', 'HelpController@index');
$router->get('/terms', 'LegalController@terms');
$router->get('/privacy', 'LegalController@privacy');

$router->get('/properties', 'PropertyController@index');
$router->get('/properties/{id}', 'PropertyController@show');
$router->post('/properties/{id}/book', 'BookingController@store', ['csrf']);

$router->get('/lands', 'LandController@index');
$router->get('/lands/{id}', 'LandController@show');

$router->get('/favorites', 'FavoritesController@index');
$router->get('/messages', 'MessagesController@index');
$router->get('/messages/start', 'MessagesController@start');
$router->post('/messages/start', 'MessagesController@start', ['csrf']);
$router->get('/messages/{id}/poll', 'MessagesController@poll');
$router->get('/api/messages/unread', 'MessagesController@unreadStatus');
$router->get('/messages/{id}', 'MessagesController@show');
$router->post('/messages/{id}', 'MessagesController@send', ['csrf']);
$router->get('/reservations', 'ReservationsController@index');
$router->get('/api/reservations/unread', 'ReservationsController@unreadStatus');
$router->get('/reservations/{id}', 'ReservationsController@show');
$router->get('/reservations/{id}/invoice', 'ReservationsController@invoice');
$router->post('/reservations/{id}/cancel', 'ReservationsController@cancel', ['csrf']);
$router->get('/reservations/{id}/review', 'ReservationsController@review');
$router->post('/reservations/{id}/review', 'ReservationsController@storeReview', ['csrf']);
$router->get('/profile', 'ProfileController@index');
$router->get('/profile/personal', 'ProfileController@personal');
$router->post('/profile/personal', 'ProfileController@updatePersonal', ['csrf']);
$router->get('/profile/security', 'ProfileController@security');
$router->post('/profile/security', 'ProfileController@updateSecurity', ['csrf']);
$router->get('/profile/travelers', 'ProfileController@travelers');
$router->get('/profile/reviews', 'ProfileController@reviews');
$router->get('/profile/preferences', 'ProfileController@preferences');
$router->post('/profile/preferences', 'ProfileController@updatePreferences', ['csrf']);
$router->get('/profile/notifications', 'ProfileController@notifications');
$router->get('/profile/help', 'ProfileController@help');
$router->get('/profile/safety', 'ProfileController@safety');
$router->get('/profile/privacy', 'ProfileController@privacy');

$router->get('/host', 'HostController@index');
$router->get('/agency', 'HostController@index');
$router->get('/host/setup', 'HostController@setup');
$router->post('/host/setup', 'HostController@storeSetup', ['csrf']);
$router->get('/host/properties', 'HostController@properties');
$router->get('/host/properties/new', 'HostController@createProperty');
$router->post('/host/properties', 'HostController@storeProperty', ['csrf']);
$router->get('/host/properties/{id}/edit', 'HostController@editProperty');
$router->post('/host/properties/{id}', 'HostController@updateProperty', ['csrf']);
$router->post('/host/properties/{id}/images', 'HostController@uploadPropertyImages', ['csrf']);
$router->post('/host/properties/{id}/video', 'HostController@uploadPropertyVideo', ['csrf']);
$router->post('/host/properties/{id}/publish', 'HostController@publishProperty', ['csrf']);
$router->post('/host/properties/{id}/delete', 'HostController@deleteProperty', ['csrf']);
$router->get('/host/lands/{id}/edit', 'HostController@editLand');
$router->post('/host/lands/{id}', 'HostController@updateLand', ['csrf']);
$router->post('/host/lands/{id}/images', 'HostController@uploadLandImages', ['csrf']);
$router->post('/host/lands/{id}/publish', 'HostController@publishLand', ['csrf']);
$router->post('/host/lands/{id}/delete', 'HostController@deleteLand', ['csrf']);
$router->get('/host/properties/{id}/availability', 'HostController@availability');
$router->post('/host/properties/{id}/availability', 'HostController@updateAvailability', ['csrf']);
$router->post('/host/bookings/{id}/reject', 'HostController@rejectBooking', ['csrf']);

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login', ['csrf']);
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@sendForgotPassword', ['csrf']);
$router->post('/forgot-password/verify', 'AuthController@verifyForgotPassword', ['csrf']);
$router->post('/forgot-password/reset', 'AuthController@resetForgotPassword', ['csrf']);
$router->get('/reset-password', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword', ['csrf']);
$router->get('/register', 'AuthController@showRegister');
$router->get('/register/form', 'AuthController@registerForm');
$router->get('/register/success', 'AuthController@registerSuccess');
$router->post('/register', 'AuthController@register', ['csrf']);
$router->post('/logout', 'AuthController@logout', ['csrf']);

$router->get('/admin', 'AdminController@index', ['admin']);
$router->get('/admin/agencies', 'AdminController@agencies', ['admin']);
$router->get('/admin/agencies/{id}', 'AdminController@showAgency', ['admin']);
$router->post('/admin/agencies/{id}/activate', 'AdminController@activateAgency', ['admin', 'csrf']);
$router->post('/admin/agencies/{id}/deactivate', 'AdminController@deactivateAgency', ['admin', 'csrf']);
$router->post('/admin/agencies/{id}/delete', 'AdminController@deleteAgency', ['admin', 'csrf']);
$router->get('/admin/users', 'AdminController@users', ['admin']);
$router->get('/admin/users/{id}', 'AdminController@showUser', ['admin']);
$router->post('/admin/users/{id}/activate', 'AdminController@activateUser', ['admin', 'csrf']);
$router->post('/admin/users/{id}/deactivate', 'AdminController@deactivateUser', ['admin', 'csrf']);
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser', ['admin', 'csrf']);
$router->get('/admin/properties', 'AdminController@properties', ['admin']);
$router->get('/admin/properties/{id}', 'AdminController@showProperty', ['admin']);
$router->post('/admin/moderation', 'AdminController@moderateListing', ['admin', 'csrf']);
$router->get('/admin/lands', 'AdminController@lands', ['admin']);
$router->get('/admin/lands/{id}', 'AdminController@showLand', ['admin']);
$router->get('/admin/rentals', 'AdminController@rentals', ['admin']);
$router->get('/admin/rentals/new', 'AdminController@createRental', ['admin']);
$router->post('/admin/rentals', 'AdminController@storeRental', ['admin', 'csrf']);
$router->get('/admin/rentals/{id}', 'AdminController@showRental', ['admin']);
$router->post('/admin/rentals/{id}/activate', 'AdminController@activateRental', ['admin', 'csrf']);
$router->post('/admin/rentals/{id}/deactivate', 'AdminController@deactivateRental', ['admin', 'csrf']);
$router->post('/admin/rentals/{id}/delete', 'AdminController@deleteRental', ['admin', 'csrf']);
$router->get('/admin/bookings', 'AdminController@bookings', ['admin']);
$router->get('/admin/construction', 'AdminController@construction', ['admin']);
$router->get('/admin/construction/new', 'AdminController@createConstruction', ['admin']);
$router->post('/admin/construction', 'AdminController@storeConstruction', ['admin', 'csrf']);
$router->get('/admin/construction/{id}/edit', 'AdminController@editConstruction', ['admin']);
$router->post('/admin/construction/{id}', 'AdminController@updateConstruction', ['admin', 'csrf']);
$router->post('/admin/construction/{id}/delete', 'AdminController@deleteConstruction', ['admin', 'csrf']);
$router->get('/admin/reports', 'AdminController@reports', ['admin']);
$router->get('/admin/reports/{id}', 'AdminController@showReport', ['admin']);
$router->post('/admin/reports/{id}', 'AdminController@updateReport', ['admin', 'csrf']);
$router->get('/admin/settings', 'AdminController@settings', ['admin']);
$router->get('/admin/settings/new', 'AdminController@createAdmin', ['admin']);
$router->post('/admin/settings', 'AdminController@storeAdmin', ['admin', 'csrf']);
$router->post('/admin/settings/{id}/deactivate', 'AdminController@deactivateAdmin', ['admin', 'csrf']);
$router->post('/admin/settings/{id}/activate', 'AdminController@activateAdmin', ['admin', 'csrf']);
$router->post('/admin/settings/{id}/delete', 'AdminController@deleteAdmin', ['admin', 'csrf']);

$router->get('/api/favorites/ids', 'FavoriteApiController@ids');
$router->post('/api/favorites/toggle', 'FavoriteApiController@toggle', ['csrf']);

$router->get('/api/search/suggest', 'SearchController@suggest');
$router->get('/api/geocode/reverse', 'GeocodeController@reverse');
