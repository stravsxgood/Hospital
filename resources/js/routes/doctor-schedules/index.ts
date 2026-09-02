import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::index
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:17
 * @route '/api/doctor-schedules'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/doctor-schedules',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::index
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:17
 * @route '/api/doctor-schedules'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::index
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:17
 * @route '/api/doctor-schedules'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::index
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:17
 * @route '/api/doctor-schedules'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::index
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:17
 * @route '/api/doctor-schedules'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::index
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:17
 * @route '/api/doctor-schedules'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::index
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:17
 * @route '/api/doctor-schedules'
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
* @see \App\Http\Controllers\Api\DoctorScheduleController::store
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:51
 * @route '/api/doctor-schedules'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/doctor-schedules',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::store
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:51
 * @route '/api/doctor-schedules'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::store
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:51
 * @route '/api/doctor-schedules'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::store
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:51
 * @route '/api/doctor-schedules'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::store
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:51
 * @route '/api/doctor-schedules'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::show
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:77
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
export const show = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/doctor-schedules/{doctor_schedule}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::show
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:77
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
show.url = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { doctor_schedule: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    doctor_schedule: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        doctor_schedule: args.doctor_schedule,
                }

    return show.definition.url
            .replace('{doctor_schedule}', parsedArgs.doctor_schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::show
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:77
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
show.get = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::show
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:77
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
show.head = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::show
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:77
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
    const showForm = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::show
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:77
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
        showForm.get = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::show
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:77
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
        showForm.head = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::update
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:98
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
export const update = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/api/doctor-schedules/{doctor_schedule}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::update
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:98
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
update.url = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { doctor_schedule: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    doctor_schedule: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        doctor_schedule: args.doctor_schedule,
                }

    return update.definition.url
            .replace('{doctor_schedule}', parsedArgs.doctor_schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::update
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:98
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
update.put = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::update
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:98
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
update.patch = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::update
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:98
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
    const updateForm = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::update
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:98
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
        updateForm.put = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::update
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:98
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
        updateForm.patch = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::destroy
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:133
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
export const destroy = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/doctor-schedules/{doctor_schedule}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::destroy
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:133
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
destroy.url = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { doctor_schedule: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    doctor_schedule: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        doctor_schedule: args.doctor_schedule,
                }

    return destroy.definition.url
            .replace('{doctor_schedule}', parsedArgs.doctor_schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DoctorScheduleController::destroy
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:133
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
destroy.delete = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::destroy
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:133
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
    const destroyForm = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\DoctorScheduleController::destroy
 * @see app/Http/Controllers/Api/DoctorScheduleController.php:133
 * @route '/api/doctor-schedules/{doctor_schedule}'
 */
        destroyForm.delete = (args: { doctor_schedule: string | number } | [doctor_schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const doctorSchedules = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
show: Object.assign(show, show),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default doctorSchedules