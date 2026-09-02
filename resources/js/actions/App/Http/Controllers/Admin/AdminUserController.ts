import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\AdminUserController::index
 * @see app/Http/Controllers/Admin/AdminUserController.php:33
 * @route '/admin/users'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AdminUserController::index
 * @see app/Http/Controllers/Admin/AdminUserController.php:33
 * @route '/admin/users'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminUserController::index
 * @see app/Http/Controllers/Admin/AdminUserController.php:33
 * @route '/admin/users'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AdminUserController::index
 * @see app/Http/Controllers/Admin/AdminUserController.php:33
 * @route '/admin/users'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AdminUserController::index
 * @see app/Http/Controllers/Admin/AdminUserController.php:33
 * @route '/admin/users'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminUserController::index
 * @see app/Http/Controllers/Admin/AdminUserController.php:33
 * @route '/admin/users'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AdminUserController::index
 * @see app/Http/Controllers/Admin/AdminUserController.php:33
 * @route '/admin/users'
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
* @see \App\Http\Controllers\Admin\AdminUserController::storeDoctor
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
export const storeDoctor = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeDoctor.url(options),
    method: 'post',
})

storeDoctor.definition = {
    methods: ["post"],
    url: '/admin/users/doctors',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AdminUserController::storeDoctor
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
storeDoctor.url = (options?: RouteQueryOptions) => {
    return storeDoctor.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminUserController::storeDoctor
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
storeDoctor.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeDoctor.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AdminUserController::storeDoctor
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
    const storeDoctorForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeDoctor.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminUserController::storeDoctor
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
        storeDoctorForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeDoctor.url(options),
            method: 'post',
        })
    
    storeDoctor.form = storeDoctorForm
/**
* @see \App\Http\Controllers\Admin\AdminUserController::storeNurse
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
export const storeNurse = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeNurse.url(options),
    method: 'post',
})

storeNurse.definition = {
    methods: ["post"],
    url: '/admin/users/nurses',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AdminUserController::storeNurse
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
storeNurse.url = (options?: RouteQueryOptions) => {
    return storeNurse.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminUserController::storeNurse
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
storeNurse.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeNurse.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AdminUserController::storeNurse
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
    const storeNurseForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeNurse.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminUserController::storeNurse
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
        storeNurseForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeNurse.url(options),
            method: 'post',
        })
    
    storeNurse.form = storeNurseForm
/**
* @see \App\Http\Controllers\Admin\AdminUserController::toggleStatus
 * @see app/Http/Controllers/Admin/AdminUserController.php:239
 * @route '/admin/users/{user}/toggle-status'
 */
export const toggleStatus = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})

toggleStatus.definition = {
    methods: ["patch"],
    url: '/admin/users/{user}/toggle-status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\AdminUserController::toggleStatus
 * @see app/Http/Controllers/Admin/AdminUserController.php:239
 * @route '/admin/users/{user}/toggle-status'
 */
toggleStatus.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return toggleStatus.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminUserController::toggleStatus
 * @see app/Http/Controllers/Admin/AdminUserController.php:239
 * @route '/admin/users/{user}/toggle-status'
 */
toggleStatus.patch = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\AdminUserController::toggleStatus
 * @see app/Http/Controllers/Admin/AdminUserController.php:239
 * @route '/admin/users/{user}/toggle-status'
 */
    const toggleStatusForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleStatus.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminUserController::toggleStatus
 * @see app/Http/Controllers/Admin/AdminUserController.php:239
 * @route '/admin/users/{user}/toggle-status'
 */
        toggleStatusForm.patch = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleStatus.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggleStatus.form = toggleStatusForm
/**
* @see \App\Http\Controllers\Admin\AdminUserController::resetPassword
 * @see app/Http/Controllers/Admin/AdminUserController.php:283
 * @route '/admin/users/{user}/reset-password'
 */
export const resetPassword = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetPassword.url(args, options),
    method: 'post',
})

resetPassword.definition = {
    methods: ["post"],
    url: '/admin/users/{user}/reset-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AdminUserController::resetPassword
 * @see app/Http/Controllers/Admin/AdminUserController.php:283
 * @route '/admin/users/{user}/reset-password'
 */
resetPassword.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return resetPassword.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminUserController::resetPassword
 * @see app/Http/Controllers/Admin/AdminUserController.php:283
 * @route '/admin/users/{user}/reset-password'
 */
resetPassword.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetPassword.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AdminUserController::resetPassword
 * @see app/Http/Controllers/Admin/AdminUserController.php:283
 * @route '/admin/users/{user}/reset-password'
 */
    const resetPasswordForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resetPassword.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminUserController::resetPassword
 * @see app/Http/Controllers/Admin/AdminUserController.php:283
 * @route '/admin/users/{user}/reset-password'
 */
        resetPasswordForm.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resetPassword.url(args, options),
            method: 'post',
        })
    
    resetPassword.form = resetPasswordForm
const AdminUserController = { index, storeDoctor, storeNurse, toggleStatus, resetPassword }

export default AdminUserController