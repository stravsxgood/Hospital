import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../wayfinder';
/**
 * @see \App\Http\Controllers\ClinicLocationController::location
 * @see app/Http/Controllers/ClinicLocationController.php:18
 * @route '/clinic-location'
 */
export const location = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: location.url(options),
    method: 'get',
});

location.definition = {
    methods: ['get', 'head'],
    url: '/clinic-location',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\ClinicLocationController::location
 * @see app/Http/Controllers/ClinicLocationController.php:18
 * @route '/clinic-location'
 */
location.url = (options?: RouteQueryOptions) => {
    return location.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\ClinicLocationController::location
 * @see app/Http/Controllers/ClinicLocationController.php:18
 * @route '/clinic-location'
 */
location.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: location.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\ClinicLocationController::location
 * @see app/Http/Controllers/ClinicLocationController.php:18
 * @route '/clinic-location'
 */
location.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: location.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\ClinicLocationController::location
 * @see app/Http/Controllers/ClinicLocationController.php:18
 * @route '/clinic-location'
 */
const locationForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: location.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\ClinicLocationController::location
 * @see app/Http/Controllers/ClinicLocationController.php:18
 * @route '/clinic-location'
 */
locationForm.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: location.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\ClinicLocationController::location
 * @see app/Http/Controllers/ClinicLocationController.php:18
 * @route '/clinic-location'
 */
locationForm.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: location.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

location.form = locationForm;
const clinic = {
    location: Object.assign(location, location),
};

export default clinic;
