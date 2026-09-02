import AdminDashboardController from './AdminDashboardController';
import AdminUserController from './AdminUserController';
import AdminPoliController from './AdminPoliController';
import AdminScheduleController from './AdminScheduleController';
import AdminAuditLogController from './AdminAuditLogController';
import AdminSettingController from './AdminSettingController';
import DisplayVideoController from './DisplayVideoController';
const Admin = {
    AdminDashboardController: Object.assign(
        AdminDashboardController,
        AdminDashboardController,
    ),
    AdminUserController: Object.assign(
        AdminUserController,
        AdminUserController,
    ),
    AdminPoliController: Object.assign(
        AdminPoliController,
        AdminPoliController,
    ),
    AdminScheduleController: Object.assign(
        AdminScheduleController,
        AdminScheduleController,
    ),
    AdminAuditLogController: Object.assign(
        AdminAuditLogController,
        AdminAuditLogController,
    ),
    AdminSettingController: Object.assign(
        AdminSettingController,
        AdminSettingController,
    ),
    DisplayVideoController: Object.assign(
        DisplayVideoController,
        DisplayVideoController,
    ),
};

export default Admin;
