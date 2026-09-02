import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import reservations from './reservations'
import prescriptions from './prescriptions'
import print from './print'
import billing from './billing'
import medicines from './medicines'
import cashierShifts from './cashier-shifts'
import auditLogs from './audit-logs'
/**
* @see \App\Http\Controllers\StaffDashboardController::dashboard
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/staff/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\StaffDashboardController::dashboard
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\StaffDashboardController::dashboard
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\StaffDashboardController::dashboard
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\StaffDashboardController::dashboard
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\StaffDashboardController::dashboard
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\StaffDashboardController::dashboard
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
const staff = {
    dashboard: Object.assign(dashboard, dashboard),
reservations: Object.assign(reservations, reservations),
prescriptions: Object.assign(prescriptions, prescriptions),
print: Object.assign(print, print),
billing: Object.assign(billing, billing),
medicines: Object.assign(medicines, medicines),
cashierShifts: Object.assign(cashierShifts, cashierShifts),
auditLogs: Object.assign(auditLogs, auditLogs),
}

export default staff