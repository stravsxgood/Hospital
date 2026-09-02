import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::index
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:27
 * @route '/admin/schedules'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/schedules',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::index
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:27
 * @route '/admin/schedules'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::index
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:27
 * @route '/admin/schedules'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::index
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:27
 * @route '/admin/schedules'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::index
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:27
 * @route '/admin/schedules'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::index
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:27
 * @route '/admin/schedules'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::index
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:27
 * @route '/admin/schedules'
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
* @see \App\Http\Controllers\Admin\AdminScheduleController::store
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:84
 * @route '/admin/schedules'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/schedules',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::store
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:84
 * @route '/admin/schedules'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::store
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:84
 * @route '/admin/schedules'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::store
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:84
 * @route '/admin/schedules'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::store
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:84
 * @route '/admin/schedules'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::show
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:0
 * @route '/admin/schedules/{schedule}'
 */
export const show = (args: { schedule: string | number } | [schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/schedules/{schedule}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::show
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:0
 * @route '/admin/schedules/{schedule}'
 */
show.url = (args: { schedule: string | number } | [schedule: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { schedule: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    schedule: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        schedule: args.schedule,
                }

    return show.definition.url
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::show
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:0
 * @route '/admin/schedules/{schedule}'
 */
show.get = (args: { schedule: string | number } | [schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::show
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:0
 * @route '/admin/schedules/{schedule}'
 */
show.head = (args: { schedule: string | number } | [schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::show
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:0
 * @route '/admin/schedules/{schedule}'
 */
    const showForm = (args: { schedule: string | number } | [schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::show
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:0
 * @route '/admin/schedules/{schedule}'
 */
        showForm.get = (args: { schedule: string | number } | [schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::show
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:0
 * @route '/admin/schedules/{schedule}'
 */
        showForm.head = (args: { schedule: string | number } | [schedule: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\AdminScheduleController::update
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:102
 * @route '/admin/schedules/{schedule}'
 */
export const update = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/schedules/{schedule}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::update
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:102
 * @route '/admin/schedules/{schedule}'
 */
update.url = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { schedule: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'doctor_schedule_id' in args) {
            args = { schedule: args.doctor_schedule_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    schedule: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        schedule: typeof args.schedule === 'object'
                ? args.schedule.doctor_schedule_id
                : args.schedule,
                }

    return update.definition.url
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::update
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:102
 * @route '/admin/schedules/{schedule}'
 */
update.put = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::update
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:102
 * @route '/admin/schedules/{schedule}'
 */
update.patch = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::update
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:102
 * @route '/admin/schedules/{schedule}'
 */
    const updateForm = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::update
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:102
 * @route '/admin/schedules/{schedule}'
 */
        updateForm.put = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::update
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:102
 * @route '/admin/schedules/{schedule}'
 */
        updateForm.patch = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\AdminScheduleController::destroy
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:120
 * @route '/admin/schedules/{schedule}'
 */
export const destroy = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/schedules/{schedule}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::destroy
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:120
 * @route '/admin/schedules/{schedule}'
 */
destroy.url = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { schedule: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'doctor_schedule_id' in args) {
            args = { schedule: args.doctor_schedule_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    schedule: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        schedule: typeof args.schedule === 'object'
                ? args.schedule.doctor_schedule_id
                : args.schedule,
                }

    return destroy.definition.url
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminScheduleController::destroy
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:120
 * @route '/admin/schedules/{schedule}'
 */
destroy.delete = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::destroy
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:120
 * @route '/admin/schedules/{schedule}'
 */
    const destroyForm = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminScheduleController::destroy
 * @see app/Http/Controllers/Admin/AdminScheduleController.php:120
 * @route '/admin/schedules/{schedule}'
 */
        destroyForm.delete = (args: { schedule: number | { doctor_schedule_id: number } } | [schedule: number | { doctor_schedule_id: number } ] | number | { doctor_schedule_id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const AdminScheduleController = { index, store, show, update, destroy }

export default AdminScheduleController