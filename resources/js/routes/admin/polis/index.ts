import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../wayfinder';
/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::index
 * @see app/Http/Controllers/Admin/AdminPoliController.php:24
 * @route '/admin/polis'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/admin/polis',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::index
 * @see app/Http/Controllers/Admin/AdminPoliController.php:24
 * @route '/admin/polis'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::index
 * @see app/Http/Controllers/Admin/AdminPoliController.php:24
 * @route '/admin/polis'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::index
 * @see app/Http/Controllers/Admin/AdminPoliController.php:24
 * @route '/admin/polis'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::index
 * @see app/Http/Controllers/Admin/AdminPoliController.php:24
 * @route '/admin/polis'
 */
const indexForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::index
 * @see app/Http/Controllers/Admin/AdminPoliController.php:24
 * @route '/admin/polis'
 */
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::index
 * @see app/Http/Controllers/Admin/AdminPoliController.php:24
 * @route '/admin/polis'
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
 * @see \App\Http\Controllers\Admin\AdminPoliController::store
 * @see app/Http/Controllers/Admin/AdminPoliController.php:54
 * @route '/admin/polis'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/admin/polis',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::store
 * @see app/Http/Controllers/Admin/AdminPoliController.php:54
 * @route '/admin/polis'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::store
 * @see app/Http/Controllers/Admin/AdminPoliController.php:54
 * @route '/admin/polis'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::store
 * @see app/Http/Controllers/Admin/AdminPoliController.php:54
 * @route '/admin/polis'
 */
const storeForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::store
 * @see app/Http/Controllers/Admin/AdminPoliController.php:54
 * @route '/admin/polis'
 */
storeForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

store.form = storeForm;
/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::show
 * @see app/Http/Controllers/Admin/AdminPoliController.php:0
 * @route '/admin/polis/{poli}'
 */
export const show = (
    args: { poli: string | number } | [poli: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});

show.definition = {
    methods: ['get', 'head'],
    url: '/admin/polis/{poli}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::show
 * @see app/Http/Controllers/Admin/AdminPoliController.php:0
 * @route '/admin/polis/{poli}'
 */
show.url = (
    args: { poli: string | number } | [poli: string | number] | string | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { poli: args };
    }

    if (Array.isArray(args)) {
        args = {
            poli: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        poli: args.poli,
    };

    return (
        show.definition.url
            .replace('{poli}', parsedArgs.poli.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::show
 * @see app/Http/Controllers/Admin/AdminPoliController.php:0
 * @route '/admin/polis/{poli}'
 */
show.get = (
    args: { poli: string | number } | [poli: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::show
 * @see app/Http/Controllers/Admin/AdminPoliController.php:0
 * @route '/admin/polis/{poli}'
 */
show.head = (
    args: { poli: string | number } | [poli: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::show
 * @see app/Http/Controllers/Admin/AdminPoliController.php:0
 * @route '/admin/polis/{poli}'
 */
const showForm = (
    args: { poli: string | number } | [poli: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::show
 * @see app/Http/Controllers/Admin/AdminPoliController.php:0
 * @route '/admin/polis/{poli}'
 */
showForm.get = (
    args: { poli: string | number } | [poli: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::show
 * @see app/Http/Controllers/Admin/AdminPoliController.php:0
 * @route '/admin/polis/{poli}'
 */
showForm.head = (
    args: { poli: string | number } | [poli: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

show.form = showForm;
/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::update
 * @see app/Http/Controllers/Admin/AdminPoliController.php:72
 * @route '/admin/polis/{poli}'
 */
export const update = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
});

update.definition = {
    methods: ['put', 'patch'],
    url: '/admin/polis/{poli}',
} satisfies RouteDefinition<['put', 'patch']>;

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::update
 * @see app/Http/Controllers/Admin/AdminPoliController.php:72
 * @route '/admin/polis/{poli}'
 */
update.url = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { poli: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'poli_id' in args) {
        args = { poli: args.poli_id };
    }

    if (Array.isArray(args)) {
        args = {
            poli: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        poli: typeof args.poli === 'object' ? args.poli.poli_id : args.poli,
    };

    return (
        update.definition.url
            .replace('{poli}', parsedArgs.poli.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::update
 * @see app/Http/Controllers/Admin/AdminPoliController.php:72
 * @route '/admin/polis/{poli}'
 */
update.put = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
});
/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::update
 * @see app/Http/Controllers/Admin/AdminPoliController.php:72
 * @route '/admin/polis/{poli}'
 */
update.patch = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::update
 * @see app/Http/Controllers/Admin/AdminPoliController.php:72
 * @route '/admin/polis/{poli}'
 */
const updateForm = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
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
 * @see \App\Http\Controllers\Admin\AdminPoliController::update
 * @see app/Http/Controllers/Admin/AdminPoliController.php:72
 * @route '/admin/polis/{poli}'
 */
updateForm.put = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
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
 * @see \App\Http\Controllers\Admin\AdminPoliController::update
 * @see app/Http/Controllers/Admin/AdminPoliController.php:72
 * @route '/admin/polis/{poli}'
 */
updateForm.patch = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

update.form = updateForm;
/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::destroy
 * @see app/Http/Controllers/Admin/AdminPoliController.php:90
 * @route '/admin/polis/{poli}'
 */
export const destroy = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
});

destroy.definition = {
    methods: ['delete'],
    url: '/admin/polis/{poli}',
} satisfies RouteDefinition<['delete']>;

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::destroy
 * @see app/Http/Controllers/Admin/AdminPoliController.php:90
 * @route '/admin/polis/{poli}'
 */
destroy.url = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { poli: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'poli_id' in args) {
        args = { poli: args.poli_id };
    }

    if (Array.isArray(args)) {
        args = {
            poli: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        poli: typeof args.poli === 'object' ? args.poli.poli_id : args.poli,
    };

    return (
        destroy.definition.url
            .replace('{poli}', parsedArgs.poli.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::destroy
 * @see app/Http/Controllers/Admin/AdminPoliController.php:90
 * @route '/admin/polis/{poli}'
 */
destroy.delete = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
});

/**
 * @see \App\Http\Controllers\Admin\AdminPoliController::destroy
 * @see app/Http/Controllers/Admin/AdminPoliController.php:90
 * @route '/admin/polis/{poli}'
 */
const destroyForm = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
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
 * @see \App\Http\Controllers\Admin\AdminPoliController::destroy
 * @see app/Http/Controllers/Admin/AdminPoliController.php:90
 * @route '/admin/polis/{poli}'
 */
destroyForm.delete = (
    args:
        | { poli: number | { poli_id: number } }
        | [poli: number | { poli_id: number }]
        | number
        | { poli_id: number },
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
const polis = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
};

export default polis;
