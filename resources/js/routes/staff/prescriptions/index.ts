import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../wayfinder';
/**
 * @see \App\Http\Controllers\StaffActionController::process
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
export const process = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: process.url(args, options),
    method: 'post',
});

process.definition = {
    methods: ['post'],
    url: '/staff/prescriptions/{id}/process',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\StaffActionController::process
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
process.url = (
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
        process.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StaffActionController::process
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
process.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: process.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\StaffActionController::process
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
const processForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: process.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\StaffActionController::process
 * @see app/Http/Controllers/StaffActionController.php:104
 * @route '/staff/prescriptions/{id}/process'
 */
processForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: process.url(args, options),
    method: 'post',
});

process.form = processForm;
/**
 * @see \App\Http\Controllers\StaffActionController::complete
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
export const complete = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: complete.url(args, options),
    method: 'post',
});

complete.definition = {
    methods: ['post'],
    url: '/staff/prescriptions/{id}/complete',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\StaffActionController::complete
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
complete.url = (
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
        complete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StaffActionController::complete
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
complete.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: complete.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\StaffActionController::complete
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
const completeForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: complete.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\StaffActionController::complete
 * @see app/Http/Controllers/StaffActionController.php:143
 * @route '/staff/prescriptions/{id}/complete'
 */
completeForm.post = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: complete.url(args, options),
    method: 'post',
});

complete.form = completeForm;
const prescriptions = {
    process: Object.assign(process, process),
    complete: Object.assign(complete, complete),
};

export default prescriptions;
