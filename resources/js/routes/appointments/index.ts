import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\AppointmentController::store
 * @see app/Http/Controllers/AppointmentController.php:42
 * @route '/appointments'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/appointments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AppointmentController::store
 * @see app/Http/Controllers/AppointmentController.php:42
 * @route '/appointments'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AppointmentController::store
 * @see app/Http/Controllers/AppointmentController.php:42
 * @route '/appointments'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AppointmentController::store
 * @see app/Http/Controllers/AppointmentController.php:42
 * @route '/appointments'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AppointmentController::store
 * @see app/Http/Controllers/AppointmentController.php:42
 * @route '/appointments'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\AppointmentController::cancel
 * @see app/Http/Controllers/AppointmentController.php:128
 * @route '/appointments/{appointment}/cancel'
 */
export const cancel = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
})

cancel.definition = {
    methods: ["patch"],
    url: '/appointments/{appointment}/cancel',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\AppointmentController::cancel
 * @see app/Http/Controllers/AppointmentController.php:128
 * @route '/appointments/{appointment}/cancel'
 */
cancel.url = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions) => {
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

    return cancel.definition.url
            .replace('{appointment}', parsedArgs.appointment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AppointmentController::cancel
 * @see app/Http/Controllers/AppointmentController.php:128
 * @route '/appointments/{appointment}/cancel'
 */
cancel.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\AppointmentController::cancel
 * @see app/Http/Controllers/AppointmentController.php:128
 * @route '/appointments/{appointment}/cancel'
 */
    const cancelForm = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancel.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AppointmentController::cancel
 * @see app/Http/Controllers/AppointmentController.php:128
 * @route '/appointments/{appointment}/cancel'
 */
        cancelForm.patch = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancel.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    cancel.form = cancelForm
/**
* @see \App\Http\Controllers\AppointmentController::destroy
 * @see app/Http/Controllers/AppointmentController.php:154
 * @route '/appointments/{appointment}'
 */
export const destroy = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/appointments/{appointment}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\AppointmentController::destroy
 * @see app/Http/Controllers/AppointmentController.php:154
 * @route '/appointments/{appointment}'
 */
destroy.url = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{appointment}', parsedArgs.appointment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AppointmentController::destroy
 * @see app/Http/Controllers/AppointmentController.php:154
 * @route '/appointments/{appointment}'
 */
destroy.delete = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\AppointmentController::destroy
 * @see app/Http/Controllers/AppointmentController.php:154
 * @route '/appointments/{appointment}'
 */
    const destroyForm = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AppointmentController::destroy
 * @see app/Http/Controllers/AppointmentController.php:154
 * @route '/appointments/{appointment}'
 */
        destroyForm.delete = (args: { appointment: number | { appointment_id: number } } | [appointment: number | { appointment_id: number } ] | number | { appointment_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const appointments = {
    store: Object.assign(store, store),
cancel: Object.assign(cancel, cancel),
destroy: Object.assign(destroy, destroy),
}

export default appointments