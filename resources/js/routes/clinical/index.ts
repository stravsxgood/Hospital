import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
} from './../../wayfinder';
import soapTemplates from './soap-templates';
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::icd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
export const icd10 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: icd10.url(options),
    method: 'get',
});

icd10.definition = {
    methods: ['get', 'head'],
    url: '/api/clinical/icd10',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::icd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
icd10.url = (options?: RouteQueryOptions) => {
    return icd10.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::icd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
icd10.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: icd10.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::icd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
icd10.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: icd10.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::icd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
const icd10Form = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: icd10.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::icd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
icd10Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: icd10.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::icd10
 * @see app/Http/Controllers/ClinicalAssistantController.php:32
 * @route '/api/clinical/icd10'
 */
icd10Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: icd10.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

icd10.form = icd10Form;
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::safetyCheck
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
export const safetyCheck = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: safetyCheck.url(options),
    method: 'post',
});

safetyCheck.definition = {
    methods: ['post'],
    url: '/api/clinical/safety-check',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::safetyCheck
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
safetyCheck.url = (options?: RouteQueryOptions) => {
    return safetyCheck.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::safetyCheck
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
safetyCheck.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: safetyCheck.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::safetyCheck
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
const safetyCheckForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: safetyCheck.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::safetyCheck
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/safety-check'
 */
safetyCheckForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: safetyCheck.url(options),
    method: 'post',
});

safetyCheck.form = safetyCheckForm;
/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
export const checkSafety = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: checkSafety.url(options),
    method: 'post',
});

checkSafety.definition = {
    methods: ['post'],
    url: '/api/clinical/check-safety',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
checkSafety.url = (options?: RouteQueryOptions) => {
    return checkSafety.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
checkSafety.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkSafety.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
const checkSafetyForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: checkSafety.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\ClinicalAssistantController::checkSafety
 * @see app/Http/Controllers/ClinicalAssistantController.php:99
 * @route '/api/clinical/check-safety'
 */
checkSafetyForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: checkSafety.url(options),
    method: 'post',
});

checkSafety.form = checkSafetyForm;
const clinical = {
    icd10: Object.assign(icd10, icd10),
    soapTemplates: Object.assign(soapTemplates, soapTemplates),
    safetyCheck: Object.assign(safetyCheck, safetyCheck),
    checkSafety: Object.assign(checkSafety, checkSafety),
};

export default clinical;
