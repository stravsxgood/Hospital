import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SpecializationController::index
 * @see app/Http/Controllers/SpecializationController.php:21
 * @route '/specializations'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/specializations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SpecializationController::index
 * @see app/Http/Controllers/SpecializationController.php:21
 * @route '/specializations'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SpecializationController::index
 * @see app/Http/Controllers/SpecializationController.php:21
 * @route '/specializations'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SpecializationController::index
 * @see app/Http/Controllers/SpecializationController.php:21
 * @route '/specializations'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SpecializationController::index
 * @see app/Http/Controllers/SpecializationController.php:21
 * @route '/specializations'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SpecializationController::index
 * @see app/Http/Controllers/SpecializationController.php:21
 * @route '/specializations'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SpecializationController::index
 * @see app/Http/Controllers/SpecializationController.php:21
 * @route '/specializations'
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
const SpecializationController = { index }

export default SpecializationController