import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
const index329fd943836cf306ed5281162dce3109 = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: index329fd943836cf306ed5281162dce3109.url(options),
    method: 'get',
});

index329fd943836cf306ed5281162dce3109.definition = {
    methods: ['get', 'head'],
    url: '/staff',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
index329fd943836cf306ed5281162dce3109.url = (options?: RouteQueryOptions) => {
    return (
        index329fd943836cf306ed5281162dce3109.definition.url +
        queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
index329fd943836cf306ed5281162dce3109.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: index329fd943836cf306ed5281162dce3109.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
index329fd943836cf306ed5281162dce3109.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: index329fd943836cf306ed5281162dce3109.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
const index329fd943836cf306ed5281162dce3109Form = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index329fd943836cf306ed5281162dce3109.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
index329fd943836cf306ed5281162dce3109Form.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index329fd943836cf306ed5281162dce3109.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff'
 */
index329fd943836cf306ed5281162dce3109Form.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index329fd943836cf306ed5281162dce3109.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

index329fd943836cf306ed5281162dce3109.form =
    index329fd943836cf306ed5281162dce3109Form;
/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
const index3013a377ced7597dcdb8281acb1d2eac = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: index3013a377ced7597dcdb8281acb1d2eac.url(options),
    method: 'get',
});

index3013a377ced7597dcdb8281acb1d2eac.definition = {
    methods: ['get', 'head'],
    url: '/staff/dashboard',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
index3013a377ced7597dcdb8281acb1d2eac.url = (options?: RouteQueryOptions) => {
    return (
        index3013a377ced7597dcdb8281acb1d2eac.definition.url +
        queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
index3013a377ced7597dcdb8281acb1d2eac.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: index3013a377ced7597dcdb8281acb1d2eac.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
index3013a377ced7597dcdb8281acb1d2eac.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: index3013a377ced7597dcdb8281acb1d2eac.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
const index3013a377ced7597dcdb8281acb1d2eacForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index3013a377ced7597dcdb8281acb1d2eac.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
index3013a377ced7597dcdb8281acb1d2eacForm.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index3013a377ced7597dcdb8281acb1d2eac.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StaffDashboardController::index
 * @see app/Http/Controllers/StaffDashboardController.php:28
 * @route '/staff/dashboard'
 */
index3013a377ced7597dcdb8281acb1d2eacForm.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index3013a377ced7597dcdb8281acb1d2eac.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

index3013a377ced7597dcdb8281acb1d2eac.form =
    index3013a377ced7597dcdb8281acb1d2eacForm;

/**
 * Multiple routes resolve to \App\Http\Controllers\StaffDashboardController::index, so this export is a
 * dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
 * or import the route by name from your generated `routes/` directory.
 */
export const index = {
    '/staff': index329fd943836cf306ed5281162dce3109,
    '/staff/dashboard': index3013a377ced7597dcdb8281acb1d2eac,
};

const StaffDashboardController = { index };

export default StaffDashboardController;
