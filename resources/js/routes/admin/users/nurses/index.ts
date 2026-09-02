import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/admin/users/nurses',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
const storeForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Admin\AdminUserController::store
 * @see app/Http/Controllers/Admin/AdminUserController.php:188
 * @route '/admin/users/nurses'
 */
storeForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

store.form = storeForm;
const nurses = {
    store: Object.assign(store, store),
};

export default nurses;
