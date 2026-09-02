import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../wayfinder';
/**
 * @see \App\Http\Controllers\MedicalDocumentController::resume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
export const resume = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: resume.url(args, options),
    method: 'get',
});

resume.definition = {
    methods: ['get', 'head'],
    url: '/staff/print/medical-resume/{id}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MedicalDocumentController::resume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
resume.url = (
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
        resume.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MedicalDocumentController::resume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
resume.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: resume.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MedicalDocumentController::resume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
resume.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: resume.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MedicalDocumentController::resume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
const resumeForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: resume.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\MedicalDocumentController::resume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
resumeForm.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: resume.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MedicalDocumentController::resume
 * @see app/Http/Controllers/MedicalDocumentController.php:18
 * @route '/staff/print/medical-resume/{id}'
 */
resumeForm.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: resume.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

resume.form = resumeForm;
/**
 * @see \App\Http\Controllers\MedicalDocumentController::sickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
export const sickLetter = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: sickLetter.url(args, options),
    method: 'get',
});

sickLetter.definition = {
    methods: ['get', 'head'],
    url: '/staff/print/sick-letter/{id}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MedicalDocumentController::sickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
sickLetter.url = (
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
        sickLetter.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MedicalDocumentController::sickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
sickLetter.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: sickLetter.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MedicalDocumentController::sickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
sickLetter.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: sickLetter.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MedicalDocumentController::sickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
const sickLetterForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: sickLetter.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\MedicalDocumentController::sickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
sickLetterForm.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: sickLetter.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MedicalDocumentController::sickLetter
 * @see app/Http/Controllers/MedicalDocumentController.php:59
 * @route '/staff/print/sick-letter/{id}'
 */
sickLetterForm.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: sickLetter.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

sickLetter.form = sickLetterForm;
/**
 * @see \App\Http\Controllers\MedicalDocumentController::referral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
export const referral = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: referral.url(args, options),
    method: 'get',
});

referral.definition = {
    methods: ['get', 'head'],
    url: '/staff/print/referral-letter/{id}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MedicalDocumentController::referral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
referral.url = (
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
        referral.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MedicalDocumentController::referral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
referral.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: referral.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MedicalDocumentController::referral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
referral.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: referral.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MedicalDocumentController::referral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
const referralForm = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: referral.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\MedicalDocumentController::referral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
referralForm.get = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: referral.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MedicalDocumentController::referral
 * @see app/Http/Controllers/MedicalDocumentController.php:93
 * @route '/staff/print/referral-letter/{id}'
 */
referralForm.head = (
    args: { id: string | number } | [id: string | number] | string | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: referral.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

referral.form = referralForm;
const print = {
    resume: Object.assign(resume, resume),
    sickLetter: Object.assign(sickLetter, sickLetter),
    referral: Object.assign(referral, referral),
};

export default print;
