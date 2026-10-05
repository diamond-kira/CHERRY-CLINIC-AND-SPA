<?php

use App\Http\Controllers\AppointmentBookingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ConsultationWorkflowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientManagementController;
use App\Http\Controllers\PaymentManagementController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PrescriptionWorkflowController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceManagementController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SpaTreatmentWorkflowController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\UserManagementController;
use App\Models\AuditLog;
use App\Models\Staff;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::redirect('/about-us', '/#about')->name('about');
Route::redirect('/our-produce', '/#produce')->name('produce');
Route::redirect('/investors', '/#investors')->name('investors');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route(request()->user()->dashboardRouteName()))->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/notifications', [PortalController::class, 'notifications'])->name('notifications.index');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/appointments', [PortalController::class, 'appointments'])->name('appointments.index');
    Route::get('/appointments/{appointment}/consultation', [ConsultationWorkflowController::class, 'create'])->name('appointments.consultation.create');
    Route::post('/appointments/{appointment}/consultation', [ConsultationWorkflowController::class, 'store'])->name('appointments.consultation.store');
    Route::get('/consultations', [PortalController::class, 'consultations'])->name('consultations.index');
    Route::get('/consultations/{consultation}', [ConsultationWorkflowController::class, 'edit'])->name('consultations.edit');
    Route::patch('/consultations/{consultation}', [ConsultationWorkflowController::class, 'update'])->name('consultations.update');
    Route::post('/consultations/{consultation}/complete', [ConsultationWorkflowController::class, 'complete'])->name('consultations.complete');
    Route::get('/prescriptions', [PortalController::class, 'prescriptions'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [PrescriptionWorkflowController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions', [PrescriptionWorkflowController::class, 'store'])->name('prescriptions.store');
    Route::get('/appointments/{appointment}/treatment', [SpaTreatmentWorkflowController::class, 'create'])->name('appointments.treatment.create');
    Route::post('/appointments/{appointment}/treatment', [SpaTreatmentWorkflowController::class, 'store'])->name('appointments.treatment.store');
    Route::get('/treatments/{record}', [SpaTreatmentWorkflowController::class, 'edit'])->name('treatments.edit');
    Route::patch('/treatments/{record}', [SpaTreatmentWorkflowController::class, 'update'])->name('treatments.update');
    Route::post('/treatments/{record}/complete', [SpaTreatmentWorkflowController::class, 'complete'])->name('treatments.complete');
    Route::get('/patients', [PortalController::class, 'patients'])->name('patients.index');
    Route::get('/patients/create', [PatientManagementController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientManagementController::class, 'store'])->name('patients.store');
    Route::get('/patients/{patient}', [PortalController::class, 'patientDetails'])->middleware('can:view,patient')->name('patients.show');
    Route::get('/services', [PortalController::class, 'services'])->name('services.index');
    Route::get('/services/create', [ServiceManagementController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceManagementController::class, 'store'])->name('services.store');
    Route::get('/staff', [PortalController::class, 'staff'])->middleware('can:viewAny,'.Staff::class)->name('staff.index');
    Route::get('/staff/create', [StaffManagementController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffManagementController::class, 'store'])->name('staff.store');
    Route::patch('/staff/{staff}/role', [StaffManagementController::class, 'updateRole'])->name('staff.role.update');
    Route::get('/payments', [PortalController::class, 'payments'])->name('payments.index');
    Route::get('/payments/create', [PaymentManagementController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentManagementController::class, 'store'])->name('payments.store');
    Route::get('/reports', [PortalController::class, 'reports'])->name('reports.index');
    Route::get('/audit-logs', [PortalController::class, 'auditLogs'])->middleware('can:viewAny,'.AuditLog::class)->name('audit-logs.index');
    Route::get('/notifications', [PortalController::class, 'notifications'])->name('notifications.index');
    Route::get('/appointments/create', [AppointmentBookingController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentBookingController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/check-in', [AppointmentBookingController::class, 'checkIn'])->name('appointments.check-in');
    Route::post('/appointments/{appointment}/confirm', [AppointmentBookingController::class, 'confirm'])->name('appointments.confirm');
    Route::patch('/appointments/{appointment}/reschedule', [AppointmentBookingController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/cancel', [AppointmentBookingController::class, 'cancel'])->name('appointments.cancel');
});

Route::middleware(['auth', 'role:receptionist'])->prefix('receptionist')->name('receptionist.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/appointments', [PortalController::class, 'appointments'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentBookingController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentBookingController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/check-in', [AppointmentBookingController::class, 'checkIn'])->name('appointments.check-in');
    Route::post('/appointments/{appointment}/confirm', [AppointmentBookingController::class, 'confirm'])->name('appointments.confirm');
    Route::patch('/appointments/{appointment}/reschedule', [AppointmentBookingController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/cancel', [AppointmentBookingController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/patients', [PortalController::class, 'patients'])->name('patients.index');
    Route::get('/patients/create', [PatientManagementController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientManagementController::class, 'store'])->name('patients.store');
    Route::get('/services', [PortalController::class, 'services'])->name('services.index');
    Route::get('/payments', [PortalController::class, 'payments'])->name('payments.index');
    Route::get('/payments/create', [PaymentManagementController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentManagementController::class, 'store'])->name('payments.store');
    Route::get('/reports', [PortalController::class, 'reports'])->name('reports.index');
    Route::get('/notifications', [PortalController::class, 'notifications'])->name('notifications.index');
});

Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/appointments', [PortalController::class, 'appointments'])->name('appointments.index');
    Route::get('/appointments/{appointment}/consultation', [ConsultationWorkflowController::class, 'create'])->name('appointments.consultation.create');
    Route::post('/appointments/{appointment}/consultation', [ConsultationWorkflowController::class, 'store'])->name('appointments.consultation.store');
    Route::get('/patients', [PortalController::class, 'patients'])->name('patients.index');
    Route::get('/patients/{patient}', [PortalController::class, 'patientDetails'])->middleware('can:view,patient')->name('patients.show');
    Route::get('/consultations', [PortalController::class, 'consultations'])->name('consultations.index');
    Route::get('/consultations/{consultation}', [ConsultationWorkflowController::class, 'edit'])->name('consultations.edit');
    Route::patch('/consultations/{consultation}', [ConsultationWorkflowController::class, 'update'])->name('consultations.update');
    Route::post('/consultations/{consultation}/complete', [ConsultationWorkflowController::class, 'complete'])->name('consultations.complete');
    Route::get('/prescriptions', [PortalController::class, 'prescriptions'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [PrescriptionWorkflowController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions', [PrescriptionWorkflowController::class, 'store'])->name('prescriptions.store');
    Route::get('/reports', [PortalController::class, 'reports'])->name('reports.index');
    Route::get('/notifications', [PortalController::class, 'notifications'])->name('notifications.index');
});

Route::middleware(['auth', 'role:therapist'])->prefix('therapist')->name('therapist.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/appointments', [PortalController::class, 'appointments'])->name('appointments.index');
    Route::get('/patients', [PortalController::class, 'patients'])->name('patients.index');
    Route::get('/patients/{patient}', [PortalController::class, 'patientDetails'])->middleware('can:view,patient')->name('patients.show');
    Route::get('/services', [PortalController::class, 'services'])->name('services.index');
    Route::get('/appointments/{appointment}/treatment', [SpaTreatmentWorkflowController::class, 'create'])->name('appointments.treatment.create');
    Route::post('/appointments/{appointment}/treatment', [SpaTreatmentWorkflowController::class, 'store'])->name('appointments.treatment.store');
    Route::get('/treatments', [PortalController::class, 'treatments'])->name('treatments.index');
    Route::get('/treatments/{record}', [SpaTreatmentWorkflowController::class, 'edit'])->name('treatments.edit');
    Route::patch('/treatments/{record}', [SpaTreatmentWorkflowController::class, 'update'])->name('treatments.update');
    Route::post('/treatments/{record}/complete', [SpaTreatmentWorkflowController::class, 'complete'])->name('treatments.complete');
    Route::get('/reports', [PortalController::class, 'reports'])->name('reports.index');
    Route::get('/notifications', [PortalController::class, 'notifications'])->name('notifications.index');
});

Route::middleware(['auth', 'role:patient'])->prefix('patient')->name('patient.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/appointments', [PortalController::class, 'appointments'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentBookingController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentBookingController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/cancel', [AppointmentBookingController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/services', [PortalController::class, 'services'])->name('services.index');
    Route::get('/treatments', [PortalController::class, 'treatments'])->name('treatments.index');
    Route::get('/payments', [PortalController::class, 'payments'])->name('payments.index');
    Route::get('/records', fn () => redirect()->route('patient.records.show', request()->user()->patient))->name('records');
    Route::get('/records/{patient}', [PortalController::class, 'patientRecords'])->middleware('can:view,patient')->name('records.show');
    Route::get('/notifications', [PortalController::class, 'notifications'])->name('notifications.index');
});
