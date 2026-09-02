import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/display',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
const indexForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\PublicDisplayController::index
 * @see app/Http/Controllers/PublicDisplayController.php:19
 * @route '/display'
 */
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});
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
        },
    }),
    method: 'get',
});

index.form = indexForm;
/**
 * @see \App\Http\Controllers\PublicDisplayController::liveData
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
export const liveData = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: liveData.url(options),
    method: 'get',
});

liveData.definition = {
    methods: ['get', 'head'],
    url: '/display/live-data',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\PublicDisplayController::liveData
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
liveData.url = (options?: RouteQueryOptions) => {
    return liveData.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\PublicDisplayController::liveData
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
liveData.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: liveData.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\PublicDisplayController::liveData
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
liveData.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: liveData.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\PublicDisplayController::liveData
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
const liveDataForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: liveData.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\PublicDisplayController::liveData
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
liveDataForm.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: liveData.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\PublicDisplayController::liveData
 * @see app/Http/Controllers/PublicDisplayController.php:42
 * @route '/display/live-data'
 */
liveDataForm.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: liveData.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

liveData.form = liveDataForm;
const PublicDisplayController = { index, liveData };

export default PublicDisplayController;
