import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DoctorQueueController::index
 * @see app/Http/Controllers/DoctorQueueController.php:19
 * @route '/doctor/queue'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/doctor/queue',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DoctorQueueController::index
 * @see app/Http/Controllers/DoctorQueueController.php:19
 * @route '/doctor/queue'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorQueueController::index
 * @see app/Http/Controllers/DoctorQueueController.php:19
 * @route '/doctor/queue'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DoctorQueueController::index
 * @see app/Http/Controllers/DoctorQueueController.php:19
 * @route '/doctor/queue'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DoctorQueueController::index
 * @see app/Http/Controllers/DoctorQueueController.php:19
 * @route '/doctor/queue'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DoctorQueueController::index
 * @see app/Http/Controllers/DoctorQueueController.php:19
 * @route '/doctor/queue'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DoctorQueueController::index
 * @see app/Http/Controllers/DoctorQueueController.php:19
 * @route '/doctor/queue'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\DoctorQueueController::callPatient
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
export const callPatient = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: callPatient.url(args, options),
    method: 'patch',
})

callPatient.definition = {
    methods: ["patch"],
    url: '/doctor/queue/{appointment}/call',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\DoctorQueueController::callPatient
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
callPatient.url = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { appointment: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'appointment_id' in args) {
            args = { appointment: args.appointment_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    appointment: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        appointment: typeof args.appointment === 'object'
                ? args.appointment.appointment_id
                : args.appointment,
                }

    return callPatient.definition.url
            .replace('{appointment}', parsedArgs.appointment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorQueueController::callPatient
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
callPatient.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: callPatient.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\DoctorQueueController::callPatient
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
    const callPatientForm = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: callPatient.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DoctorQueueController::callPatient
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
        callPatientForm.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: callPatient.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    callPatient.form = callPatientForm
/**
* @see \App\Http\Controllers\DoctorQueueController::completeConsultation
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
export const completeConsultation = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: completeConsultation.url(args, options),
    method: 'patch',
})

completeConsultation.definition = {
    methods: ["patch"],
    url: '/doctor/queue/{appointment}/complete',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\DoctorQueueController::completeConsultation
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
completeConsultation.url = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { appointment: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'appointment_id' in args) {
            args = { appointment: args.appointment_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    appointment: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        appointment: typeof args.appointment === 'object'
                ? args.appointment.appointment_id
                : args.appointment,
                }

    return completeConsultation.definition.url
            .replace('{appointment}', parsedArgs.appointment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorQueueController::completeConsultation
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
completeConsultation.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: completeConsultation.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\DoctorQueueController::completeConsultation
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
    const completeConsultationForm = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: completeConsultation.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DoctorQueueController::completeConsultation
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
        completeConsultationForm.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: completeConsultation.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    completeConsultation.form = completeConsultationForm
/**
* @see \App\Http\Controllers\DoctorQueueController::skipPatient
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
export const skipPatient = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: skipPatient.url(args, options),
    method: 'patch',
})

skipPatient.definition = {
    methods: ["patch"],
    url: '/doctor/queue/{appointment}/skip',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\DoctorQueueController::skipPatient
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
skipPatient.url = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { appointment: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'appointment_id' in args) {
            args = { appointment: args.appointment_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    appointment: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        appointment: typeof args.appointment === 'object'
                ? args.appointment.appointment_id
                : args.appointment,
                }

    return skipPatient.definition.url
            .replace('{appointment}', parsedArgs.appointment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorQueueController::skipPatient
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
skipPatient.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: skipPatient.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\DoctorQueueController::skipPatient
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
    const skipPatientForm = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: skipPatient.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DoctorQueueController::skipPatient
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
        skipPatientForm.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: skipPatient.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    skipPatient.form = skipPatientForm
const DoctorQueueController = { index, callPatient, completeConsultation, skipPatient }

export default DoctorQueueController