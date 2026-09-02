import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\SatuSehatController::getFhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
export const getFhirBundle = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: getFhirBundle.url(args, options),
    method: 'get',
});

getFhirBundle.definition = {
    methods: ['get', 'head'],
    url: '/api/satusehat/records/{medical_record_id}/fhir-bundle',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\SatuSehatController::getFhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
getFhirBundle.url = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { medical_record_id: args };
    }

    if (Array.isArray(args)) {
        args = {
            medical_record_id: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        medical_record_id: args.medical_record_id,
    };

    return (
        getFhirBundle.definition.url
            .replace(
                '{medical_record_id}',
                parsedArgs.medical_record_id.toString(),
            )
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\SatuSehatController::getFhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
getFhirBundle.get = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: getFhirBundle.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\SatuSehatController::getFhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
getFhirBundle.head = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: getFhirBundle.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\SatuSehatController::getFhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
const getFhirBundleForm = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: getFhirBundle.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\SatuSehatController::getFhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
getFhirBundleForm.get = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: getFhirBundle.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\SatuSehatController::getFhirBundle
 * @see app/Http/Controllers/SatuSehatController.php:26
 * @route '/api/satusehat/records/{medical_record_id}/fhir-bundle'
 */
getFhirBundleForm.head = (
    args:
        | { medical_record_id: string | number }
        | [medical_record_id: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: getFhirBundle.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

getFhirBundle.form = getFhirBundleForm;
const SatuSehatController = { getFhirBundle };

export default SatuSehatController;
