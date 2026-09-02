import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DisplayVideoController::store
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:20
 * @route '/admin/display-videos'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/display-videos',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DisplayVideoController::store
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:20
 * @route '/admin/display-videos'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DisplayVideoController::store
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:20
 * @route '/admin/display-videos'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\DisplayVideoController::store
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:20
 * @route '/admin/display-videos'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DisplayVideoController::store
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:20
 * @route '/admin/display-videos'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\DisplayVideoController::toggle
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:55
 * @route '/admin/display-videos/{displayVideo}/toggle'
 */
export const toggle = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/admin/display-videos/{displayVideo}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\DisplayVideoController::toggle
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:55
 * @route '/admin/display-videos/{displayVideo}/toggle'
 */
toggle.url = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { displayVideo: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { displayVideo: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    displayVideo: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        displayVideo: typeof args.displayVideo === 'object'
                ? args.displayVideo.id
                : args.displayVideo,
                }

    return toggle.definition.url
            .replace('{displayVideo}', parsedArgs.displayVideo.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DisplayVideoController::toggle
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:55
 * @route '/admin/display-videos/{displayVideo}/toggle'
 */
toggle.patch = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\DisplayVideoController::toggle
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:55
 * @route '/admin/display-videos/{displayVideo}/toggle'
 */
    const toggleForm = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DisplayVideoController::toggle
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:55
 * @route '/admin/display-videos/{displayVideo}/toggle'
 */
        toggleForm.patch = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggle.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggle.form = toggleForm
/**
* @see \App\Http\Controllers\Admin\DisplayVideoController::destroy
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:69
 * @route '/admin/display-videos/{displayVideo}'
 */
export const destroy = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/display-videos/{displayVideo}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DisplayVideoController::destroy
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:69
 * @route '/admin/display-videos/{displayVideo}'
 */
destroy.url = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { displayVideo: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { displayVideo: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    displayVideo: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        displayVideo: typeof args.displayVideo === 'object'
                ? args.displayVideo.id
                : args.displayVideo,
                }

    return destroy.definition.url
            .replace('{displayVideo}', parsedArgs.displayVideo.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DisplayVideoController::destroy
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:69
 * @route '/admin/display-videos/{displayVideo}'
 */
destroy.delete = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\DisplayVideoController::destroy
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:69
 * @route '/admin/display-videos/{displayVideo}'
 */
    const destroyForm = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DisplayVideoController::destroy
 * @see app/Http/Controllers/Admin/DisplayVideoController.php:69
 * @route '/admin/display-videos/{displayVideo}'
 */
        destroyForm.delete = (args: { displayVideo: number | { id: number } } | [displayVideo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const DisplayVideoController = { store, toggle, destroy }

export default DisplayVideoController