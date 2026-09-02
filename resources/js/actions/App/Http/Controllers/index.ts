import Api from './Api'
import XenditWebhookController from './XenditWebhookController'
import DoctorConsultationController from './DoctorConsultationController'
import PublicDisplayController from './PublicDisplayController'
import DoctorSchedulePageController from './DoctorSchedulePageController'
import PatientStoryController from './PatientStoryController'
import SpecializationController from './SpecializationController'
import PoliTeamController from './PoliTeamController'
import ClinicLocationController from './ClinicLocationController'
import ProfileController from './ProfileController'
import PatientDashboardController from './PatientDashboardController'
import AppointmentController from './AppointmentController'
import Teams from './Teams'
import StaffDashboardController from './StaffDashboardController'
import StaffActionController from './StaffActionController'
import MedicalDocumentController from './MedicalDocumentController'
import BillingController from './BillingController'
import MedicineController from './MedicineController'
import CashierShiftController from './CashierShiftController'
import AuditLogController from './AuditLogController'
import DoctorQueueController from './DoctorQueueController'
import DoctorSupervisionController from './DoctorSupervisionController'
import KoasLogbookController from './KoasLogbookController'
import ClinicalAssistantController from './ClinicalAssistantController'
import SatuSehatController from './SatuSehatController'
import Admin from './Admin'
import DashboardController from './DashboardController'
import Settings from './Settings'
const Controllers = {
    Api: Object.assign(Api, Api),
XenditWebhookController: Object.assign(XenditWebhookController, XenditWebhookController),
DoctorConsultationController: Object.assign(DoctorConsultationController, DoctorConsultationController),
PublicDisplayController: Object.assign(PublicDisplayController, PublicDisplayController),
DoctorSchedulePageController: Object.assign(DoctorSchedulePageController, DoctorSchedulePageController),
PatientStoryController: Object.assign(PatientStoryController, PatientStoryController),
SpecializationController: Object.assign(SpecializationController, SpecializationController),
PoliTeamController: Object.assign(PoliTeamController, PoliTeamController),
ClinicLocationController: Object.assign(ClinicLocationController, ClinicLocationController),
ProfileController: Object.assign(ProfileController, ProfileController),
PatientDashboardController: Object.assign(PatientDashboardController, PatientDashboardController),
AppointmentController: Object.assign(AppointmentController, AppointmentController),
Teams: Object.assign(Teams, Teams),
StaffDashboardController: Object.assign(StaffDashboardController, StaffDashboardController),
StaffActionController: Object.assign(StaffActionController, StaffActionController),
MedicalDocumentController: Object.assign(MedicalDocumentController, MedicalDocumentController),
BillingController: Object.assign(BillingController, BillingController),
MedicineController: Object.assign(MedicineController, MedicineController),
CashierShiftController: Object.assign(CashierShiftController, CashierShiftController),
AuditLogController: Object.assign(AuditLogController, AuditLogController),
DoctorQueueController: Object.assign(DoctorQueueController, DoctorQueueController),
DoctorSupervisionController: Object.assign(DoctorSupervisionController, DoctorSupervisionController),
KoasLogbookController: Object.assign(KoasLogbookController, KoasLogbookController),
ClinicalAssistantController: Object.assign(ClinicalAssistantController, ClinicalAssistantController),
SatuSehatController: Object.assign(SatuSehatController, SatuSehatController),
Admin: Object.assign(Admin, Admin),
DashboardController: Object.assign(DashboardController, DashboardController),
Settings: Object.assign(Settings, Settings),
}

export default Controllers