import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::index
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:19
 * @route '/api/doctor/consultations'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/api/doctor/consultations',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::index
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:19
 * @route '/api/doctor/consultations'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::index
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:19
 * @route '/api/doctor/consultations'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::index
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:19
 * @route '/api/doctor/consultations'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::index
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:19
 * @route '/api/doctor/consultations'
 */
const indexForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::index
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:19
 * @route '/api/doctor/consultations'
 */
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::index
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:19
 * @route '/api/doctor/consultations'
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
 * @see \App\Http\Controllers\Api\DoctorConsultationController::updateStatus
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:56
 * @route '/api/doctor/consultations/{id}/status'
 */
export const updateStatus = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: updateStatus.url(args, options),
    method: 'patch',
});

updateStatus.definition = {
    methods: ['patch'],
    url: '/api/doctor/consultations/{id}/status',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::updateStatus
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:56
 * @route '/api/doctor/consultations/{id}/status'
 */
updateStatus.url = (
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
        updateStatus.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::updateStatus
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:56
 * @route '/api/doctor/consultations/{id}/status'
 */
updateStatus.patch = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: updateStatus.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::updateStatus
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:56
 * @route '/api/doctor/consultations/{id}/status'
 */
const updateStatusForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: updateStatus.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::updateStatus
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:56
 * @route '/api/doctor/consultations/{id}/status'
 */
updateStatusForm.patch = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: updateStatus.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

updateStatus.form = updateStatusForm;
/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::storeInspection
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:77
 * @route '/api/doctor/consultations/{id}/inspection'
 */
export const storeInspection = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeInspection.url(args, options),
    method: 'post',
});

storeInspection.definition = {
    methods: ['post'],
    url: '/api/doctor/consultations/{id}/inspection',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::storeInspection
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:77
 * @route '/api/doctor/consultations/{id}/inspection'
 */
storeInspection.url = (
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
        storeInspection.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::storeInspection
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:77
 * @route '/api/doctor/consultations/{id}/inspection'
 */
storeInspection.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeInspection.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::storeInspection
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:77
 * @route '/api/doctor/consultations/{id}/inspection'
 */
const storeInspectionForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: storeInspection.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Api\DoctorConsultationController::storeInspection
 * @see app/Http/Controllers/Api/DoctorConsultationController.php:77
 * @route '/api/doctor/consultations/{id}/inspection'
 */
storeInspectionForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: storeInspection.url(args, options),
    method: 'post',
});

storeInspection.form = storeInspectionForm;
const DoctorConsultationController = { index, updateStatus, storeInspection };

export default DoctorConsultationController;
