import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\XenditWebhookController::handle
 * @see app/Http/Controllers/XenditWebhookController.php:18
 * @route '/api/webhooks/xendit'
 */
export const handle = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: handle.url(options),
    method: 'post',
});

handle.definition = {
    methods: ['post'],
    url: '/api/webhooks/xendit',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\XenditWebhookController::handle
 * @see app/Http/Controllers/XenditWebhookController.php:18
 * @route '/api/webhooks/xendit'
 */
handle.url = (options?: RouteQueryOptions) => {
    return handle.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\XenditWebhookController::handle
 * @see app/Http/Controllers/XenditWebhookController.php:18
 * @route '/api/webhooks/xendit'
 */
handle.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handle.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\XenditWebhookController::handle
 * @see app/Http/Controllers/XenditWebhookController.php:18
 * @route '/api/webhooks/xendit'
 */
const handleForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: handle.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\XenditWebhookController::handle
 * @see app/Http/Controllers/XenditWebhookController.php:18
 * @route '/api/webhooks/xendit'
 */
handleForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: handle.url(options),
    method: 'post',
});

handle.form = handleForm;
/**
 * @see \App\Http\Controllers\XenditWebhookController::handleQrCallback
 * @see app/Http/Controllers/XenditWebhookController.php:138
 * @route '/api/webhooks/xendit/qr'
 */
export const handleQrCallback = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: handleQrCallback.url(options),
    method: 'post',
});

handleQrCallback.definition = {
    methods: ['post'],
    url: '/api/webhooks/xendit/qr',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\XenditWebhookController::handleQrCallback
 * @see app/Http/Controllers/XenditWebhookController.php:138
 * @route '/api/webhooks/xendit/qr'
 */
handleQrCallback.url = (options?: RouteQueryOptions) => {
    return handleQrCallback.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\XenditWebhookController::handleQrCallback
 * @see app/Http/Controllers/XenditWebhookController.php:138
 * @route '/api/webhooks/xendit/qr'
 */
handleQrCallback.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: handleQrCallback.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\XenditWebhookController::handleQrCallback
 * @see app/Http/Controllers/XenditWebhookController.php:138
 * @route '/api/webhooks/xendit/qr'
 */
const handleQrCallbackForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: handleQrCallback.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\XenditWebhookController::handleQrCallback
 * @see app/Http/Controllers/XenditWebhookController.php:138
 * @route '/api/webhooks/xendit/qr'
 */
handleQrCallbackForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: handleQrCallback.url(options),
    method: 'post',
});

handleQrCallback.form = handleQrCallbackForm;
const XenditWebhookController = { handle, handleQrCallback };

export default XenditWebhookController;
