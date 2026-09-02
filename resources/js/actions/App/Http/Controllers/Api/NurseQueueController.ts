import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\NurseQueueController::index
 * @see app/Http/Controllers/Api/NurseQueueController.php:17
 * @route '/api/nurse/queues'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/nurse/queues',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\NurseQueueController::index
 * @see app/Http/Controllers/Api/NurseQueueController.php:17
 * @route '/api/nurse/queues'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\NurseQueueController::index
 * @see app/Http/Controllers/Api/NurseQueueController.php:17
 * @route '/api/nurse/queues'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\NurseQueueController::index
 * @see app/Http/Controllers/Api/NurseQueueController.php:17
 * @route '/api/nurse/queues'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\NurseQueueController::index
 * @see app/Http/Controllers/Api/NurseQueueController.php:17
 * @route '/api/nurse/queues'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\NurseQueueController::index
 * @see app/Http/Controllers/Api/NurseQueueController.php:17
 * @route '/api/nurse/queues'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\NurseQueueController::index
 * @see app/Http/Controllers/Api/NurseQueueController.php:17
 * @route '/api/nurse/queues'
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
* @see \App\Http\Controllers\Api\NurseQueueController::verify
 * @see app/Http/Controllers/Api/NurseQueueController.php:43
 * @route '/api/nurse/queues/{id}/verify'
 */
export const verify = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: verify.url(args, options),
    method: 'patch',
})

verify.definition = {
    methods: ["patch"],
    url: '/api/nurse/queues/{id}/verify',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Api\NurseQueueController::verify
 * @see app/Http/Controllers/Api/NurseQueueController.php:43
 * @route '/api/nurse/queues/{id}/verify'
 */
verify.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return verify.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\NurseQueueController::verify
 * @see app/Http/Controllers/Api/NurseQueueController.php:43
 * @route '/api/nurse/queues/{id}/verify'
 */
verify.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: verify.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Api\NurseQueueController::verify
 * @see app/Http/Controllers/Api/NurseQueueController.php:43
 * @route '/api/nurse/queues/{id}/verify'
 */
    const verifyForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verify.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\NurseQueueController::verify
 * @see app/Http/Controllers/Api/NurseQueueController.php:43
 * @route '/api/nurse/queues/{id}/verify'
 */
        verifyForm.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verify.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    verify.form = verifyForm
const NurseQueueController = { index, verify }

export default NurseQueueController