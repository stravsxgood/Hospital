import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../wayfinder';
/**
 * @see \App\Http\Controllers\DoctorSupervisionController::index
 * @see app/Http/Controllers/DoctorSupervisionController.php:27
 * @route '/doctor/supervision'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/doctor/supervision',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::index
 * @see app/Http/Controllers/DoctorSupervisionController.php:27
 * @route '/doctor/supervision'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::index
 * @see app/Http/Controllers/DoctorSupervisionController.php:27
 * @route '/doctor/supervision'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSupervisionController::index
 * @see app/Http/Controllers/DoctorSupervisionController.php:27
 * @route '/doctor/supervision'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::index
 * @see app/Http/Controllers/DoctorSupervisionController.php:27
 * @route '/doctor/supervision'
 */
const indexForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::index
 * @see app/Http/Controllers/DoctorSupervisionController.php:27
 * @route '/doctor/supervision'
 */
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSupervisionController::index
 * @see app/Http/Controllers/DoctorSupervisionController.php:27
 * @route '/doctor/supervision'
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
 * @see \App\Http\Controllers\DoctorSupervisionController::show
 * @see app/Http/Controllers/DoctorSupervisionController.php:103
 * @route '/doctor/supervision/{id}'
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
    url: '/doctor/supervision/{id}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::show
 * @see app/Http/Controllers/DoctorSupervisionController.php:103
 * @route '/doctor/supervision/{id}'
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
 * @see \App\Http\Controllers\DoctorSupervisionController::show
 * @see app/Http/Controllers/DoctorSupervisionController.php:103
 * @route '/doctor/supervision/{id}'
 */
show.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSupervisionController::show
 * @see app/Http/Controllers/DoctorSupervisionController.php:103
 * @route '/doctor/supervision/{id}'
 */
show.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::show
 * @see app/Http/Controllers/DoctorSupervisionController.php:103
 * @route '/doctor/supervision/{id}'
 */
const showForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::show
 * @see app/Http/Controllers/DoctorSupervisionController.php:103
 * @route '/doctor/supervision/{id}'
 */
showForm.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\DoctorSupervisionController::show
 * @see app/Http/Controllers/DoctorSupervisionController.php:103
 * @route '/doctor/supervision/{id}'
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
 * @see \App\Http\Controllers\DoctorSupervisionController::review
 * @see app/Http/Controllers/DoctorSupervisionController.php:129
 * @route '/doctor/supervision/{id}/review'
 */
export const review = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: review.url(args, options),
    method: 'post',
});

review.definition = {
    methods: ['post'],
    url: '/doctor/supervision/{id}/review',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::review
 * @see app/Http/Controllers/DoctorSupervisionController.php:129
 * @route '/doctor/supervision/{id}/review'
 */
review.url = (
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
        review.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::review
 * @see app/Http/Controllers/DoctorSupervisionController.php:129
 * @route '/doctor/supervision/{id}/review'
 */
review.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: review.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::review
 * @see app/Http/Controllers/DoctorSupervisionController.php:129
 * @route '/doctor/supervision/{id}/review'
 */
const reviewForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: review.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\DoctorSupervisionController::review
 * @see app/Http/Controllers/DoctorSupervisionController.php:129
 * @route '/doctor/supervision/{id}/review'
 */
reviewForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: review.url(args, options),
    method: 'post',
});

review.form = reviewForm;
const supervision = {
    index: Object.assign(index, index),
    show: Object.assign(show, show),
    review: Object.assign(review, review),
};

export default supervision;
