<?php
/**
 * Application Web Routes
 */

use App\Config\Router;

$router = new Router();

// Dashboard Route
$router->add('dashboard', 'DashboardController@index');

// Authentication Routes
$router->add('auth/login', 'LoginController@index');
$router->add('auth/authenticate', 'LoginController@authenticate');

// Resident Routes
$router->add('residents', 'ResidentController@index');
$router->add('residents/create', 'ResidentController@create');
$router->add('residents/store', 'ResidentController@store');
$router->add('residents/view', 'ResidentController@view');
$router->add('residents/edit', 'ResidentController@edit');
$router->add('residents/update', 'ResidentController@update');
$router->add('residents/archive', 'ResidentController@archive');
$router->add('residents/restore', 'ResidentController@restore');
$router->add('residents/detail_json', 'ResidentController@detail_json');

// Family Routes
$router->add('family', 'FamilyController@index');
$router->add('family/store', 'FamilyController@store');
$router->add('family/update', 'FamilyController@update');
$router->add('family/delete', 'FamilyController@delete');
$router->add('family/detail_json', 'FamilyController@detail_json');

// Consultation Routes
$router->add('consultation', 'ConsultationController@index');
$router->add('consultation/store', 'ConsultationController@store');
$router->add('consultation/update', 'ConsultationController@update');
$router->add('consultation/delete', 'ConsultationController@delete');
$router->add('consultation/detail_json', 'ConsultationController@detail_json');

// Immunization Routes
$router->add('immunization', 'ImmunizationController@index');
$router->add('immunization/store', 'ImmunizationController@store');
$router->add('immunization/update', 'ImmunizationController@update');
$router->add('immunization/delete', 'ImmunizationController@delete');
$router->add('immunization/detail_json', 'ImmunizationController@detail_json');

// Medicine Routes
$router->add('medicine', 'MedicineController@index');
$router->add('medicine/store', 'MedicineController@store');
$router->add('medicine/restock', 'MedicineController@restock');
$router->add('medicine/update', 'MedicineController@update');
$router->add('medicine/delete', 'MedicineController@delete');
$router->add('medicine/detail_json', 'MedicineController@detail_json');
$router->add('medicine/distribute', 'MedicineController@distribute');
$router->add('medicine/distribution_list', 'MedicineController@distribution_list');
$router->add('medicine/delete_distribution', 'MedicineController@delete_distribution');

// Reports Routes
$router->add('reports', 'ReportController@index');
$router->add('reports/residents', 'ReportController@residents');
$router->add('reports/consultations', 'ReportController@consultations');
$router->add('reports/immunizations', 'ReportController@immunizations');
$router->add('reports/medicine', 'ReportController@medicine');
$router->add('reports/health_summary', 'ReportController@health_summary');

// User Management Routes
$router->add('users', 'UserController@index');
$router->add('users/create', 'UserController@create');
$router->add('users/store', 'UserController@store');
$router->add('users/edit', 'UserController@edit');
$router->add('users/update', 'UserController@update');
$router->add('users/toggle_status', 'UserController@toggle_status');
$router->add('users/delete', 'UserController@delete');
$router->add('users/update_settings', 'UserController@update_settings');
$router->add('users/settings', 'UserController@settings');


// Dispatch Router
$route = $_GET['route'] ?? 'dashboard';
$router->dispatch($route);
