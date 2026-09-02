import AuthController from './AuthController';
import PaymentController from './PaymentController';
import DoctorScheduleController from './DoctorScheduleController';
import PatientRegistrationController from './PatientRegistrationController';
import NurseQueueController from './NurseQueueController';
import DoctorConsultationController from './DoctorConsultationController';
const Api = {
    AuthController: Object.assign(AuthController, AuthController),
    PaymentController: Object.assign(PaymentController, PaymentController),
    DoctorScheduleController: Object.assign(
        DoctorScheduleController,
        DoctorScheduleController,
    ),
    PatientRegistrationController: Object.assign(
        PatientRegistrationController,
        PatientRegistrationController,
    ),
    NurseQueueController: Object.assign(
        NurseQueueController,
        NurseQueueController,
    ),
    DoctorConsultationController: Object.assign(
        DoctorConsultationController,
        DoctorConsultationController,
    ),
};

export default Api;
