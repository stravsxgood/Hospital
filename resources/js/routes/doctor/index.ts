import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import queue from './queue'
import supervision from './supervision'
import consultations from './consultations'
import patients from './patients'
import medicines from './medicines'
/**
* @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
export const schedules = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedules.url(options),
    method: 'get',
})

schedules.definition = {
    methods: ["get","head"],
    url: '/doctor-schedules',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
schedules.url = (options?: RouteQueryOptions) => {
    return schedules.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
schedules.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: schedules.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
schedules.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: schedules.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
    const schedulesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: schedules.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
        schedulesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: schedules.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DoctorSchedulePageController::__invoke
 * @see app/Http/Controllers/DoctorSchedulePageController.php:11
 * @route '/doctor-schedules'
 */
        schedulesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: schedules.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    schedules.form = schedulesForm
const doctor = {
    schedules: Object.assign(schedules, schedules),
queue: Object.assign(queue, queue),
supervision: Object.assign(supervision, supervision),
consultations: Object.assign(consultations, consultations),
patients: Object.assign(patients, patients),
medicines: Object.assign(medicines, medicines),
}

export default doctor