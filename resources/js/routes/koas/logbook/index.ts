import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\KoasLogbookController::index
 * @see app/Http/Controllers/KoasLogbookController.php:30
 * @route '/koas/logbook'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/koas/logbook',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\KoasLogbookController::index
 * @see app/Http/Controllers/KoasLogbookController.php:30
 * @route '/koas/logbook'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\KoasLogbookController::index
 * @see app/Http/Controllers/KoasLogbookController.php:30
 * @route '/koas/logbook'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\KoasLogbookController::index
 * @see app/Http/Controllers/KoasLogbookController.php:30
 * @route '/koas/logbook'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\KoasLogbookController::index
 * @see app/Http/Controllers/KoasLogbookController.php:30
 * @route '/koas/logbook'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\KoasLogbookController::index
 * @see app/Http/Controllers/KoasLogbookController.php:30
 * @route '/koas/logbook'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\KoasLogbookController::index
 * @see app/Http/Controllers/KoasLogbookController.php:30
 * @route '/koas/logbook'
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
* @see \App\Http\Controllers\KoasLogbookController::store
 * @see app/Http/Controllers/KoasLogbookController.php:103
 * @route '/koas/logbook'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/koas/logbook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\KoasLogbookController::store
 * @see app/Http/Controllers/KoasLogbookController.php:103
 * @route '/koas/logbook'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\KoasLogbookController::store
 * @see app/Http/Controllers/KoasLogbookController.php:103
 * @route '/koas/logbook'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\KoasLogbookController::store
 * @see app/Http/Controllers/KoasLogbookController.php:103
 * @route '/koas/logbook'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\KoasLogbookController::store
 * @see app/Http/Controllers/KoasLogbookController.php:103
 * @route '/koas/logbook'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\KoasLogbookController::update
 * @see app/Http/Controllers/KoasLogbookController.php:141
 * @route '/koas/logbook/{id}'
 */
export const update = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/koas/logbook/{id}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\KoasLogbookController::update
 * @see app/Http/Controllers/KoasLogbookController.php:141
 * @route '/koas/logbook/{id}'
 */
update.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\KoasLogbookController::update
 * @see app/Http/Controllers/KoasLogbookController.php:141
 * @route '/koas/logbook/{id}'
 */
update.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\KoasLogbookController::update
 * @see app/Http/Controllers/KoasLogbookController.php:141
 * @route '/koas/logbook/{id}'
 */
    const updateForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\KoasLogbookController::update
 * @see app/Http/Controllers/KoasLogbookController.php:141
 * @route '/koas/logbook/{id}'
 */
        updateForm.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\KoasLogbookController::submit
 * @see app/Http/Controllers/KoasLogbookController.php:188
 * @route '/koas/logbook/{id}/submit'
 */
export const submit = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(args, options),
    method: 'post',
})

submit.definition = {
    methods: ["post"],
    url: '/koas/logbook/{id}/submit',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\KoasLogbookController::submit
 * @see app/Http/Controllers/KoasLogbookController.php:188
 * @route '/koas/logbook/{id}/submit'
 */
submit.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return submit.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\KoasLogbookController::submit
 * @see app/Http/Controllers/KoasLogbookController.php:188
 * @route '/koas/logbook/{id}/submit'
 */
submit.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\KoasLogbookController::submit
 * @see app/Http/Controllers/KoasLogbookController.php:188
 * @route '/koas/logbook/{id}/submit'
 */
    const submitForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submit.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\KoasLogbookController::submit
 * @see app/Http/Controllers/KoasLogbookController.php:188
 * @route '/koas/logbook/{id}/submit'
 */
        submitForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submit.url(args, options),
            method: 'post',
        })
    
    submit.form = submitForm
/**
* @see \App\Http\Controllers\KoasLogbookController::destroy
 * @see app/Http/Controllers/KoasLogbookController.php:223
 * @route '/koas/logbook/{id}'
 */
export const destroy = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/koas/logbook/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\KoasLogbookController::destroy
 * @see app/Http/Controllers/KoasLogbookController.php:223
 * @route '/koas/logbook/{id}'
 */
destroy.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\KoasLogbookController::destroy
 * @see app/Http/Controllers/KoasLogbookController.php:223
 * @route '/koas/logbook/{id}'
 */
destroy.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\KoasLogbookController::destroy
 * @see app/Http/Controllers/KoasLogbookController.php:223
 * @route '/koas/logbook/{id}'
 */
    const destroyForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\KoasLogbookController::destroy
 * @see app/Http/Controllers/KoasLogbookController.php:223
 * @route '/koas/logbook/{id}'
 */
        destroyForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const logbook = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
submit: Object.assign(submit, submit),
destroy: Object.assign(destroy, destroy),
}

export default logbook