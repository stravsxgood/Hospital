import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DoctorConsultationController::history
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
export const history = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/doctor/patients/{patient_id}/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DoctorConsultationController::history
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
history.url = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { patient_id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    patient_id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        patient_id: args.patient_id,
                }

    return history.definition.url
            .replace('{patient_id}', parsedArgs.patient_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorConsultationController::history
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
history.get = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DoctorConsultationController::history
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
history.head = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DoctorConsultationController::history
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
    const historyForm = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: history.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DoctorConsultationController::history
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
        historyForm.get = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DoctorConsultationController::history
 * @see app/Http/Controllers/DoctorConsultationController.php:217
 * @route '/doctor/patients/{patient_id}/history'
 */
        historyForm.head = (args: { patient_id: string | number } | [patient_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    history.form = historyForm
const patients = {
    history: Object.assign(history, history),
}

export default patients