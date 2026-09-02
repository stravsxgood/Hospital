import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\ClinicalAssistantController::index
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/clinical/soap-templates',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClinicalAssistantController::index
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClinicalAssistantController::index
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ClinicalAssistantController::index
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ClinicalAssistantController::index
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ClinicalAssistantController::index
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ClinicalAssistantController::index
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
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
* @see \App\Http\Controllers\ClinicalAssistantController::store
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/clinical/soap-templates',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ClinicalAssistantController::store
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClinicalAssistantController::store
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ClinicalAssistantController::store
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ClinicalAssistantController::store
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const soapTemplates = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
}

export default soapTemplates