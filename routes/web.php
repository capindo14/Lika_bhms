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
$router->add('auth/logout', 'LoginController@logout');

// Resident Routes
$router->add('residents', 'ResidentController@index');
$router->add('residents/detail_json', 'ResidentController@detail_json');
$router->add('api/residents/list', 'ResidentController@apiList');
$router->add('api/residents/detail', 'ResidentController@apiDetail');
$router->add('api/residents/store', 'ResidentController@apiStore');
$router->add('api/residents/update', 'ResidentController@apiUpdate');
$router->add('api/residents/delete', 'ResidentController@apiDelete');

// Family Routes
$router->add('family', 'FamilyController@index');
$router->add('api/family/list', 'FamilyController@apiList');
$router->add('api/family/detail', 'FamilyController@apiDetail');
$router->add('api/family/store', 'FamilyController@apiStore');
$router->add('api/family/update', 'FamilyController@apiUpdate');
$router->add('api/family/delete', 'FamilyController@apiDelete');

// Consultation Routes
$router->add('consultation', 'ConsultationController@index');
$router->add('consultation/detail_json', 'ConsultationController@detail_json');
$router->add('api/consultation/list', 'ConsultationController@apiList');
$router->add('api/consultation/detail', 'ConsultationController@apiDetail');
$router->add('api/consultation/store', 'ConsultationController@apiStore');
$router->add('api/consultation/update', 'ConsultationController@apiUpdate');
$router->add('api/consultation/delete', 'ConsultationController@apiDelete');

// Immunization Routes
$router->add('immunization', 'ImmunizationController@index');
$router->add('api/immunization/list', 'ImmunizationController@apiList');
$router->add('api/immunization/detail', 'ImmunizationController@apiDetail');
$router->add('api/immunization/store', 'ImmunizationController@apiStore');
$router->add('api/immunization/update', 'ImmunizationController@apiUpdate');
$router->add('api/immunization/delete', 'ImmunizationController@apiDelete');

// Medicine Routes
$router->add('medicine', 'MedicineController@index');
$router->add('medicine/distribute', 'MedicineController@distribute');
$router->add('medicine/distribution_list', 'MedicineController@distribution_list');
$router->add('medicine/delete_distribution', 'MedicineController@delete_distribution');
$router->add('medicine/distribution_detail', 'MedicineController@distribution_detail');
$router->add('medicine/update_distribution', 'MedicineController@update_distribution');
$router->add('api/medicine/list', 'MedicineController@apiList');
$router->add('api/medicine/detail', 'MedicineController@apiDetail');
$router->add('api/medicine/store', 'MedicineController@apiStore');
$router->add('api/medicine/update', 'MedicineController@apiUpdate');
$router->add('api/medicine/delete', 'MedicineController@apiDelete');
$router->add('api/medicine/restock', 'MedicineController@apiRestock');

// Reports Routes
$router->add('reports', 'ReportController@index');
$router->add('reports/residents', 'ReportController@residents');
$router->add('reports/consultations', 'ReportController@consultations');
$router->add('reports/immunizations', 'ReportController@immunizations');
$router->add('reports/medicine', 'ReportController@medicine');
$router->add('reports/health_summary', 'ReportController@health_summary');

// User Management Routes
$router->add('users', 'UserController@index');
$router->add('users/update_settings', 'UserController@update_settings');
$router->add('users/settings', 'UserController@settings');
$router->add('api/users/list', 'UserController@apiList');
$router->add('api/users/detail', 'UserController@apiDetail');
$router->add('api/users/store', 'UserController@apiStore');
$router->add('api/users/update', 'UserController@apiUpdate');
$router->add('api/users/delete', 'UserController@apiDelete');


// Dispatch Router
$route = $_GET['route'] ?? 'dashboard';
$router->dispatch($route);
