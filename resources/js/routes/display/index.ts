import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/display',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
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
* @see \App\Http\Controllers\PublicDisplayController::live
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
export const live = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: live.url(options),
    method: 'get',
})

live.definition = {
    methods: ["get","head"],
    url: '/display/live-data',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicDisplayController::live
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
live.url = (options?: RouteQueryOptions) => {
    return live.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicDisplayController::live
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
live.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: live.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicDisplayController::live
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
live.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: live.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicDisplayController::live
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
    const liveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: live.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicDisplayController::live
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
        liveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: live.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicDisplayController::live
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
        liveForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: live.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    live.form = liveForm
const display = {
    index: Object.assign(index, index),
live: Object.assign(live, live),
}

export default display