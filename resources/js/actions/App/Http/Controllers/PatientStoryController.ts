import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\PatientStoryController::index
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/patient-story',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\PatientStoryController::index
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\PatientStoryController::index
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\PatientStoryController::index
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\PatientStoryController::index
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
const indexForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\PatientStoryController::index
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
 */
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\PatientStoryController::index
 * @see app/Http/Controllers/PatientStoryController.php:19
 * @route '/patient-story'
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
const PatientStoryController = { index };

export default PatientStoryController;
