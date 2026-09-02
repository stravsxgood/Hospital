import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/admin/users/doctors',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
const storeForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:123
 * @route '/admin/users/doctors'
 */
storeForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

store.form = storeForm;
const doctors = {
    store: Object.assign(store, store),
};

export default doctors;
