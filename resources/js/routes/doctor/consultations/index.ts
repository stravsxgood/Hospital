import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../../wayfinder';
/**
 * @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/doctor/consultations',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
const storeForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\DoctorConsultationController::store
 * @see app/Http/Controllers/DoctorConsultationController.php:32
 * @route '/doctor/consultations'
 */
storeForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

store.form = storeForm;
const consultations = {
    store: Object.assign(store, store),
};

export default consultations;
