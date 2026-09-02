import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
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
* @see \App\Http\Controllers\DoctorQueueController::call
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
export const call = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: call.url(args, options),
    method: 'patch',
})

call.definition = {
    methods: ["patch"],
    url: '/doctor/queue/{appointment}/call',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\DoctorQueueController::call
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
call.url = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions) => {
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

    return call.definition.url
            .replace('{appointment}', parsedArgs.appointment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorQueueController::call
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
call.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: call.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\DoctorQueueController::call
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
    const callForm = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: call.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DoctorQueueController::call
 * @see app/Http/Controllers/DoctorQueueController.php:132
 * @route '/doctor/queue/{appointment}/call'
 */
        callForm.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: call.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    call.form = callForm
/**
* @see \App\Http\Controllers\DoctorQueueController::complete
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
export const complete = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: complete.url(args, options),
    method: 'patch',
})

complete.definition = {
    methods: ["patch"],
    url: '/doctor/queue/{appointment}/complete',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\DoctorQueueController::complete
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
complete.url = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions) => {
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

    return complete.definition.url
            .replace('{appointment}', parsedArgs.appointment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorQueueController::complete
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
complete.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: complete.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\DoctorQueueController::complete
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
    const completeForm = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: complete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DoctorQueueController::complete
 * @see app/Http/Controllers/DoctorQueueController.php:175
 * @route '/doctor/queue/{appointment}/complete'
 */
        completeForm.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: complete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    complete.form = completeForm
/**
* @see \App\Http\Controllers\DoctorQueueController::skip
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
export const skip = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: skip.url(args, options),
    method: 'patch',
})

skip.definition = {
    methods: ["patch"],
    url: '/doctor/queue/{appointment}/skip',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\DoctorQueueController::skip
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
skip.url = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions) => {
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

    return skip.definition.url
            .replace('{appointment}', parsedArgs.appointment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DoctorQueueController::skip
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
skip.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: skip.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\DoctorQueueController::skip
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
    const skipForm = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: skip.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DoctorQueueController::skip
 * @see app/Http/Controllers/DoctorQueueController.php:187
 * @route '/doctor/queue/{appointment}/skip'
 */
        skipForm.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: skip.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    skip.form = skipForm
const queue = {
    index: Object.assign(index, index),
call: Object.assign(call, call),
complete: Object.assign(complete, complete),
skip: Object.assign(skip, skip),
}

export default queue