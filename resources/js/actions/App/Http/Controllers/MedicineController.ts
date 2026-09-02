import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MedicineController::index
 * @see app/Http/Controllers/MedicineController.php:27
 * @route '/staff/medicines'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/staff/medicines',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MedicineController::index
 * @see app/Http/Controllers/MedicineController.php:27
 * @route '/staff/medicines'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MedicineController::index
 * @see app/Http/Controllers/MedicineController.php:27
 * @route '/staff/medicines'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MedicineController::index
 * @see app/Http/Controllers/MedicineController.php:27
 * @route '/staff/medicines'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MedicineController::index
 * @see app/Http/Controllers/MedicineController.php:27
 * @route '/staff/medicines'
 */
const indexForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\MedicineController::index
 * @see app/Http/Controllers/MedicineController.php:27
 * @route '/staff/medicines'
 */
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MedicineController::index
 * @see app/Http/Controllers/MedicineController.php:27
 * @route '/staff/medicines'
 */
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

index.form = indexForm;
/**
 * @see \App\Http\Controllers\MedicineController::store
 * @see app/Http/Controllers/MedicineController.php:90
 * @route '/staff/medicines'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/staff/medicines',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MedicineController::store
 * @see app/Http/Controllers/MedicineController.php:90
 * @route '/staff/medicines'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MedicineController::store
 * @see app/Http/Controllers/MedicineController.php:90
 * @route '/staff/medicines'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MedicineController::store
 * @see app/Http/Controllers/MedicineController.php:90
 * @route '/staff/medicines'
 */
const storeForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MedicineController::store
 * @see app/Http/Controllers/MedicineController.php:90
 * @route '/staff/medicines'
 */
storeForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

store.form = storeForm;
/**
 * @see \App\Http\Controllers\MedicineController::update
 * @see app/Http/Controllers/MedicineController.php:117
 * @route '/staff/medicines/{id}'
 */
export const update = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
});

update.definition = {
    methods: ['put'],
    url: '/staff/medicines/{id}',
} satisfies RouteDefinition<['put']>;

/**
 * @see \App\Http\Controllers\MedicineController::update
 * @see app/Http/Controllers/MedicineController.php:117
 * @route '/staff/medicines/{id}'
 */
update.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        update.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MedicineController::update
 * @see app/Http/Controllers/MedicineController.php:117
 * @route '/staff/medicines/{id}'
 */
update.put = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
});

/**
 * @see \App\Http\Controllers\MedicineController::update
 * @see app/Http/Controllers/MedicineController.php:117
 * @route '/staff/medicines/{id}'
 */
const updateForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MedicineController::update
 * @see app/Http/Controllers/MedicineController.php:117
 * @route '/staff/medicines/{id}'
 */
updateForm.put = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

update.form = updateForm;
/**
 * @see \App\Http\Controllers\MedicineController::destroy
 * @see app/Http/Controllers/MedicineController.php:183
 * @route '/staff/medicines/{id}'
 */
export const destroy = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
});

destroy.definition = {
    methods: ['delete'],
    url: '/staff/medicines/{id}',
} satisfies RouteDefinition<['delete']>;

/**
 * @see \App\Http\Controllers\MedicineController::destroy
 * @see app/Http/Controllers/MedicineController.php:183
 * @route '/staff/medicines/{id}'
 */
destroy.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        destroy.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MedicineController::destroy
 * @see app/Http/Controllers/MedicineController.php:183
 * @route '/staff/medicines/{id}'
 */
destroy.delete = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
});

/**
 * @see \App\Http\Controllers\MedicineController::destroy
 * @see app/Http/Controllers/MedicineController.php:183
 * @route '/staff/medicines/{id}'
 */
const destroyForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MedicineController::destroy
 * @see app/Http/Controllers/MedicineController.php:183
 * @route '/staff/medicines/{id}'
 */
destroyForm.delete = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

destroy.form = destroyForm;
/**
 * @see \App\Http\Controllers\MedicineController::adjustStock
 * @see app/Http/Controllers/MedicineController.php:145
 * @route '/staff/medicines/{id}/adjust-stock'
 */
export const adjustStock = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: adjustStock.url(args, options),
    method: 'post',
});

adjustStock.definition = {
    methods: ['post'],
    url: '/staff/medicines/{id}/adjust-stock',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MedicineController::adjustStock
 * @see app/Http/Controllers/MedicineController.php:145
 * @route '/staff/medicines/{id}/adjust-stock'
 */
adjustStock.url = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args };
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        id: args.id,
    };

    return (
        adjustStock.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MedicineController::adjustStock
 * @see app/Http/Controllers/MedicineController.php:145
 * @route '/staff/medicines/{id}/adjust-stock'
 */
adjustStock.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: adjustStock.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MedicineController::adjustStock
 * @see app/Http/Controllers/MedicineController.php:145
 * @route '/staff/medicines/{id}/adjust-stock'
 */
const adjustStockForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: adjustStock.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MedicineController::adjustStock
 * @see app/Http/Controllers/MedicineController.php:145
 * @route '/staff/medicines/{id}/adjust-stock'
 */
adjustStockForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: adjustStock.url(args, options),
    method: 'post',
});

adjustStock.form = adjustStockForm;
const MedicineController = { index, store, update, destroy, adjustStock };

export default MedicineController;
