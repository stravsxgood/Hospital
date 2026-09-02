import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Api\PaymentController::handleWebhook
 * @see app/Http/Controllers/Api/PaymentController.php:101
 * @route '/api/xendit/webhook'
 */
export const handleWebhook = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: handleWebhook.url(options),
    method: 'post',
});

handleWebhook.definition = {
    methods: ['post'],
    url: '/api/xendit/webhook',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Api\PaymentController::handleWebhook
 * @see app/Http/Controllers/Api/PaymentController.php:101
 * @route '/api/xendit/webhook'
 */
handleWebhook.url = (options?: RouteQueryOptions) => {
    return handleWebhook.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Api\PaymentController::handleWebhook
 * @see app/Http/Controllers/Api/PaymentController.php:101
 * @route '/api/xendit/webhook'
 */
handleWebhook.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: handleWebhook.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Api\PaymentController::handleWebhook
 * @see app/Http/Controllers/Api/PaymentController.php:101
 * @route '/api/xendit/webhook'
 */
const handleWebhookForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: handleWebhook.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Api\PaymentController::handleWebhook
 * @see app/Http/Controllers/Api/PaymentController.php:101
 * @route '/api/xendit/webhook'
 */
handleWebhookForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: handleWebhook.url(options),
    method: 'post',
});

handleWebhook.form = handleWebhookForm;
/**
 * @see \App\Http\Controllers\Api\PaymentController::show
 * @see app/Http/Controllers/Api/PaymentController.php:25
 * @route '/api/payments/{id}'
 */
export const show = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});

show.definition = {
    methods: ['get', 'head'],
    url: '/api/payments/{id}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Api\PaymentController::show
 * @see app/Http/Controllers/Api/PaymentController.php:25
 * @route '/api/payments/{id}'
 */
show.url = (
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
        show.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Api\PaymentController::show
 * @see app/Http/Controllers/Api/PaymentController.php:25
 * @route '/api/payments/{id}'
 */
show.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Api\PaymentController::show
 * @see app/Http/Controllers/Api/PaymentController.php:25
 * @route '/api/payments/{id}'
 */
show.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Api\PaymentController::show
 * @see app/Http/Controllers/Api/PaymentController.php:25
 * @route '/api/payments/{id}'
 */
const showForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Api\PaymentController::show
 * @see app/Http/Controllers/Api/PaymentController.php:25
 * @route '/api/payments/{id}'
 */
showForm.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Api\PaymentController::show
 * @see app/Http/Controllers/Api/PaymentController.php:25
 * @route '/api/payments/{id}'
 */
showForm.head = (
    args: { id: string | number } | [id: string | number] | string | number,
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
 * @see \App\Http\Controllers\Api\PaymentController::payOnline
 * @see app/Http/Controllers/Api/PaymentController.php:43
 * @route '/api/payments/{id}/online'
 */
export const payOnline = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: payOnline.url(args, options),
    method: 'post',
});

payOnline.definition = {
    methods: ['post'],
    url: '/api/payments/{id}/online',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Api\PaymentController::payOnline
 * @see app/Http/Controllers/Api/PaymentController.php:43
 * @route '/api/payments/{id}/online'
 */
payOnline.url = (
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
        payOnline.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Api\PaymentController::payOnline
 * @see app/Http/Controllers/Api/PaymentController.php:43
 * @route '/api/payments/{id}/online'
 */
payOnline.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: payOnline.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Api\PaymentController::payOnline
 * @see app/Http/Controllers/Api/PaymentController.php:43
 * @route '/api/payments/{id}/online'
 */
const payOnlineForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payOnline.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Api\PaymentController::payOnline
 * @see app/Http/Controllers/Api/PaymentController.php:43
 * @route '/api/payments/{id}/online'
 */
payOnlineForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payOnline.url(args, options),
    method: 'post',
});

payOnline.form = payOnlineForm;
/**
 * @see \App\Http\Controllers\Api\PaymentController::payCash
 * @see app/Http/Controllers/Api/PaymentController.php:74
 * @route '/api/payments/{id}/cash'
 */
export const payCash = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: payCash.url(args, options),
    method: 'patch',
});

payCash.definition = {
    methods: ['patch'],
    url: '/api/payments/{id}/cash',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Api\PaymentController::payCash
 * @see app/Http/Controllers/Api/PaymentController.php:74
 * @route '/api/payments/{id}/cash'
 */
payCash.url = (
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
        payCash.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Api\PaymentController::payCash
 * @see app/Http/Controllers/Api/PaymentController.php:74
 * @route '/api/payments/{id}/cash'
 */
payCash.patch = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: payCash.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Api\PaymentController::payCash
 * @see app/Http/Controllers/Api/PaymentController.php:74
 * @route '/api/payments/{id}/cash'
 */
const payCashForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payCash.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Api\PaymentController::payCash
 * @see app/Http/Controllers/Api/PaymentController.php:74
 * @route '/api/payments/{id}/cash'
 */
payCashForm.patch = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: payCash.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

payCash.form = payCashForm;
const PaymentController = { handleWebhook, show, payOnline, payCash };

export default PaymentController;
