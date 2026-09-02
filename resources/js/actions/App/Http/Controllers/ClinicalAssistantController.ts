import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::searchIcd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
export const searchIcd10 = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: searchIcd10.url(options),
    method: 'get',
});

searchIcd10.definition = {
    methods: ['get', 'head'],
    url: '/api/clinical/icd10',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::searchIcd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
searchIcd10.url = (options?: RouteQueryOptions) => {
    return searchIcd10.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::searchIcd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
searchIcd10.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchIcd10.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::searchIcd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
searchIcd10.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: searchIcd10.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::searchIcd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
const searchIcd10Form = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: searchIcd10.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::searchIcd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
searchIcd10Form.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: searchIcd10.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::searchIcd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
searchIcd10Form.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: searchIcd10.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

searchIcd10.form = searchIcd10Form;
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::getSoapTemplates
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
export const getSoapTemplates = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: getSoapTemplates.url(options),
    method: 'get',
});

getSoapTemplates.definition = {
    methods: ['get', 'head'],
    url: '/api/clinical/soap-templates',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::getSoapTemplates
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
getSoapTemplates.url = (options?: RouteQueryOptions) => {
    return getSoapTemplates.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::getSoapTemplates
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
getSoapTemplates.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: getSoapTemplates.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::getSoapTemplates
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
getSoapTemplates.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: getSoapTemplates.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::getSoapTemplates
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
const getSoapTemplatesForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: getSoapTemplates.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::getSoapTemplates
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
getSoapTemplatesForm.get = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: getSoapTemplates.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::getSoapTemplates
 * @see app/Http/Controllers/ClinicalAssistantController.php:58
 * @route '/api/clinical/soap-templates'
 */
getSoapTemplatesForm.head = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: getSoapTemplates.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

getSoapTemplates.form = getSoapTemplatesForm;
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::storeSoapTemplate
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
export const storeSoapTemplate = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeSoapTemplate.url(options),
    method: 'post',
});

storeSoapTemplate.definition = {
    methods: ['post'],
    url: '/api/clinical/soap-templates',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::storeSoapTemplate
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
storeSoapTemplate.url = (options?: RouteQueryOptions) => {
    return storeSoapTemplate.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::storeSoapTemplate
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
storeSoapTemplate.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeSoapTemplate.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::storeSoapTemplate
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
const storeSoapTemplateForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: storeSoapTemplate.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::storeSoapTemplate
 * @see app/Http/Controllers/ClinicalAssistantController.php:79
 * @route '/api/clinical/soap-templates'
 */
storeSoapTemplateForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: storeSoapTemplate.url(options),
    method: 'post',
});

storeSoapTemplate.form = storeSoapTemplateForm;
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
const checkSafetycc9ca54e5752b4b0692b8e31978ffa2b = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: checkSafetycc9ca54e5752b4b0692b8e31978ffa2b.url(options),
    method: 'post',
});

checkSafetycc9ca54e5752b4b0692b8e31978ffa2b.definition = {
    methods: ['post'],
    url: '/api/clinical/safety-check',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
checkSafetycc9ca54e5752b4b0692b8e31978ffa2b.url = (
    options?: RouteQueryOptions,
) => {
    return (
        checkSafetycc9ca54e5752b4b0692b8e31978ffa2b.definition.url +
        queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
checkSafetycc9ca54e5752b4b0692b8e31978ffa2b.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: checkSafetycc9ca54e5752b4b0692b8e31978ffa2b.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
const checkSafetycc9ca54e5752b4b0692b8e31978ffa2bForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: checkSafetycc9ca54e5752b4b0692b8e31978ffa2b.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
checkSafetycc9ca54e5752b4b0692b8e31978ffa2bForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: checkSafetycc9ca54e5752b4b0692b8e31978ffa2b.url(options),
    method: 'post',
});

checkSafetycc9ca54e5752b4b0692b8e31978ffa2b.form =
    checkSafetycc9ca54e5752b4b0692b8e31978ffa2bForm;
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
const checkSafety2b3eb623d19f80b471fb9e3607650d57 = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: checkSafety2b3eb623d19f80b471fb9e3607650d57.url(options),
    method: 'post',
});

checkSafety2b3eb623d19f80b471fb9e3607650d57.definition = {
    methods: ['post'],
    url: '/api/clinical/check-safety',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
checkSafety2b3eb623d19f80b471fb9e3607650d57.url = (
    options?: RouteQueryOptions,
) => {
    return (
        checkSafety2b3eb623d19f80b471fb9e3607650d57.definition.url +
        queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
checkSafety2b3eb623d19f80b471fb9e3607650d57.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: checkSafety2b3eb623d19f80b471fb9e3607650d57.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
const checkSafety2b3eb623d19f80b471fb9e3607650d57Form = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: checkSafety2b3eb623d19f80b471fb9e3607650d57.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
checkSafety2b3eb623d19f80b471fb9e3607650d57Form.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: checkSafety2b3eb623d19f80b471fb9e3607650d57.url(options),
    method: 'post',
});

checkSafety2b3eb623d19f80b471fb9e3607650d57.form =
    checkSafety2b3eb623d19f80b471fb9e3607650d57Form;

/**
 * Multiple routes resolve to \App\Http\Controllers\ClinicalAssistantController::checkSafety, so this export is a
 * dictionary keyed by URI rather than a callable. Call a specific route with `checkSafety['<uri>'](...)`,
 * or import the route by name from your generated `routes/` directory.
 */
export const checkSafety = {
    '/api/clinical/safety-check': checkSafetycc9ca54e5752b4b0692b8e31978ffa2b,
    '/api/clinical/check-safety': checkSafety2b3eb623d19f80b471fb9e3607650d57,
};

const ClinicalAssistantController = {
    searchIcd10,
    getSoapTemplates,
    storeSoapTemplate,
    checkSafety,
};

export default ClinicalAssistantController;
